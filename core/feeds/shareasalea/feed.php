<?php

  /********************************************************************
  Version 2.0
    An Amazon Feed (Product Ads)
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PShareASaleAFeed extends PCSVFeed {

	function __construct () {

		parent::__construct();
		$this->providerName = 'ShareASaleA';
		$this->providerNameL = 'shareasalea';
		$this->fileformat = 'csv';
		$this->fields = explode('|', 'Custom1|Custom2|Custom3|Custom4|Custom5|Manufacturer|PartNumber|MerchantCategory|MerchantSubcategory|ShortDescription|ISBN|UPC');
		$this->fieldDelimiter = '|';

		//Create some attributes (Mapping 3.0)
		$this->addAttributeMapping('id', 'ProductID');
		$this->addAttributeMapping('title', 'Name');
		$this->addAttributeMapping('merchant_id', 'MerchantID');
		$this->addAttributeMapping('merchant', 'Merchant');
		$this->addAttributeMapping('link', 'Link');
		$this->addAttributeMapping('other_image_url_0', 'Thumbnail');
		$this->addAttributeMapping('feature_imgurl', 'BigImage');
		$this->addAttributeMapping('regular_price', 'Price');
		$this->addAttributeMapping('regular_price', 'RetailPrice');
		$this->addAttributeMapping('category', 'Category');
		$this->addAttributeMapping('subcategory', 'SubCategory');
		$this->addAttributeMapping('description', 'Description');
		$this->addAttributeMapping('brand', 'Manufacturer');
		$this->addAttributeMapping('sku', 'PartNumber');
		$this->addAttributeMapping('merchant_category', 'MerchantCategory');
		$this->addAttributeMapping('merchant_subcategory', 'MerchantSubcategory');
		$this->addAttributeMapping('description_short', 'ShortDescription');
		$this->addAttributeMapping('isbn', 'ISBN');
		$this->addAttributeMapping('upc', 'UPC');
		$this->addAttributeMapping('last_updated', 'LastUpdated');
		$this->addAttributeMapping('status', 'status');
		
	}

	function formatProduct($product) {

		//********************************************************************
		//Pre-flight
		//********************************************************************
		if (!isset($this->merchant_id)) {
			$this->addErrorMessage(9000, 'MerchantID not configured. Need advanced command: $merchant-id = ....');
			$this->merchant_id = '';
		}
		if (!isset($this->merchant)) {
			$this->addErrorMessage(9001, 'Merchant not configured. Need advanced command: $merchant = ....');
			$this->merchant = '';
		}

		//********************************************************************
		//Prepare
		//********************************************************************
		$product->attributes['merchant_id'] = $this->merchant_id;
		$product->attributes['merchant'] = $this->merchant;
		$product->attributes['description'] = $this->description_long;
		$product->attributes['description_short'] = $this->description_short;

		return parent::formatProduct($product);
		
	}

}

?>