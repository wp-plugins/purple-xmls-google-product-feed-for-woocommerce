<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Manufacturer Part Number';
	//Use this to specify the target audience for your product. Example: Amphibians. Refer to BTG
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'target_audience_keywords' . $i, true)->localized_name = 'Target Audience' . $i;
?>