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
		$this->fileformat = 'txt';
		$this->fieldDelimiter = "\t";
		$this->fields = array();
		//$this->fields = array('Seller-id', 'gtin', 'mfg-name', 'mfg-part-number', 'Seller-sku', 'title', 'description', 'main-image', 'additional-images', 'weight', 'category-id', 'product-set-id', 'listing-price');

		//$this->addAttributeMapping('ListingId', 'ListingId'); //assigned by Rakuten.com Shopping

		$this->addAttributeMapping('', 'ProductId',true,true); 
		$this->addAttributeMapping('', 'ProductIdType',true,true);
		$this->addAttributeMapping('ItemCondition', 'ItemCondition',true,true);
		$this->addAttributeMapping('Price', 'Price',true,true); //Seller-sku must be unique in the feed
		$this->addAttributeMapping('MAP', 'MAP');
		$this->addAttributeMapping('MAPType', 'MAPType');
		$this->addAttributeMapping('Quantity', 'Quantity',true,true);
		$this->addAttributeMapping('OfferExpeditedShipping', 'OfferExpeditedShipping',true,true);
		$this->addAttributeMapping('Description', 'Description', true);
		$this->addAttributeMapping('', 'ShippingRateStandard');
		$this->addAttributeMapping('', 'ShippingRateExpedited');
		$this->addAttributeMapping('', 'ShippingLeadTime');
		$this->addAttributeMapping('', 'OfferTwoDayShipping');
		$this->addAttributeMapping('', 'ShippingRateTwoDay');
		$this->addAttributeMapping('', 'OfferOneDayShipping');
		$this->addAttributeMapping('', 'ShippingRateOneDay');
		$this->addAttributeMapping('', 'OfferLocalDeliveryShippingRates');
		$this->addAttributeMapping('sku', 'ReferenceId',true,true); //unique product id assigned to the product by you

	}
	
	function getFeedHeader($file_name, $file_path) 
	{
		//Rakuten header line 1
		$output = implode(
			$this->fieldDelimiter, 
			array('##Type=Inventory;Version=5.0')
		) .  "\r\n";

		//Rakuten header line 2
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
		//$product->attributes['ProductId'] = 'a';

		$product->attributes['Description'] = (strlen($product->attributes['description']) > 250) ? substr($product->attributes['description'],0,250) : $product->attributes['description'];

		$product->attributes['ItemCondition'] = '1';

		if (strlen($product->attributes['regular_price']) == 0)
			$rakuten_price = '0.00';
		if ($product->attributes['has_sale_price'])
			$rakuten_price = $product->attributes['sale_price'];
		else
			$rakuten_price = $product->attributes['regular_price'];
		$product->attributes['Price'] = $rakuten_price;

		if ( $product->attributes['stock_status'] == 1 ) {
			if ( !empty($product->attributes['stock_quantity']) )
				$product->attributes['Quantity'] = $product->attributes['stock_quantity'];
			//else
				//$product->attributes['Quantity'] = $product->attributes['Quantity'];
			}
		else {
		$product->attributes['Quantity'] = '0';
		}

		$product->attributes['OfferExpeditedShipping'] = 1;

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