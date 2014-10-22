<?php

	/********************************************************************
	Version 3.0
	A Rakuten Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-24
		2014-09 Moved to 100% Mapping v3 compliance
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PRakutenFeed extends PCSVFeedEx {

	function __construct () {
		parent::__construct();
		$this->providerName = 'Rakuten';
		$this->providerNameL = 'rakuten';
		$this->fileformat = 'csv';
		$this->fieldDelimiter = "\t";
		$this->fields = array();
		//$this->fields = array('Seller-id', 'gtin', 'mfg-name', 'mfg-part-number', 'Seller-sku', 'title', 'description', 'main-image', 'additional-images', 'weight', 'category-id', 'product-set-id', 'listing-price');
		$this->descriptionStrict = true;

		$this->addAttributeMapping('seller-id', 'Seller-id');
		$this->addAttributeMapping('gtin', 'gtin');
		$this->addAttributeMapping('brand', 'mfg-name');
		$this->addAttributeMapping('sku', 'mfg-part-number');
		$this->addAttributeMapping('id', 'Seller-sku'); //Seller-sku must be unique in the feed
		$this->addAttributeMapping('title', 'title');
		$this->addAttributeMapping('description', 'description');
		$this->addAttributeMapping('feature_imgurl', 'feature-imgurl');
		$this->addAttributeMapping('additional_images', 'additional-images');
		$this->addAttributeMapping('weight', 'weight');
		$this->addAttributeMapping('category_id', 'category-id');
		$this->addAttributeMapping('product_set_id', 'product-set-id');
		$this->addAttributeMapping('listing_price', 'listing-price');
	}

	function formatProduct($product) {

		if (!isset($this->seller_id) && !isset($product->attributes['seller-id'])) {
			$this->addErrorMessage(1000, 'Seller ID not configured. Need advanced command: $seller-id = ....');
			$this->addErrorMessage(1001, '*Note: Seller ID is the number in the upper right hand corner of your Rakuten Merchant Tools Page.');
			$this->productCount--; //Make sure the parent class knows we failed to make a product
			return '';
		}

		//cheat: Remap these
		$product->attributes['category'] = $this->current_category;
		$product->attributes['description'] = $product->description;		
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;

		//Prepare input
		if (isset($this->seller_id))
			$product->attributes['seller-id'] = $this->seller_id;

		$product->attributes['gtin'] = $product->attributes['sku'];
		while (strlen($product->attributes['gtin']) < 12)
			$product->attributes['gtin'] = '0' . $product->attributes['gtin'];

		if ( $this->allow_additional_images && (count($product->imgurls) > 0) )
			$product->attributes['additional_images'] = implode('|', $product->imgurls);

		$product->attributes['category_id'] = explode("\t", $this->current_category)[0];

		if (isset($product->item_group_id))
			$product->attributes['product_set_id'] = $product->item_group_id;

		$product->attributes['listing_price'] = sprintf($this->currency_format, $product->attributes['regular_price']);

		return parent::formatProduct($product);
	}

}

?>