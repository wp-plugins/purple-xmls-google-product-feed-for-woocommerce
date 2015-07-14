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
		$this->addAttributeMapping('title', 'description', true,true);
		$this->addAttributeMapping('link', 'url', true,true);
		$this->addAttributeMapping('price', 'price', true,true); //sale price if defined
		$this->addAttributeMapping('', 'caliber', true,true);
		//one of the below are required
		$this->addAttributeMapping('stock_quantity', 'numrounds', true,true); //number of rounds for a given price
		$this->addAttributeMapping('', 'count', true,true);	//number of bullets for given price
		//optional
		$this->addAttributeMapping('brand', 'manufacturer', true);
		$this->addAttributeMapping('stock_status', 'availability', true);
		$this->addAttributeMapping('', 'grains', true);
		$this->addAttributeMapping('', 'type', true);
		$this->addAttributeMapping('', 'gun', true);		
		$this->addAttributeMapping('', 'ammo_type', true);
		$this->addAttributeMapping('', 'shot_size', true);
		$this->addAttributeMapping('', 'shell_length', true);
		$this->addAttributeMapping('', 'casing', true);
		$this->addAttributeMapping('', 'boxlimit', true);	

		$this->ammoseek_combo_title = false;
		$this->ammoseek_combo_idvar = false;
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');

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

		//get the mapped-to attribute value
		foreach( $this->attributeMappings as $thisAttributeMapping ) 
			if ( $thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && !empty($product->attributes[$thisAttributeMapping->attributeName]) ) {
				if ( $thisAttributeMapping->mapTo == 'caliber' )
					$cpf_attribute_caliber = $product->attributes[$thisAttributeMapping->attributeName];
				if ( $thisAttributeMapping->mapTo == 'numrounds' )
					$cpf_attribute_numrounds = $product->attributes[$thisAttributeMapping->attributeName];
				if ( $thisAttributeMapping->mapTo == 'count' )
					$cpf_attribute_count = $product->attributes[$thisAttributeMapping->attributeName];
				if ( $thisAttributeMapping->mapTo == 'grains' )
					$cpf_attribute_grains = $product->attributes[$thisAttributeMapping->attributeName];
				if ( $thisAttributeMapping->mapTo == 'ammo_type' )
					$cpf_attribute_ammotype = $product->attributes[$thisAttributeMapping->attributeName];
				if ( $thisAttributeMapping->mapTo == 'shot_size' )
					$cpf_attribute_shotsize = $product->attributes[$thisAttributeMapping->attributeName];
				if ( $thisAttributeMapping->mapTo == 'shell_length' )
					$cpf_attribute_shell_length = $product->attributes[$thisAttributeMapping->attributeName];

			}
		$ammoSeekTitle = $product->attributes['title'];
		if ( $this->ammoseek_combo_title ) {

			if ( !empty($cpf_attribute_caliber) ) 
				$ammoSeekTitle .= ' | ' . $cpf_attribute_caliber;
			if ( !empty($cpf_attribute_numrounds) ) 
				$ammoSeekTitle .= ' | ' . $cpf_attribute_numrounds . ' rounds';
			if ( !empty($cpf_attribute_count) ) 
				$ammoSeekTitle .= ' | count: ' . $cpf_attribute_count;
			if ( !empty($cpf_attribute_grains) ) 
				$ammoSeekTitle .= ' | grains: '  . $cpf_attribute_grains;
			if ( !empty($cpf_attribute_ammotype) ) 
				$ammoSeekTitle .= ' | ammo type: ' . $cpf_attribute_ammotype;
			if ( !empty($cpf_attribute_shotsize) ) 
				$ammoSeekTitle .= ' | shot size: ' . $cpf_attribute_shotsize;
			if ( !empty($cpf_attribute_shell_length) ) 
				$ammoSeekTitle .= ' | shell length: ' . $cpf_attribute_shell_length;

		}
		else if ( $this->ammoseek_combo_idvar ) {
			$ammoSeekTitle = $product->attributes['id'] . ' - ' . $product->attributes['title'] . ' - ' . $product->attributes['item_group_id']; 
		}
		else 
			$ammoSeekTitle = $product->attributes['title'];
		
		$product->attributes['title'] = $ammoSeekTitle;

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
		// if (!isset($product->attributes['numrounds']) || (strlen($product->attributes['numrounds']) == 0))
		// 	if (!isset($product->attributes['count']) || (strlen($product->attributes['count']) == 0))
		// 		if ($countable)
		// 			$this->addErrorMessage(10002, 'Missing numrounds for ' . $product->attributes['title']);
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

	function getFeedFooter($file_name, $file_path) {   
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