<?php

  /********************************************************************
  Version 3.0
    Export a Product List to XML format (Not for any particular destination)
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-09

  ********************************************************************/

class ProductlistxmlDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Productlistxml';
		$this->service_name_long = 'Product List XML Export';
		$this->options = array();
		$this->blockCategoryList = true;
	}

}

?>