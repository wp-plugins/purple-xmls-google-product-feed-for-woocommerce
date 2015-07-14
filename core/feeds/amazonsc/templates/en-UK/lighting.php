<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	//Lighting
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	//$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Model Number';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';
	
/** Offer **/
	//Indicates how many items are in the package.  For example, a box of 12 health bars, each of which can be sold individually, would have a count of 12.
	$this->addAttributeMapping('', 'item_package_quantity',true,false)->localized_name = 'Package Quantity'; 
	//$this->addAttributeMapping('', 'product_site_launch_date',true,false)->localized_name = 'Launch Date'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,true)->localized_name = 'Quantity';
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Some more preferred/optional attributes ***/
	/** Light Bulbs **/
	$this->addAttributeMapping('', 'cap_type',true,false)->localized_name = 'Cap Type'; // Industry identifier for the cap type or part of the lamp which provides connection to the electrical supply by means of a socket or lamp connector. his attribute will be used for refinement filters in the lighting shop, so we recommend you provide this value for all lamps and lightbulbs.
	$this->addAttributeMapping('', 'specific_uses_for_product',true,false)->localized_name = 'Specific Uses'; //Specifies appropriate uses for this product.
	$this->addAttributeMapping('', 'light_source_type',true,false)->localized_name = 'Type of Bulb';//Indicates the type of bulb. A value must be specified for bulbs and lamps sold with a bulb. If the lamp has multiple bulbs (e.g.  a torchiere with an additional reading spot lamp), specify main type.  This attribute will be used for refinement filters in the lighting shop, so we recommend you provide this value for all lamps and lightbulbs.
	$this->addAttributeMapping('', 'efficiency',true,false)->localized_name = 'EU Energy Efficiency Label'; //The EU Energy Efficiency Rating of a bulb. This attribute will be used for refinement filters in the lighting shop, so we recommend you provide this value for all lamps and lightbulbs.
	$this->addAttributeMapping('', 'are_batteries_included',true,false)->localized_name = 'Batteries are Included'; // True or False
	$this->addAttributeMapping('', 'batteries_required',true,false)->localized_name = 'Are Batteries Required'; // Ture or False.

	
?>