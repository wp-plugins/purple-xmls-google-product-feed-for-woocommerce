<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

/*** Basic ***/
	//CustomProductBundle, ReplacementPart
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';

/*** Offer ***/
	$this->addAttributeMapping('item_package_quantity', 'item_package_quantity',true,false)->localized_name = 'Package Quantity';

/*** Discovery ***/ 
	$this->addAttributeMapping('target_audience_keywords1', 'target_audience_keywords1',true,true)->localized_name = 'Target Audience1';

	for ($i = 2; $i < 4; $i++)
		$this->addAttributeMapping( '', 'target_audience_keywords' . $i, false )->localized_name = 'Target Audience' . $i;

/*** Wheel ***/	
	$this->addAttributeMapping('', 'model_name')->localized_name = 'Series'; //ex: Aspire (series/chassis of the product)
	$this->addAttributeMapping('', 'pitch_circle_diameter1',true,true)->localized_name = 'Pitch Circle Diameter1';
	$this->addAttributeMapping('', 'pitch_circle_diameter2',true,false)->localized_name = 'Pitch Circle Diameter2';
	
	$this->addAttributeMapping('', 'color_name')->localized_name = 'Color'; //ex: Navy Blue	
	//$this->addAttributeMapping('', 'size_name')->localized_name = 'Size'; //ex: Kids, Husky
?>