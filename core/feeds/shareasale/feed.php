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
		$this->fields = array("SKU","Name","URL to product","Price","Retail Price","URL to image",
			"URL to thumbnail image","Commission","Category","SubCategory","Description",
			"SearchTerms","Status","Your MerchantID", "Custom 1", "Custom 2", "Custom 3",
			"Custom 4", "Custom 5","Manufacturer","PartNumber","MerchantCategory",
			"MerchantSubcategory","ShortDescription","ISBN","UPC");
		$this->fieldDelimiter = ',';
		$this->stripHTML = true;

		//Create some attributes (Mapping 3.0). All columns must be represented in the datafeed & in the specified orders
		$this->addAttributeMapping('sku', 'SKU',true,true); //1
		$this->addAttributeMapping('title', 'Name', true,false);
		$this->addAttributeMapping('link', 'URL', true,true);
		$this->addAttributeMapping('regular_price', 'Price',true,true);
		$this->addAttributeMapping('retail_price', 'Retail Price',false,false); //5
		$this->addAttributeMapping('feature_imgurl', 'Full Image');
		$this->addAttributeMapping('other_image_url_0', 'Thumbnail Image');
		$this->addAttributeMapping('commission', 'Commission');
		$this->addAttributeMapping('category', 'Category',true,true);
		$this->addAttributeMapping('subcategory', 'SubCategory',true,true); //10
		$this->addAttributeMapping('description', 'Description', true);
		$this->addAttributeMapping('searchterms', 'SearchTerms', true);
		$this->addAttributeMapping('status', 'Status');
		$this->addAttributeMapping('merchant_id', 'Your MerchantID',true,true); 
		$this->addAttributeMapping('custom1', 'Custom 1'); //15
		$this->addAttributeMapping('custom2', 'Custom 2');
		$this->addAttributeMapping('custom3', 'Custom 3');
		$this->addAttributeMapping('custom4', 'Custom 4');
		$this->addAttributeMapping('custom5', 'Custom 5');
		$this->addAttributeMapping('brand', 'Manufacturer'); //20
		$this->addAttributeMapping('partnumber', 'PartNumber');
		$this->addAttributeMapping('localCategory', 'MerchantCategory');
		$this->addAttributeMapping('localsubcategory', 'MerchantSubcategory');
		$this->addAttributeMapping('description_short', 'ShortDescription');
		$this->addAttributeMapping('isbn', 'ISBN');
		$this->addAttributeMapping('upc', 'UPC');
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
	}

	function formatProduct($product) {

		//********************************************************************
		//Pre-flight
		//********************************************************************
		if (!isset($this->merchant_id)) {
			$this->addErrorMessage(9000, 'MerchantID not configured. Need advanced command: $merchant-id = ....', true);
			$this->addErrorMessage(9001, 'You can find your Merchant ID in the top left corner of the ShareASale web interface for advertisers/merchants (login required)', true);
			$this->merchant_id = '';
		}

		//********************************************************************
		//Prepare
		//********************************************************************
		$product->attributes['merchant_id'] = $this->merchant_id;
		$product->attributes['description_short'] = $product->description_short;

		$product->attributes['retail_price'] = $product->attributes['regular_price'];
		if ($product->attributes['has_sale_price'])
			$product->attributes['retail_price'] = $product->attributes['sale_price'];

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['status'] = 'instock';
		else
			$product->attributes['status'] = 'soldout';

		$cat = explode(':', $this->current_category);
		if (count($cat) < 2)
			$product->attributes['category'] = '';
		else
			$product->attributes['category'] = (int)$cat[0];

		return parent::formatProduct($product);
		
	}

}

?>