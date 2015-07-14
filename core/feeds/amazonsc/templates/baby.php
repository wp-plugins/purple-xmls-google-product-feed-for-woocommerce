<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

/*** Basic ***/
	//1 product type
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	
/*** Discovery ***/
	//Browse node - a positive integer, example: 243826011
	$this->addAttributeMapping( '', 'recommended_browse_nodes1' . $i, true,true )->localized_name = 'Recommended Browse Node1';
	$this->addAttributeMapping( '', 'recommended_browse_nodes2' . $i, true,false )->localized_name = 'Recommended Browse Node2';
	
/*** Baby Product ***/
	$this->addAttributeMapping('','mfg_minimum',true,true)->localized_name = 'Minimum Manufacturer Age Recommended'; //positive integer: 12
	$this->addAttributeMapping('','mfg_minimum_unit_of_measure',true,true)->localized_name = 'Mfg Minimum Unit Of Measure'; //months or years
	$this->addAttributeMapping('','mfg_maximum',true,true)->localized_name = 'Maximum Manufacturer Age Recommended'; //positive integer: 8
	$this->addAttributeMapping('','mfg_maximum_unit_of_measure',true,true)->localized_name = 'Mfg Maximum Unit Of Measure'; //years
?>