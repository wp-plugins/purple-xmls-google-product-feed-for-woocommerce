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

class PCategoryTree extends PActionBeforeFeed {

	public function getValue($item) {
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
		return $output;
	}

}

class PCatVar extends PActionAfterHarmonize {

	public function getValue($item) {

		//Any product containing value in its list of category_ids is not variable
		$catIDs = $item->attributes['category_ids'];
		if (in_array($this->value, $catIDs))
			$item->attributes['isVariable'] = false;
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

class PSalePriceIfDefined extends PActionBeforeFeed {

	public function getValue($item) {
		if ($item->attributes['has_sale_price'])
			return $item->attributes['sale_price'];
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