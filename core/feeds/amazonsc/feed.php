<?php

	/********************************************************************
	Version 2.0
		An Amazon Feed (Product Ads)
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAmazonSCFeed extends PCSVFeed 
{
	public $headerTemplateType; //Attached to the top of the feed
	public $headerTemplateVersion;

	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'AmazonSC';
		$this->providerNameL = 'amazonsc';
		$this->fileformat = 'csv';
		$this->fields = array();
		$this->fieldDelimiter = "\t";

		$this->external_product_id_type = '';
		$this->descriptionStrict = true;
		$this->stripHTML = true;		
	}

	function formatProduct($product) {

		//********************************************************************
		//Prepare the product
		//********************************************************************
		$product->attributes['external_product_id_type'] = $this->external_product_id_type;
		$product->attributes['description'] = str_replace('"', '', $product->description);
		$product->attributes['category'] = $this->current_category;
		if (!isset($product->attributes['manufacturer']))
			$product->attributes['manufacturer'] = '';
		if (!isset($product->attributes['feed_product_type']))
			$product->attributes['feed_product_type'] = '';
		if ((strlen($this->feed_product_type) > 0) && (strlen($product->attributes['feed_product_type']) > 0))
			$product->attributes['feed_product_type'] = $this->feed_product_type;
		if (!isset($product->attributes['external_product_id']))
			$product->attributes['external_product_id'] = '';
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;
		$image_count = 0;
		foreach($product->imgurls as $imgurl) {
			$image_index = "other_image_url_$image_count";
			$product->attributes[$image_index] = $imgurl;
			$image_count++;
			if ($image_count >= 9)
				break;
		}
		$product->attributes['price'] = $product->attributes['regular_price'];
		//if (($product->attributes['has_sale_price']) && ($product->attributes['sale_price'] != ""))
			//$product->attributes['sale_price'] = $product->attributes['sale_price']
		if (!$product->attributes['has_sale_price'])
			$product->attributes['sale_price'] = '';
		if (!isset($product->attributes['currency']) || (strlen($product->attributes['currency']) == 0))
			$product->attributes['currency'] = $this->currency;
		if (!isset($product->attributes['item_package_quantity']))
			$product->attributes['item_package_quantity'] = 1;
		$product->attributes['shipping_cost'] = '0.00';
		$product->attributes['shipping_weight'] = $product->attributes['weight'];
		$product->attributes['item_weight_unit_of_measure'] = $this->weight_unit;
		//if ($product->isVariable)
			//$product->attributes['parent_child'] = 'Variation'; //Trying without variations for now

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************
		if (strlen($product->attributes['description']) > 500) {
			$product->attributes['description'] = substr($product->attributes['description'], 0, 500);
			$this->addErrorMessage(8000, 'Description truncated for ' . $product->attributes['title'], true);
		}
		/*if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			$this->addErrorMessage(8001, 'Brand not set for ' . $product->attributes['title'], true);
		if (($this->external_product_id_type == 'UPC') && (strlen($product->attributes['external_product_id']) == 0))
			$this->addErrorMessage(8002, 'external_product_id not set for ' . $product->attributes['title'], true);
		if (($this->template == 'health') && (strlen($product->attributes['manufacturer']) == 0))
			$this->addErrorMessage(8003, 'Manufacturer not set for ' . $product->attributes['title'], true);*/
		//8004 seems a bit too aggressive
		//if ($product->attributes['has_sale_price'] && (!isset($product->attributes['sale_from_date']) || !isset($product->attributes['sale_end_date'])))
			//$this->addErrorMessage(8004, 'Sale price set for ' . $product->attributes['title'] . ' but no sale_from_date and/or sale_end_date provided', true);

		//********************************************************************
		//Trigger Mapping 3.0 Before-Feed Event
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);

		//********************************************************************
		//Build output in order of fields
		//********************************************************************
		$output = '';
		foreach($this->attributeMappings as $thisAttributeMapping) {
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) ) {
				if ($thisAttributeMapping->usesCData)
					$quotes = '"';
				else
					$quotes = '';
				$output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;
			}
			$output .= $this->fieldDelimiter;
		}

		//********************************************************************
		//Trigger Mapping 3.0 After-Feed Event
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 3)
				$thisDefault->postProcess($product, $output);

		//Trim trailing comma
		return substr($output, 0, -1) . "\r\n";
		
	}

	function getFeedHeader($file_name, $file_path) {

		//Amazon header line 1
		$output = implode(
			$this->fieldDelimiter, 
			array('TemplateType=' . $this->headerTemplateType,  'Version=' . $this->headerTemplateVersion, 'The top 3 rows are for Amazon.com use only. Do not modify or delete the top 3 rows.')
		) .  "\r\n";

		//Amazon header line 2
		$localizedNames = array();
		foreach($this->attributeMappings as $thisMapping)
			if (isset($thisMapping->localized_name))
				$localizedNames[] = $thisMapping->localized_name;
			else
				$localizedNames[] = '';
		$output .= implode($this->fieldDelimiter, $localizedNames) .  "\r\n";

		//Amazon header line 3
		foreach($this->attributeMappings as $thisMapping)
			if ($thisMapping->enabled && !$thisMapping->deleted)
				$output .= $thisMapping->mapTo . $this->fieldDelimiter;

		return substr($output, 0, -1) .  "\r\n";
	}

	function initializeFeed($category, $remote_category) {

		$this->template = strtolower($remote_category);
		switch ($this->template) {
			case 'health':
				$this->external_product_id_type = 'UPC';
				$this->feed_product_type = 'HealthMisc';
				$this->headerTemplateType = 'Health';
				$this->headerTemplateVersion = '2014.0115';
				break;
			case 'clothing':
				$this->external_product_id_type = 'UPC';
				$this->feed_product_type = '';
				$this->headerTemplateType = 'Clothing';
				$this->headerTemplateVersion = '2014.0409';
				break;
			case 'office products';
				$this->external_product_id_type = 'UPC';
				$this->feed_product_type = '';
				$this->headerTemplateType = 'Office';
				$this->headerTemplateVersion = '2014.0611';
				break;
			case 'home.and.garden';
				$this->external_product_id_type = 'UPC';
				$this->feed_product_type = '';
				$this->headerTemplateType = 'Home';
				$this->headerTemplateVersion = '2014.0808';
				break;
			case 'jewelry';
				$this->external_product_id_type = 'UPC';
				$this->feed_product_type = '';
				$this->headerTemplateType = 'Jewelry';
				$this->headerTemplateVersion = '2014.0318';
				break;
				
			default:
				$this->external_product_id_type = '';
				$this->headerTemplateType = $remote_category;
				$this->feed_product_type = '';
				$this->headerTemplateVersion = '2014.0409';
		}

		/** Template: Basics - these are attributes that are important to buyers. Some are required to create an offer **/
		$this->addAttributeMapping('id', 'item_sku')->localized_name = 'SKU'; 

		//local name differs for jewelry
		if ( $this->template == 'jewelry' ) $this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Title'; //jewlry local label name differs..
		else $this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Product Name'; //..from all others
				
		$this->addAttributeMapping('external_product_id', 'external_product_id')->localized_name = 'Product ID';
		$this->addAttributeMapping('external_product_id_type', 'external_product_id_type')->localized_name = 'Product ID Type';
		$this->addAttributeMapping('brand', 'brand_name', true)->localized_name = 'Brand Name';
		$this->addAttributeMapping('description', 'product_description', true)->localized_name = 'Product Description'; //preferred
		$this->addAttributeMapping('update_delete', 'update_delete')->localized_name = 'Update Delete'; //preferred or optional

		//item_type differs for office
		if ( $this->template == 'office products' ) $this->addAttributeMapping('product_type', 'item_type')->localized_name = 'Category (item-type)'; //office local label name differs..
		else $this->addAttributeMapping('product_type', 'item_type')->localized_name = 'Item Type Keyword'; //..from all others
		
		if ( $this->template == 'clothing' )
			$this->addAttributeMapping('model', 'model')->localized_name = 'Style Number';
		if ( $this->template == 'jewelry' )
			$this->addAttributeMapping('model', 'model')->localized_name = 'Model Number';

		if ( !$this->template == 'clothing' )
		{
			$this->addAttributeMapping('feed_product_type', 'feed_product_type')->localized_name = 'Product Type'; //not in clothing
			$this->addAttributeMapping('manufacturer', 'manufacturer')->localized_name = 'Manufacturer'; //not in clothing 
			$this->addAttributeMapping('part_number', 'part_number')->localized_name = 'Manufacturer Part Number'; //not in clothing
		}
		//optional in: office, home & garden
		if ( $this->template == 'office' || $this->template == 'home' ) {
			$this->addAttributeMapping('gtin_exemption_reason', 'gtin_exemption_reason')->localized_name = 'Product Exemption Reason'; 
			$this->addAttributeMapping('related_product_id', 'related_product_id')->localized_name = 'Related Product Identifier'; 
			$this->addAttributeMapping('related_product_id_type', 'related_product_id_type')->localized_name = 'Related Product Identifier Type'; 
		}

		/** Template: Offer - required to make your item buyable for customers on site **/
		$this->addAttributeMapping('price', 'standard_price')->localized_name = 'Standard Price';
		$this->addAttributeMapping('list_price', 'list_price')->localized_name = 'Manufacturer\'s Suggested Retail Price';

		$this->addAttributeMapping('currency', 'currency')->localized_name = 'Currency'; 
		$this->addAttributeMapping('stock_quantity', 'quantity')->localized_name = 'Quantity';
		//$this->addAttributeMapping('product_site_launch_date', 'product_site_launch_date')->localized_name = '';
		//$this->addAttributeMapping('merchant_release_date', 'merchant_release_date')->localized_name = '';
		//$this->addAttributeMapping('restock_date', 'restock_date')->localized_name = '';
		//$this->addAttributeMapping('fulfillment_latency', 'fulfillment_latency')->localized_name = '';
		$this->addAttributeMapping('sale_price', 'sale_price')->localized_name = 'Sale Price';
		$this->addAttributeMapping('item_package_quantity', 'item_package_quantity')->localized_name = 'Package Quantity';

		/** Template: Dimensions - These attributes specify the size and weight of a product */
		if ( $this->template == 'jewelry' )
			$this->addAttributeMapping('display_dimensions_unit_of_measure', 'display_dimensions_unit_of_measure')->localized_name = 'Display Dimensions Unit Of Measure';
		else		
		{
			$this->addAttributeMapping('weight', 'item_weight')->localized_name = 'Item Weight';
			$this->addAttributeMapping('item_weight_unit_of_measure', 'item_weight_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';
		}

		//Template: Discovery
		for ($i = 1; $i < 4; $i++)
			$this->addAttributeMapping('bullet_point' . $i, 'bullet_point' . $i, true)->localized_name = 'Key Product Features' . $i;
		for ($i = 1; $i < 4; $i++)
			$this->addAttributeMapping('generic_keywords' . $i, 'generic_keywords' . $i, true)->localized_name = 'Search Terms' . $i;
		for ($i = 1; $i < 4; $i++)
			$this->addAttributeMapping('specific_uses_keywords' . $i, 'specific_uses_keywords' . $i, true)->localized_name = 'Intended Use' . $i;

		//Template: Images
		$this->addAttributeMapping('feature_imgurl', 'main_image_url', true)->localized_name = 'Main Image URL';
		for ($i = 1; $i < 8; $i++)
			$this->addAttributeMapping("other_image_url_$i", "other_image_url$i", true)->localized_name = 'Other Image URL' . $i;

		//Template: Variation
		//$this->addAttributeMapping('item_group_id', 'parent_sku');
		//$this->addAttributeMapping('parent_child', 'parent_child');

	}

}

?>