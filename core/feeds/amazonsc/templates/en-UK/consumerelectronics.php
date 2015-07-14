<?php
	//********************************************************************
	//UK Amazon Seller Template
	//2015-02
	//********************************************************************
// 30 product types: 
/*** Basic ***/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Model Number';

/** Offer **/
	//A date in this format: yyyy-mm-dd.
	$this->addAttributeMapping('regular_price', 'standard_price',true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency',true,true)->localized_name = 'Currency';  //GBP
	$this->addAttributeMapping('quantity', 'quantity',true,true)->localized_name = 'Quantity'; 
	$this->addAttributeMapping('condition', 'condition_type',true,false)->localized_name = 'Condition Type'; 

/*** Dimenstions ***/
/*** Some more preferred/optional attributes ***/
	$this->addAttributeMapping('', 'color_map',true,false)->localized_name = 'Colour Map';  
	$this->addAttributeMapping('', 'color_name',true,false)->localized_name = 'Colour'; 
	//$this->addAttributeMapping('', 'size_map',true,true)->localized_name = 'Size Map'; 
	$this->addAttributeMapping('', 'size_name',true,false)->localized_name = 'Size';

		
?>