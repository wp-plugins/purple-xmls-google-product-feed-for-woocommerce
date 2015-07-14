<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for PriceGrabber
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-25

  ********************************************************************/

class PriceGrabberDlg extends PBaseFeedDialog {

  function __construct() {
    parent::__construct();
    $this->service_name = 'PriceGrabber';
    $this->service_name_long = 'PriceGrabber Product Feed';
	  $this->options = array(
	     'Retsku', 
       'Parent Retsku', 
       'Product Title', 
       'Detailed Description', 
       'Categorization', 
       'Merchant Categorization', 
       'Product URL', 
	     'Primary Image URL', 
       'Selling Price', 
       'Regular Price', 
       'Condition', 
       'Availability', 
       'Manufacturer Name', 
       'Manufacturer Part Number', 
       'GTIN',
       'Color',
       'Size',
       'Material',
       'Pattern',
       'Gender',
       'Age',
       'Shipping Cost',
       'Weight'
	  );
  }

}