<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'part_number',true)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('', 'swatch_image_url',true)->localized_name = 'Swatch Image URL';
	
	//Use this to specify the target audience for your product. Example: Amphibians. Refer to BTG
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'target_audience_keywords' . $i, false)->localized_name = 'Target Audience' . $i;
	
	//Apparel (non-saleable base product type) required
	$this->addAttributeMapping('mfg_minimum','mfg_minimum')->localized_name = 'Minimum Manufacturer Age Recommended'; //positive integer: 12
	$this->addAttributeMapping('mfg_minimum_unit_of_measure','mfg_minimum_unit_of_measure')->localized_name = 'Mfg Minimum Unit Of Measure'; //months or years

?>