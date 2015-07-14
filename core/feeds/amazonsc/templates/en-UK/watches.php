<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************

/*** Basic ***/
	//No Feed Product Type required.
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';

/*** Offer ***/
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,false)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Discovery ***/
	//Specify the recipient for this item. Ex: Women's
	$this->addAttributeMapping('target_audience_keywords','target_audience_keywords', true,true)->localized_name = 'Target Audience';

/*** Watch ***/
	$this->addAttributeMapping('', 'display_type',true,true)->localized_name = 'Display Type';  //Chronograph or Digital
	$this->addAttributeMapping('', 'watch_movement_type',true,true)->localized_name = 'Watch Movement Type';  //Automatic Self-wind

	$this->addAttributeMapping('', 'band_material_type',true,false)->localized_name = 'Band Material Type'; //gold and platum 
	$this->addAttributeMapping('', 'band_color',true,false)->localized_name = 'Band Colour'; //Black 

?>