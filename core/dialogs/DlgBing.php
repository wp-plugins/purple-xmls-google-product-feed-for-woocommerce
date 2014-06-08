<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Bing
	By: Keneto 2014-06-04

  ********************************************************************/

include_once 'basefeeddialogs.php';

class BingDlg extends PBaseFeedDialog {

  function __construct() {
    parent::__construct();
    $this->service_name = 'Bing';
    $this->service_name_long = 'Bing Product Feed';
	$this->options = array(
	  'MPID', 'Title', 'Brand', 'ProductURL', 'Price', 'Description', 'ImageURL', 'SKU', 'Availability', 'Condition', 'ProductType', 'B_Category'
	);
  }

}