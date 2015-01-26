<?php

	/********************************************************************
	Version 2.1
		A Google Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAmmoSeekFeed extends PBasicFeed{

	function __construct () {
		parent::__construct();
		$this->providerName = 'AmmoSeek';
		$this->providerNameL = 'ammoseek';
		//Create some attributes (Mapping 3.0)
		//required
		$this->addAttributeMapping('description', 'description', true,true);
		$this->addAttributeMapping('link', 'url', true,true);
		$this->addAttributeMapping('regular_price', 'price', true,true);
		$this->addAttributeMapping('', 'caliber', true,true);
		$this->addAttributeMapping('stock_quantity', 'numrounds', true,true);
		$this->addAttributeMapping('', 'count', true,true);	//number of rounds for a given price
		//optional
		$this->addAttributeMapping('brand', 'manufacturer', true);		
		$this->addAttributeMapping('', 'grains', true);
		$this->addAttributeMapping('', 'type', true);
		$this->addAttributeMapping('', 'gun', true);
		$this->addAttributeMapping('', 'shot_size', true);
		$this->addAttributeMapping('', 'shell_length', true);
		$this->addAttributeMapping('stock_status', 'availability', true);

	}
  
  function formatProduct($product) {

		$output = '
      <product Type="' . $this->current_category . '">';

		//********************************************************************
		//Prepare the Product Attributes
		//********************************************************************

		//Cheat: This should be a CustomModifier
		if (isset($product->attributes['disable_vars'])) {
			$ids = explode(',', $product->attributes['disable_vars']);
			if (in_array($product->attributes['id'], $ids))
				return '';
		}

		//Stock Status
		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'in stock';
		else
			$product->attributes['stock_status'] = 'out of stock';

		//Price
		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';
		$product->attributes['regular_price'] = sprintf($this->currency_format, $product->attributes['regular_price']) . $this->currency;
		if ($product->attributes['has_sale_price'])
			$product->attributes['sale_price'] = sprintf($this->currency_format, $product->attributes['sale_price']) . $this->currency;

		//********************************************************************
		//Mapping 3.0 pre processing
		//********************************************************************

		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);

		//********************************************************************
		//Add attributes (Mapping 3.0)
		//********************************************************************

		foreach($this->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) )
				$output .= $this->formatLine($thisAttributeMapping->mapTo, $product->attributes[$thisAttributeMapping->attributeName], $thisAttributeMapping->usesCData);

		//********************************************************************
		//Mapping 3.0 post processing
		//********************************************************************

		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 3)
				$thisDefault->postProcess($product, $output);

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************

		$countable = true;
		if ($this->current_category == 'guns')
			$countable = false;
		if (!isset($product->attributes['numrounds']) || (strlen($product->attributes['numrounds']) == 0))
			if (!isset($product->attributes['count']) || (strlen($product->attributes['count']) == 0))
				if ($countable)
					$this->addErrorMessage(10002, 'Missing numrounds for ' . $product->attributes['title']);
		if (!isset($product->attributes['caliber']) || (strlen($product->attributes['caliber']) == 0))
			if (strpos(strtolower($product->attributes['title']), 'caliber') === false)
				$this->addErrorMessage(10003, 'Missing caliber for ' . $product->attributes['title']);
		if (strlen($product->attributes['description']) > 499) {
			$product->attributes['description'] = substr($product->attributes['description'], 0, 499);
			$this->addErrorMessage(10004, 'Description too long for ' . $product->attributes['title'], true);
		}

    $output .= '
      </product>';

		return $output;

	}

	function getFeedFooter() {   
    $output = '
  </productlist>';
		return $output;
	}

	function getFeedHeader($file_name, $file_path) {
		if (!isset($this->retailer_name)) {
			$this->retailer_name = '';
			$this->addErrorMessage(10001, 'Retailer name not specified');
		}
		$output = '<?xml version="1.0" encoding="UTF-8" ?>
  <productlist retailer="' . $this->retailer_name .'">';
		return $output;
  }

}