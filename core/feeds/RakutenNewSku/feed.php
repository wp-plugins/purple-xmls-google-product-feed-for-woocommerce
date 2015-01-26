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

		$this->addAttributeMapping('', 'seller-id',true,true); //assigned by Rakuten.com Shopping
		$this->addAttributeMapping('', 'gtin',true,true);
		$this->addAttributeMapping('isbn', 'isbn');
		$this->addAttributeMapping('', 'mfg-name',true,true);
		$this->addAttributeMapping('', 'mfg-part-number',true,true); 
		$this->addAttributeMapping('', 'asin');
		$this->addAttributeMapping('sku', 'seller-sku',true,true); //seller-sku must be unique in the feed
		$this->addAttributeMapping('title', 'title',true,true);
		$this->addAttributeMapping('description', 'description', true,true);
		$this->addAttributeMapping('feature_imgurl', 'main-image',true,true);
		$this->addAttributeMapping('', 'additional-images');
		$this->addAttributeMapping('weight', 'weight',true,true);
		$this->addAttributeMapping('features', 'features',true);
		$this->addAttributeMapping('listing-price', 'listing-price',true,true);
		$this->addAttributeMapping('', 'msrp');
		$this->addAttributeMapping('category-id', 'category-id');
		$this->addAttributeMapping('', 'keywords');
		$this->addAttributeMapping('', 'product-set-id');
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

	function getFeedFooter() {
		//Override parent and do nothing
	}

	function formatProduct($product) {

		// if (!isset($this->seller_id) && !isset($product->attributes['seller-id'])) {
		// 	$this->addErrorMessage(1000, 'Seller ID not configured. Need advanced command: $seller-id = ....');
		// 	$this->addErrorMessage(1001, '*Note: Seller ID is the number in the upper right hand corner of your Rakuten Merchant Tools Page.');
		// 	$this->productCount--; //Make sure the parent class knows we failed to make a product
		// 	return '';
		// }
		//cheat: Remap these	
		
		$rakuten_description = (strlen($product->attributes['description']) > 8000) ? substr($product->attributes['description'],0,8000) : $product->attributes['description'];
		$rakuten_description = str_replace('"','""',$rakuten_description);		
		$product->attributes['description'] = $rakuten_description;	
	
		if (strlen($product->attributes['listing-price']) == 0)
			$rakuten_price = '0.00';
		if ($product->attributes['has_sale_price'])
			$rakuten_price = $product->attributes['sale_price'];
		else
			$rakuten_price = $product->attributes['regular_price'];
		$product->attributes['listing-price'] = $rakuten_price;

		//if product weight is in ounces, conver to lbs
		$product_weight_in_lbs = $product->attributes['weight']*0.0625;
		$product->attributes['weight'] = sprintf('%0.2f', $product_weight_in_lbs);

		$category = explode(" ", $product->attributes['current_category']);
		if (isset($category[0]))
			$product->attributes['category-id'] = $category[0];
		else
			$product->attributes['category-id'] = '';
		
		//Prepare input (New Rakuten SKU Feed)
		// if (isset($this->seller_id))
		// 	$product->attributes['seller-id'] = $this->seller_id;
		// 		$product->attributes['gtin'] = $product->attributes['sku'];
		// while (strlen($product->attributes['gtin']) < 12)
		// 	$product->attributes['gtin'] = '0' . $product->attributes['gtin'];
		// if ( $this->allow_additional_images && (count($product->imgurls) > 0) )
		// 	$product->attributes['additional_images'] = implode('|', $product->imgurls);
		// $product->attributes['category_id'] = explode("\t", $this->current_category)[0];
		// if (isset($product->item_group_id))
		// 	$product->attributes['product_set_id'] = $product->item_group_id;
		// $product->attributes['listing_price'] = sprintf($this->currency_format, $product->attributes['regular_price']);
		
		return parent::formatProduct($product);
	}

}

?>