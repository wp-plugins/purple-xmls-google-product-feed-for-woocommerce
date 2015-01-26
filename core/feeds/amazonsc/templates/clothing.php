<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	//$this->addAttributeMapping('', 'external_product_id')->localized_name = 'Product ID'; //valid GCID, UPC, or EAN
	//$this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Product Name';
	//$this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand Name';
	//$this->addAttributeMapping('item_type', 'item_type')->localized_name = 'Item Type Keyword';
	$this->addAttributeMapping('', 'model')->localized_name = 'Style Number';
	$this->addAttributeMapping('color', 'color_name')->localized_name = 'Color'; //ex: Navy Blue
	$this->addAttributeMapping('', 'department_name')->localized_name = 'Department'; //ex: womens
	$this->addAttributeMapping('', 'size_name')->localized_name = 'Size'; //ex: X-Large, One Size

?>