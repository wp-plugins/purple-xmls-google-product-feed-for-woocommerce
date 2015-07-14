<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-01
	//********************************************************************

/* Feed Product Types:
BrassAndWoodwindInstruments
Guitars
InstrumentPartsAndAccessories
KeyboardInstruments
MiscWorldInstruments
PercussionInstruments
SoundAndRecordingEquipment
StringedInstruments
*/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';

	$this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number';
	$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Model Number';


?>