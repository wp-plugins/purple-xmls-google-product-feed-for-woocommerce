<?php

  /********************************************************************
  Version 2.0
    Taxation Data
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: 

  ********************************************************************/
 
class PTaxationData {

	function __construct($parentfeed) {
		global $pfcore;
		$loadProc = 'loadTaxationData' . $pfcore->callSuffix;
		return $this->$loadProc($parentfeed);
	}

	function loadTaxationDataJ($parentfeed) {
		//Joomla version
	}

	function loadTaxationDataJS($parentfeed) {
		//Joomla/Shopify version
	}

	function loadTaxationDataW($parentfeed, $status = '', $class = '') {
		//This function needs to load the WordPress Taxation information and store it here
		//extract tax rate from wp_woocommerce_tax_rates
		//the rate is in percentage.
		global $wpdb;
		$tax_rate = 0; 
		//"standard rates" is a blank in woocommerce_tax_rates table
		$sql = "SELECT tax_rate, tax_rate_priority FROM wp_woocommerce_tax_rates
			WHERE tax_rate_class = '" . $class . "'";
		$results = array();
		//$results = $wpdb->get_results($sql) or die(mysql_error()); //"or die" killed the page load
		foreach( $results as $result ) {
        	$tax_rate = $result->tax_rate;
        	break;
    	}
    	return $tax_rate;
		//thus later:
		//a) we can apply overrides in some manner that makes sense
		//b) when the products are loading, they can refer to this object
		//   example: $product->attributes['tax'] = $thisfeed->taxationdata->taxpercent;
	}

	function loadTaxationDataWe($parentfeed) {
		//WP E-Commerce Version
	}

}