<?php

  /********************************************************************
  Version 2.0
    eBay Seller - File Exhange Base Template
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2014-14-10

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PeBaySellerFeed extends PCSVFeedEx 
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'eBaySeller';
		$this->providerNameL = 'eBay Seller';
		$this->fileformat = 'csv';
		$this->fields = array();
		//$this->fields = explode(',', 'Category,Title,Link,SKU,Price,Brand,Department,UPC,Image,Description,Manufacturer,Mfr part number,Other image-url1,Other image-url2,Other image-url3,Other image-url4,Other image-url5,Other image-url6,Other image-url7,Other image-url8,Weight,Shipping Cost,Shipping Weight');
		$this->fieldDelimiter = ",";

		//Create some attributes (Mapping 3.0)
		//Basic Template
		//$this->addAttributeMapping('action', 'Action(SiteID=US|Country=US|Currency=USD|Version=745)');
		//Action(SiteID=UK|Country=GB|Currency=GBP|Version=745)
		$this->addAttributeMapping('', 'Action',true,true); //should select from list of valid values
		$this->addAttributeMapping('Category', 'Category',true,true);
		$this->addAttributeMapping('Title', 'Title',true,true);
		$this->addAttributeMapping('Description', 'Description',true);
		//$this->addAttributeMapping('productname', 'ProductName');
		$this->addAttributeMapping('ConditionID', 'ConditionID',true,true);
		$this->addAttributeMapping('PicURL', 'PicURL',true);
		$this->addAttributeMapping('Quantity', 'Quantity',true,true);
		$this->addAttributeMapping('Format', 'Format',true,true);	//Auction, FixedPrice
		$this->addAttributeMapping('StartPrice', 'StartPrice',true,true);	
		$this->addAttributeMapping('duration', 'Duration',true,true); //5,7,10,GTC
		$this->addAttributeMapping('location', 'Location',true,true); //state and country where item is located
		$this->addAttributeMapping('', 'PostalCode',true,true); //state and country where item is located
		$this->addAttributeMapping('ReturnsAcceptedOption', 'ReturnsAcceptedOption',true,true); //ReturnsAccepted
		$this->addAttributeMapping('ShippingType', 'ShippingType',true,true); //Calculated, Flat, Freight 
		$this->addAttributeMapping('ShippingService-1:Option', 'ShippingService-1:Option'); //UPSGround
		$this->addAttributeMapping('ShippingService-1:Cost', 'ShippingService-1:Cost'); //UPSGround
		$this->addAttributeMapping('DispatchTimeMax', 'DispatchTimeMax',true,true); //max # business days you take to prepare an item for shipment to a domestic buyer
		$this->addAttributeMapping('PayPalAccepted', 'PayPalAccepted');	
		$this->addAttributeMapping('PayPalEmailAddress', 'PayPalEmailAddress');		
	}

	function formatProduct($product) {

		if ($product->attributes['isVariation']) {
			//Not used in original code
			//$variantUPC = rand();
			//$variantMfr = rand();
		}
		//Prepare
		//$product->attributes['id'] = $product->attributes['id'] . $variantUPC; //Not used in original code
		//$product->attributes['mfr_part_number'] = $product->attributes['id'] . $variantMfr;
		//cheat
		//$product->attributes['Action'] = "VerifyAdd";
		$category = explode(":", $this->current_category);
		if (isset($category[1]))
			$product->attributes['Category'] = trim($category[1]);
		else
			$product->attributes['Category'] = 'no_category_selected';
		//$product->attributes['category'] = $this->current_category;
		$productDescription = str_replace('"','""',$product->attributes['description']);		
		$product->attributes['Description'] = $productDescription;
		$product->attributes['Quantity'] = $product->attributes['stock_quantity'];
		//$product->attributes['Location'] = $product->attributes['woocommerce_default_country'];
		//ebayseller title cannot be greater than 80 chars
		$product->attributes['Title'] = (strlen($product->attributes['title']) > 80) ? substr($product->attributes['title'],0,80) : $product->attributes['title'];
		$product->attributes['PicURL'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);
		
		//List the product price in US dollars, without a $ sign, commas, text, or quotation marks.
		$product->attributes['StartPrice'] = $product->attributes['regular_price'];
		if ( ($product->attributes['has_sale_price']) && ($product->attributes['sale_price'] != "") )
			$product->attributes['StartPrice'] = $product->attributes['sale_price'];
		
		$product->attributes['ConditionID'] = '1000'; //1000=new, 3000=used
		$product->attributes['Format'] = "FixedPrice";
		$product->attributes['ReturnsAcceptedOption'] = 'ReturnsAccepted';

		if ( isset($product->attributes['weight']) ) {
			$product->attributes['WeightMajor'] = $product->attributes['weight'];
		}
		
		return parent::formatProduct($product);
	}

}

?>