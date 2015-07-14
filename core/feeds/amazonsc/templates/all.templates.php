<?php
	//********************************************************************
	//Amazon Seller Template
	//All (with a handful of exceptions)
	//2014-12
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('sku', 'item_sku', true, true)->localized_name = 'SKU';
	$this->addAttributeMapping('title', 'item_name', true,true)->localized_name = 'Product Name';
	//Item Type: refer to BTG
	$this->addAttributeMapping('item_type', 'item_type',true,true)->localized_name = 'Item Type Keyword';

	//Brand: required by default
	$brandRequired = !( $thisHeaderTemplateType == 'foodandbeverages'
					|| $this->template == 'home.and.garden' 
					|| $this->template == 'software.and.video games' 
					|| $this->template == 'sports.and.outdoors' 
					|| $this->template == 'sports collectibles');
	$this->addAttributeMapping('brand', 'brand_name', true, $brandRequired)->localized_name = 'Brand';
	
	//Manufacturer: required by default
	$manufacturerRequired = !($thisHeaderTemplateType == 'clothing'
							|| $this->template == 'gift cards' 
							|| $this->template == 'home.and.garden' 
							|| $this->template == 'musical instruments' 
							|| $thisHeaderTemplateType == 'shoes' 
							|| $this->template == 'sports.and.outdoors' 
							|| $this->template == 'sports collectibles');
	$this->addAttributeMapping('brand', 'manufacturer', true, $manufacturerRequired)->localized_name = 'Manufacturer';
		
	//External product id: UPC/ASIN/EAN required by default
	$external_product_id_required = !($this->template == 'baby' 
								   || $thisHeaderTemplateType == 'mechanicalfasteners' 
								   || $thisHeaderTemplateType == 'labsupplies' 
								   || $thisHeaderTemplateType == 'powertransmission' 
								   || $thisHeaderTemplateType == 'rawmaterials' 
								   || $thisHeaderTemplateType == 'industrial' 
								   || $this->template == 'jewelry' 
								   || $this->template == 'software.and.video games' 
								   || $this->template == 'sports collectibles');
	$this->addAttributeMapping('', 'external_product_id', true, $external_product_id_required)->localized_name = 'Product ID'; //ex: 0452011876
	
	//Description: optional by default
	$descriptionRequired = ($this->template == 'pet supplies' || 
							$this->template == 'baby' || 
							$this->template == 'gift cards');
	$this->addAttributeMapping('description', 'product_description', true, $descriptionRequired)->localized_name = 'Product Description';
	$this->addAttributeMapping('update_delete', 'update_delete', false,false)->localized_name = 'Update Delete';	

/*** Offer ***/
	//standard_price, currency, quantity, condition
	$this->addAttributeMapping('regular_price', 'standard_price')->localized_name = 'Standard Price';
	$this->addAttributeMapping('currency', 'currency')->localized_name = 'Currency'; 
	$this->addAttributeMapping('quantity', 'quantity')->localized_name = 'Quantity';
	$this->addAttributeMapping('condition', 'condition_type')->localized_name = 'Item Condition';
	//sale_price (sale_from_date, sale_end_date),	
	$this->addAttributeMapping('sale_price', 'sale_price',true,false)->localized_name = 'Sale Price';		
	$this->addAttributeMapping('sale_from_date', 'sale_from_date',true,false)->localized_name = 'Sale From Date';
	$this->addAttributeMapping('sale_end_date', 'sale_end_date',true,false)->localized_name = 'Sale End Date';
	//handling time
	$this->addAttributeMapping('', 'fulfillment_latency',true,false)->localized_name = 'Fulfillment Latency';
	
/*** Dimension ***/
	//'set get_wc_shipping_attributes as true' to extract l,w,h
	$this->addAttributeMapping('length', 'item_length')->localized_name = 'Item Length';
	$this->addAttributeMapping('width', 'item_width')->localized_name = 'Item Width';
	$this->addAttributeMapping('height', 'item_height')->localized_name = 'Item Height';
	$this->addAttributeMapping('dimension_unit', 'item_length_unit_of_measure')->localized_name = 'Item Length Unit Of Measure';

	$this->addAttributeMapping('weight', 'website_shipping_weight')->localized_name = 'Shipping Weight';
	$this->addAttributeMapping('weight_unit', 'website_shipping_weight_unit_of_measure')->localized_name = 'Website Shipping Weight Unit Of Measure';
	
/*** Discovery ***/
	$this->addAttributeMapping('title', 'bullet_point1',true,true)->localized_name = 'Key Product Features1';
	for ($i = 2; $i <= 5; $i++)
	$this->addAttributeMapping('', 'bullet_point' . $i, true,false)->localized_name = 'Key Product Features' . $i;

	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'generic_keywords' . $i, true,false)->localized_name = 'Search Terms' . $i;

/*** Image ***/
	$this->addAttributeMapping('feature_imgurl', 'main_image_url', true,true)->localized_name = 'Main Image URL';
	for ($i = 1; $i < 6; $i++)
		$this->addAttributeMapping('other_image_url'.$i, 'other_image_url'.$i, true)->localized_name = 'Other Image URL' . $i;

/*** Variation ***/