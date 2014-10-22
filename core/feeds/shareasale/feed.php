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
		$this->descriptionStrict = true;
		$this->stripHTML = true;

		//Create some attributes (Mapping 3.0)
		$this->addAttributeMapping('sku', 'SKU');
		$this->addAttributeMapping('title', 'Name', true);
		$this->addAttributeMapping('link', 'URL to product', true);
		$this->addAttributeMapping('regular_price', 'Price');
		$this->addAttributeMapping('retail_price', 'Retail Price');
		$this->addAttributeMapping('feature_imgurl', 'URL to image');
		$this->addAttributeMapping('other_image_url_0', 'URL to thumbnail image');
		$this->addAttributeMapping('commission', 'Commission');
		$this->addAttributeMapping('category', 'Category');
		$this->addAttributeMapping('subcategory', 'SubCategory');
		$this->addAttributeMapping('description', 'Description', true);
		$this->addAttributeMapping('searchterms', 'SearchTerms', true);
		$this->addAttributeMapping('status', 'Status');
		$this->addAttributeMapping('merchant_id', 'Your MerchantID');
		$this->addAttributeMapping('custom1', 'Custom 1');
		$this->addAttributeMapping('custom2', 'Custom 2');
		$this->addAttributeMapping('custom3', 'Custom 3');
		$this->addAttributeMapping('custom4', 'Custom 4');
		$this->addAttributeMapping('custom5', 'Custom 5');
		$this->addAttributeMapping('brand', 'Manufacturer');
		$this->addAttributeMapping('partnumber', 'PartNumber');
		$this->addAttributeMapping('localCategory', 'MerchantCategory');
		$this->addAttributeMapping('localsubcategory', 'MerchantSubcategory');
		$this->addAttributeMapping('description_short', 'ShortDescription');
		$this->addAttributeMapping('isbn', 'ISBN');
		$this->addAttributeMapping('upc', 'UPC');
		
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
			$product->attributes['stock_status'] = 'instock';
		else
			$product->attributes['stock_status'] = 'soldout';

		$cat = explode(':', $product->attributes['category']);
		if (count($cat) < 2)
			$product->attributes['category'] = '';
		else
			$product->attributes['category'] = (int) $cat[0];

		return parent::formatProduct($product);
		
	}

}

?>