<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

/*** Basic ***/	
	$this->addAttributeMapping('title', 'item_name', true,true)->localized_name = 'Title';
	$this->addAttributeMapping('item_type', 'item_type',true,true)->localized_name = 'Item Type';
	$this->addAttributeMapping('sku', 'item_sku', true,false)->localized_name = 'SKU';
	$this->addAttributeMapping('description', 'product_description', true, false)->localized_name = 'Product Description';
	$this->addAttributeMapping('', 'external_product_id')->localized_name = 'Product ID';
	$this->addAttributeMapping('', 'update_delete', true, false)->localized_name = 'Update Delete';
/*** Offer ***/
	$this->addAttributeMapping('regular_price', 'standard_price', true,true)->localized_name = 'Standard Price';
	$this->addAttributeMapping('quantity', 'quantity', true,true)->localized_name = 'Quantity';
	$this->addAttributeMapping('handling_time', 'fulfillment_latency', true,true)->localized_name = 'Handling Time';
/*** Dimensions ***/
	$this->addAttributeMapping('weight', 'website_shipping_weight')->localized_name = 'Shipping Weight';
	$this->addAttributeMapping('weight_unit', 'website_shipping_weight_unit_of_measure')->localized_name = 'Website Shipping Weight Unit Of Measure';
/*** Discovery ***/
	$this->addAttributeMapping('bullet_point1', 'bullet_point1',true,true)->localized_name = 'Key Product Features1';
	for ($i = 2; $i <= 5; $i++)
		$this->addAttributeMapping('', 'bullet_point' . $i, true,false)->localized_name = 'Key Product Features' . $i;
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'generic_keywords' . $i, true)->localized_name = 'Search Terms' . $i;
/*** Images ***/
	$this->addAttributeMapping('feature_imgurl', 'main_image_url', true, true)->localized_name = 'Main Image URL';
	for ($i = 1; $i < 5; $i++)
		$this->addAttributeMapping('other_image_url'. $i, "other_image_url$i", true)->localized_name = 'Other Image URL' . $i;
/*** Collectibles required attributes ***/	
	$this->addAttributeMapping('', 'style_name',false,false)->localized_name = 'Entertainment Type'; //Movies, Music, Theatre
	$this->addAttributeMapping('', 'genre')->localized_name = 'Entertainment Genre'; //Country, Sci-Fi, Horror, Drama, Comedy
	$this->addAttributeMapping('', 'theme')->localized_name = 'Entertainment Brand'; //Game of Thrones, Walking Dead, X-men, DC Comics, Marvel
	$this->addAttributeMapping('', 'additional_product_information')->localized_name = 'Product Type'; 
	$this->addAttributeMapping('', 'originality')->localized_name = 'Collectible Type'; //Original, Reprint, Replica	
	$this->addAttributeMapping('', 'authenticated_by')->localized_name = 'Authenticity By'; //PSA/DNA, Seller
	$this->addAttributeMapping('', 'graded_by')->localized_name = 'Grading By'; //PSA, CGC, Seller
	$this->addAttributeMapping('', 'grade_rating')->localized_name = 'Condition Type'; //Damaged, Like New, Mint
	
?>