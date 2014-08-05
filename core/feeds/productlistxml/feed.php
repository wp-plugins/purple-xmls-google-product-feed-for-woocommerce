<?php

  /********************************************************************
  Version 2.1
    A Product List XML Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-09

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlistxmlFeed extends PBasicFeed{

	function __construct () {
		$this->providerName = 'Productlistxml';
		$this->providerNameL = 'productlistxml';
		parent::__construct();
	}
  
	function formatProduct($product) {
		$output = '
	<item>';
		$output .= $this->formatLine('id', $product->id);
		if (isset($product->item_group_id))
			$output .= $this->formatLine('item_group_id', $product->item_group_id);
		$output .= $this->formatLine('description', $product->description, true);    
		$output .= $this->formatLine('product_category', $this->current_category, true);

		$image_count = 0;
		foreach($product->imgurls as $imgurl) {
			$output .= $this->formatLine('additional_image_link', $imgurl, true);
			$image_count++;
			if ($image_count > 9)
				break;
		}

		foreach($product->attributes as $key => $value)
			$output .= $this->formatLine($key, $value);
		/*$output .= $this->formatLine('title', $product->attributes['title'], true);		
		$output .= $this->formatLine('product_type', $product->attributes['product_type'], true);
		$output .= $this->formatLine('link', $product->attributes['link'], true);
		$output .= $this->formatLine('image_link', $product->feature_imgurl, true);

		$output.= $this->formatLine('condition', $product->attributes['condition']);
	
		if ($product->attributes['stock_status'] == 1)
			$stockStatus = 'in stock';
		else
			$stockStatus = 'out of stock';
		$output.= $this->formatLine('availability', $stockStatus);
	
		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';
		$output.= $this->formatLine('price', sprintf($this->currency_format, $product->attributes['regular_price']) . $this->currency);
		if ($product->attributes['has_sale_price']) 
			$output.= $this->formatLine('sale_price', sprintf($this->currency_format, $product->attributes['sale_price']) . $this->currency);

		$output.= $this->formatLine('sku', $product->attributes['sku']);
	
		if ($product->attributes['weight'] != "")
			$output.= $this->formatLine('weight', $product->attributes['weight'] . ' ' . $this->weight_unit);

		$used_so_far = array();
		foreach($product->attributes as $key => $a) {
			//Only use the override if it's set and hasn't been used_so_far in this product
			if (isset($this->feedOverrides->overrides[$key]) && !in_array($this->feedOverrides->overrides[$key], $used_so_far)) {
				$output .= $this->formatLine($key, $a);
				$used_so_far[] = $this->feedOverrides->overrides[$key];
			}
		}*/

    $output .= '
	</item>';

    return $output;
  }

  function getFeedFooter() {   
    $output = '
 </items>';
	return $output;
  }

  function getFeedHeader($file_name, $file_path) {

    $output = '<?xml version="1.0" encoding="UTF-8" ?>
<items>';
	return $output;
  }

}