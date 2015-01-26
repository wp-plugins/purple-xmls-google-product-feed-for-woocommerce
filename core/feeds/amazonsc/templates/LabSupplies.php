<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('number_of_items', 'number_of_items',false,true)->localized_name = 'Number Of Items';
	$this->addAttributeMapping('fulfillment_latency', 'fulfillment_latency',true,true)->localized_name = 'Fulfillment Latency';

	//$this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Product Name';
	//$this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand Name';
	//$this->addAttributeMapping('item_type', 'item_type')->localized_name = 'Item Type Keyword';
	//$this->addAttributeMapping('item_weight', 'item_weight')->localized_name = 'Item Weight';
	//$this->addAttributeMapping('item_weight_unit_of_measure', 'item_weight_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';
	
	
?>