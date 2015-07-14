<?php
	//********************************************************************
	//Kelkoo categories
	//Mobilephones with subscription and Prepaid cards
	//2015-01 Calv
	//********************************************************************

	$this->addAttributeMapping('', 'mobilephone-network', true, false); //The operator or provider for the subscription (Telenor, Netcom, Vodaphone, Orange, Tele2, etc.)
	$this->addAttributeMapping('', 'mobilephone-contract-type', true, false); //Type of contract. Subscription information for the mobile phone.
	$this->addAttributeMapping('', 'mobilephone-contract-length', true, false); 
	$this->addAttributeMapping('', 'mobilephone-contract-total-cost', true, false); 
	$this->addAttributeMapping('', 'mobilephone-tariff', true, false); 
	$this->addAttributeMapping('', 'fashion-size', true, false); 
	$this->addAttributeMapping('', 'mobilephone-monthly-hours', true, false); //Number of hours of communication per month included.
?>