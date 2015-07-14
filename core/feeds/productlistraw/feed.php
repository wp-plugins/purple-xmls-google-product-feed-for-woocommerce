<?php

  /********************************************************************
  Version 2.1
    A Product List Raw Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2015-04

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlistrawFeed extends PBasicFeed {

	function __construct () {

		parent::__construct();
		$this->providerName = 'Productlistraw';
		$this->providerNameL = 'productlistraw';
		$this->fileformat = 'txt';

	}
  
	function formatProduct($product) {

		//Images now soft-coded
		foreach($product->imgurls as $image_count => $imgurl) {
			$product->attributes['additional_image_link' . $image_count] = $imgurl;
			if ($image_count > 9)
				break;
		}

		$result = '
//********************************************************************
' . $product->attributes['title'] . '
//********************************************************************';

		foreach($product->attributes as $key => $value)
			if (gettype($value) != 'array')
				$result .= '
' . $key . ': ' . $value;

		return $result;

  }

}