<?php

	/********************************************************************
	Version 2.1
		A Google Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PGoogleFeed extends PBasicFeed{

	function __construct () {
		parent::__construct();
		$this->providerName = 'Google';
		$this->providerNameL = 'google';
		//Create some attributes (Mapping 3.0)
		$this->addAttributeMapping('id', 'g:id');
		$this->addAttributeMapping('item_group_id', 'g:item_group_id');
		$this->addAttributeMapping('title', 'title', true);
		$this->addAttributeMapping('link', 'link', true);
		$this->addAttributeMapping('product_type', 'g:product_type', true);
		$this->addAttributeMapping('description', 'description', true);
		$this->addAttributeMapping('current_category', 'g:google_product_category', true);
		$this->addAttributeMapping('condition', 'g:condition');
		$this->addAttributeMapping('stock_status', 'g:availability');
		$this->addAttributeMapping('sku', 'g:mpn');
		$this->addAttributeMapping('regular_price', 'g:price');
		$this->addAttributeMapping('sale_price', 'g:sale_price');
		$this->addAttributeMapping('weight', 'g:shipping_weight');
		$this->addAttributeMapping('feature_imgurl', 'g:image_link', true);
	}
  
  function formatProduct($product) {

		$output = '
      <item>';

		//$output .= $this->formatLine('g:id', $product->attributes['id']);
		//if (isset($product->item_group_id))
			//$output .= $this->formatLine('g:item_group_id', $product->item_group_id);

		//$output .= $this->formatLine('description', $product->description, true);    
		//$output .= $this->formatLine('g:google_product_category', $this->current_category, true);
		//$output .= $this->formatLine('g:image_link', $product->feature_imgurl, true);
		
		//Cheat: These three fields aren't ready to be attributes yet, so adding manually:
		$product->attributes['description'] = $product->description;
		$product->attributes['current_category'] = $this->current_category;
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;
		$product->attributes['tax_country'] = 'US';

		//Stock Status
		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'in stock';
		else
			$product->attributes['stock_status'] = 'out of stock';

		//Price
		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';
		$product->attributes['regular_price'] = sprintf($this->currency_format, $product->attributes['regular_price']) . $this->currency;
		$this->getMapping('sale_price')->enabled = $product->attributes['has_sale_price'];
		if ($product->attributes['has_sale_price'])
			$product->attributes['sale_price'] = sprintf($this->currency_format, $product->attributes['sale_price']) . $this->currency;

		//Misc
		if (isset($product->attributes['weight']) && ($product->attributes['weight'] != "")) {
			$this->getMapping('weight')->enabled = true;
			$product->attributes['weight'] = $product->attributes['weight'] . ' ' . $this->weight_unit;
		} else
			$this->getMapping('weight')->enabled = false;

		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);

		//Add attributes (Mapping 3.0)
		foreach($this->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) )
				$output .= $this->formatLine($thisAttributeMapping->mapTo, $product->attributes[$thisAttributeMapping->attributeName], $thisAttributeMapping->usesCData);

		if ($this->allow_additional_images) {
			$image_count = 0;
			foreach($product->imgurls as $imgurl) {
				$output .= $this->formatLine('g:additional_image_link', $imgurl, true);
				$image_count++;
				if ($image_count > 9)
					break;
			}
		}

		if ($this->system_wide_shipping) {
			/*Deprecated / Legacy
			if (strpos($this->system_wide_shipping_rate, '%') === false)
				$product->shipping_amount = $this->system_wide_shipping_rate;
			else
				$product->shipping_amount = sprintf("%1.2f", substr($this->system_wide_shipping_rate, 1) * $product->attributes['regular_price']);
			*/
			$output.= '
        <g:shipping>' .
		$this->formatLine('g:service', $this->system_wide_shipping_type, false, '  ') .
		$this->formatLine('g:price', sprintf($this->currency_format, $product->shipping_amount) . $this->currency_shipping, false, '  ') . '
        </g:shipping>';
		}

		if (isset($product->attributes['tax'])) {
			$output .= '
        <g:tax>' .
		$this->formatLine('g:country', $product->attributes['tax_country'], false, '  ') .
		$this->formatLine('g:rate', $product->attributes['tax'], false, '  ') . '
        </g:tax>';
		}

		//This is mapping 2.0
		$used_so_far = array();
		foreach($product->attributes as $key => $a) {
			//Only use the override if it's set and hasn't been used_so_far in this product
			if (isset($this->feedOverrides->overrides[$key]) && !in_array($this->feedOverrides->overrides[$key], $used_so_far)) {
				$output .= $this->formatLine($key, $a);
				$used_so_far[] = $this->feedOverrides->overrides[$key];
			}
		}

		//Mapping 3.0 post processing
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 3)
				$thisDefault->postProcess($product, $output);

    $output .= '
      </item>';

		return $output;

	}

	function getFeedFooter() {   
    $output = '
  </channel>
  </rss>';
		return $output;
	}

	function getFeedHeader($file_name, $file_path) {

		$output = '<?xml version="1.0" encoding="UTF-8" ?>
  <rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">
  <channel>
    <title>' . $file_name . '</title>
    <link><![CDATA[' . $file_path . ']]></link>
    <description>' . $file_name . '</description>';
		return $output;
  }

}