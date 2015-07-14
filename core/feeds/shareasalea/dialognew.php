<?php

	/********************************************************************
	Version 2.0
		Front Page Dialog for ShareASale Affiliate
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08

	********************************************************************/

class ShareASaleADlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'ShareASaleA';
		$this->service_name_long = 'ShareASale Affiliate Data Feed';
		$this->options = explode('|', 'Custom1|Custom2|Custom3|Custom4|Custom5|Manufacturer|PartNumber|MerchantCategory|MerchantSubcategory|ShortDescription|ISBN|UPC');
	}

}