<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-01
	//********************************************************************

/* valid feed product type values
	BuildingMaterials
	Electrical
	Hardware
	MajorHomeAppliances
	OrganizersAndStorage
	PlumbingFixtures
	SecurityElectronics
	Tools
*/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
?>