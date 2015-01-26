<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('sku', 'sku', false, true)->localized_name = 'sku';
	$this->addAttributeMapping('item-type-keyword', 'item-type-keyword', false, true)->localized_name = 'item-type-keyword';  
	$this->addAttributeMapping('', 'product-id-number', false, true)->localized_name = 'product-id-number';
	//$this->addAttributeMapping('product-id-type', 'product-id-type')->localized_name = 'product-id-number';
	$this->addAttributeMapping('', 'brand', false, true)->localized_name = 'brand';
	$this->addAttributeMapping('title', 'product-name', false, true)->localized_name = 'product-name';
	$this->addAttributeMapping('', 'manufacturer', false, true)->localized_name = 'manufacturer';
	$this->addAttributeMapping('part-number', 'part-number', false, true)->localized_name = 'part-number';
	$this->addAttributeMapping('number-of-items', 'number-of-items', false, true)->localized_name = 'number-of-items';	
	$this->addAttributeMapping('price', 'item-price', false, true)->localized_name = 'item-price';
	$this->addAttributeMapping('leadtime-to-ship', 'leadtime-to-ship', false, true)->localized_name = 'leadtime-to-ship';
	//optional
	$this->addAttributeMapping('product_description', 'product-description', false, false)->localized_name = 'product-description';
	$this->addAttributeMapping('quantity', 'Quantity')->localized_name = 'Quantity';
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('bullet-point' . $i, 'bullet-point' . $i, true, false)->localized_name = 'bullet-point' . $i; //in camera.and.photo, localized name is Bullet Point1
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('generic-keywords' . $i, 'generic-keywords' . $i, true, false)->localized_name = 'generic-keywords' . $i;
	//Template: Images. Not required but usually clients will have this in woocommerce
	$this->addAttributeMapping('feature_imgurl', 'main-image-url', true, false)->localized_name = 'main-image-url';
	for ($i = 1; $i < 5; $i++)
		$this->addAttributeMapping("other-image_url-$i", "other-image-url$i", true, false)->localized_name = 'other-image-url' . $i;

	
	$this->addAttributeMapping('standard_price', 'list-price', false, false)->localized_name = 'list-price';
	$this->addAttributeMapping('sales-price', 'sales-price', false, false)->localized_name = 'sales-price';
	$this->addAttributeMapping('currency', 'currency', false, false)->localized_name = 'currency';
	$this->addAttributeMapping('weight', 'item-weight', false, false)->localized_name = 'item-weight';			
	$this->addAttributeMapping('item-weight-unit-of-measure', 'item-weight-unit-of-measure', false, false)->localized_name = 'item-weight-unit-of-measure';
	$this->addAttributeMapping('website-shipping-weight', 'website-shipping-weight', false, false)->localized_name = 'website-shipping-weight';
	$this->addAttributeMapping('website-shipping-weight-unit-of-measure', 'website-shipping-weight-unit-of-measure', false, false)->localized_name = 'website-shipping-weight-unit-of-measure';

?>