<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('external_product_id', 'external_product_id', true,false)->localized_name = 'Product ID';
	//$this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Title';
	$this->addAttributeMapping('', 'feed_product_type',true,true)->localized_name = 'Product Type';
	//Browse node - a positive integer, example: 243826011
	for ($i = 1; $i <= 2; $i++)
		$this->addAttributeMapping( '- browse nodes from BTG -', 'recommended_browse_nodes' . $i, true,true )->localized_name = 'Recommended Browse Node' . $i;
	$this->addAttributeMapping('','mfg_minimum',true,true)->localized_name = 'Minimum Manufacturer Age Recommended'; //positive integer: 12
	$this->addAttributeMapping('','mfg_minimum_unit_of_measure',true,true)->localized_name = 'Mfg Minimum Unit Of Measure'; //months or years
	$this->addAttributeMapping('','mfg_maximum',true,true)->localized_name = 'Maximum Manufacturer Age Recommended'; //positive integer: 8
	$this->addAttributeMapping('','mfg_maximum_unit_of_measure',true,true)->localized_name = 'Mfg Maximum Unit Of Measure'; //years
?>