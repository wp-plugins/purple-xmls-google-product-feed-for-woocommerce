<?php

  /********************************************************************
  Version 3.0
    Export a Become CSV data feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2015-05-22

  ********************************************************************/

class HardwareInfoDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'HardwareInfo';
		$this->service_name_long = 'Hardware.Info CSV Export';
		$this->blockCategoryList = true;
		$this->options = array();
	}

}

?>