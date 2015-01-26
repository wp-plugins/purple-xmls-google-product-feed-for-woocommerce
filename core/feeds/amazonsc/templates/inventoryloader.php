<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('sku', 'sku', false, true);
	$this->addAttributeMapping('product-id', 'product-id', false, false);
	//$this->addAttributeMapping('product-id-type', 'product-id-type');
	$this->addAttributeMapping('price', 'price', false, false);
	$this->addAttributeMapping('minimum-seller-allowed-price', 'minimum-seller-allowed-price', false, false);
	$this->addAttributeMapping('maximum-seller-allowed-price', 'maximum-seller-allowed-price', false, false);
	$this->addAttributeMapping('item-condition', 'item-condition', false, false);
	$this->addAttributeMapping('quantity', 'quantity', false, false);
	$this->addAttributeMapping('add-delete', 'add-delete', false, false);
	$this->addAttributeMapping('will-ship-internationally', 'will-ship-internationally', false, false);
	$this->addAttributeMapping('expedited-shipping', 'expedited-shipping', false, false);
	$this->addAttributeMapping('standard-plus', 'standard-plus', false, false);
	$this->addAttributeMapping('item-note', 'item-note', false, false);
	$this->addAttributeMapping('fulfillment-center-id', 'fulfillment-center-id', false, false);
	$this->addAttributeMapping('product-tax-code', 'product-tax-code', false, false);
	$this->addAttributeMapping('leadtime-to-ship', 'leadtime-to-ship', false, false);
?>