<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-01
	//********************************************************************

/* Feed Product Types
GolfClubHybrid
GolfClubIron
GolfClubPutter
GolfClubWedge
GolfClubWood
GolfClubs
SportingGoods
*/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type', true,false)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number', true)->localized_name = 'Manufacturer Part Number';
	
?>
