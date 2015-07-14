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

//general information
	$this->addAttributeMapping('sku', 'Retsku', true,true);
	$this->addAttributeMapping('item_group_id', 'Parent Retsku', true); 	
	$this->addAttributeMapping('title', 'Product Title', true,true);
	$this->addAttributeMapping('description', 'Detailed Description', true,true); //1500 chars max
	$this->addAttributeMapping('current_category', 'Categorization', true,true);
	$this->addAttributeMapping('local_category', 'Merchant Categorization', true,true);
	$this->addAttributeMapping('link', 'Product URL', true,true);
	$this->addAttributeMapping('feature_imgurl', 'Primary Image URL', true,true);
	for ($i = 1; $i <= 10; $i++) 
		$this->addAttributeMapping('additional_images' . $i, 'Additional Image URL ' . $i, true,false); //up to 10
	$this->addAttributeMapping('price', 'Selling Price', true);
	$this->addAttributeMapping('regular_price', 'Regular Price', true);
	$this->addAttributeMapping('condition', 'Condition', true);
	$this->addAttributeMapping('stock_status', 'Availability', true); //
	$this->addAttributeMapping('', 'Video URL', true);
//additional product information
	$this->addAttributeMapping('brand', 'Manufacturer Name', true,true);	
	//1 of 2 required
	$this->addAttributeMapping('', 'Manufacturer Part Number', true,true);
	$this->addAttributeMapping('', 'GTIN (UPC / EAN / ISBN)', true,true);
//attribute information
	$this->addAttributeMapping('', 'Color', true);
	$this->addAttributeMapping('', 'Size', true);
	$this->addAttributeMapping('', 'Material', true);
	$this->addAttributeMapping('', 'Pattern', true);
	$this->addAttributeMapping('', 'Gender', true);
	$this->addAttributeMapping('', 'Age',true);
//mature
	$this->addAttributeMapping('', 'Mature',true);
//merchant multipacks & bundles
	$this->addAttributeMapping('', 'Parent UPC', true);
	$this->addAttributeMapping('', 'Multpack (Bulk Quantity)', true);
//shipping information
	$this->addAttributeMapping('', 'Shipping Cost', true);
	$this->addAttributeMapping('', 'Weight', true);

	$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
	$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
	$this->addRule( 'description', 'description',array('max_length=1500','strict') );
	//$this->addRule( 'csv_standard', 'CSVStandard',array('title','100') ); //100 char limit for pricegrabber titles
	$this->addRule( 'substr','substr', array('title','0','100',true) ); //127 length
	}

  function formatProduct($product) {
		//Prepare input	
		$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);

		$image_count = 1;
		$product->attributes['additional_images' . $image_count ] = '';
		foreach($product->imgurls as $imgurl) {
			$product->attributes['additional_images' . $image_count] .= $imgurl;
			$product->attributes['additional_images'. $image_count] = str_replace( 'https://','http://',$product->attributes['additional_images' . $image_count] );
			$image_count++;
			if ($image_count >= 10)
				break;
		}	

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'Yes';
		else
			$product->attributes['stock_status'] = 'No';

		if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
				$this->addErrorMessage(15000, 'Missing brand for ' . $product->attributes['title']);

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