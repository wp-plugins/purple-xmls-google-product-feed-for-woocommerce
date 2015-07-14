<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-03
	//********************************************************************

/*** Basic ***/
	//17 types for Clothing
	$this->addAttributeMapping('feed_product_type', 'product_subtype',true,true)->localized_name = 'Product type';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';

/** Offer **/
	//$this->addAttributeMapping('product_site_launch_date', 'product_site_launch_date',true,true)->localized_name = 'Launch Date'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 
	//$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Discovery ***/
//bullet point1-5

/*** Other required product attributes ***/
	$this->addAttributeMapping('', 'color_map',true,true)->localized_name = 'Colour Map';  
	$this->addAttributeMapping('', 'color_name',true,true)->localized_name = 'Colour'; 
	$this->addAttributeMapping('', 'size_map',true,true)->localized_name = 'Size Map'; 
	$this->addAttributeMapping('', 'size_name',true,true)->localized_name = 'Size';
	$this->addAttributeMapping('', 'material_composition',true,true)->localized_name = 'Material Composition';
	$this->addAttributeMapping('', 'outer_material_type',true,true)->localized_name = 'Outer Material Type';
	$this->addAttributeMapping('', 'is_adult_product',true,false)->localized_name = 'Adult Flag';

?>