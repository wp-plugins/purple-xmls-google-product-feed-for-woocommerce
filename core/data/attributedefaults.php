<?php

	/********************************************************************
	Version 2.0
		Attribute defaults
	Can by value or expression
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

	********************************************************************/

//The base class occurs before item loads from database
//These values will be overwritten by db-values if found
//Only Title and ID exist at this moment
class PAttributeDefault {

	public $attributeName;
	public $enabled = true;
	public $isRuled = false;
	public $parent_feed = null; //points to feed provider that owns this mapping
	public $stage = 0;
	public $value;

	function __destruct() {
		unset($this->parent_feed);
	}

	public function initialize() {
	}

	public function getValue($item) {
		return $this->value;
	}

}

//After Defaults but before load
//Most attributes exist. Calc fields do not.
//All bare defaults have been set
//Variations do not exist yet
class PActionAfterHarmonize extends PAttributeDefault {

	public function __construct() {
		$this->stage = 5;
	}

}

//Occurs after item loads from database
//Most attributes exist but calc fields like price do not
class PActionAfterLoad extends PAttributeDefault {

	public function __construct() {
		$this->stage = 1;
	}

}

//The feed is about to be generated. 
//all calc fields exist
class PActionBeforeFeed extends PAttributeDefault {

	public function __construct() {
		$this->stage = 2;
	}

}

//The feed has been generated
//Descendent may modify the feed output
class PActionAfterFeed extends PAttributeDefault {

	public function __construct() {
		$this->stage = 3;
	}

	public function postProcess($product, &$output) {
	}

}

//********************************************************************
//Built-in Feed Modifiers
//********************************************************************

//Look up category and copy it to an attribute. Designed for WP-ECommerce. Needs testing in woo
class PCategoryLookUp extends PAttributeDefault {

	public function getValue($item) {

		global $pfcore;
		if ($pfcore->callSuffix[0] == 'W') {
			global $wpdb;
			$id = $item->attributes['id'];
			$sql = "
				SELECT postsAsTaxo.ID, category_terms.name as category_name, category_terms.term_id as category_id
				FROM $wpdb->posts postsAsTaxo
				LEFT JOIN $wpdb->term_relationships category_relationships ON (postsAsTaxo.ID = category_relationships.object_id)
				LEFT JOIN $wpdb->term_taxonomy category_taxonomy ON (category_relationships.term_taxonomy_id = category_taxonomy.term_taxonomy_id)
				LEFT JOIN $wpdb->terms category_terms ON (category_taxonomy.term_id = category_terms.term_id)
				#LEFT JOIN $wpdb->term_taxonomy parent_taxonomy on (category_taxonomy.parent = parent_taxonomy.term_taxonomy_id) #Woo?
				#LEFT JOIN $wpdb->terms parent_taxonomy_name on (parent_taxonomy.term_id = parent_taxonomy_name.term_id) #Woo?
				LEFT JOIN $wpdb->terms parent_taxonomy_name on (category_taxonomy.parent = parent_taxonomy_name.term_id)
				WHERE (category_taxonomy.taxonomy = 'wpsc_product_category') AND (postsAsTaxo.ID = $id) AND (parent_taxonomy_name.name = '$this->attributeName')
			";
			$categories = $wpdb->get_results($sql);

			if (count($categories) > 0)
				return $categories[0]->category_name;
			else
				return '';

		}

	}

}

//Category Tree: display the full category for an item.

class PCategoryTree extends PActionBeforeFeed {

	public function getValue($item) {

		global $pfcore;
		if ($pfcore->callSuffix != 'W') {
			return $item->attributes['localCategory'];
		}
			

		$category = $this->parent_feed->categories->idToCategory($item->attributes['category_id']);
		$output = '';
		while ($category != null) {
			if (strlen($output) == 0)
				$output = $category->title;
			else
				$output = $category->title . ' > ' . $output;
			if (isset($category->parent_category))
				$category = $category->parent_category;
			else
				break;
		}
		//Case: exporting a child category when products have been categorized into parent AND child categories (will return parent category)
		//otherwise modifier may return blank
		if (strlen($output) == 0)
			$output = $item->attributes['localCategory']; 
		return $output;
	}

}

//ex: setAttributeDefault xVar as 17 PCatVar -- removes variations for category ID: 17
class PCatVar extends PActionAfterHarmonize {

	public function getValue($item) {

		//Any product containing value in its list of category_ids is not variable
		$catIDs = $item->attributes['category_ids'];
		if ($this->attributeName == 'allcat') {
			foreach ( $catIDs as $allCatIds ) {
				$item->attributes['isVariable'] = false;
			}
		}
		else {
			if ( in_array($this->value, $catIDs) )
				$item->attributes['isVariable'] = false;
		}
	}
}


class PConvertSpecialCharacters extends PActionAfterFeed {

	public function postProcess($product, &$output) {
		//For WordPress only
		$output = ent2ncr($output);
	}

}

class PFirstFoundColor extends PAttributeDefault {

	public $colorwords = array(
		'amber', 'amethyst', 'aqua', 'aquamarine', 'auburn', 'azure', 'beige', 'black', 'blue', 'bronze', 
		'brown', 'cerise', 'cerulean', 'charcoal', 'copper', 'coral', 'cream', 'crimson', 'crystal', 'cyan',
		'diamond', 'denim', 'ebony', 'ecru', 'emerald', 'fuchsia', 'gold', 'gray', 'green', 'grey', 'indigo', 
		'ivory', 'jade', 'jet', 'lavender', 'lemon', 'lilac', 'lime', 'magenta', 'mahogany', 'maroon', 'mauve', 
		'ocher', 'olive', 'orange', 'orchid', 'pastel', 'peach', 'peridot', 'periwinkle', 'persimmon', 'pearl', 
		'pewter', 'pink', 'purple', 'red', 'rhodium', 'rose', 'ruby', 'saffron', 'sapphire', 'scarlet', 'silver', 
		'tan', 'taupe', 'teal', 'topaz', 'turquoise', 'ultramarine', 'vermilion', 'violet', 'white', 'yellow');
 

	function firstColorWord($text) {
		$options = explode(' ', $text);
		foreach ($options as $option) {
			$searchterm = preg_replace("/[^a-zA-Z 0-9]+/", "", $option);
			if (in_array(strtolower($searchterm), $this->colorwords))
				return $searchterm;
		}
		return '';
	}
		/* Inaccurate:
		$textl = strtolower($text);
		foreach ($this->colorwords as $color)
			if (strpos($textl, $color) !== false)
				return $color;*/

	public function getValue($item) {

		$color = $this->firstColorWord($item->description_short);
		if (strlen($color) > 0) return $color;

		$color = $this->firstColorWord($item->description_long);
		if (strlen($color) > 0) return $color;

		$color = $this->firstColorWord($item->attributes['title']);
		if (strlen($color) > 0) return $color;

		return '';
	}

}

//GraziaShop Business rule
//setAttributeDefualt business-attribute as none PGraziaBusinessRule
class PGraziaBusinessRule extends PActionBeforeFeed {

	public function getValue($item){

		if ( strlen($item->attributes[$this->attributeName]) > 0 ) {
			if ( strtolower( $item->attributes[str_replace('"','',$this->attributeName)] ) == 'no' ) 
				$item->attributes['stock_quantity'] = 0;
		}
		return '';
	}

}

class PGoogleAdditionalImages extends PActionAfterFeed {

	public function postProcess($product, &$output) {

		if ($this->parent_feed->allow_additional_images) {
			$image_count = 0;
			foreach($product->imgurls as $imgurl) {
				$output .= $this->parent_feed->formatLine('g:additional_image_link', $imgurl, true);
				$image_count++;
				if ($image_count > 9)
					break;
			}
		}

	}

}

class PGoogleShipping extends PActionAfterFeed {

	public function postProcess($product, &$output) {

		$output.= '
        <g:shipping>' .
			$this->parent_feed->formatLine('g:service', $product->attributes['shipping_type'], false, '  ') .
			$this->parent_feed->formatLine('g:price', sprintf($this->parent_feed->currency_format, $product->attributes['shipping_amount']), false, '  ') . '
        </g:shipping>';

	}

}

class PGoogleTax extends PActionAfterFeed {

	public function postProcess($product, &$output) {

		if ( isset($product->attributes['tax']) ) {
			$output .= '
        <g:tax>' .
			$this->parent_feed->formatLine('g:country', $product->attributes['tax_country'], false, '  ') .
			$this->parent_feed->formatLine('g:rate', sprintf($this->parent_feed->currency_format, $product->attributes['tax']), false, '  ') . '
        </g:tax>';	
		}

	}

}

/** PMergeFields
* Merges attributeName with value
* example: setAttributeDefault screen-size as size PMergeFields
* woocommerce attributes takes precedence & depends on order of commands
*/
class PMergeFields extends PActionBeforeFeed {

	public function getValue($item) {

		if ( strlen($item->attributes[$this->attributeName]) > 0 )
			if ( strlen($item->attributes[$this->value]) == 0 ) //if this attribute has value, don't override
				$item->attributes[$this->value] = $item->attributes[$this->attributeName];

		return '';
	}
}

class PSalePriceIfDefined extends PActionBeforeFeed {

	public function getValue($item) 
	{
		if ($item->attributes['has_sale_price']) 
		{
			if ( $item->attributes['sale_price'] > 0 )
				return $item->attributes['sale_price'];
			else 
				return $item->attributes['regular_price'];
		}
		else
			return $item->attributes['regular_price'];
	}

}

//create attribute for variations based on IDs
/*
customfield name: feedUPC
customfield value: I6GLD16=12345678&I6W16=12345678&I616GBSG=1234567
*/
class PVaryOnId extends PActionBeforeFeed {

	public function getValue($item) {
		if (!isset($item->attributes[$this->attributeName]))
			return trim($item->attributes['upc']);
		$data = $item->attributes[$this->attributeName];
		if (strlen($data) == 0)
			return '';
		$list = array_map('trim',explode('&', $data));
		foreach ($list as $listpair) {
			$subitems = explode('=', $listpair);
			if (count($subitems) > 1 && $subitems[0] == $item->attributes['id'])
				return $subitems[1];
		}
		return '';
	}
}

//create attribute for variations based on SKUs
class PVaryOnSku extends PActionBeforeFeed {

	public function getValue($item) {
		if (!isset($item->attributes[$this->attributeName]))
			return '';
		$data = $item->attributes[$this->attributeName];
		if (strlen($data) == 0)
			return '';
		$list = array_map('trim',explode('&', $data));
		foreach ($list as $listpair) {
			$subitems = explode('=', $listpair);
			if (count($subitems) > 1 && $subitems[0] == $item->attributes['sku'])
				return $subitems[1];
		}
		return '';
	}
}

//remove zero priced items from feed
class PRemoveZeroPricedItems extends PActionBeforeFeed {

	public function getValue($item) {
		if ($item->attributes['regular_price'] == 0)
  			$item->attributes['valid'] = false;		
		return true;
	}
}

?>