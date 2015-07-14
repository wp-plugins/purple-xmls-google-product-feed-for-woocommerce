<?php

  /********************************************************************
  Version 3.0
    Export a Product List to CSV format (Not for any particular destination)
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-10

  ********************************************************************/

class ProductlistcsvDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Productlistcsv';
		$this->service_name_long = 'Product List CSV Export';
		$this->options = array();
		$this->blockCategoryList = true;
	}

}

?>