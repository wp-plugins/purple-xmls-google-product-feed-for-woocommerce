<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('sku', 'sku', false, true)->localized_name = 'sku';
	//item-type-keyword: refer to BTG
	$this->addAttributeMapping('item-type-keyword', 'item-type-keyword', false, true)->localized_name = 'item-type-keyword';  
	$this->addAttributeMapping('', 'product-id-number', false, true)->localized_name = 'product-id-number';
	//$this->addAttributeMapping('product-id-type', 'product-id-type')->localized_name = 'product-id-number';
	$this->addAttributeMapping('brand', 'brand', false, true)->localized_name = 'brand';
	$this->addAttributeMapping('title', 'product-name', false, true)->localized_name = 'product-name';
	$this->addAttributeMapping('', 'manufacturer', false, true)->localized_name = 'manufacturer';
	$this->addAttributeMapping('part-number', 'part-number', false, true)->localized_name = 'part-number';
	$this->addAttributeMapping('number-of-items', 'number-of-items', false, true)->localized_name = 'number-of-items';	
	$this->addAttributeMapping('regular_price', 'item-price', false, true)->localized_name = 'item-price';
	$this->addAttributeMapping('leadtime-to-ship', 'leadtime-to-ship', false, true)->localized_name = 'leadtime-to-ship';
	//optional
	$this->addAttributeMapping('product_description', 'product-description', false, false)->localized_name = 'product-description';
	$this->addAttributeMapping('quantity', 'Quantity')->localized_name = 'Quantity';
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('bullet-point' . $i, 'bullet-point' . $i, true, false)->localized_name = 'bullet-point' . $i; //in camera.and.photo, localized name is Bullet Point1
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('generic-keywords' . $i, 'generic-keywords' . $i, true, false)->localized_name = 'generic-keywords' . $i;
	//Template: Images. Not required but usually clients will have this in woocommerce
	$this->addAttributeMapping('feature_imgurl', 'main-image-url', true, true)->localized_name = 'main-image-url';
	for ($i = 1; $i < 5; $i++)
		$this->addAttributeMapping("other-image_url-$i", "other-image-url$i", true, false)->localized_name = 'other-image-url' . $i;

	$this->addAttributeMapping('list-price', 'list-price', false, false)->localized_name = 'list-price';
	//sales price information
	$this->addAttributeMapping('sale-end-date', 'sale-end-date', false, false)->localized_name = 'sale-end-date';
	$this->addAttributeMapping('sales-price', 'sales-price', false, false)->localized_name = 'sales-price';
	$this->addAttributeMapping('sale-start-date', 'sale-start-date', false, false)->localized_name = 'sale-start-date';
	
	$this->addAttributeMapping('currency', 'currency', false, true)->localized_name = 'currency';
	$this->addAttributeMapping('weight', 'item-weight', false, false)->localized_name = 'item-weight';			
	

	$this->addAttributeMapping('height', 'item-height', false, false)->localized_name = 'item-height';
	$this->addAttributeMapping('length', 'item-length', false, false)->localized_name = 'item-length';
	$this->addAttributeMapping('width', 'item-width', false, false)->localized_name = 'item-width';
	$this->addAttributeMapping('item-dimensions-unit-of-measure', 'item-dimensions-unit-of-measure', false, false)->localized_name = 'item-dimensions-unit-of-measure';
	
	$this->addAttributeMapping('item-weight-unit-of-measure', 'item-weight-unit-of-measure', false, false)->localized_name = 'item-weight-unit-of-measure';
	$this->addAttributeMapping('website-shipping-weight', 'website-shipping-weight', false, false)->localized_name = 'website-shipping-weight';
	$this->addAttributeMapping('item-weight-unit-of-measure', 'website-shipping-weight-unit-of-measure', false, false)->localized_name = 'website-shipping-weight-unit-of-measure';

?>