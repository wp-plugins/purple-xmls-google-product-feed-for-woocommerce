<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	/* feed product types: Beverages, Food */
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	
	//The number of units in the item being offered for sale, such that each unit is packaged for individual sale, and has a scannable bar code
	$this->addAttributeMapping('item_package_quantity', 'item_package_quantity',true,true)->localized_name = 'Package Quantity';
//Grocery
	$this->addAttributeMapping('', 'unit_count',true)->localized_name = 'Unit Count';
	$this->addAttributeMapping('', 'unit_count_type',true)->localized_name = 'Unit Count Type';

?>