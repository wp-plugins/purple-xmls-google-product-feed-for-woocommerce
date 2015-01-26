<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('standard_price', 'list_price',true,false)->localized_name = 'Manufacturer\'s Suggested Retail Price';
	$this->addAttributeMapping('number_of_items', 'number_of_items',true,true)->localized_name = 'Number Of Items';
	$this->addAttributeMapping('fulfillment_latency', 'fulfillment_latency',true,true)->localized_name = 'Fulfillment Latency';
	$this->addAttributeMapping('material_type', 'material_type',true,true)->localized_name = 'Material Type'; //material: Gold
	$this->addAttributeMapping('measurement_system', 'measurement_system')->localized_name = 'System of Measurement'; //English or Metric

?>