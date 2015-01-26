<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************
	$this->addAttributeMapping('', 'external_product_id',true,true)->localized_name = 'Product ID';
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type'; //ex: BeautyMisc

//required for 'Beauty' according to the template.. leave optional for now
	$this->addAttributeMapping('', 'unit_count',true,false)->localized_name = 'Unit Count';
	$this->addAttributeMapping('', 'unit_count_type',true,false)->localized_name = 'Unit Count Type';
?>