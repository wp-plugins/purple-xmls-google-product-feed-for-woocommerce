<?php

  /********************************************************************
  Version 3.0
    Export a Webgains CSV data feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2014-11-12

  ********************************************************************/

class AffiliateWindowDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'AffiliateWindow';
		$this->service_name_long = 'Affiliate Window CSV Feed';
		$this->blockCategoryList = false;
		$this->options = array(	);
	}

}

?>