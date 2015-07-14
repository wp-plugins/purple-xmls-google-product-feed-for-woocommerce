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
		//$this->addAttributeMapping('action', 'Action(SiteID=US|Country=US|Currency=USD|Version=745)');
		//Action(SiteID=UK|Country=GB|Currency=GBP|Version=745)
		//*Action(SiteID=US|Country=US|Currency=USD|Version=745)
		$this->addAttributeMapping('Action', 'Action',true,true); //should select from list of valid values
		$this->addAttributeMapping('category', 'Category',true,true);
		$this->addAttributeMapping('title', 'Title',true,true);
		$this->addAttributeMapping('description', 'Description',true,true);			
		$this->addAttributeMapping('ebay_images', 'PicURL',true);
		$this->addAttributeMapping('price', 'StartPrice',true,true);
		$this->addAttributeMapping('quantity', 'Quantity',true,true);	

		$this->addAttributeMapping('ConditionID', 'ConditionID',true,true); //default: 1000 (new)
		$this->addAttributeMapping('Format', 'Format',true,true);	//Auction, FixedPrice
		$this->addAttributeMapping('Duration', 'Duration',true,true); //5,7,10,GTC
		
		//If you use the PostalCode field, do not use the Location field.The location will be derived from the postal code value.
		//state and country where item is located
		$this->addAttributeMapping('Location', 'Location',true,false); //location of the item
		$this->addAttributeMapping('PostalCode', 'PostalCode',true,true); //state and country where item is located

		//brand and MPN must be used together
		$this->addAttributeMapping('brand', 'C:Brand',true);
		$this->addAttributeMapping('sku', 'C:MPN');
		$this->addAttributeMapping('', 'C:Color');
		$this->addAttributeMapping('', 'C:Size');
		$this->addAttributeMapping('', 'C:Style');
		$this->addAttributeMapping('', 'C:EAN');
		$this->addAttributeMapping('', 'C:UPC');

/** Returns **/
		$this->addAttributeMapping('ReturnsAcceptedOption', 'ReturnsAcceptedOption',true,true); //ReturnsAccepted
		$this->addAttributeMapping('ReturnsWithinOption', 'ReturnsWithinOption',true,false); //Days_14, Days_30: length of time buyer has in which to notify you of their intent to return an item
		
/** Shipping **/
		$this->addAttributeMapping('ShippingType', 'ShippingType',true,true); //Calculated, Flat, Freight 		
		$this->addAttributeMapping('ShippingService-1:Option', 'ShippingService-1:Option',true); //UPSGround
		$this->addAttributeMapping('ShippingService-1:Cost', 'ShippingService-1:Cost',true); //UPSGround, do not use for calculated
		$this->addAttributeMapping('ShippingService-1:AdditionalCost', 'ShippingService-1:AdditionalCost',true); //UPSGround, do not use for calculated
		//aka handling time; max # business days you take to prepare an item for shipment to a domestic buyer once you receive a cleared payment
		$this->addAttributeMapping('DispatchTimeMax', 'DispatchTimeMax',true,true); 
		
		//if ShippingType is Calculated (US only)
		$this->addAttributeMapping('OriginatingPostalCode', 'OriginatingPostalCode',true); //ZIP code from which the item will be shipped
		$this->addAttributeMapping('WeightMajor', 'WeightMajor'); //lbs or kg
		$this->addAttributeMapping('WeightMinor', 'WeightMinor'); //oz or hundeths of kg
		$this->addAttributeMapping('weight_unit', 'WeightUnit'); //lb or kg

/** Payment **/
		$this->addAttributeMapping('PayPalAccepted', 'PayPalAccepted');	
		$this->addAttributeMapping('PayPalEmailAddress', 'PayPalEmailAddress',true);	
		$this->addAttributeMapping('AmEx', 'AmEx'); //1 or 0
		$this->addAttributeMapping('Discover', 'Discover');
		$this->addAttributeMapping('VisaMastercard', 'VisaMastercard');

		//category2 available. If category2 is used, the user may be charged additional insertion fees
		$this->addAttributeMapping('Category2', 'Category2',true);

/** Business Policies **/
		$this->addAttributeMapping('PaymentProfileName', 'PaymentProfileName',true);
		$this->addAttributeMapping('ReturnProfileName', 'ReturnProfileName',true);
		$this->addAttributeMapping('ShippingProfileName', 'ShippingProfileName',true);

		$this->use_business_policy = false;
		
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addRule('price_rounding','pricerounding');	
		//$this->addRule( 'csv_standard', 'CSVStandard',array('title','80') ); 
		$this->addRule( 'substr','substr', array('title','0','80',true) ); //127 length
		//$this->addRule( 'csv_standard', 'CSVStandard',array('description') ); 

	}

	function formatProduct($product) {

		if ($product->attributes['isVariation']) {
			//Not used in original code
			//$variantUPC = rand();
			//$variantMfr = rand();
		}
		//Prepare
		//$product->attributes['Action'] = "VerifyAdd";
		$category = explode(":", $this->current_category);
		if (isset($category[1]))
			$product->attributes['category'] = trim($category[1]);
		else
			$product->attributes['category'] = 'no eBay category selected';
			
//set default quantity
		if ( !isset($product->attributes['quantity']) ) {
			$product->attributes['quantity'] = $product->attributes['stock_quantity'];
		}		
//set default condition
		if ( !isset($product->attributes['ConditionID']) ) {
			$product->attributes['ConditionID'] = '1000'; //1000=new, 3000=used
		}
//set defualt duration to GTC
		if ( !isset($product->attributes['Duration']) ) {
			$product->attributes['Duration'] = 'GTC'; //5,7,10,GTC
		}
//set Format to FixedPrice
		if ( !isset($product->attributes['Format']) ) {
				$product->attributes['Format'] = "FixedPrice";
		}
//featured image / multiple images		
		$product->attributes['ebay_images'] = $product->attributes['feature_imgurl'];
		//$product->attributes['feature_imgurl']= str_replace('https://','http://',$product->attributes['feature_imgurl']);
		if ( $this->allow_additional_images && (count($product->imgurls) > 0) ) {
	 		$product->attributes['additional_images'] = implode('|', $product->imgurls); 
			$product->attributes['ebay_images'] = $product->attributes['ebay_images'] . '|' . $product->attributes['additional_images'];
		}
//Business Policy
		if ( $this->use_business_policy )
		{
			if ( isset($this->payment_name) )
				$product->attributes['PaymentProfileName'] = $this->payment_name;
			if ( isset($this->return_name) )
				$product->attributes['ReturnProfileName'] = $this->return_name;
			if ( isset($this->shipping_name) )
				$product->attributes['ShippingProfileName'] = $this->shipping_name;			
			$product->attributes['weight_unit'] = ''; //weight unit not required for business policies
		}
		else 
		{			
			if ( !isset($product->attributes['ReturnsAcceptedOption']) ) {
				$product->attributes['ReturnsAcceptedOption'] = 'ReturnsAccepted';
			}
					
			$shippingWeight = $product->attributes['weight'];
			$shippingTypeCalculated = '';
			if ( isset($product->attributes['ShippingType']) )
				$shippingTypeCalculated = strtolower($product->attributes['ShippingType']);
			if ( isset($shippingWeight) && $shippingTypeCalculated =='calculated') 
			{
				
				if ( $this->weight_unit == 'kg' || $this->weight_unit == 'lbs' ) {
					$shippingWeight = number_format($shippingWeight, 2, '.', '');			
					$shippingWeight_int = explode('.', $shippingWeight, 2);
				}			
				//WeightMajor: whole number portion of the shipping weight. 3 = 3lbs			
				//WeightMinor: Imperial: ounces; Metric: reflect hundreths of kg
				switch ( $this->weight_unit  )
				{
					case 'lbs':
						$product->attributes['WeightMajor'] = $shippingWeight_int[0];	
						$product->attributes['WeightMinor'] = round( ($shippingWeight-$shippingWeight_int[0])*16, 0); //1 lb = 16 oz
						$product->attributes['weight_unit'] = 'lbs';
						break;
					case 'kg':
						$product->attributes['WeightMajor'] = $shippingWeight_int[0];	
						$product->attributes['WeightMinor'] = round( ($shippingWeight-$shippingWeight_int[0])*100, 0);
						$product->attributes['weight_unit'] = 'kg';
						break;
					case 'g':
						$product->attributes['WeightMajor'] = '';
						$product->attributes['WeightMinor'] = round($shippingWeight*0.1, 0);
						$product->attributes['weight_unit'] = 'g';
						break;
					case 'oz':
						$product->attributes['WeightMajor'] = '';	
						$product->attributes['WeightMinor'] = round($shippingWeight, 0);
						$product->attributes['weight_unit'] = 'oz';
						break;
					default:
						$product->attributes['WeightMajor'] = '';
						$product->attributes['WeightMinor'] = ''; //$shippingWeight_int[1];	
						$product->attributes['weight_unit'] = $this->weight_unit;		
				}		
			}
			else
				$product->attributes['weight_unit'] = ''; //weight unit not required for flat-rate shipping
		}// else not using business policy

		return parent::formatProduct($product);
	}
	
}

?>