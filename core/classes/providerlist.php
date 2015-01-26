<?php

	/***************************************************
	Cartproductfeed: List of feed providers
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
		2014-11 by Keneto
	***************************************************/

class PProviderList {

	public $items = array();

	public function __construct() {

		global $pfcore;

		//***************************************************
		//Targetted Feeds
		//***************************************************

		$this->addProvider('Google', 'Google Merchant Feed');
		$this->addProvider('Amazon', 'Amazon Product Ads Feed', 'txt');
		$np = $this->addProvider('AmazonSC', 'Amazon Seller Central', 'csv'); $np->prettyName = 'Amazon Seller';
		$this->addProvider('eBay', 'eBay Feed');
		$this->addProvider('eBaySeller', 'eBay Seller');
		$this->addProvider('', '------'); //A gap for dialogfeedpage
		$this->addProvider('AmmoSeek', 'AmmoSeek');
		$this->addProvider('Beslist', 'Beslist Feed');
		$this->addProvider('Bing', 'Bing Feed', 'csv');
		$this->addProvider('GoDataFeed', 'GoDataFeed');
		$this->addProvider('kelkoo', 'Kelkoo');
		$this->addProvider('Newegg', 'Newegg');
		$this->addProvider('Nextag', 'Nextag Feed', 'csv');
		$this->addProvider('PriceGrabber', 'PriceGrabber', 'csv');
		$this->addProvider('Rakuten', 'Rakuten Feed', 'txt');
		$this->addProvider('RakutenNewSku', 'Rakuten New SKU Feed', 'txt');
		if ($pfcore->callSuffix == 'JS')
			$this->addProvider('Sears', 'Sears Commerce Services', 'xls');
		$this->addProvider('ShareASale', 'ShareASale Merchant Data Feed', 'csv');
		$this->addProvider('Shopzilla', 'Shopzilla Feed', 'csv');
		$this->addProvider('Webgains', 'Webgains Feed', 'csv');

		//***************************************************
		//Generic Export Feeds
		//***************************************************
		$this->addProvider('', '------');
		$np = $this->addProvider('Productlistxml', 'Product List XML Export');
		$np->prettyName = 'XML Export';
		$np = $this->addProvider('Productlistcsv', 'Product List CSV Export', 'csv');
		$np->prettyName = 'CSV Export';
		$np = $this->addProvider('Productlisttxt', 'Product List TXT Export', 'txt');
		$np->prettyName = 'TXT Export';

		//***************************************************
		//Aggregate Feeds
		//***************************************************
		$np = $this->addProvider('AggXml', 'XML Aggregate Feed', 'xml');
		$np->prettyName = 'XML Aggregate';

	}

	private function addProvider($name, $description, $fileformat = 'xml') {
		$np = new stdClass();
		$np->name = $name;
		$np->prettyName = $name; //Used by ManageFeeds Page
		$np->description = $description;
		$np->fileformat = $fileformat;
		$this->items[] = $np;
		return $np;
	}

	public function asOptionList() {
		$output = '';
		foreach($this->items as $item)
			$output .= '
						<option value="' . $item->name . '">' . $item->description . '</option>';
		return $output;
	}

	public function getExtensionByType($type) {
		//Used by ManageFeeds to create a filename
		foreach($this->items as $provider)
			if ($provider->name == $type)
				return $provider->fileformat;
		return '';
	}

	public function getFileFormatByType($type) {
		//Used by ManageFeeds to create a filename
		foreach($this->items as $provider)
			if ($provider->name == $type)
				return $provider->fileformat;
		return '';
	}

	public function getPrettyNameByType($type) {
		//Used by ManageFeeds to create a filename
		foreach($this->items as $provider)
			if ($provider->name == $type)
				return $provider->prettyName;
		return '';
	}

}

?>