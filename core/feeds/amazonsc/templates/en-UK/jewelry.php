<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
//upc is preferred (not required)
	//8 product types
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('', 'model',true,true)->localized_name = 'Model';

/** Offer **/
	//A date in this format: yyyy-mm-dd.
	$this->addAttributeMapping('', 'product_site_launch_date',true,false)->localized_name = 'Launch Date'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,true)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Discovery ***/
	//Specify the product's target audience. Ex Women
	$this->addAttributeMapping('target_audience_base','target_audience_base', true,true)->localized_name = 'Target Audience';
	
/*** Some more preferred/optional attributes ***/
	for ($i = 1; $i <= 3; $i++)
	$this->addAttributeMapping('', 'material_type' . $i, true,false)->localized_name = 'Material Type' . $i;
	
	$this->addAttributeMapping('', 'color_name',true,false)->localized_name = 'Colour'; 
	for ($i = 1; $i <= 2; $i++)
		$this->addAttributeMapping(''.$i, 'color_map' . $i, true,false)->localized_name = 'Color Map' . $i;

?>