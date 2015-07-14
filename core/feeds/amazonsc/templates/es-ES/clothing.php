<?php
	//********************************************************************
	//ES Amazon Seller Template
	//2015-06
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('feed_product_type', 'product_subtype',true,true)->localized_name = 'Clothing Type';

/*** Offer ***/
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 
	//$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

	$this->addAttributeMapping('', 'size_name',true,false)->localized_name = 'Size'; 
	$this->addAttributeMapping('', 'color_name',true,false)->localized_name = 'Color'; 
	$this->addAttributeMapping('', 'style_name',true,false)->localized_name = 'Style'; 
	$this->addAttributeMapping('', 'department_name',true,false)->localized_name = 'Department'; 

?>