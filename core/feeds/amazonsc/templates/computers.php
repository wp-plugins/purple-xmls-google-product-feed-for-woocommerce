<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
/* valid values for feed product type (Compouters)
CarryingCaseOrBag
Computer
ComputerAddOn
ComputerComponent
ComputerCoolingDevice
ComputerDriveOrStorage
ComputerInputDevice
ComputerProcessor
ComputerSpeaker
FlashMemory
Keyboards
MemoryReader
Monitor
Motherboard
NetworkingDevice
NotebookComputer
PersonalComputer
RAMMemory
SoundCard
SystemCabinet
SystemPowerDevice
TabletComputer
VideoCard
VideoProjector
Webcam
*/
	$this->addAttributeMapping('', 'part_number', true)->localized_name = 'Mfr Part Number';
	$this->addAttributeMapping('', 'model', true)->localized_name = 'Model Number';
	//$this->addAttributeMapping('item_type', 'item_type')->localized_name = 'Item Type';
	//$this->addAttributeMapping('item_weight', 'item_weight')->localized_name = 'Item Weight';
	//$this->addAttributeMapping('item_weight_unit_of_measure', 'item_weight_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';

?>