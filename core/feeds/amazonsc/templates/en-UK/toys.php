<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product type';
	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';

/*** Offer ***/
	$this->addAttributeMapping('regular_price', 'standard_price',true,false)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,false)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Toys and Games ***/
	$this->addAttributeMapping('mfg_minimum', 'mfg_minimum',true,true)->localized_name = 'Minimum Age Recommendation';
	$this->addAttributeMapping('mfg_minimum_unit_of_measure', 'mfg_minimum_unit_of_measure',true,true)->localized_name = 'Mfg Minimum Unit Of Measure';
	$this->addAttributeMapping('mfg_maximum', 'mfg_maximum',true,true)->localized_name = 'Maximum Age Recommendation'; 
	$this->addAttributeMapping('mfg_maximum_unit_of_measure', 'mfg_maximum_unit_of_measure',true,true)->localized_name = 'Mfg Maximum Unit Of Measure'; 

?>