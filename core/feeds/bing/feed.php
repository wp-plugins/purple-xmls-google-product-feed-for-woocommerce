<?php

  /********************************************************************
  Version 2.0
    A Bing Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-04

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PBingFeed extends PBasicFeed{

  public $bingForceGoogleCategory = false;
  public $bingForcePriceDiscount = false;

  function __construct () {
	$this->providerName = 'Bing';
	$this->providerNameL = 'bing';
	$this->fileformat = 'csv';
	$this->fields = array('MPID', 'Title', 'Brand', 'ProductURL', 'Price', 'Description', 'ImageURL', 'SKU', 'Availability', 'Condition', 'ProductType', 'B_Category');
	parent::__construct();
  }

  function formatProduct($product) {
	
	//Prepare input
	$current_feed['MPID'] = $product->id;
    //if ($product->isVariable) {
	  //$current_feed['Item Group ID'] = $product->item_group_id;
	//}
	$current_feed['Title'] = $product->title;
	$current_feed['ProductURL'] = $product->link;

	if (strlen($product->regular_price) == 0) {
	  $product->regular_price = '0.00';
	}
	$current_feed['Price'] = sprintf($this->currency_format, $product->regular_price);
	if (isset($product->sale_price)) {
	  $current_feed['PriceWithDiscount'] = sprintf($this->currency_format, $product->sale_price);
	}
	$current_feed['Description'] = $product->description;
	$current_feed['ImageURL'] = $product->feature_imgurl;
	
	$current_feed['SKU'] = $product->sku;
	//Note: Bing Specs require SKU != MPN. MPN currnetly not used

	//Note: Only In stock && New products will publish on Bing
	$current_feed['Condition'] = 'New';
	if ($product->stock_status == 1) {
	  $current_feed['Availability'] = 'In Stock';
	} else {
	  $current_feed['Availability'] = 'Out of Stock';
	}
	
    
	$current_feed['ProductType'] = $product->product_type;
	$current_feed['B_Category'] = $this->current_category;
	//if ($this->bingForceGoogleCategory) {
	  //For this to work, we need to enable a Google taxonomy dialog box.
	//}
	//Need one day: Bingads_grouping, Bingads_label, Bingads_redirect

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
    if ($this->bingForcePriceDiscount)
	  $this->insertField('PriceWithDiscount', 'Price');
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