<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

/* Feed Product Types for Office Products
ArtSupplies
BarCodeReader
Calculator
EducationalSupplies
InkOrToner
MultiFunctionDevice
OfficeElectronics
OfficeProducts
PaperProducts
Phone
Printer
Scanner
VoiceRecorder
WritingInstruments
*/
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	
	$this->addAttributeMapping('', 'gtin_exemption_reason')->localized_name = 'Product Exemption Reason'; 
	$this->addAttributeMapping('', 'related_product_id')->localized_name = 'Related Product Identifier'; 
	$this->addAttributeMapping('', 'related_product_id_type')->localized_name = 'Related Product Identifier Type'; 

?>