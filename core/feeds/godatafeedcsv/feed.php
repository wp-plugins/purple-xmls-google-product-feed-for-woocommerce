<?php

	/********************************************************************
	Version 2.0
		A GoDataFeed Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-09

	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PGoDataFeedCSVFeed extends PCSVFeedEx {

	function __construct ()  {

		parent::__construct();

		$this->providerName = 'GoDataFeedCSV';
		$this->providerNameL = 'godatafeedcsv';
		$this->fileformat = 'csv';
		$this->fields = array();
		$this->fieldDelimiter = ",";

		//Create some attributes (Mapping 3.0)
//Required
		$this->addAttributeMapping('id', 'UniqueID', true,true);
		$this->addAttributeMapping('title', 'Name', true,true); //product name (15-70 chars)
		$this->addAttributeMapping('description', 'Description', true,true);
		$this->addAttributeMapping('regular_price', 'Price', true,true);
		$this->addAttributeMapping('local_category', 'Merchant Category', true,true);
		$this->addAttributeMapping('link', 'URL', true,true); //begin with http://
		$this->addAttributeMapping('feature_imgurl', 'ImageURL', true,true); //begin with http://
		$this->addAttributeMapping('', 'Manufacturer', true,true);
		$this->addAttributeMapping('sku', 'Manufacturer Part Number', true,true);
		$this->addAttributeMapping('brand', 'Brand', true,true);
//Suggested
		$this->addAttributeMapping('', 'Keywords', true); //iPod,Apple iPod,iPod Video
		$this->addAttributeMapping('', 'Shipping Price', true);
		$this->addAttributeMapping('stock_quantity', 'Quantity', true);
		$this->addAttributeMapping('weight', 'Weight', true);
		$this->addAttributeMapping('condition', 'Condition', true);
		$this->addAttributeMapping('', 'UPC', true);
		$this->addAttributeMapping('sale_price', 'Sale Price', true);

		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
			$this->addRule('price_rounding','pricerounding'); //2 decimals
		
	}
  
  function formatProduct($product) {

  	//For CSV files: replace all " with "" 
  	//make rule?
	$product->attributes['link'] = str_replace('https://','http://',$product->attributes['link']);
	$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);
		
	return parent::formatProduct($product);
	}



}