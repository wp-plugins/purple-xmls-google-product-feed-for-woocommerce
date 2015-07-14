<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	//3 product types: BedAndBath, FurnitureAndDecor, Home
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';

/** Offer **/
	//A date in this format: yyyy-mm-dd.
	$this->addAttributeMapping('', 'product_site_launch_date',true,false)->localized_name = 'Launch Date'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,true)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Discovery ***/
//already in all.templates
	
/*** Some more preferred/optional attributes ***/
	for ($i = 1; $i <= 3; $i++)
	$this->addAttributeMapping('', 'material_type' . $i, true,false)->localized_name = 'Material Type' . $i;
	
	$this->addAttributeMapping('', 'color_name',true,false)->localized_name = 'Colour'; 
	for ($i = 1; $i <= 2; $i++)
		$this->addAttributeMapping('color_map'.$i, 'color_map' . $i, true,false)->localized_name = 'Color Map' . $i;

?>