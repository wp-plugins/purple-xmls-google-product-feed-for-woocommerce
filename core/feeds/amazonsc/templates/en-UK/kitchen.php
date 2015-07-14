<?php
	//********************************************************************
	//UK Amazon Seller Template for Kitchen uk
	//2015-03
	//********************************************************************

/*** Basic ***/
	//1 type: kitchen
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';

/** Offer **/
	$this->addAttributeMapping('', 'product_site_launch_date',true,true)->localized_name = 'Launch Date'; 
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,true)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 


?>