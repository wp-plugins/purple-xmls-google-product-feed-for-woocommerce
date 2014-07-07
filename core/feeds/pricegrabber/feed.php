<?php

  /********************************************************************
  Version 2.0
    A PriceGrabber Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-25

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PPriceGrabberFeed extends PBasicFeed{

  function __construct () {
	$this->providerName = 'PriceGrabber';
	$this->providerNameL = 'pricegrabber';
	$this->fileformat = 'csv';
	$this->fields = array('Retsku', 'Parent Retsku', 'Product Title', 'Detailed Description', 'Categorization', 'Merchant Categorization', 'Product URL', 
	  'Primary Image URL', 'Selling Price', 'Regular Price', 'Condition', 'Availability', 'Manufacturer Name', 'Manufacturer Part Number', 'GTIN');
	parent::__construct();
  }

  function formatProduct($product) {
	
	//Prepare input
	$current_feed['Retsku'] = $product->id;
    if (isset($product->item_group_id)) {
	  $current_feed['Parent Retsku'] = $product->item_group_id;
	}
	$current_feed['Product Title'] = $product->title;
	$current_feed['Categorization'] = $this->current_category;
	$current_feed['Merchant Categorization'] = $product->product_type;
	if (strlen($product->description) > 1500)
	  $product->description = substr($product->description, 0, 1500);
	$current_feed['Detailed Description'] = $product->description;
	$current_feed['Product URL'] = $product->link;
	$current_feed['Primary Image URL'] = $product->feature_imgurl;

	if (strlen($product->regular_price) == 0) {
	  $product->regular_price = '0.00';
	}
	$current_feed['Regular Price'] = sprintf($this->currency_format, $product->regular_price);
	if (isset($product->sale_price))
	  $current_feed['Selling Price'] = sprintf($this->currency_format, $product->sale_price);
	else
	  $current_feed['Selling Price'] = $current_feed['Regular Price'];

	$current_feed['Condition'] = 'New';
	if ($product->stock_status == 1) {
	  $current_feed['Availability'] = 'Yes';
	} else {
	  $current_feed['Availability'] = 'No';
	}

	//Run overrides 
	//Note: One day, when the feed can report errors, we need to report duplicate overrides when used_so_far makes a catch
	$used_so_far = array();
	foreach($product->attributes as $key => $a) {
	  if (isset($this->feedOverrides->overrides[$key]) && !in_array($this->feedOverrides->overrides[$key], $used_so_far)) {
	    $current_feed[$this->feedOverrides->overrides[$key]] = $a;
		$used_so_far[] = $this->feedOverrides->overrides[$key]; 
	  }
	}

	//Post-override cleanup
	if (($this->system_wide_tax) && (!isset($current_feed['Tax']))){
	  //$current_feed['Tax'] = 
	}
	
	if (($this->system_wide_shipping_type) && (!isset($current_feed['Tax']))){
	  //$current_feed['Tax'] = 
	  //$current_feed['ShippingWeight'] = $product->weight;
	}

	//Build output in order of fields
	$output = '';
	foreach($this->fields as $field) {
	  if (isset($current_feed[$field])) {
	    $output .= $current_feed[$field] . $this->fieldDelimiter;
	  } else {
	    $output .= $this->fieldDelimiter;
	  }
	}

	//Trim trailing comma
	return substr($output, 0, -1) . "\r\n";
  }

  function getFeedHeader($file_name, $file_path) {
    $output = '';
    foreach($this->fields as $field) {
	  if (isset($this->feedOverrides->overrides[$field])) {
	    $field = $this->feedOverrides->overrides[$field];
	  }
	  $output .= $field . $this->fieldDelimiter;
	}
	//Trim trailing comma
	return substr($output, 0, -1) .  "\r\n";
  }

}

?>