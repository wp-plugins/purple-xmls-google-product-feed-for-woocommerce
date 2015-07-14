<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

//Industrial & Scientific > Raw Materials
//If applicable, please submit the manufacturer's part number for the product.  
//For most products, this will be identical to the model number; however, some manufacturers distinguish part number from model number.
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('number_of_items', 'number_of_items',true,true)->localized_name = 'Number Of Items';

	$this->addAttributeMapping('', 'material_type', true)->localized_name = 'Material Type'; //ex: stainless_steel
	//$this->addAttributeMapping('weight_unit_word', 'item_weight_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';
	
?>