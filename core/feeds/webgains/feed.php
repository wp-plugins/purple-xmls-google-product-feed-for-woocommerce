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
		$this->addAttributeMapping('local_category', 'Category',true,true); 
			$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category
		$this->addAttributeMapping('price', 'Price',true,true);
		$this->addAttributeMapping('sku', 'ProductID',true,true); //unique SKU or woo id..
		$this->addAttributeMapping('description', 'Description', true,true);
		$this->addAttributeMapping('feature_imgurl', 'Image_URL',true,true);
		$this->addAttributeMapping('Delivery_time', 'Delivery_time',true,true); //default
		$this->addAttributeMapping('Delivery_cost', 'Delivery_cost',true,true); //default

		//Non-mandatory fields
		$this->addAttributeMapping('', 'Extra_price_field'); 
		$this->addAttributeMapping('', 'Thumbnail_image_URL'); 
		$this->addAttributeMapping('', 'Manufacturer'); 
		$this->addAttributeMapping('brand', 'Brand');
		$this->addAttributeMapping('', 'Related_products_IDs');
		$this->addAttributeMapping('', 'Promotions');
		$this->addAttributeMapping('stock_status', 'Availability'); 
		$this->addAttributeMapping('', 'Best_sellers'); //integer

		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addRule('price_rounding','pricerounding'); //2 decimals
	
	}

    function formatProduct($product) {

		//cheat: Remap these
  		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'in stock';
		else
			$product->attributes['stock_status'] = '0';		

	
		//Cycle through required attributes. If missing, show error
		//$error_count = 0; //<-- No need... $this->addErrorMessage() already counts the error messages of matching IDs
		foreach($this->attributeMappings as $thisAttributeMapping) 
		{
			if ( $thisAttributeMapping->isRequired && ($thisAttributeMapping->mapTo == 'Delivery_time' || $thisAttributeMapping->mapTo == 'Delivery_cost') ) 
			{				
				if ( !isset($product->attributes[$thisAttributeMapping->attributeName]) || strlen($product->attributes[$thisAttributeMapping->attributeName]) == 0 )
				{
					$this->addErrorMessage(12000, 'Missing required: ' . $thisAttributeMapping->mapTo);			
					$this->productCount--;
					//return;
				}
				//$error_count++;
			}
		}
		return parent::formatProduct($product);
	
  }

}

?>