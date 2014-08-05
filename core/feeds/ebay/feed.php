<?php

  /********************************************************************
  Version 2.0
    An eBay Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PeBayFeed extends PBasicFeed {

  function __construct () {
	$this->providerName = 'eBay';
	$this->providerNameL = 'ebay';
	parent::__construct();
  }

  function formatProduct($product) {

	$output = '
      <Product>';
	$output .= $this->formatLine('Product_Name', $product->attributes['title'], true);
	if (!isset($product->parent_title))
		$product->parent_title = '';
	$output .= $this->formatLine('Parent_Name', $product->parent_title, true);
	$output .= $this->formatLine('Product_Description', $product->description, true);
	$category = explode(":", $this->current_category);
	if (isset($category[1])) {$this_category = $category[1];} else {$this_category = '';}
	$output .= $this->formatLine('Category', $this_category, true);
	$output .= $this->formatLine('Product_Type', $this_category, true);
	$output .= $this->formatLine('Category_ID', $category[0]);
	$output .= $this->formatLine('Product_URL', $product->attributes['link'], true);
	$output .= $this->formatLine('Image_URL', $product->feature_imgurl, true);
	$image_count = 0;
	foreach($product->imgurls as $imgurl) {
	  $output .= $this->formatLine('Alternative_Image_URL_' . $image_count++, $imgurl, true);
	}
	$output .= $this->formatLine('Condition', $product->attributes['condition']);

	if ($product->attributes['stock_status'] == 1) {
	  $stockStatus = 'in stock';
	} else {
	  $stockStatus = 'out of stock';
	}
	$output.= $this->formatLine('Stock_Availability', $stockStatus);
	$output.= $this->formatLine('Original_Price', $product->attributes['regular_price'] . ' ' . $this->currency);
	if ($product->attributes['has_sale_price']) {
	  $output.= $this->formatLine('Current_Price', $product->attributes['sale_price'] . ' ' . $this->currency);
	}
	$output.= $this->formatLine('Merchant_SKU', $product->attributes['sku']);


	if ($product->attributes['weight'] != "") {
	  $output.= $this->formatLine('Product_Weight', $product->attributes['weight']);
	  $output.= $this->formatLine('Shipping_Weight', $product->attributes['weight']);
	  $output.= $this->formatLine('Weight_Unit_of_Measure', $this->weight_unit);
	}
	$output.= $this->formatLine('Shipping_Rate', '0.00 ' . $this->currency);

	foreach($product->attributes as $key => $a) {
	  $output .= $this->formatLine($key, $a);
	}
    $output .= '
	  </Product>';
    return $output;
  }

  function getFeedFooter() {
    $output = null;
    $output.= '
  </Products>';
	return $output;
  }

  function getFeedHeader($file_name, $file_path) {
    $output = '<?xml version="1.0" encoding="UTF-8" ?>
  <Products>';
	return $output;
  }

}
?>