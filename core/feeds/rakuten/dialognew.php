<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Rakuten
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-24

  ********************************************************************/

class RakutenDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Rakuten';		
		$this->service_name_long = 'Rakuten Inventory Load Feed';
		$this->blockCategoryList = true;
	
	}

}