<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';

/** Offer **/
	//$this->addAttributeMapping('condition', 'condition_type',true,true)->localized_name = 'Condition Type'; 
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('quantity', 'quantity',true,true)->localized_name = 'Quantity'; 

/*** Add some optional/preferred attributes ***/
	$this->addAttributeMapping('', 'size_name',true,false)->localized_name = 'Size'; 
	$this->addAttributeMapping('', 'color_name',true,false)->localized_name = 'Colour';
	$this->addAttributeMapping('', 'color_map',true,false)->localized_name = 'Colour Map'; 

?>