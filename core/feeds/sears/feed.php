<?php

	/********************************************************************
	Version 2.1
		A Sears Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-10
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PSearsFeed extends PBasicFeedXls {

	function __construct () {

		parent::__construct();
		$this->providerName = 'Sears';
		$this->providerNameL = 'sears';
		$this->fileformat = 'xls';
		$this->stripHTML = true;

		//Create some attributes (Mapping 3.0)
		$this->addAttributeMapping('current_category', 'Category');
		$this->addAttributeMapping('title', 'title');
		$this->addAttributeMapping('link', 'Item URL');
		$this->addAttributeMapping('sku', 'SKU');
		$this->addAttributeMapping('regular_price', 'Price');
		$this->addAttributeMapping('sale_price', 'Sale Price');
		$this->addAttributeMapping('sale_start_date', 'Sale Start Date');
		$this->addAttributeMapping('sale_end_date', 'Sale End Date');
		$this->addAttributeMapping('sku', 'UPC');
		$this->addAttributeMapping('feature_imgurl', 'Image URL');
		$this->addAttributeMapping('', 'Mature Content');
		$this->addAttributeMapping('description_short', 'Short Description');
		$this->addAttributeMapping('', 'Manufacturer Model #');
		$this->addAttributeMapping('', 'Shipping Cost');
		$this->addAttributeMapping('', 'Promotional Text');
		$this->addAttributeMapping('brand', 'Brand');
		$this->addAttributeMapping('', 'Action Flag');

	}
  
  function formatProduct($product)  {

		//********************************************************************
		//Prepare the Product Attributes
		//********************************************************************

		if (isset($product->attributes['sale_price']) && strlen($product->attributes['sale_price']) > 0)
			$product->attributes['sale_start_date'] = date('m/d/Y', mktime(0, 0, 0, date("m")+1, date("d"),   date("Y")));

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************

		//if (strlen($product->attributes['description_short']) == 0)
			//$this->addErrorMessage(2000, 'Missing description for ' . $product->attributes['title']);
		//if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			//if (($this->getMappingByMapto('g:brand') == null))
				//$this->addErrorMessage(2000, 'Missing brand for ' . $product->attributes['title']);


		return parent::formatProduct($product);

	}

  function getFeedHeader($file_name, $file_path) {
		$cat = explode("\t", $this->current_category);
		if (count($cat) > 1)
			$this->current_category = $cat[1];
		parent::getFeedHeader($file_name, $file_path);
  }

}