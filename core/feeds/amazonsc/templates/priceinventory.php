<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-03
	//********************************************************************

	$this->addAttributeMapping('sku', 'sku', false, true);
	$this->addAttributeMapping('price', 'price', false, true);
	$this->addAttributeMapping('', 'minimum-seller-allowed-price', false, false);
	$this->addAttributeMapping('', 'maximum-seller-allowed-price', false, false);
	$this->addAttributeMapping('quantity', 'quantity', false, true);
	$this->addAttributeMapping('leadtime_to_ship', 'leadtime-to-ship', false, true);
	$this->addAttributeMapping('', 'fulfillment-channel', false, false);
?>