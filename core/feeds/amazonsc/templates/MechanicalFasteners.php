<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'part_number', true,true)->localized_name = 'Part Number';
	$this->addAttributeMapping('', 'model', true)->localized_name = 'Model';
	//$this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand Name';
	$this->addAttributeMapping('number_of_items', 'number_of_items',true,true)->localized_name = 'Number Of Items';
	$this->addAttributeMapping('handling_time', 'fulfillment_latency')->localized_name = 'Fulfillment Latency';

?>