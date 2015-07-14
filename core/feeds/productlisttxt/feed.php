<?php

	/********************************************************************
	Version 2.1
		A Product List TXT Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-11
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlisttxtFeed extends PCSVFeedEx {

	public $mapAttributesOnTheFly = true;

	function __construct () {
		parent::__construct();
		global $pfcore;
		$this->providerName = 'Productlisttxt';
		$this->providerNameL = 'productlisttxt';
		$this->fileformat = 'txt';
		$this->stripHTML = true;

		//$this->addAttributeMapping('description', 'description', true);
		$this->addAttributeMapping('local_category', 'local_category', true);

		//if ($pfcore->callSuffix == 'W')
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule('price_rounding','pricerounding'); //2 decimals

	}
  
	function formatProduct($product) {

		//Cheat: These three fields aren't ready to be attributes yet, so adding manually:		
		//********************************************************************
		//Make sure all the fields for this product are mapped
		//********************************************************************
		if ($this->mapAttributesOnTheFly)
			foreach($product->attributes as $key => $value)
				if ($this->getMappingByMapto($key) == null)
					if ($key != 'category_ids') {
						$this->addAttributeMapping($key, $key, true);
						//enclose in quotes + escape any inner quotes						
						// $this->addRule( 'csv_standard', 'CSVStandard', array($key) );
						// //apply strictAttribute rule (to remove special chars) to descriptions
						// if ( strpos($key,'descr') !== false )
						// 	$this->addRule( 'strict_attribute','strictAttribute', array($key) );
						
					}

		return parent::formatProduct($product);

  }

}