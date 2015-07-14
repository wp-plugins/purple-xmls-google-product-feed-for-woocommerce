<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	/* valid values for feed product type (Gift Cards):
	electronicgiftcard
	physicalgiftcard
	*/
//Discovery
	//Use this to specify the target audience for your product. Example: Amphibians. Refer to BTG
	$this->addAttributeMapping('', 'target_audience_keywords1',true,true)->localized_name = 'Target Audience1';
	for ($i = 2; $i <= 5; $i++)
		$this->addAttributeMapping('', 'target_audience_keywords' . $i, true,false)->localized_name = 'Target Audience' . $i;
//Compliance
	$this->addAttributeMapping('', 'legal_disclaimer_description')->localized_name = 'Legal Disclaimer'; //Describes any legal language needed with the product (500 chars)
//Gift Card
	$this->addAttributeMapping('', "state_string1", true)->localized_name = 'State1'; //state code..
	for ($i = 2; $i < 4; $i++)
		$this->addAttributeMapping('', "state_string$i", false)->localized_name = 'State' . $i; //state code..
	$this->addAttributeMapping('format', 'format')->localized_name = 'Format'; //ex: email, plastic, facebook, print at home or multi-pack
	$this->addAttributeMapping('', "genre1", true)->localized_name = 'Category1'; //state code..
	for ($i = 2; $i < 4; $i++)
		$this->addAttributeMapping('', "genre$i", false)->localized_name = 'Category' . $i; //state code..

?>