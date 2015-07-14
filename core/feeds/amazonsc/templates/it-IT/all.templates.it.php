<?php
	//********************************************************************
	//Amazon Seller Template - Italian
	//All (with a handful of exceptions)
	//2015-02
	//********************************************************************

/*** Basic ***/
	//Amazon attributes that almost all templates use.
	$this->addAttributeMapping('item_sku', 'item_sku', true, true)->localized_name = 'SKU';
	$this->addAttributeMapping('item_name', 'item_name', true,true)->localized_name = 'Product Name';

	//product_id on by default
	$external_product_id_required = true;
	$this->addAttributeMapping('', 'external_product_id', true, $external_product_id_required)->localized_name = 'Product ID'; //ex: 0452011876

	//brand required by default
	$brandRequired = true;
	$this->addAttributeMapping('brand', 'brand_name', true, $brandRequired)->localized_name = 'Brand Name';
		
	//description otional by default
	$descriptionRequired = false;
	$this->addAttributeMapping('product_description', 'product_description', true, $descriptionRequired)->localized_name = 'Product Description';

	//manufacturer required by default
	$manufacturerRequired = !($thisHeaderTemplateType == 'luggage');
	$this->addAttributeMapping('manufacturer', 'manufacturer', true, $manufacturerRequired)->localized_name = 'Manufacturer';

	//+ model, part number, product type
	$this->addAttributeMapping('update_delete', 'update_delete', true, $brandRequired)->localized_name = 'Update or delete';
	
/*** Offer ***/
	//standard_price, currency, quantity, condition
	//sale_price (sale_from_date, sale_end_date),	
	$this->addAttributeMapping('sale_price', 'sale_price',true,false)->localized_name = 'Sale Price';		
	$this->addAttributeMapping('sale_from_date', 'sale_from_date',true,false)->localized_name = 'Sale From Date';
	$this->addAttributeMapping('sale_end_date', 'sale_end_date',true,false)->localized_name = 'Sale End Date';


/*** Dimension ***/
	$this->addAttributeMapping('weight', 'website_shipping_weight',true,false)->localized_name = 'Shipping Weight';
	$this->addAttributeMapping('weight_unit', 'website_shipping_weight_unit_of_measure',true,false)->localized_name = 'Website Shipping Weight Unit Of Measure';

/*** Discovery ***/
	$this->addAttributeMapping('recommended_browse_nodes', 'recommended_browse_nodes', true,true)->localized_name = 'Recommended Browse Nodes'; 
	for ($i = 1; $i <= 2; $i++)
		$this->addAttributeMapping('recommended_browse_nodes'.$i, 'recommended_browse_nodes' . $i, true)->localized_name = 'Recommended Browse Nodes' . $i; 

/*** Images ***/
	$this->addAttributeMapping('feature_imgurl', 'main_image_url', true,true)->localized_name = 'Main Image URL';
	for ($i = 1; $i < 6; $i++)
		$this->addAttributeMapping('other_image_url'.$i, 'other_image_url'.$i, true)->localized_name = 'Other Image URL' . $i;

/*** Variation ***/
/*
	$this->addAttributeMapping('currency', 'currency')->localized_name = 'Currency'; 
	$this->addAttributeMapping('quantity', 'quantity')->localized_name = 'Quantity';
	$this->addAttributeMapping('condition', 'condition_type')->localized_name = 'Item Condition';
	
	//need to 'set get_wc_shipping_attributes as true' to enable pulling l,w,h
	$this->addAttributeMapping('length', 'item_length')->localized_name = 'Item Length';
	$this->addAttributeMapping('width', 'item_width')->localized_name = 'Item Width';
	$this->addAttributeMapping('height', 'item_height')->localized_name = 'Item Height';
	$this->addAttributeMapping('dimension_unit', 'item_length_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';

	$this->addAttributeMapping('weight', 'item_weight')->localized_name = 'Item Weight';
	$this->addAttributeMapping('weight_unit', 'item_weight_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';
	*/
?>