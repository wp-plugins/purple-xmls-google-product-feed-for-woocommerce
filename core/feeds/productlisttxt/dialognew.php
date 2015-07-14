<?php

	/********************************************************************
	Version 3.0
		Export a Product List to TXT format (Not for any particular destination)
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-11
	********************************************************************/

class ProductlisttxtDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Productlisttxt';
		$this->service_name_long = 'Product List TXT Export';
		$this->options = array();
		$this->blockCategoryList = true;
	}

}

?>