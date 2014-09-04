<?php

	/********************************************************************
	Version 2.0
		Front Page Dialog for Amazon Seller Central
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08-08

	********************************************************************/

class AmazonSCDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Amazonsc';
		$this->service_name_long = 'Amazon Seller Central';
		$this->options = explode(',', 'item_sku,item_name,brand_name,manufacturer,part_number,item_type,external_product_id,list_price,standard_price,item_package_quantity,model');
	}

	function categoryList($initial_remote_category) {
		if ($this->blockCategoryList)
			return '';
		else
			return '
				  <span class="label">Template : </span>
				  <span><input type="text" name="categoryDisplayText" class="text_big" id="categoryDisplayText"  onkeyup="doFetchCategory_timed(\'' . $this->service_name . '\',  this.value)" value="' . $initial_remote_category . '" autocomplete="off" placeholder="Start typing template name" /></span>
				  <div id="categoryList" class="categoryList"></div>
				  <input type="hidden" id="remote_category" name="remote_category" value="' . $initial_remote_category . '">';
	}

}