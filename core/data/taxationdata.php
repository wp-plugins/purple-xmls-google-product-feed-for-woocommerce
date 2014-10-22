<?php

  /********************************************************************
  Version 2.0
    Taxation Data
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: 

  ********************************************************************/
 
class PTaxationData {

	function __construct($parentfeed) {
		global $pfcore;
		$loadProc = 'loadTaxationData' . $pfcore->callSuffix;
		return $this->$loadProc($parentfeed);
	}

	function loadTaxationDataJ($parentfeed) {
		//Joomla version
	}

	function loadTaxationDataJS($parentfeed) {
		//Joomla/Shopify version
	}

	function loadTaxationDataW($parentfeed) {
		//This function needs to load the WordPress Taxation information and store it here
		//thus later:
		//a) we can apply overrides in some manner that makes sense
		//b) when the products are loading, they can refer to this object
		//   example: $product->attributes['tax'] = $thisfeed->taxationdata->taxpercent;
	}

	function loadTaxationDataWe($parentfeed) {
		//WP E-Commerce Version
	}

}