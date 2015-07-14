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
		$this->addProvider('Amazon', 'Amazon Product Ads', 'txt');
		$np = $this->addProvider('AmazonPAUK', 'Amazon Product Ads (UK)','txt');
		$np = $this->addProvider('AmazonSC', 'Amazon Seller Central', 'txt'); $np->prettyName = 'Amazon Seller';
		$this->addProvider('eBaySeller', 'eBay Seller','csv');
		$this->addProvider('', '------'); //A gap for dialogfeedpage
		$this->addProvider('AffiliateWindow', 'Affiliate Window','csv');		
		$this->addProvider('AmmoSeek', 'AmmoSeek');		
		$this->addProvider('Become', 'Become Europe','csv');
		$this->addProvider('Beslist', 'Beslist Feed');
		$this->addProvider('Bing', 'Bing Feed', 'txt');
		$this->addProvider('eBay', 'eBayCommerceNetwork'); //xml
		$this->addProvider('FacebookXML', 'FacebookXML');
		$this->addProvider('GoDataFeed', 'GoDataFeedXML');
		$this->addProvider('GoDataFeedCSV', 'GoDataFeedCSV','csv');
		$this->addProvider('GraziaShop', 'GraziaShop','csv');
		$this->addProvider('HardwareInfo', 'HardwareInfo','csv');
		$this->addProvider('Houzz', 'Houzz','csv');
		$this->addProvider('Kelkoo', 'Kelkoo');
		$this->addProvider('Newegg', 'Newegg','csv');
		$this->addProvider('Nextag', 'Nextag Feed', 'csv');
		$this->addProvider('PriceGrabber', 'PriceGrabber', 'csv');
		$this->addProvider('Pronto', 'Pronto Feed', 'txt');		
		$this->addProvider('Rakuten', 'Rakuten Inventory Feed', 'txt');
		$this->addProvider('RakutenNewSku', 'Rakuten New SKU Feed', 'txt');
		$this->addProvider('RakutenUK', 'Rakuten UK', 'csv');
		$this->addProvider('ShareASale', 'ShareASale', 'csv');
		$this->addProvider('Shopzilla', 'Shopzilla', 'txt');
		$this->addProvider('Webgains', 'Webgains', 'csv');

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
		$np = $this->addProvider('Productlistraw', 'Product List RAW Export', 'txt');
		$np->prettyName = 'Raw Export';

		//***************************************************
		//Aggregate Feeds
		//***************************************************
		$np = $this->addProvider('AggXmlGoogle', 'Google Aggregate Feed', 'xml');
		$np->prettyName = 'Google Aggregate'; //name shown on Manage Feeds page
		$np = $this->addProvider('AggXml', 'XML Aggregate Feed', 'xml');
		$np->prettyName = 'XML Aggregate';
		$np = $this->addProvider('AggCsv', 'CSV Aggregate Feed', 'xml');
		$np->prettyName = 'CSV Aggregate';

		$pfcore->triggerFilter('cpf_init_provider', $this);

	}

	public function addProvider($name, $description, $fileformat = 'xml') {
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