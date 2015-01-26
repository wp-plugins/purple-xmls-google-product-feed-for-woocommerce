<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	//$this->addAttributeMapping('external_product_id', 'external_product_id')->localized_name = 'Product ID';
	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	/* valid values for feed product type (Gift Cards):
	electronicgiftcard
	physicalgiftcard
	*/
	//$this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand Name';
	//$this->addAttributeMapping('item_type', 'item_type')->localized_name = 'Category (item-type)';
	//$this->addAttributeMapping('item_weight', 'item_weight')->localized_name = 'Item Weight';
	
	//Use this to specify the target audience for your product. Example: Amphibians. Refer to BTG
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('target_audience_keywords' . $i, 'target_audience_keywords' . $i, true)->localized_name = 'Target Audience' . $i;
	$this->addAttributeMapping('legal_disclaimer_description', 'legal_disclaimer_description')->localized_name = 'Legal Disclaimer'; //Describes any legal language needed with the product (500 chars)
	for ($i = 1; $i < 4; $i++)
		$this->addAttributeMapping("state_string$i", "state_string$i", true)->localized_name = 'State' . $i; //state code..
	$this->addAttributeMapping('format', 'format')->localized_name = 'Format'; //ex: email, plastic, facebook, print at home or multi-pack
	for ($i = 1; $i < 4; $i++)
	$this->addAttributeMapping("genre$i", "genre$i", true)->localized_name = 'Category' . $i; //state code..

?>