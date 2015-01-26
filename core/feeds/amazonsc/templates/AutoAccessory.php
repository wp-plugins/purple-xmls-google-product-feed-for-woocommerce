<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'external_product_id',true,true)->localized_name = 'Product ID';
	//$this->addAttributeMapping('title', 'item_name', true,true)->localized_name = 'Product Name';
	//$this->addAttributeMapping('', 'brand_name', true,true)->localized_name = 'Brand Name';
	//$this->addAttributeMapping('', 'manufacturer', true,true)->localized_name = 'Manufacturer';
	//$this->addAttributeMapping('', 'item_type',true,true)->localized_name = 'Item Type Keyword';
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	/** List of AutoAccessory Product Types:
	AutoAccessoryMisc
	AutoPart
	Helmet
	PowersportsPart
	ProtectiveGear
	RidingApparel
	**/

	$this->addAttributeMapping('', 'item_package_quantity')->localized_name = 'Package Quantity';
	//for both these, select from valid values list
	for ($i = 1; $i < 4; $i++)
		$this->addAttributeMapping( '', 'style_keywords' . $i, true )->localized_name = 'Style-specific Terms' . $i;
	for ($i = 1; $i < 4; $i++)
		$this->addAttributeMapping( '', 'department_name' . $i, true )->localized_name = 'Department' . $i;
	$this->addAttributeMapping('', 'color_name')->localized_name = 'Color'; //ex: Navy Blue	
	$this->addAttributeMapping('', 'model_name')->localized_name = 'Series'; //ex: Aspire (series/chassis of the product)
	$this->addAttributeMapping('', 'size_name')->localized_name = 'Size'; //ex: Kids, Husky
?>