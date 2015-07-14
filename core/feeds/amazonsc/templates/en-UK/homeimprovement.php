<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	//HomeImprovement: 7 product types
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	//$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Model Number';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Part Number';
	
/** Offer **/
	//Indicates how many items are in the package.  For example, a box of 12 health bars, each of which can be sold individually, would have a count of 12.
	$this->addAttributeMapping('', 'item_package_quantity',true,false)->localized_name = 'Package Quantity'; 
	$this->addAttributeMapping('', 'product_site_launch_date',true,false)->localized_name = 'Launch Date'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,false)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity';
	$this->addAttributeMapping('condition', 'condition_type',true,true)->localized_name = 'Condition Type'; 

/*** Some more preferred/optional attributes ***/
	$this->addAttributeMapping('', 'are_batteries_included',true,false)->localized_name = 'Batteries are Included';  
	$this->addAttributeMapping('', 'batteries_required',true,false)->localized_name = 'Are Batteries Required';
	//$this->addAttributeMapping('', 'size_name',true,false)->localized_name = 'Size';  
	//$this->addAttributeMapping('', 'item_gender',true,false)->localized_name = 'Item gender';
	//$this->addAttributeMapping('', '',true,false)->localized_name = '';  
	//$this->addAttributeMapping('', '',true,false)->localized_name = '';  

?>