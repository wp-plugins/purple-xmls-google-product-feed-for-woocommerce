<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	
/** Offer **/
	//Indicates how many items are in the package.  For example, a box of 12 health bars, each of which can be sold individually, would have a count of 12.
	$this->addAttributeMapping('item_package_quantity', 'item_package_quantity',true,true)->localized_name = 'Package Quantity'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,false)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity';
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Some more preferred/optional attributes ***/
	$this->addAttributeMapping('', 'size_map',true,false)->localized_name = 'Cuff Size';  
	$this->addAttributeMapping('', 'unit_count',true,false)->localized_name = 'Unit Count'; 

?>