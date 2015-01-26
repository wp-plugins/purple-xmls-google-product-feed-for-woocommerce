<?php

  /********************************************************************
  Version 2.0
    A PriceGrabber Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-25

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PPriceGrabberFeed extends PCSVFeedEx {

	function __construct () {
		$this->providerName = 'PriceGrabber';
		$this->providerNameL = 'pricegrabber';
		$this->fileformat = 'csv';
		$this->fieldDelimiter = ",";
		$this->fields = array();
		parent::__construct();

//Required
	$this->addAttributeMapping('id', 'Retsku', true,true); 	
	$this->addAttributeMapping('title', 'Product Title', true,true);
	$this->addAttributeMapping('Detailed Description', 'Detailed Description', true,true);
	$this->addAttributeMapping('Categorization', 'Categorization', true,true);
	$this->addAttributeMapping('link', 'Product URL', true,true);
	$this->addAttributeMapping('feature_imgurl', 'Primary Image URL', true,true);
	$this->addAttributeMapping('Selling Price', 'Selling Price', true);
	$this->addAttributeMapping('condition', 'Condition', true);
	$this->addAttributeMapping('Availability', 'Availability', true);
	$this->addAttributeMapping('', 'Manufacturer Name', true);
//Recommended 
	$this->addAttributeMapping('item_group_id', 'Parent Retsku', true);
	$this->addAttributeMapping('Regular Price', 'Regular Price', true);
//1 of 2 required
	$this->addAttributeMapping('', 'Manufacturer Part Number', true,true);
	$this->addAttributeMapping('', 'GTIN', true,true);
	
	}

  function formatProduct($product) {
	
		//Prepare input
		$product->attributes['Product Title'] = $product->attributes['title'];
		$product->attributes['Categorization'] = $this->current_category;
		$product->attributes['Merchant Categorization'] = $product->attributes['product_type'];

		if (strlen($product->attributes['description']) > 1500)
			$product->attributes['description'] = substr($product->attributes['description'], 0, 1500);
		$product->attributes['description'] = str_replace('"','""',$product->attributes['description']);
		$product->attributes['Detailed Description'] = trim($product->attributes['description']);

		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';
		$product->attributes['Regular Price'] = sprintf($this->currency_format, $product->attributes['regular_price']);
		
		if ($product->attributes['has_sale_price'])
			$product->attributes['Selling Price'] = sprintf($this->currency_format, $product->attributes['sale_price']);
		else
			$product->attributes['Selling Price'] = $product->attributes['Regular Price'];

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['Availability'] = 'Yes';
		else
			$product->attributes['Availability'] = 'No';

		return parent::formatProduct($product);
	}

	function getFeedHeader($file_name, $file_path) {
		// $output = '';
		// foreach($this->fields as $field) {
		// 	if (isset($this->feedOverrides->overrides[$field]))
		// 		$field = $this->feedOverrides->overrides[$field];
		// 	$output .= $field . $this->fieldDelimiter;
		// }
		// //Trim trailing comma
		// return substr($output, 0, -1) .  "\r\n";
	}

}

?>