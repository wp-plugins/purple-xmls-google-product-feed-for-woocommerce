<?php

  /********************************************************************
  Version 2.1
    A Product List XML Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-09

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlistxmlFeed extends PXMLFeed {

	public $mapAttributesOnTheFly = true;

	function __construct () {
		parent::__construct();
		global $pfcore;
		$this->forceCData = true;
		$this->providerName = 'Productlistxml';
		$this->providerNameL = 'productlistxml';
		$this->addAttributeMapping('local_category', 'local_category', true,true);

		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		if ($pfcore->callSuffix == 'W')
			$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
	
	}
  
	function formatProduct($product) {

		//Price
		// if (strlen($product->attributes['regular_price']) == 0)
		// 	$product->attributes['regular_price'] = '0.00';
		// $product->attributes['regular_price'] = sprintf($this->currency_format, $product->attributes['regular_price']) . $this->currency;
		// $sale_price = $this->getMapping('sale_price');
		// if ($sale_price != null)
		// 	$sale_price->enabled = $product->attributes['has_sale_price'];
		// if ($product->attributes['has_sale_price'])
		// 	$product->attributes['sale_price'] = sprintf($this->currency_format, $product->attributes['sale_price']) . $this->currency;

		//Images now soft-coded
		foreach($product->imgurls as $image_count => $imgurl) {
			$product->attributes['additional_image_link' . $image_count] = $imgurl;
			if ($image_count > 9)
				break;
		}

		//********************************************************************
		//Make sure all the fields for this product are mapped
		//********************************************************************
		$arrayKey = array();
		$keyCount = 0;
		if ($this->mapAttributesOnTheFly)
			foreach($product->attributes as $key => $value) 
			{
				//$keyto = str_replace(' ', '_', $key);
				//if ($this->getMappingByMapto($keyto) == null)
				if ($this->getMappingByMapto($key) == null)
					if ($key != 'category_ids')
					{					
						//temp solution for spaces in xml names. Need solution for all illegal chars: /, <, >
						$this->addAttributeMapping($key, str_replace(' ', '._', $key), $this->forceCData);
					}
			}

			return parent::formatProduct($product);
  }

}