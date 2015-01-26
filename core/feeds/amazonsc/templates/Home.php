<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-1
	//********************************************************************

	/* feed product type:
	Art
	BedAndBath
	FurnitureAndDecor
	Home
	Kitchen
	OutdoorLiving
	SeedsAndPlants
	*/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number', true,false)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('material_type','material_type',true,false)->localized_name = "Material Type";
	$this->addAttributeMapping('included_features', 'included_features',true,false)->localized_name = 'Included Features'; //Indicates whether the product is organic, biodegradable etc.
	//$this->addAttributeMapping('unit_count', 'unit_count')->localized_name = 'Unit Count';
	//$this->addAttributeMapping('unit_count_type', 'unit_count_type')->localized_name = 'Unit Count Type';

?>