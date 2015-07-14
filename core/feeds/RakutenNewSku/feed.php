<?php

	/********************************************************************
	Version 3.0
	A Rakuten Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-24
		2014-09 Moved to 100% Mapping v3 compliance
		Note: Due to camel-case in folder name, the plugin will be unable to find this provider on *nix systems
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PRakutenNewSkuFeed extends PCSVFeedEx {

	function __construct () {
		parent::__construct();
		$this->providerName = 'RakutenNewSku';
		$this->providerNameL = 'rakutennewsku';
		$this->fileformat = 'txt';
		$this->fieldDelimiter = "\t";
		$this->fields = array();
		//$this->fields = array('Seller-id', 'gtin', 'mfg-name', 'mfg-part-number', 'Seller-sku', 'title', 'description', 'main-image', 'additional-images', 'weight', 'category-id', 'product-set-id', 'listing-price');

		$this->addAttributeMapping('seller_id', 'seller-id',true,true); //assigned by Rakuten.com Shopping
		$this->addAttributeMapping('', 'gtin',true,true); //upc or ean
		$this->addAttributeMapping('', 'isbn',true);
		$this->addAttributeMapping('brand', 'mfg-name',true,true);
		$this->addAttributeMapping('', 'mfg-part-number',true,true); //may selet id or sku
		$this->addAttributeMapping('', 'asin');
		$this->addAttributeMapping('sku', 'seller-sku',true,true); //seller-sku must be unique in the feed
		$this->addAttributeMapping('title', 'title',true,true);
		$this->addAttributeMapping('description', 'description', true,true);
		$this->addAttributeMapping('feature_imgurl', 'main-image',true,true);
		$this->addAttributeMapping('additional-images', 'additional-images');
		$this->addAttributeMapping('weight', 'weight',true,true);
		$this->addAttributeMapping('', 'features',true);
		$this->addAttributeMapping('price', 'listing-price',true,true);
		$this->addAttributeMapping('price', 'msrp',true);
		$this->addAttributeMapping('current_category', 'category-id',true);
		$this->addAttributeMapping('', 'keywords',true); //separated by |
		$this->addAttributeMapping('item_group_id', 'product-set-id');
		//user may add more attributes via mapAttribute attLocal to attributeName	

		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule('price_rounding','pricerounding'); //2 decimals
		$this->addRule( 'description', 'description',array('max_length=8000','strict') ); 
		// $this->addRule( 'csv_standard', 'CSVStandard',array('description') ); 
		// $this->addRule( 'csv_standard', 'CSVStandard',array('title','100') ); //title char limit
		$this->addRule( 'substr','substr', array('title','0','100',true) ); //100 length

	}
	
	function getFeedHeader($file_name, $file_path) 
	{
		//Rakuten header line 1
		foreach($this->attributeMappings as $thisMapping)
		{
			if ($thisMapping->enabled && !$thisMapping->deleted)
			{
				$output .= $thisMapping->mapTo . $this->fieldDelimiter;
			}
		}
	    return substr($output, 0, -1) .  "\r\n";
	}

	function getFeedFooter($file_name, $file_path) {
		//Override parent and do nothing
	}

	function formatProduct($product) {
		
//if product weight is in ounces, convert to lbs
		if ($this->weight_unit == 'oz' || $this->weight_unit == 'ounces') {
			$product_weight_in_lbs = $product->attributes['weight']*0.0625;
			$product->attributes['weight'] = sprintf('%0.2f', $product_weight_in_lbs);
		}
//additional-images
	 	if ( $this->allow_additional_images && (count($product->imgurls) > 0) )
	 		$product->attributes['additional-images'] = implode('|', $product->imgurls); 
//category-id
	 	$product->attributes['current_category'] = explode("\t", $product->attributes['current_category'])[0];
//seller id	
		if ( isset($this->seller_id) )
			$product->attributes['seller_id'] = $this->seller_id;			
//result code notificaitons		
		if ( strlen($product->attributes['seller_id']) == 0 ) {
			$this->addErrorMessage(1000, 'seller-id not configured. Add advanced command: $seller-id = ....', true);
			$this->productCount--; //Make sure the parent class knows we failed to make a product
			return '';
		}

		// if (!isset($this->seller_id) && !isset($product->attributes['seller-id'])) {
		// 	$this->addErrorMessage(1000, 'Seller ID not configured. Need advanced command: $seller-id = ....');
		// 	$this->addErrorMessage(1001, '*Note: Seller ID is the number in the upper right hand corner of your Rakuten Merchant Tools Page.');
		// 	$this->productCount--; //Make sure the parent class knows we failed to make a product
		// 	return '';
		// }
		//Prepare input (New Rakuten SKU Feed)
		// if (isset($this->seller_id))
		// 	$product->attributes['seller-id'] = $this->seller_id;
		// 		$product->attributes['gtin'] = $product->attributes['sku'];
		// while (strlen($product->attributes['gtin']) < 12)
		// 	$product->attributes['gtin'] = '0' . $product->attributes['gtin']; 	 
		// $product->attributes['listing_price'] = sprintf($this->currency_format, $product->attributes['regular_price']);

		return parent::formatProduct($product);
	}

}

?>