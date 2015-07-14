<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Beslist
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calvin 2014-09-09

  ********************************************************************/

class eBaySellerDlg extends PBaseFeedDialog 
{

	function __construct() 
	{
		parent::__construct();
		$this->service_name = 'eBaySeller';
		$this->service_name_long = 'eBay Seller';
		$this->options = array(
			'Category',
			'Title',
			'Description',
			'ConditionID',
			'picURL',
			'Quantity',
			'Format',
			'Duration',
			'Location',
			'StartPrice',
			'BuyItNowPrice',
			'Location',
			'ReturnsAcceptedOption',
			'ShippingType'
			);
	}

	// function convert_option($option) 
	// {
	// 	return strtolower(str_replace(" ", "_", $option));
	// }
}
?>