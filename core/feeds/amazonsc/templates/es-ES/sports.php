<?php
	//********************************************************************
	//ES Amazon Seller Template
	//2015-06
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';

/*** Offer ***/
	$this->addAttributeMapping('regular_price', 'standard_price',true,false)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,false)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 
	//$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

	$this->addAttributeMapping('', 'size_name',true,false)->localized_name = 'Size'; 

?>