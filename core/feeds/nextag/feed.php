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
		$this->descriptionStrict = true;
		$this->fields = array();
		//$this->fields = array("UPC", "Product Name", "Description", "Price", "Click-out URL", "Category", "Image URL", "Stock Status", "List Price");

		$this->addAttributeMapping('id', 'UPC');
		$this->addAttributeMapping('title', 'Product Name');
		$this->addAttributeMapping('description', 'Description', true);
		$this->addAttributeMapping('regular_price', 'Price');
		$this->addAttributeMapping('sale_price', 'List Price');
		$this->addAttributeMapping('link', 'Click-out URL');
		$this->addAttributeMapping('category', 'Category', true);
		$this->addAttributeMapping('feature_imgurl', 'Image URL');
		$this->addAttributeMapping('stock_status', 'Stock Status');

	}

	function formatProduct($product) {

		//cheat: Remap these
		$product->attributes['category'] = $this->current_category;
		$product->attributes['description'] = $product->description;		
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;

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