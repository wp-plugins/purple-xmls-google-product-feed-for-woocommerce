<?php

	/********************************************************************
	Version 3.0
	A Become (Europe) Feed
		Copyright 2015 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PBecomeFeed extends PCSVFeedEx{

	function __construct () {

		parent::__construct();
		$this->providerName = 'Become';
		$this->providerNameL = 'become';
		$this->fileformat = 'csv';
		$this->fieldDelimiter = ",";
		$this->fields = array();
		
//Required fields (7)
		$this->addAttributeMapping('current_category', 'merchant-category', true,true);
		$this->addAttributeMapping('id', 'offer-id', true,true);
		$this->addAttributeMapping('title', 'label',true,true); //127 char limit
		$this->addAttributeMapping('link', 'offer-url',true,true);
		$this->addAttributeMapping('feature_imgurl', 'image-url',true,true);
		$this->addAttributeMapping('description', 'description', true,true); //2048 char limit
		$this->addAttributeMapping('sale_price', 'prices',true,true);		
//highly recommended			
		$this->addAttributeMapping('ean', 'product-id',true);
		$this->addAttributeMapping('delivery_charge', 'delivery-charge',true,true); //GBP4.50
//Optional
		$this->addAttributeMapping('brand', 'Brand', true);
		$this->addAttributeMapping('', 'delivery-period'); //delivery time
		$this->addAttributeMapping('delivery_period_text', 'delivery-period-text'); //25 hours, 3-5 days
		$this->addAttributeMapping('', 'mfpn');
		$this->addAttributeMapping('regular_price', 'old-prices'); //former product price
		
//Formatting	
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule( 'description', 'description',array('max_length=2048','strict') );
		//$this->addRule('csv_standard', 'CSVStandard',array('title','127')); 
		//$this->addRule('csv_standard', 'CSVStandard',array('description')); 
		$this->addRule( 'substr','substr', array('title','0','127',true) ); //127 length

	}

	function formatProduct($product) {

		if ( isset($product->attributes['delivery_charge']) ) {
			$product->attributes['delivery_charge'] = $this->currency.$product->attributes['delivery_charge'];			
		}
		elseif ( isset($this->delivery_charge) ) {
			$product->attributes['delivery_charge'] = $this->currency.$this->delivery_charge;
		}
		else {
			$this->addErrorMessage(21000, 'Recommended field "Delivery charge" not configured.');
			//$this->productCount--; //not required as delivery_charge is not mandatory (just highly recommended)
		}
		//Prepare input:
		$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);
		
		return parent::formatProduct($product);
	}

}

?>