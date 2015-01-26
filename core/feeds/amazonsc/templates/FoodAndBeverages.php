<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	/* feed product types:
	Beverages
	Food
	*/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	
	//The number of units in the item being offered for sale, such that each unit is packaged for individual sale, and has a scannable bar code
	$this->addAttributeMapping('item_package_quantity', 'item_package_quantity',true,true)->localized_name = 'Package Quantity';

	//$this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Product Name';
	//$this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand Name';
	//$this->addAttributeMapping('item_type', 'item_type')->localized_name = 'Item Type Keyword';
	
	$this->addAttributeMapping('', 'unit_count')->localized_name = 'Unit Count';
	$this->addAttributeMapping('', 'unit_count_type')->localized_name = 'Unit Count Type';

?>