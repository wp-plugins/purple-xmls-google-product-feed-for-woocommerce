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
		$this->addAttributeMapping('action', 'Action(SiteID=US|Country=US|Currency=USD|Version=745)');
		$this->addAttributeMapping('category1', 'Category');
		$this->addAttributeMapping('title', 'Title',true);
		$this->addAttributeMapping('description', 'Description',true);
		//$this->addAttributeMapping('productname', 'ProductName');
		$this->addAttributeMapping('conditionid', 'ConditionID');
		$this->addAttributeMapping('PicURL', 'PicURL');
		$this->addAttributeMapping('quantity', 'Quantity');
		$this->addAttributeMapping('format', 'Format');	//Auction, FixedPrice
		$this->addAttributeMapping('startprice', 'StartPrice');	//
		$this->addAttributeMapping('duration', 'Duration');
		$this->addAttributeMapping('location', 'Location',true); //state and country where item is located
						
	}

	function formatProduct($product) {

		if ($product->isVariable) {
			//Not used in original code
			//$variantUPC = rand();
			//$variantMfr = rand();
		}
		//Prepare
		//$product->attributes['id'] = $product->attributes['id'] . $variantUPC; //Not used in original code
		//$product->attributes['mfr_part_number'] = $product->attributes['id'] . $variantMfr;
		//cheat
		$category = explode(":", $product->attributes['current_category']);
		if (isset($category[1]))
			$product->attributes['category'] = $category[1];
		else
			$product->attributes['category'] = '';
		//$product->attributes['category'] = $this->current_category;
		$product->attributes['description'] = $product->description;		
		$product->attributes['PicURL'] = $product->feature_imgurl;
		
		//List the product price in US dollars, without a $ sign, commas, text, or quotation marks.
		$product->attributes['startprice'] = $product->attributes['regular_price'];
		if ( ($product->attributes['has_sale_price']) && ($product->attributes['sale_price'] != "") )
			$product->attributes['startprice'] = $product->attributes['sale_price'];

		return parent::formatProduct($product);
	}

}

?>