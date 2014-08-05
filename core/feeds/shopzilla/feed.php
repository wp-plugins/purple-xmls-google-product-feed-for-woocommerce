<?php

  /********************************************************************
  Version 2.0
    A Shopzilla Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
  Note: Shopzilla (like Amazon) has limited support for FeedOverrides.
    See the Bing feed provider for a better sample

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PShopzillaFeed extends PBasicFeed{

  function __construct () {
	$this->providerName = 'Shopzilla';
	$this->providerNameL = 'shopzilla';
	$this->fileformat = 'csv';
	$this->fields = array("Unique ID", "Title", "Description", "Category", "Product URL", "Image URL", "Additional Image URL", "Condition", "Availability", "Current Price", "Original Price", "Brand", "Ship Cost", "Ship Weight", "Item Group ID");
	parent::__construct();
  }

  function formatProduct($product) {
	
	//Prepare input
	$current_feed['Unique ID'] = $product->id;
    if ($product->isVariable) {
	  $current_feed['Item Group ID'] = $product->item_group_id;
	}
	$current_feed['Title'] = $product->attributes['title'];
	$current_feed['Description'] = '"' . $product->description . '"';
    $current_feed['Category'] = '"' . str_replace(",", '', $this->current_category) . '"';
	//$current_feed['Category'] = '"' . $this->current_category . '"';
	$current_feed['Product URL'] = $product->attributes['link'];
	$current_feed['Image URL'] = $product->feature_imgurl;

	$current_feed['Additional Image URL'] = '';
	$image_count = 0;
	foreach($product->imgurls as $imgurl) {
	  $current_feed['Additional Image URL'] .= $imgurl . ',';
	  $image_count++;
	  if ($image_count >= 9) {
		break;
	  }
	}
	//Trim trailing comma from Additional Image
	if (strlen($current_feed['Additional Image URL']) > 0)
	  $current_feed['Additional Image URL'] = substr($current_feed['Additional Image URL'], 0, -1);

	$current_feed['Condition'] = $product->attributes['condition'];
	if ($product->attributes['stock_status'] == 1) {
	  $current_feed['Availability'] = 'In Stock';
	} else {
	  $current_feed['Availability'] = 'Out of Stock';
	}
	
	if (strlen($product->attributes['regular_price']) == 0) {
	  $product->attributes['regular_price'] = '0.00';
	}
	$current_feed['Current Price'] = $product->attributes['regular_price'];
	$current_feed['Original Price'] = $product->attributes['regular_price'];
	if ($product->attributes['has_sale_price']) {
		$current_feed['Current Price'] = $product->attributes['sale_price'];
	}
	//$current_feed['SKU'] = $product->attributes['sku'];
	//$current_feed['Ship Cost'] = '0.00 ' . $this->currency;
	$current_feed['Ship Weight'] = $product->attributes['weight'];

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