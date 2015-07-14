<?php

	/********************************************************************
	Version 2.0
		Front Page Dialog for GraziaShop
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto, Calv 2015-23-02

	********************************************************************/

class GraziaShopDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'GraziaShop';
		$this->service_name_long = 'GraziaShop CSV Export';		
	}

	function convert_option($option) {
		return strtolower(str_replace(" ", "_", $option));
	}

}