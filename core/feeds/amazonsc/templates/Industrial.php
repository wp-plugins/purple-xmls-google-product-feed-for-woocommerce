<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	/* Feed product type for Industrial -> Other
	Abrasives
	AdhesivesAndSealants
	CuttingTools
	ElectronicComponents
	Gears
	Grommets
	IndustrialHose
	IndustrialWheels
	MechanicalComponents
	ORings
	PrecisionMeasuring
	*/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'material_type', true,false)->localized_name = 'Product Name';
	//also required
	$this->addAttributeMapping('number_of_items', 'number_of_items',true,true)->localized_name = 'Number Of Items';
	
?>