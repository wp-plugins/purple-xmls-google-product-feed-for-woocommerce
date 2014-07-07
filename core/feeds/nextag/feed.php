<?php

  /********************************************************************
  Version 2.0
    A Google Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PNextagFeed extends PBasicFeed{

  function __construct () {
	$this->providerName = 'Nextag';
	$this->providerNameL = 'nextag';
	$this->fileformat = 'csv';
	$this->fields = array("UPC", "Product Name", "Description", "Price", "Click-out URL", "Category", "Image URL", "Stock Status", "List Price");
	parent::__construct();
  }

  function formatProduct($product) {


	//Prepare input: Required
	$current_feed['UPC'] = $product->id;
    //if ($product->isVariable) {
	  //$current_feed[????] = $product->item_group_id; // Nothing in Nextag specs about product variations
	//}
	$current_feed['Product Name'] = $product->title;
	if (strlen($product->description) > 500) {
	  $product->description = substr($product->description, 0, 500);
	}
	$current_feed['Description'] = '"' . $product->description . '"';
	if (strlen($product->regular_price) == 0) {
	  $product->regular_price = '0.00';
	}
	$current_feed['Price'] = $product->regular_price . ' ' . $this->currency;
	$current_feed['Click-out URL'] = $product->link;
	$current_feed['Category'] = '"' . $this->current_category . '"';

	//Input: Optional
	$current_feed['Image URL'] = $product->feature_imgurl;
	if ($product->stock_status == 1) {
	  $current_feed['Stock Status'] = 'In Stock';
	} else {
	  $current_feed['Stock Status'] = 'Out Of Stock';
	}

	if (isset($product->sale_price)) {
		$current_feed['List Price'] = $product->sale_price;
	}

	//Build output in order of fields
	$output = '';
	foreach($this->fields as $field) {
	  if (isset($current_feed[$field])) {
	    $output .= $current_feed[$field] . "\t";
	  } else {
	    $output .= "\t";
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
	  $output .= $field . "\t";
	}
	//Trim trailing comma
	return substr($output, 0, -1) .  "\r\n";
  }

}

?>