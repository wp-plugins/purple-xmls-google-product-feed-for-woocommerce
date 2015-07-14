<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

/*** Basic ***/
	//List of AutoAccessory Product Types: AutoAccessoryMisc, AutoPart, Helmet, PowersportsPart, ProtectiveGear,RidingApparel
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';

/*** Offer ***/
	$this->addAttributeMapping('item_package_quantity', 'item_package_quantity',true,true)->localized_name = 'Package Quantity';

/*** Discovery ***/ 
	$this->addAttributeMapping('', 'style_keywords1',true,true)->localized_name = 'Style-specific Terms1'; //A word or phrase that best describes the product. Select from valid values tab

	for ($i = 2; $i <= 4; $i++)
		$this->addAttributeMapping( '', 'style_keywords' . $i, false )->localized_name = 'Style-specific Terms' . $i;
/*** Motorsport vehicle part ***/	
	$this->addAttributeMapping('', 'department_name1',true,true)->localized_name = 'Department1';

	for ($i = 2; $i <= 4; $i++)
		$this->addAttributeMapping( '', 'department_name' . $i, true )->localized_name = 'Department' . $i;
	
	$this->addAttributeMapping('', 'color_name')->localized_name = 'Color'; //ex: Navy Blue	
	$this->addAttributeMapping('', 'model_name')->localized_name = 'Series'; //ex: Aspire (series/chassis of the product)
	$this->addAttributeMapping('', 'size_name')->localized_name = 'Size'; //ex: Kids, Husky
?>