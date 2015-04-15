<?php
	//********************************************************************
	//Amazon Seller UK Listing Loader
	//2014-12
	//********************************************************************

/*
Listing Loader is for uploading listings of products that already exist on Amazon.com. If a product does not exist on Amazon.com, you must first add that product to the Amazon.com catalog. 									
*/
	$this->addAttributeMapping('sku', 'sku', true, true)->localized_name = 'sku';
	$this->addAttributeMapping('regular_price', 'price', false, true)->localized_name = 'price';
	$this->addAttributeMapping('quantity', 'quantity', false, true)->localized_name = 'quantity';
	$this->addAttributeMapping('', 'product-id', false, true)->localized_name = 'product-id';
	//$this->addAttributeMapping('product-id-type', 'product-id-type');
	$this->addAttributeMapping('condition', 'condition-type', false, true)->localized_name = 'condition-type';
	$this->addAttributeMapping('', 'condition-note', false, true)->localized_name = 'condition-note';

	$this->addAttributeMapping('', 'ASIN-hint', false, false)->localized_name = 'ASIN-hint';
	$this->addAttributeMapping('', 'title', false, false)->localized_name = 'title'; //Product title that is automatically populated when you use product lookup.
//Enter the product tax code supplied to you by Amazon.com.  If no entry is provided, the default is "A_GEN_NOTAX".
	$this->addAttributeMapping('', 'product-tax-code', false, false)->localized_name = 'product-tax-code'; //
	$this->addAttributeMapping('', 'operation-type', false, false)->localized_name = 'operation-type';
	$this->addAttributeMapping('sale_price', 'sale-price', false, false)->localized_name = 'sale-price';
	$this->addAttributeMapping('sale_from_date', 'sale-start-date', false, false)->localized_name = 'sale-start-date';
	$this->addAttributeMapping('sale_end_date', 'sale-end-date', false, false)->localized_name = 'sale-end-date';
	$this->addAttributeMapping('leadtime_to_ship', 'leadtime-to-ship', false, false)->localized_name = 'leadtime-to-ship';
	$this->addAttributeMapping('', 'launch-date', false, false)->localized_name = 'launch-date';
	$this->addAttributeMapping('', 'is-giftwrap-available', false, false)->localized_name = 'is-giftwrap-available'; //true or false
	$this->addAttributeMapping('', 'is-gift-message-available', false, false)->localized_name = 'is-gift-message-available'; //true or false
	$this->addAttributeMapping('', 'fulfillment-center-id', false, false)->localized_name = 'fulfillment-center-id'; //required for amazon fulfilled products
	//$this->addAttributeMapping('feature_image', 'main-offer-image', false, false)->localized_name = 'main-offer-image';
	//for ($i = 1; $i < 6; $i++)
	//	$this->addAttributeMapping('other_image_url'.$i, 'offer-image'.$i, true)->localized_name = 'offer-image' . $i;

?>