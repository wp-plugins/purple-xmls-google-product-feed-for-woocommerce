<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-1
	//********************************************************************

	/* feed product type: HealthMisc, PersonalCareAppliances */
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number', true,true)->localized_name = 'Manufacturer Part Number';
	
	$this->addAttributeMapping('item_package_quantity', 'item_package_quantity',true,false)->localized_name = 'Package Quantity';
	
	$this->addAttributeMapping('', 'unit_count')->localized_name = 'Unit Count';
	$this->addAttributeMapping('', 'unit_count_type')->localized_name = 'Unit Count Type';

?>