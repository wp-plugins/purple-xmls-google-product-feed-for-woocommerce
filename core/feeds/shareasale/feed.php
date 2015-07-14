<?php

  /********************************************************************
  Version 2.0
    ShareASale
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PShareASaleFeed extends PCSVFeedEx {

	function __construct () {

		parent::__construct();
		$this->providerName = 'ShareASale';
		$this->providerNameL = 'shareasale';
		$this->fileformat = 'csv';
		//$this->fields = array();
		$this->fieldDelimiter = ',';
		$this->stripHTML = true;

		//Create some attributes (Mapping 3.0). All columns must be represented in the datafeed & in the specified orders
		$this->addAttributeMapping('sku', 'SKU',true,true); //1
		$this->addAttributeMapping('title', 'Name', true,false);
		$this->addAttributeMapping('link', 'URL', true,true);
		$this->addAttributeMapping('price', 'Price',true,true);
		$this->addAttributeMapping('regular_price', 'Retail Price',false,false); //5
		$this->addAttributeMapping('feature_imgurl', 'Full Image');
		$this->addAttributeMapping('other_image_url_0', 'Thumbnail Image');
		$this->addAttributeMapping('', 'Commission');
		//Dollar amount of product commission (do not enter in a commission percentage). 
		//This will not affect the tracking or the actual commission rewarded on the sale, 
		//and is only for a quick reference for the affiliate.
		$this->addAttributeMapping('current_category', 'Category',true,true);
//Missing ShareASale SubCategories
		$this->addAttributeMapping('subcategory', 'SubCategory',true,true); //10
		$this->addAttributeMapping('description', 'Description', true);
		$this->addAttributeMapping('', 'SearchTerms', true); //comma separated list
		$this->addAttributeMapping('stock_status', 'Status');
		$this->addAttributeMapping('merchant_id', 'Your MerchantID',true,true); 
		$this->addAttributeMapping('', 'Custom 1'); //15
		$this->addAttributeMapping('', 'Custom 2');
		$this->addAttributeMapping('', 'Custom 3');
		$this->addAttributeMapping('', 'Custom 4');
		$this->addAttributeMapping('', 'Custom 5');
		$this->addAttributeMapping('brand', 'Manufacturer'); //20
		$this->addAttributeMapping('', 'PartNumber');
		$this->addAttributeMapping('localCategory', 'MerchantCategory');
		$this->addAttributeMapping('', 'MerchantSubcategory'); 
		$this->addAttributeMapping('description_short', 'ShortDescription',true);
		$this->addAttributeMapping('', 'ISBN');
		$this->addAttributeMapping('', 'UPC');
	// these were found from exampledatafeed csv file	
		$this->addAttributeMapping('', 'CrossSell');
		$this->addAttributeMapping('', 'MerchantGroup');
		$this->addAttributeMapping('', 'MerchantSubgroup');
		$this->addAttributeMapping('', 'CompatibleWith');
		$this->addAttributeMapping('', 'CompareTo');
		$this->addAttributeMapping('', 'QuantityDiscount');
		$this->addAttributeMapping('', 'Bestseller');
		$this->addAttributeMapping('', 'AddToCartURL');
		$this->addAttributeMapping('', 'ReviewsRSSURL');
		$this->addAttributeMapping('', 'Option1');
		$this->addAttributeMapping('', 'Option2');
		$this->addAttributeMapping('', 'Option3');
		$this->addAttributeMapping('', 'Option4');
		$this->addAttributeMapping('', 'Option5');
		$this->addAttributeMapping('', 'customCommissions');
		$this->addAttributeMapping('', 'customCommissionIsFlatRate');
		$this->addAttributeMapping('', 'customCommissionNewCustomerMultiplier');
		$this->addAttributeMapping('', 'mobileURL');
		$this->addAttributeMapping('', 'mobileImage');
		$this->addAttributeMapping('', 'mobileThumbnail');
		$this->addAttributeMapping('', 'ReservedForFutureUse');
		$this->addAttributeMapping('', 'ReservedForFutureUse');
		$this->addAttributeMapping('', 'ReservedForFutureUse');
		$this->addAttributeMapping('', 'ReservedForFutureUse');	
	
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree		
		$this->addRule('price_rounding','pricerounding'); //2 decimals	 
	
	}

	function formatProduct($product) {

		//********************************************************************
		//Prepare
		//********************************************************************
		if ( isset($this->merchant_id) )
			$product->attributes['merchant_id'] = $this->merchant_id;
		//$product->attributes['description_short'] = $product->description_short;

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'instock';
		else
			$product->attributes['stock_status'] = 'soldout';

		$cat = explode(':', $this->current_category);
		if (count($cat) < 2)
			$product->attributes['current_category'] = '';
		else
			$product->attributes['current_category'] = (int)$cat[0];

		//********************************************************************
		//Post-flight
		//********************************************************************
		if ( !isset($this->merchant_id) || strlen($product->attributes['merchant_id']) == 0 ) {
			$this->addErrorMessage(9000, 'MerchantID not configured. Need advanced command: $merchant-id = ....', true);
			//$this->addErrorMessage(9001, 'You can find your Merchant ID in the top left corner of the ShareASale web interface for advertisers/merchants (login required)', true);
			$this->productCount--;
			$this->merchant_id = '';
		}
		return parent::formatProduct($product);
		
	}

}

?>