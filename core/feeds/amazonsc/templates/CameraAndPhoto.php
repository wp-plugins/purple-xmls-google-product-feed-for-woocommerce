
<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'external_product_id',true,true)->localized_name = 'Product ID';
	//$this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Title';
	//$this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand';
	$this->addAttributeMapping('', 'feed_product_type',false,true)->localized_name = 'Item Type';
	/* valid values for feed product type (camera and photos)
	BagCase
	Binocular
	BlankMedia
	Camcorder
	Darkroom
	DigitalCamera
	DigitalFrame
	Film
	FilmCamera
	Filter
	Flash
	ImagingAccessory
	Lens
	LightMeter
	Lighting
	Microscope
	PhotoPaper
	PhotoStudio
	Projection
	SecurityAndSurveillance
	Telescope
	Tripod
	*/
	$this->addAttributeMapping('', 'part_number',true,true)->localized_name = 'Mfr Part Number';
	
?>