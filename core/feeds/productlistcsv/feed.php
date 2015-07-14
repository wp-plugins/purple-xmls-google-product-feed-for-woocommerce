<?php

	/********************************************************************
	Version 2.1
		A Product List CSV Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-10
		2014-10 Moved to Attribute Mapping v3 (K)
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlistcsvFeed extends PCSVFeedEx {

	public $mapAttributesOnTheFly = true;

	function __construct () {
		parent::__construct();
		global $pfcore;
		$this->providerName = 'Productlistcsv';
		$this->providerNameL = 'productlistcsv';
		$this->fieldDelimiter = ',';
		$this->fileformat = 'csv';
		$this->stripHTML = true;

		//$this->addAttributeMapping('description', 'description', true);
		//$this->addAttributeMapping('title', 'title', true);
		$this->addAttributeMapping('local_category', 'local_category', true);

		//if ($pfcore->callSuffix == 'W')
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule('price_rounding','pricerounding'); //2 decimals	
	}
  
	function formatProduct($product) {
	
		//********************************************************************
		//Make sure all the fields for this product are mapped
		//********************************************************************
		if ($this->mapAttributesOnTheFly)
			foreach($product->attributes as $key => $value)
				if ($this->getMappingByMapto($key) == null)
					if ($key != 'category_ids' ) {
						$this->addAttributeMapping($key, $key, true); 
					}

		return parent::formatProduct($product);

  }

}