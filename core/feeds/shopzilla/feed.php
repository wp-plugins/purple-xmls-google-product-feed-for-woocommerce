<?php

	/********************************************************************
	Version 3.0
	A Shopzilla Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
		2014-09 Moved to Mapping v3
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PShopzillaFeed extends PCSVFeedEx {

	function __construct () {
		parent::__construct();
		$this->providerName = 'Shopzilla';
		$this->providerNameL = 'shopzilla';
		$this->fileformat = 'txt';
		$this->fieldDelimiter = "\t";
		$this->fields = array();

//required
		$this->addAttributeMapping('id', 'Unique ID',true,true);
		$this->addAttributeMapping('title', 'Title',true,true);
		$this->addAttributeMapping('description', 'Description', true,true);
		$this->addAttributeMapping('curent_category', 'Category', true,true);
		$this->addAttributeMapping('link', 'Product URL',true,true);
		$this->addAttributeMapping('feature_imgurl', 'Image URL',true,true);
		$this->addAttributeMapping('condition', 'Condition',true,true);
		$this->addAttributeMapping('stock_status', 'Availability',true,true);
		$this->addAttributeMapping('price', 'Current Price',true,true);
//optional
		$this->addAttributeMapping('additional_images', 'Additional Image URL',true);
		$this->addAttributeMapping('item_group_id', 'Item Group ID');
		$this->addAttributeMapping('regular_price', 'Original Price',true);
		$this->addAttributeMapping('weight', 'Ship Weight');
		$this->addAttributeMapping('brand', 'Brand',true);
		$this->addAttributeMapping('', 'GTIN',true);
		$this->addAttributeMapping('', 'MPN',true);
		$this->addAttributeMapping('gender', 'Gender',true);
		$this->addAttributeMapping('age_group', 'Age Group',true);
		$this->addAttributeMapping('size', 'Size',true);
		$this->addAttributeMapping('color', 'Color',true);
		$this->addAttributeMapping('', 'Material',true);
		$this->addAttributeMapping('', 'Pattern',true);
		$this->addAttributeMapping('', 'Bid',true);
		$this->addAttributeMapping('', 'Promo Text',true);
		
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule('price_rounding','pricerounding'); //2 decimals
	}
		
  function formatProduct($product) {

//Category: ShopZilla accepts breadcrumbs (text)
		$category = explode(";", $this->current_category);
	  	if (isset($category[0]))
			$product->attributes['curent_category'] = trim($category[0]);
		else
			$product->attributes['curent_category'] = "0,000,001"; //Other miscellaneous category id
		$product->attributes['curent_category'] = str_replace(',', '', $product->attributes['curent_category']);

		if (strpos($product->attributes['feature_imgurl'], 'https') !== false) {
			$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);
			//Warn user because server might not be listening for http connections
			//$this->addErrorMessage(<Shopzilla Range Warning + 1>, 'Converted an https image url http ' . $product->attributes['title'] . image url);
		}

		//Images: Max 9 Additional Images
		if ( $this->allow_additional_images && (count($product->imgurls) > 0) ) {
			$product->attributes['additional_images'] = implode(',', $product->imgurls); 
			$product->attributes['additional_images'] = str_replace('https://','http://',$product->attributes['additional_images']);
			//$this->addErrorMessage(<Shopzilla Range Warning + 2>, 'Converted an https additional image to http ' . $product->attributes['title'] . image url);
		}
		
//Availability
		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'In Stock';
		else
			$product->attributes['stock_status'] = 'Out of Stock';

//result code notificaitons		
		//if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
		//	$this->addErrorMessage(13000, 'Missing brand for ' . $product->attributes['title']);
		return parent::formatProduct($product);		
  }  

}