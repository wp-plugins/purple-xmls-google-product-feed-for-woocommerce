<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	//luggage: Luggage
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	//$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Model Number';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';
	
/** Offer **/
	//Indicates how many items are in the package.  For example, a box of 12 health bars, each of which can be sold individually, would have a count of 12.
	$this->addAttributeMapping('', 'item_package_quantity',true,false)->localized_name = 'Number of items in package'; 
	$this->addAttributeMapping('', 'product_site_launch_date',true,false)->localized_name = 'Launch Date'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity';
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Dimensions ***/
	$this->addAttributeMapping('', 'volume_capacity_name',true,true)->localized_name = 'Volume or capacity';
	$this->addAttributeMapping('', 'volume_capacity_name_unit_of_measure',true,true)->localized_name = 'Volume Capacity Name Unit Of Measure';

/*** Some more preferred/optional attributes ***/
	//$this->addAttributeMapping('', 'voltage',true,false)->localized_name = 'Voltage';  
	//$this->addAttributeMapping('', 'wattage',true,false)->localized_name = 'Wattage';
	//$this->addAttributeMapping('', 'size_name',true,false)->localized_name = 'Size';  
	//$this->addAttributeMapping('', 'item_gender',true,false)->localized_name = 'Item gender'; 
	$this->addAttributeMapping('', 'color_name',true,true)->localized_name = 'Color';
	$this->addAttributeMapping('', 'color_map',true,true)->localized_name = 'Standard Color'; //valid values
?>