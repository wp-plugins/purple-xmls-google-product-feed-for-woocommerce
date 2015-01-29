<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************
//required - basic
$this->addAttributeMapping('item_sku', 'item_sku', true, true)->localized_name = 'SKU';
$this->addAttributeMapping('product_description', 'product_description', true, true)->localized_name = 'Product Description';
$this->addAttributeMapping('item_type', 'item_type', true, true)->localized_name = 'Item Type';
/* valid values for item type (Coins)
collectible-coins
collectible-gold-coins
collectible-platinum-coins
collectible-silver-coins
us-mint-sealed-collectible-coins
*/
$this->addAttributeMapping('weight', 'item_weight', true)->localized_name = 'Total Item Weight';
$this->addAttributeMapping('weight_unit', 'item_weight_unit_of_measure', true)->localized_name = 'Total Item Weight Unit of Measure';
//required - offer - these attributes are required to make your item buyable for customers on the site
$this->addAttributeMapping('standard_price', 'standard_price', true, true)->localized_name = 'Your Price';
$this->addAttributeMapping('quantity', 'quantity', true, true)->localized_name = 'Quantity';
$this->addAttributeMapping('', 'fulfillment_latency', true, true)->localized_name = 'Handling Time';
//required - image
$this->addAttributeMapping('feature_imgurl', 'main_image_url', true, true)->localized_name = 'Obverse Image URL';
$this->addAttributeMapping('', 'back_label_image_url', true, true)->localized_name = 'Reverse Image URL';

$this->addAttributeMapping('', 'external_product_id',true,false)->localized_name = 'Product ID';
$this->addAttributeMapping('', 'update_delete', true, false)->localized_name = 'Update Delete';

$this->addAttributeMapping('', 'country_of_origin', true, false)->localized_name = 'Country of Origin';
$this->addAttributeMapping('', 'model_year', false, false)->localized_name = 'Year';
$this->addAttributeMapping('', 'mint_mark', true, false)->localized_name = 'Mint Mark';
$this->addAttributeMapping('', 'denomination_unit', true, false)->localized_name = 'Denomination';
$this->addAttributeMapping('', 'series_title', true, false)->localized_name = 'Coin Series';
$this->addAttributeMapping('', 'graded_by', true, false)->localized_name = 'Grading Provided By';
$this->addAttributeMapping('', 'grade_rating', true, false)->localized_name = 'Grade Rating';
$this->addAttributeMapping('', 'unit_grouping', true, false)->localized_name = 'Set';
for ($i = 1; $i < 5; $i++)
			$this->addAttributeMapping('other_image_url'. $i, "other_image_url$i", true)->localized_name = 'Other Image URL' . $i;
?>