<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	//7 product types: Auto Accessories, Auto Battery, Auto Chemical, Auto Oil, Auto Part, Moto Accessories, Moto Parts
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';

/** Offer **/
	//A date in this format: yyyy-mm-dd.
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('regular_price', 'standard_price',true,false)->localized_name = 'Standard Price';
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('item_package_quantity', 'item_package_quantity',true,true)->localized_name = 'Package Quantity'; 
	$this->addAttributeMapping('', 'number_of_items',true,true)->localized_name = 'Number of Items';
	$this->addAttributeMapping('condition', 'condition_type',true,true)->localized_name = 'Item Condition'; 
	$this->addAttributeMapping('', 'condition_note',true,false)->localized_name = 'Offer Condition Type';

/*** Dimenstions ***/
	$this->addAttributeMapping('', 'item_display_volume',true,true)->localized_name = 'Item Display Volume'; // Volume of the product displayed on the product detail page. Number up to 10 digits and 2 decimal points long.
	$this->addAttributeMapping('', 'item_display_volume_unit_of_measure',true,true)->localized_name = 'Item Display Volume Unit of Measure'; //Unit of measure used to describe the item display volume. For Example: mL or L.

/*** Some more preferred/optional attributes ***/
	$this->addAttributeMapping('', 'viscosity', true,false)->localized_name = 'Viscosity'; 
	$this->addAttributeMapping('', 'battery_form_factor', true,false)->localized_name = 'Battery Form Factor'; // Form factor for vehicle batteries.
	$this->addAttributeMapping('', 'battery_type', true,false)->localized_name = 'Battery Type'; // Type of battery.
	$this->addAttributeMapping('', 'battery_cell_composition', true,false)->localized_name = 'Battery Composition'; 
	$this->addAttributeMapping('', 'battery_weight', true,false)->localized_name = 'Battery Weight'; 
	$this->addAttributeMapping('', 'battery_weight_unit_of_measure', true,false)->localized_name = 'Battery Weight Unit of Measure'; 
	
?>