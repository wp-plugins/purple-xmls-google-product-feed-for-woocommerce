<?php
	//********************************************************************
	//Kelkoo categories
	//All
	//2015-01 Calv
	//********************************************************************

	//Kelkoo attributes that  all templates use.
	//Create some attributes (Mapping 3.0) in the form (title, Google-title, CData, isRequired)
	$this->addAttributeMapping('title', 'title', true, true); //order: 1
	$this->addAttributeMapping('link', 'product-url', true, true); //order: 2
	$this->addAttributeMapping('price', 'price', true, true); //3
	$this->addAttributeMapping('brand', 'brand', true, false); //order: 4
	$this->addAttributeMapping('description', 'description', true, false); //order: 5
	$this->addAttributeMapping('feature_imgurl', 'image-url', true, false); //order: 6
	$this->addAttributeMapping('', 'ean', true, false); //7
	$this->addAttributeMapping('local_category', 'merchant-category', true, true); //8
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category

	$this->addAttributeMapping('stock_status', 'availability', true, false); //order: 9
	$this->addAttributeMapping('', 'delivery-cost', true, false); //10
	$this->addAttributeMapping('', 'delivery-time', true, false); //11

	$this->addAttributeMapping('', 'mpn', true, false); //17
	$this->addAttributeMapping('sku', 'sku', true, false); //17

?>