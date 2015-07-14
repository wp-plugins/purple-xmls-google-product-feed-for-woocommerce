<?php

	/********************************************************************
	Version 3.0
	A Nextag Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
		2014-09 Moved to Attribute mapping v3

	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PNextagFeed extends PCSVFeedEx{

	function __construct () {

		parent::__construct();
		$this->providerName = 'Nextag';
		$this->providerNameL = 'nextag';
		$this->fileformat = 'csv';
		$this->fieldDelimiter = ",";
		$this->fields = array();
		//$this->fields = array("UPC", "Product Name", "Description", "Price", "Click-out URL", "Category", "Image URL", "Stock Status", "List Price");

		//identifiers: 
		//must provide one of:
		$this->addAttributeMapping('brand', 'Manufacturer',true,true); //manufacturer and mpn must be used together
		$this->addAttributeMapping('sku', 'Manufacturer Part Number',true,true); //you can use your internal MPN (your internal product reference number; not preferred) 
		$this->addAttributeMapping('', 'UPC',true,false);
		$this->addAttributeMapping('', 'ISBN',true,false);		
		$this->addAttributeMapping('', 'MUZE ID',true,false);
		$this->addAttributeMapping('', 'Distributor ID',true,false);
		//required
		$this->addAttributeMapping('title', 'Product Name',true,true); //manufacturer added automatically (on nextag's side)
		$this->addAttributeMapping('description', 'Description', true,true);
		$this->addAttributeMapping('price', 'Price',true,true);		
		$this->addAttributeMapping('link', 'Click-out URL',true,true);
		$this->addAttributeMapping('current_category', 'Category', true,true);
		//recommended
		$this->addAttributeMapping('feature_imgurl', 'Image URL');
		$this->addAttributeMapping('stock_status', 'Stock Status');
		$this->addAttributeMapping('condition', 'Product Condition');
		$this->addAttributeMapping('', 'Ground Shipping',true);
		$this->addAttributeMapping('weight', 'Weight');
		$this->addAttributeMapping('', 'Promo Text',true);
		$this->addAttributeMapping('', 'Promo Text Start', true);
		$this->addAttributeMapping('', 'Promo Text End',true);
		//Users may add more with mapAttribute command.
	
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule( 'description', 'description',array('max_length=500','strict') );

	}

	function formatProduct($product) {

		//Prepare input:
		$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'In Stock';
		else
			$product->attributes['stock_status'] = 'Out Of Stock';

		//Allowed condition values: New, Open Box, OEM, Refurbished, Pre-Owned, Like New, Good, Very Good, Acceptable 
		return parent::formatProduct($product);
	}

}

?>