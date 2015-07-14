<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Bing
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-04, Calvin 2014-28-08

  ********************************************************************/

class BingDlg extends PBaseFeedDialog {

  function __construct() {
    parent::__construct();
    $this->service_name = 'Bing';
    $this->service_name_long = 'Bing Product Ads Feed';
	$this->options = array(
	  'Id', 
    'Title', 
    'Brand', 
    'ProductURL', 
    'Price', 
    'Description', 
    'ImageURL', 
    'SellerName',
    'SKU', 
    'Availability', 
    'Condition', 
    'ProductType', 
    'B_Category'
	);
  }

}