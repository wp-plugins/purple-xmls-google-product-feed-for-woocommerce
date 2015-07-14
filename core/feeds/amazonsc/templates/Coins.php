<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('sku', 'item_sku', true, true)->localized_name = 'SKU';
	$this->addAttributeMapping('', 'external_product_id',true,false)->localized_name = 'Product ID';
	$this->addAttributeMapping('description', 'product_description', true, true)->localized_name = 'Product Description';
	$this->addAttributeMapping('item_type', 'item_type', true, true)->localized_name = 'Item Type';
	$this->addAttributeMapping('update_delete', 'update_delete', true, false)->localized_name = 'Update Delete';

//required - offer - these attributes are required to make your item buyable for customers on the site
	$this->addAttributeMapping('regular_price', 'standard_price', true, true)->localized_name = 'Your Price';
	$this->addAttributeMapping('quantity', 'quantity', true, true)->localized_name = 'Quantity';
	$this->addAttributeMapping('handling_time', 'fulfillment_latency', true, true)->localized_name = 'Handling Time';

//Dimension
	$this->addAttributeMapping('weight', 'item_weight', true)->localized_name = 'Total Item Weight';
	$this->addAttributeMapping('weight_unit', 'item_weight_unit_of_measure', true)->localized_name = 'Total Item Weight Unit of Measure';

//Discovery
	$this->addAttributeMapping('title', 'bullet_point1',true,true)->localized_name = 'Key Product Features1';
	for ($i = 2; $i <= 5; $i++)
		$this->addAttributeMapping('', 'bullet_point' . $i, true,false)->localized_name = 'Key Product Features' . $i;

//Image
	$this->addAttributeMapping('feature_imgurl', 'main_image_url', true, true)->localized_name = 'Obverse Image URL';
	$this->addAttributeMapping('', 'back_label_image_url', true, true)->localized_name = 'Reverse Image URL';
	for ($i = 1; $i < 5; $i++)
		$this->addAttributeMapping('other_image_url'. $i, "other_image_url$i", true)->localized_name = 'Other Image URL' . $i;
//Compliance
	$this->addAttributeMapping('', 'country_of_origin', true, true)->localized_name = 'Country of Origin';

//Collectibles / Coins
	$this->addAttributeMapping('', 'model_year', false, false)->localized_name = 'Year';
	$this->addAttributeMapping('', 'mint_mark', true, false)->localized_name = 'Mint Mark';
	$this->addAttributeMapping('', 'denomination_unit', true, false)->localized_name = 'Denomination';
	$this->addAttributeMapping('', 'series_title', true, false)->localized_name = 'Coin Series';
	$this->addAttributeMapping('', 'graded_by', true, false)->localized_name = 'Grading Provided By';
	$this->addAttributeMapping('', 'grade_rating', true, false)->localized_name = 'Grade Rating';
	$this->addAttributeMapping('', 'unit_grouping', true, false)->localized_name = 'Set';
	
?>