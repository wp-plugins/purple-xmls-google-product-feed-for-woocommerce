<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	//no feed_product_type

/*** Wireless Accessories ***/
	$this->addAttributeMapping('', 'color_name', true)->localized_name = 'Color';
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'special_features'. $i, true)->localized_name = 'Additional Features'. $i;
	$this->addAttributeMapping('', 'phone_talk_time', true)->localized_name = 'Talk Time';
	$this->addAttributeMapping('', 'phone_standby_time', true)->localized_name = 'Standby Time';
	$this->addAttributeMapping('', 'battery_charge_time_unit_of_measure', true)->localized_name = 'Battery Charge Time Unit Of Measure';
	$this->addAttributeMapping('', 'battery_power', true)->localized_name = 'Battery Power';
	
?>