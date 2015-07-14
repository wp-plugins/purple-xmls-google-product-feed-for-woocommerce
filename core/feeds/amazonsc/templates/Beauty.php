<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

/*** Basic ***/	
	//1 feed product type: BeautyMisc
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
	
/*** Beauty ***/
	//these are required
	$this->addAttributeMapping('', 'unit_count',true,true)->localized_name = 'Unit Count';
	$this->addAttributeMapping('', 'unit_count_type',true,true)->localized_name = 'Unit Count Type';
?>