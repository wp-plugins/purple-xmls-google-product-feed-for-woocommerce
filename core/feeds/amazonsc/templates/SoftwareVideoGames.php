<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-01
	//********************************************************************

/* Feed Product Types
Software
SoftwareGames
VideoGames
VideoGamesAccessories
VideoGamesHardware
*/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type', true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number', true)->localized_name = 'Manufacturer Part Number';
	
?>