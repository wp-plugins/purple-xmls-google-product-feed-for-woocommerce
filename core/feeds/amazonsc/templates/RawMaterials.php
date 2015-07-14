<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('price', 'list_price',true,false)->localized_name = 'Manufacturer\'s Suggested Retail Price';
	$this->addAttributeMapping('number_of_items', 'number_of_items',true,true)->localized_name = 'Number Of Items';

	$this->addAttributeMapping('', 'material_type',true,true)->localized_name = 'Material Type'; //material: Gold
	$this->addAttributeMapping('', 'measurement_system')->localized_name = 'System of Measurement'; //English or Metric

?>