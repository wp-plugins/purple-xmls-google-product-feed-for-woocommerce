<?php

	/********************************************************************
	Version 3.0
	A Become (Europe) Feed
		Copyright 2015 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PHardwareInfoFeed extends PCSVFeedEx{

	function __construct () {

		parent::__construct();
		$this->providerName = 'HardwareInfo';
		$this->providerNameL = 'HardwareInfo';
		$this->fileformat = 'csv';
		$this->fieldDelimiter = ",";
		$this->fields = array();
		
		//* = At least of: 'Vendor code / MPN' or 'EAN code' is required
//Fields		
		$this->addAttributeMapping('sku', 'Vendor code / MPN', true,true); //Vendor code / MPN*
		$this->addAttributeMapping('id', 'Internal item number', true,false); //Internal item number	

		$this->addAttributeMapping('brand', 'Brand', true,false); //Brand name 
		$this->addAttributeMapping('title', 'Product name', true,false); //Product name  
		$this->addAttributeMapping('price', 'Price', true,false); //Price

		$this->addAttributeMapping('Shipping_Cost', 'Shipping cost', true,false); //Shipping cost ** 
		$this->addAttributeMapping('item_group_id', 'Product group', true,false); //Product group ** 
		$this->addAttributeMapping('link', 'Deeplink', true,false); //Direct URL 
		$this->addAttributeMapping('feature_imgurl', 'Product image URL', true,false); //Link to product image **  
		$this->addAttributeMapping('', 'Specs URL', true,false); //Link to product specifications (e.g. at  manufacturer’s site) ** 

		$this->addAttributeMapping('', 'EAN code', true,true); //EAN code * 
		$this->addAttributeMapping('stock_quantity', 'Own inventory', true,false); //Inventory status own warehouse (number or  boolean) ** 
		$this->addAttributeMapping('', 'Supplier inventory', true,false); //Inventory status of your supplier’s warehouse  (number or boolean) ** 
		$this->addAttributeMapping('Delivery_Time', 'Delivery time', true,false); //Expected delivery time (text) **  
		$this->addAttributeMapping('', '24HOrder time', true,false); //Latest ordering time where buyers can be guaranteed next day delivery
		
//Formatting	
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree

	}

	function formatProduct($product) {

		if ( !isset($this->delivery_charge) ) {
			$this->addErrorMessage(22000, 'Recommended field "Delivery Time" not configured.');
			//$this->productCount--; //not required as delivery_charge is not mandatory (just highly recommended)
		}
		else {
			$product->attributes['Delivery_Time'] = $this->currency.$this->delivery_charge;
		}
		//Prepare input:
		//$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);
		
		return parent::formatProduct($product);
	}

}

?>