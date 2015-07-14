<?php

	/********************************************************************
	Version 2.0
		A GoDataFeed Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-09

	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PGoDataFeedFeed extends PBasicFeed {

	function __construct ()  {

		parent::__construct();

		$this->providerName = 'GoDataFeed';
		$this->providerNameL = 'godatafeed';
		//Create some attributes (Mapping 3.0)
		//required
		$this->addAttributeMapping('id', 'UniqueID', true,true);
		$this->addAttributeMapping('title', 'Name', true,true); //product name (15-70 chars)
		$this->addAttributeMapping('description', 'Description', true,true);
		$this->addAttributeMapping('regular_price', 'Price', true,true);
		$this->addAttributeMapping('local_category', 'MerchantCategory', true,true);
			$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addAttributeMapping('link', 'URL', true,true);
		$this->addAttributeMapping('feature_imgurl', 'ImageURL', true,true);
		$this->addAttributeMapping('', 'Manufacturer', true,true);
		$this->addAttributeMapping('sku', 'ManufacturerPartNumber', true,true);
		$this->addAttributeMapping('brand', 'Brand', true,true);
		$this->addAttributeMapping('', 'Keywords', true);
		//$this->addAttributeMapping('stock_status', 'StockStatus', true);
		$this->addAttributeMapping('', 'ShippingPrice', true);
		$this->addAttributeMapping('stock_quantity', 'Quantity', true);
		$this->addAttributeMapping('weight', 'Weight', true);
		$this->addAttributeMapping('condition', 'Condition', true);
		$this->addAttributeMapping('', 'UPC', true);
		$this->addAttributeMapping('sale_price', 'SalePrice', true);
	}
  
  function formatProduct($product) {

		//********************************************************************
		//Prepare the Product Attributes
		//********************************************************************

		//Stock Status: not found in feed spec?
		//if ($product->attributes['stock_status'] == 1)
		//	$product->attributes['stock_status'] = 'in stock';
		//else
		//	$product->attributes['stock_status'] = 'out of stock';

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

		//********************************************************************
		//Mapping 3.0 pre processing
		//********************************************************************

		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);

		$output = '
    <Product>';

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

		//if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			//$this->addErrorMessage(2000, 'Missing brand for ' . $product->attributes['title']);

    $output .= '
    </Product>';

		return $output;

	}

	function getFeedFooter($file_name, $file_path) {   
    	$output = '
  </Products>
</GoDataFeed>';
		return $output;
	}

	function getFeedHeader( $file_name, $file_path ) {
		$output = '<?xml version="1.0" encoding="UTF-8" ?>
<GoDataFeed>
  <Fields>';

		foreach($this->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted)
				$output .= '
    <Field name="' . $thisAttributeMapping->mapTo . '" />';

		$output .= '
  </Fields>
  <Products>';
		return $output;
  }

}