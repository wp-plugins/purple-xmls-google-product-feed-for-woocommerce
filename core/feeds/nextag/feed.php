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
		$this->fields = array();
		//$this->fields = array("UPC", "Product Name", "Description", "Price", "Click-out URL", "Category", "Image URL", "Stock Status", "List Price");

		$this->addAttributeMapping('', 'Manufacturer',true,true);
		//identifiers: must provide one of:
		$this->addAttributeMapping('', 'Manufacturer Part Number',true,true);
		$this->addAttributeMapping('', 'UPC',true,true);
		$this->addAttributeMapping('', 'ISBN',true,true);
		$this->addAttributeMapping('', 'MUZE ID',true,true);
		$this->addAttributeMapping('', 'Distributor ID',true,true);
		//required
		$this->addAttributeMapping('title', 'Product Name',true,true);
		$this->addAttributeMapping('description', 'Description', true,true);
		$this->addAttributeMapping('regular_price', 'Price',true,true);		
		$this->addAttributeMapping('link', 'Click-out URL',true,true);
		$this->addAttributeMapping('category', 'Category', true,true);
		//recommended
		$this->addAttributeMapping('feature_imgurl', 'Image URL');
		$this->addAttributeMapping('stock_status', 'Stock Status');
		$this->addAttributeMapping('condition', 'Product Condition');
		$this->addAttributeMapping('', 'Ground Shipping');
		$this->addAttributeMapping('weight', 'Weight');
		//$this->addAttributeMapping('', 'List Price'); //MSRP
		//$this->addAttributeMapping('', 'List Price'); 
		//$this->addAttributeMapping('', 'List Price'); 
		

	}

	function formatProduct($product) {

		//Prepare input:
		if (strlen($product->attributes['description']) > 500)
			$product->attributes['description'] = substr($product->attributes['description'], 0, 500);

		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';
		if ($product->attributes['has_sale_price'])
			$product->attributes['sale_price'] = $product->attributes['sale_price'];
		else
			$product->attributes['sale_price'] = $product->attributes['regular_price'];

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'In Stock';
		else
			$product->attributes['stock_status'] = 'Out Of Stock';

		//Validity: Description > 500 = error

		return parent::formatProduct($product);
	}

}

?>