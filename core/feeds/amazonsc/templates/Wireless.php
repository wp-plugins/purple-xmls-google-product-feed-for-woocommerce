<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'external_product_id',true,true)->localized_name = 'Product ID';
	//$this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Title';
	//$this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand';

	$this->addAttributeMapping('', 'color_name', true)->localized_name = 'Color';
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'special_features'. $i, true)->localized_name = 'Additional Features'. $i;
	$this->addAttributeMapping('', 'phone_talk_time', true)->localized_name = 'Talk Time';
	$this->addAttributeMapping('', 'phone_standby_time', true)->localized_name = 'Standby Time';
	$this->addAttributeMapping('', 'battery_charge_time_unit_of_measure', true)->localized_name = 'Battery Charge Time Unit Of Measure';
	$this->addAttributeMapping('', 'battery_power', true)->localized_name = 'Battery Power';

	
?>