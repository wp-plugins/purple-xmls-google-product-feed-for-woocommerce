<?php

  /********************************************************************
  Version 3.0
    Export a Webgains CSV data feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2014-11-12

  ********************************************************************/

class WebgainsDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Webgains';
		$this->service_name_long = 'Webgains CSV Export';
		$this->blockCategoryList = true;
		$this->options = array(
			'Delivery_time',
	        'Delivery_cost',
	        'Extra_price_field',
	        'Thumbnail_image_URL',
	        'Manufacturer',
	        'Brand',
	        'Related_product_IDs',
	        'Promosions',
	        'Availability',
	        'Best_sellers'
			);
	}

}

?>