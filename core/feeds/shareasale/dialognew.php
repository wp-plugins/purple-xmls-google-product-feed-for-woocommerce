<?php

	/********************************************************************
	Version 2.0
		Front Page Dialog for ShareASale Merchant
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08

	********************************************************************/

class ShareASaleDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'ShareASale';
		$this->service_name_long = 'ShareASale Data Feed';
		$this->options = array(
			"Commission","SubCategory", "SearchTerms","Custom 1", "Custom 2", "Custom 3", "Custom 4", "Custom 5",
			"Manufacturer","PartNumber","MerchantSubcategory","ISBN","UPC"
		);
	}

}