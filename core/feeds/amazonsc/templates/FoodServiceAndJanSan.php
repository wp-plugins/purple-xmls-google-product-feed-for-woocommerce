<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('sku', 'sku', false, true)->localized_name = 'sku';
	//item-type-keyword: refer to BTG
	$this->addAttributeMapping('item_type', 'item-type-keyword', false, true)->localized_name = 'item-type-keyword';  
	$this->addAttributeMapping('', 'product-id-number', false, true)->localized_name = 'product-id-number';
	//$this->addAttributeMapping('product-id-type', 'product-id-type')->localized_name = 'product-id-number';
	$this->addAttributeMapping('brand', 'brand', false, true)->localized_name = 'brand';
	$this->addAttributeMapping('title', 'product-name', true, true)->localized_name = 'product-name';
	$this->addAttributeMapping('brand', 'manufacturer', false, true)->localized_name = 'manufacturer';
	$this->addAttributeMapping('', 'part-number', false, true)->localized_name = 'part-number';
	$this->addAttributeMapping('number_of_items', 'number-of-items', false, true)->localized_name = 'number-of-items';		
	//optional
	$this->addAttributeMapping('description', 'product-description', true, false)->localized_name = 'product-description';
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('bullet-point' . $i, 'bullet-point' . $i, true, false)->localized_name = 'bullet-point' . $i; //in camera.and.photo, localized name is Bullet Point1

/*** Other Information: required to make your item buyable ***/	
	$this->addAttributeMapping('regular_price', 'item-price', false, true)->localized_name = 'item-price';
	$this->addAttributeMapping('handling_time', 'leadtime-to-ship', false, true)->localized_name = 'leadtime-to-ship';
	$this->addAttributeMapping('', 'list-price', false, false)->localized_name = 'list-price'; //MSRP
	$this->addAttributeMapping('currency', 'currency', false, true)->localized_name = 'currency';
	$this->addAttributeMapping('quantity', 'Quantity')->localized_name = 'Quantity';

/*** Sales price information ***/
	$this->addAttributeMapping('sale_price', 'sales-price', false, false)->localized_name = 'sales-price';
	$this->addAttributeMapping('sale_end_date', 'sale-end-date', false, false)->localized_name = 'sale-end-date';
	$this->addAttributeMapping('sale_from_date', 'sale-start-date', false, false)->localized_name = 'sale-start-date';

/*** Item Discovery Information: effect how customers can find your products on the site ***/ 
	//A word or phrase that best describes the product. This will help Amazon.com locate the product when customers perform searches on our site.
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'generic-keywords' . $i, true, false)->localized_name = 'generic-keywords' . $i;
	
/*** Image Information ***/
	$this->addAttributeMapping('feature_imgurl', 'main-image-url', true, true)->localized_name = 'main-image-url';
	for ($i = 1; $i < 5; $i++)
		$this->addAttributeMapping("other_image_url$i", "other-image-url$i", true, false)->localized_name = 'other-image-url' . $i;

/*** Product Dimension ***/
	// $this->addAttributeMapping('weight', 'item-weight', false, false)->localized_name = 'item-weight';			
	// $this->addAttributeMapping('weight_unit_word', 'item-weight-unit-of-measure', false, false)->localized_name = 'item-weight-unit-of-measure';
	
	$this->addAttributeMapping('height', 'item-height', false, false)->localized_name = 'item-height';
	$this->addAttributeMapping('length', 'item-length', false, false)->localized_name = 'item-length';
	$this->addAttributeMapping('width', 'item-width', false, false)->localized_name = 'item-width';
	$this->addAttributeMapping('dimension_unit_word', 'item-dimensions-unit-of-measure', false, false)->localized_name = 'item-dimensions-unit-of-measure';
	
	$this->addAttributeMapping('weight', 'website-shipping-weight', false, false)->localized_name = 'website-shipping-weight';
	$this->addAttributeMapping('weight_unit_word', 'website-shipping-weight-unit-of-measure', false, false)->localized_name = 'website-shipping-weight-unit-of-measure';

?>