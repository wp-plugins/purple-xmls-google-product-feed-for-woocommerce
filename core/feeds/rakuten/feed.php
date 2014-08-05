<?php

  /********************************************************************
  Version 2.0
    A Rakuten Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-24

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PRakutenFeed extends PCSVFeed {

	function __construct () {
		$this->providerName = 'Rakuten';
		$this->providerNameL = 'rakuten';
		$this->fileformat = 'csv';
		$this->fieldDelimiter = "\t";
		$this->fields = array('Seller-id', 'gtin', 'mfg-name', 'mfg-part-number', 'Seller-sku', 'title', 'description', 'main-image', 'additional-images', 'weight', 'category-id', 'product-set-id', 'listing-price');
		parent::__construct();
	}

  function formatProduct($product) {

		if (!isset($this->seller_id)) {
			$this->addErrorMessage(1000, 'Seller ID not configured. Need advanced command: $seller-id = ....');
			$this->addErrorMessage(1001, '*Note: Seller ID is the number in the upper right hand corner of your Rakuten Merchant Tools Page.');
			$this->productCount--; //Make sure the parent class knows we failed to make a product
			return '';
		}

		//Prepare input
		$current_feed['Seller-id'] = $this->seller_id;

		$gtin = $product->attributes['sku'];
		while (strlen($gtin) < 12)
			$gtin = '0' . $gtin;
		$current_feed['gtin'] = $gtin;

		if (isset($product->attributes['brand']))
			$current_feed['mfg-name'] = $product->attributes['brand'];
		$current_feed['mfg-part-number'] = $product->attributes['sku'];
		$current_feed['Seller-sku'] = $product->id; //Seller-sku must be unique in the feed
		$current_feed['title'] = $product->attributes['title'];
		$current_feed['description'] = $product->description;
		$current_feed['main-image'] = $product->feature_imgurl;

		if ( $this->allow_additional_images && (count($product->imgurls) > 0) )
			$current_feed['additional-images'] = implode('|', $product->imgurls);

		$current_feed['weight'] = $product->attributes['weight'];
		$current_feed['category-id'] = explode("\t", $this->current_category)[0];

		if (isset($product->item_group_id))
			$current_feed['product-set-id'] = $product->item_group_id;

		$current_feed['listing-price'] = sprintf($this->currency_format, $product->attributes['regular_price']);

		$this->executeOverrides($product, $current_feed);

		return $this->asCSVString($current_feed);
	}

}

?>