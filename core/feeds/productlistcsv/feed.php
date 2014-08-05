<?php

  /********************************************************************
  Version 2.1
    A Product List CSV Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-10

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlistcsvFeed extends PBasicFeed{

	function __construct () {
		$this->providerName = 'Productlistcsv';
		$this->providerNameL = 'productlistcsv';
		$this->fileformat = 'csv';
		$this->fields = explode(',', 'id,item_group_id,title,description,product_category,product_type,link,image_link,condition,availability,price,sale_price,sku,weight,shipping_type,shipping_price,tax');
		parent::__construct();
	}
  
	function formatProduct($product) {

		$current_feed['id'] = $product->id;
		if (isset($product->item_group_id))
			$current_feed['item_group_id'] = $product->item_group_id;
		$current_feed['title'] = $product->attributes['title'];
		$current_feed['description'] = '"' . $product->description . '"';
		$current_feed['product_category'] = $this->current_category;
		$current_feed['product_type'] = $product->attributes['product_type'];
		$current_feed['link'] = $product->attributes['link'];
		$current_feed['image_link'] = $product->feature_imgurl;
		$current_feed['condition'] = $product->attributes['condition'];

		if ($product->attributes['stock_status'] == 1)
			$stockStatus = 'in stock';
		else
			$stockStatus = 'out of stock';
		$current_feed['availability'] = $stockStatus;
	
		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';
		$current_feed['price'] = sprintf($this->currency_format, $product->attributes['regular_price']) . $this->currency;
		if ($product->attributes['has_sale_price']) 
			$current_feed['sale_price'] = sprintf($this->currency_format, $product->attributes['sale_price']) . $this->currency;

		$current_feed['sku'] = $product->attributes['sku'];
	
		if ($product->attributes['weight'] != "")
			$current_feed['weight'] = $product->attributes['weight'] . ' ' . $this->weight_unit;

		if ($this->system_wide_shipping) {
			$current_feed['shipping_type'] = $this->system_wide_shipping_type;
			$current_feed['shipping_price'] = sprintf($this->currency_format, $product->shipping_amount) . $this->currency_shipping;
		}
	
		if (isset($product->attributes['tax']))
			$current_feed['tax'] = $product->attributes['tax'];

		//Run overrides 
		$used_so_far = array();
		foreach($product->attributes as $key => $a) {
			if (isset($this->feedOverrides->overrides[$key]) && !in_array($this->feedOverrides->overrides[$key], $used_so_far)) {
				$current_feed[$this->feedOverrides->overrides[$key]] = $a;
				$used_so_far[] = $this->feedOverrides->overrides[$key]; 
			}
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
			if (isset($this->feedOverrides->overrides[$field]))
				$field = $this->feedOverrides->overrides[$field];
			$output .= $field . $this->fieldDelimiter;
		}

		//Trim trailing comma
		return substr($output, 0, -1) . "\r\n";
  }

}