<?php

  /********************************************************************
  Version 2.0
    Am Amazon Feed
	By: Keneto 2014-05-08

  ********************************************************************/

require_once 'basicfeed.php';

class PAmazonFeed extends PBasicFeed{

  function __construct () {
	$this->providerName = 'Amazon';
	$this->providerNameL = 'amazon';
	$this->fileformat = 'csv';
	$this->fields = explode(',', 'Category,Title,Link,SKU,Price,Brand,Department,UPC,Image,Description,Manufacturer,Mfr part number,Other image-url1,Other image-url2,Other image-url3,Other image-url4,Other image-url5,Other image-url6,Other image-url7,Other image-url8,Weight,Shipping Cost,Shipping Weight');
	parent::__construct();
  }
  
  function formatProduct($product) {
	$variantUPC = '';
	$variantMfr = '';
    if ($product->isVariable) {
	  //Not used in original
	  //$variantUPC = rand();
	  //$variantMfr = rand();
	}
	
	//Prepare input
	$current_feed['UPC'] = $product->id . $variantUPC;
	$current_feed['Mfr part number'] = $product->id . $variantMfr;
	$current_feed['Title'] = '"' . $product->title . '"';
	$current_feed['Description'] = '"' . $product->description . '"';
	$current_feed['Category'] = $this->current_category;
	$current_feed['Link'] = $product->link;
	$current_feed['Image'] = $product->feature_imgurl;
	
	$image_count = 0;
	foreach($product->imgurls as $imgurl) {
	  $image_index = "Other image-url$image_count";
	  $current_feed[$image_index] = $imgurl;
	  $image_count++;
	  if ($image_count >= 9) {
		break;
	  }
	}

	$current_feed['Price'] = $product->regular_price . ' ' . $this->currency;
	if (($product->has_sale_price) && ($product->sale_price != "")) {
		$current_feed['Price'] = $product->sale_price . ' ' . $this->currency;
	}
	$current_feed['SKU'] = $product->sku;
	$current_feed['Shipping Cost'] = '0.00 ' . $this->currency;
	$current_feed['Shipping Weight'] = $product->weight . ' ' . $this->weight_unit;
	$current_feed['Weight'] = $product->weight . $this->weight_unit;

	//Build output in order of fields
	$output = '';
	foreach($this->fields as $field) {
	  if (isset($current_feed[$field])) {
	    $output .= $current_feed[$field] . ',';
	  } else {
	    $output .= ',';
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
	  $output .= $field . ',';
	}
	//Trim trailing comma
	return substr($output, 0, -1) . "\r\n";
  }

}

?>