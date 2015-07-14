<?php

	/********************************************************************
	Version 2.0
		Front Page Dialog for Amazon
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05

	********************************************************************/

class AmazonDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Amazon';
		$this->service_name_long = 'Amazon Product Ads Export';		
	}

	function convert_option($option) {
		return strtolower(str_replace(" ", "_", $option));
	}

}