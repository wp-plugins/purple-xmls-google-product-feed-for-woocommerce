<?php
	//********************************************************************
	//Kelkoo categories
	//Fashion and Fashion accessories
	//2015-01 Calv
	//********************************************************************

	$this->addAttributeMapping('', 'fashion-type', true, false); //Product type (sweater, shoes, jacket, etc..).
	$this->addAttributeMapping('', 'fashion-gender', true, false); //Gender (male, female, child, mixed).
	//Size of the article. If the item is available in different sizes, we recommend that you provide one single offer for each size or use the ";" delimiter to separate each value.
	$this->addAttributeMapping('', 'fashion-size', true, false); 
	
?>