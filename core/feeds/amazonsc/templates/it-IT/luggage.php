<?php
//********************************************************************
//Italiano Amazon Seller Template
//2015-02
//********************************************************************

/*** Baisc ***/	
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Model number';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer part number';

/*** Offer ***/
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency'; 
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('', 'condition_type',true,false)->localized_name = 'Condition type'; 

/*** Dimension ***/
	$this->addAttributeMapping('weight', 'item_weight',true,true)->localized_name = 'Item Weight';
	$this->addAttributeMapping('weight_unit_word', 'item_weight_unit_of_measure',true,true)->localized_name = 'Unit of measure of item weight';
	$this->addAttributeMapping('', 'volume_capacity_name',true,true)->localized_name = 'Volume or capacity'; 	//Indicates the volume of the bag or suitcase (in liters).
	$this->addAttributeMapping('', 'volume_capacity_name_unit_of_measure',true,true)->localized_name = 'Volume Capacity Name Unit Of Measure';
	//volume capacity: select from these values:  cup, gallon, liter, ounce, pint, quart

/*** Luggage attributes ***/
	$this->addAttributeMapping('color', 'color_name',true,true)->localized_name = 'Color';
	/* color map valid values:
	Arancione,Argento,Avorio,Beige,Bianco,Blu,Giallo,Grigio,Marrone,Multicolore,
	Nero,Oro,Rosa,Rosso,Trasparente,Turchese,Verde,Viola
	*/
	$this->addAttributeMapping('', 'color_map',true,true)->localized_name = 'Standard color';


?>