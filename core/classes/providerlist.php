<?php

/***************************************************
	Cartproductfeed - CreateFeed View
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
		2014-06-05 by Keneto
***************************************************/

class PProviderList {

	public $items = array();

	public function __construct() {
		$this->addProvider('Google', 'Google Merchant Feed');
		$this->addProvider('Amazon', 'Amazon Product Ads Feed');
		$this->addProvider('AmazonSC', 'Amazon Seller Central');
		$this->addProvider('eBay', 'eBay Feed');
		$this->addProvider('eBaySeller', 'eBay Seller');
		$this->addProvider('------', ''); //A gap for dialogfeedpage
		$this->addProvider('Beslist', 'Beslist Feed');
		$this->addProvider('Bing', 'Bing Feed');
		$this->addProvider('Nextag', 'Nextag Feed');
		$this->addProvider('PriceGrabber', 'PriceGrabber');
		$this->addProvider('Rakuten', 'Rakuten Feed');
		$this->addProvider('ShareASale', 'ShareASale Merchant Data Feed');
		$this->addProvider('Shopzilla', 'Shopzilla Feed');
		$this->addProvider('AmmoSeek', 'AmmoSeek');
		$this->addProvider('------', '');
		$this->addProvider('Productlistxml', 'Product List XML Export');
		$this->addProvider('Productlistcsv', 'Product List CSV Export');
	}

	private function addProvider($name, $description) {
		$np = new stdClass();
		$np->name = $name;
		$np->description = $description;
		$this->items[] = $np;
	}

}

?>