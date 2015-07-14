<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	//$this->addAttributeMapping('item_type', 'item_type',true,true)->localized_name = 'Item Type';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';

/** Offer **/
	$this->addAttributeMapping('condition', 'condition_type',true,true)->localized_name = 'Item Condition'; 
	$this->addAttributeMapping('', 'condition_note',true,false)->localized_name = 'Offer Condition Note'; 
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('regular_price', 'standard_price',true,false)->localized_name = 'Standard Price';
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 

/*** Add some optional/preferred attributes ***/
	$this->addAttributeMapping('', 'special_features',true,false)->localized_name = 'Special Features'; 
	$this->addAttributeMapping('', 'platform',true,false)->localized_name = 'Platform'; 

?>