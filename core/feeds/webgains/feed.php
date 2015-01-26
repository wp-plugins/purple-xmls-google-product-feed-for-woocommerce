<?php

	/********************************************************************
	Version 3.0
	A Newegg Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2014-11-12
		2014-09 Moved to Mapping v3
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PWebgainsFeed extends PCSVFeedEx {

	function __construct () {
		parent::__construct();
		$this->providerName = 'Webgains';
		$this->providerNameL = 'Webgains';
		$this->fileformat = 'csv';
		$this->fieldDelimiter = ",";
		$this->fields = array();

		//Mandatory Fields
		$this->addAttributeMapping('title', 'Name',true,true); //name of the product
		$this->addAttributeMapping('link', 'Deeplink',true,true); //URL where the product is located on your website
		$this->addAttributeMapping('Category', 'Category',true,true); 
			$this->addAttributeDefault('Category', 'none','PCategoryTree'); //store's local category
		$this->addAttributeMapping('Price', 'Price',true,true);
		$this->addAttributeMapping('id', 'ProductID',true,true); //unique SKU or woo id..
		$this->addAttributeMapping('description', 'Description', true,true);
		$this->addAttributeMapping('feature_imgurl', 'Image_URL',true,true);
		$this->addAttributeMapping('Delivery_time', 'Delivery_time',true,true);
		$this->addAttributeMapping('Delivery_Cost', 'Delivery_Cost',true,true); 

		//Non-mandatory fields
		$this->addAttributeMapping('', 'Extra_price_field'); 
		$this->addAttributeMapping('', 'Thumbnail_image_URL'); 
		$this->addAttributeMapping('', 'Manufacturer'); 
		$this->addAttributeMapping('brand', 'Brand');
		$this->addAttributeMapping('', 'Related_products_IDs');
		$this->addAttributeMapping('', 'Promotions');
		$this->addAttributeMapping('stock_status', 'Availability'); 
		$this->addAttributeMapping('', 'Best_sellers');
	
	}

    function formatProduct($product) {

		//cheat: Remap these
		if (strlen($product->attributes['regular_price']) == 0)
		$product->attributes['regular_price'] = '0.00';
		$product->attributes['Price'] = $product->attributes['regular_price'];
		if ($product->attributes['has_sale_price'])
			$product->attributes['Price'] = $product->attributes['sale_price'];
		
			
  		$productDescription = str_replace('"','""',$product->attributes['description']);		
		$product->attributes['description'] = trim($productDescription);	
		$product->attributes['Image_URL'] = $product->attributes['feature_imgurl'];

		$product->attributes['Brand'] = $product->attributes['brand'];
		
		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'in stock';
		else
			$product->attributes['stock_status'] = '0';
	

		return parent::formatProduct($product);

  }

}

?>