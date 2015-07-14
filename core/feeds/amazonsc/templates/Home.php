<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-1
	//********************************************************************
	
/*** Basic ***/
	//feed product types: Art, BedAndBath, FurnitureAndDecor, Home,Kitchen,OutdoorLiving,SeedsAndPlants
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Model Number';
	$this->addAttributeMapping('', 'part_number', true,false)->localized_name = 'Manufacturer Part Number';

/*** Add more optional attributes ***/
	$this->addAttributeMapping('','size_name',true,false)->localized_name = "Size";
	$this->addAttributeMapping('','color_name',true,false)->localized_name = "Color";
	$this->addAttributeMapping('','material_type',true,false)->localized_name = "Material Type";
	$this->addAttributeMapping('', 'included_features',true,false)->localized_name = 'Included Features'; //Indicates whether the product is organic, biodegradable etc.

	//$this->addAttributeMapping('unit_count_type', 'unit_count_type')->localized_name = 'Unit Count Type';

?>