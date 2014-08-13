<?php

  /********************************************************************
  Version 2.0
    An Amazon Feed (Product Ads)
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAmazonSCFeed extends PCSVFeed {

	public $headerTemplateType; //Attached to the top of the feed
	public $headerTemplateVersion;

	function __construct () {

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
		$product->attributes['description'] = $product->description;
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
			$this->addErrorMessage(8000, 'Description truncated for ' . $product->attributes['title']);
		}
		if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			$this->addErrorMessage(8001, 'Brand not set for ' . $product->attributes['title']);
		if (($this->external_product_id_type == 'UPC') && (strlen($product->attributes['external_product_id']) == 0))
			$this->addErrorMessage(8002, 'external_product_id not set for ' . $product->attributes['title']);
		if (($this->template == 'health') && (strlen($product->attributes['manufacturer']) == 0))
			$this->addErrorMessage(8003, 'Manufacturer not set for ' . $product->attributes['title']);
		if ($product->attributes['has_sale_price'] && (!isset($product->attributes['sale_from_date']) || !isset($product->attributes['sale_end_date'])))
			$this->addErrorMessage(8004, 'Sale price set for ' . $product->attributes['title'] . ' but no sale_from_date and/or sale_end_date provided');

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
		$headerLine1 = array('TemplateType=' . $this->headerTemplateType,  'Version=' . $this->headerTemplateVersion, 'The top 3 rows are for Amazon.com use only. Do not modify or delete the top 3 rows.');
		$headerLine2 = explode(',', 'SKU,Product Name,Product ID,Product ID Type,Product Type,Brand Name,Manufacturer,Manufacturer Part Number,Product Description,Item Type Keyword,Update Delete');
		$output = 
			implode($this->fieldDelimiter, $headerLine1) .  "\r\n" .
			implode($this->fieldDelimiter, $headerLine2) .  "\r\n";

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
			default:
				$this->external_product_id_type = '';
				$this->headerTemplateType = $remote_category;
				$this->feed_product_type = '';
				$this->headerTemplateVersion = '2014.0409';
		}

		//Template main
		$this->addAttributeMapping('id', 'item_sku');
		$this->addAttributeMapping('title', 'item_name');
		$this->addAttributeMapping('external_product_id', 'external_product_id');
		$this->addAttributeMapping('external_product_id_type', 'external_product_id_type');
		$this->addAttributeMapping('feed_product_type', 'feed_product_type');
		$this->addAttributeMapping('brand', 'brand_name');
		$this->addAttributeMapping('manufacturer', 'manufacturer');
		$this->addAttributeMapping('part_number', 'part_number');
		$this->addAttributeMapping('description', 'product_description', true);
		$this->addAttributeMapping('product_type', 'item_type');
		if ($this->template == 'clothing')
			$this->addAttributeMapping('model', 'model');

		//Template: Offer
		$this->addAttributeMapping('list_price', 'list_price');
		$this->addAttributeMapping('price', 'standard_price');
		$this->addAttributeMapping('currency', 'currency');
		$this->addAttributeMapping('stock_quantity', 'quantity');
		//$this->addAttributeMapping('product_site_launch_date', 'product_site_launch_date');
		//$this->addAttributeMapping('merchant_release_date', 'merchant_release_date');
		//$this->addAttributeMapping('restock_date', 'restock_date');
		//$this->addAttributeMapping('fulfillment_latency', 'fulfillment_latency');
		$this->addAttributeMapping('sale_price', 'sale_price');
		$this->addAttributeMapping('item_package_quantity', 'item_package_quantity');

		//Dimensions
		$this->addAttributeMapping('weight', 'item_weight');
		$this->addAttributeMapping('item_weight_unit_of_measure', 'item_weight_unit_of_measure');

		//Template: Discovery
		for ($i = 1; $i < 4; $i++)
			$this->addAttributeMapping('bullet_point' . $i, 'bullet_point' . $i);
		for ($i = 1; $i < 4; $i++)
			$this->addAttributeMapping('generic_keywords' . $i, 'generic_keywords' . $i);
		for ($i = 1; $i < 4; $i++)
			$this->addAttributeMapping('specific_uses_keywords' . $i, 'specific_uses_keywords' . $i);

		//Template: Images
		$this->addAttributeMapping('feature_imgurl', 'main_image_url');
		for ($i = 1; $i < 8; $i++)
			$this->addAttributeMapping("other_image_url_$i", "other_image_url$i");

		//Template: Variation
		//$this->addAttributeMapping('item_group_id', 'parent_sku');
		//$this->addAttributeMapping('parent_child', 'parent_child');

	}

}

?>