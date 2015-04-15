<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	//3 product types: Handbag, Shoe-accessory, Shoes
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Model number';

/** Offer **/
	//A date in this format: yyyy-mm-dd.
	$this->addAttributeMapping('', 'product_site_launch_date',true,false)->localized_name = 'Launch Date'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Discovery ***/
/*** Some more preferred/optional attributes ***/
	$this->addAttributeMapping('', 'department_name',true,true)->localized_name = 'Department'; //men or women or unisex
    $this->addAttributeMapping('', 'style_name',true,false)->localized_name = 'Style Name'; //Provide the style name that best fits the product. Select from valid values.
	$this->addAttributeMapping('', 'color_name',true,true)->localized_name = 'Colour'; 
	$this->addAttributeMapping('', 'color_map',true,true)->localized_name = 'Colour Map'; 
	$this->addAttributeMapping('', 'collection_name',true,false)->localized_name = 'Collection Name'; //List the Season/Year of the collection for the product.
	$this->addAttributeMapping('', 'size_name',true,true)->localized_name = 'Size Name';// Size Example:- US 10 or UK 11
	$this->addAttributeMapping('', 'size_map',true,true)->localized_name = 'Size Map';//Select from the list of  Valid Values provided in a later tab within this workbook.
	$this->addAttributeMapping('', 'outer_material_type',true,false)->localized_name = 'Outer Material Type';// Leather etc.
?>