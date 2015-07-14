<?php
	//********************************************************************
	//Amazon Seller Template
	//Amazon attributes that almost all templates use.
	//2015-02
	//********************************************************************

/*** Basic ***/
	$this->addAttributeMapping('sku', 'item_sku', true, true)->localized_name = 'SKU';
	$this->addAttributeMapping('title', 'item_name', true,true)->localized_name = 'Product Name';

	//external_product_id: required by default
	$external_product_id_required = !($thisHeaderTemplateType == 'jewelry' ||
									  $thisHeaderTemplateType == 'baby' ||
									  $thisHeaderTemplateType == 'lighting');
	$this->addAttributeMapping('', 'external_product_id', true, $external_product_id_required)->localized_name = 'Product ID'; //ex: 0452011876

	//brand: required by default
	$brandRequired = !($thisHeaderTemplateType == 'softwarevideogames' ||					 
					   $thisHeaderTemplateType == 'personalcareappliances' ||
					   $thisHeaderTemplateType == 'health');
	$this->addAttributeMapping('brand', 'brand_name', true, $brandRequired)->localized_name = 'Brand Name';

	//manufacturer: required by default
	$manufacturerRequired = !($thisHeaderTemplateType == 'clothing' ||
							  $thisHeaderTemplateType == 'personalcareappliances' ||
							  $thisHeaderTemplateType == 'health' ||
							  $thisHeaderTemplateType == 'luggage'); 
	$this->addAttributeMapping('brand', 'manufacturer', true, $manufacturerRequired)->localized_name = 'Manufacturer';
		
	//description: otional by default
	$descriptionRequired = ($thisHeaderTemplateType == 'toys' ||
							$thisHeaderTemplateType == 'baby' ||
							$thisHeaderTemplateType == 'office');
	$this->addAttributeMapping('description', 'product_description', true, $descriptionRequired)->localized_name = 'Product Description';

	//Use "Update" whenever you are changing any field in the existing product's information, including reducing the inventory to 0.
	$this->addAttributeMapping('update_delete', 'update_delete',true,false)->localized_name = 'Update Delete';

/*** Offer ***/
	//standard_price, currency, quantity, condition
	//sale_price (sale_from_date, sale_end_date),	
	$this->addAttributeMapping('sale_price', 'sale_price',true,false)->localized_name = 'Sale Price';		
	$this->addAttributeMapping('sale_from_date', 'sale_from_date',true,false)->localized_name = 'Sale From Date';
	$this->addAttributeMapping('sale_end_date', 'sale_end_date',true,false)->localized_name = 'Sale End Date';
	$this->addAttributeMapping('handling_time', 'fulfillment_latency',true,false)->localized_name = 'Fulfillment Latency';
	
/*** Dimension ***/
	$this->addAttributeMapping('length', 'item_display_length')->localized_name = 'Display Length';
		$this->addAttributeMapping('dimension_unit', 'item_display_length_unit_of_measure',true,false)->localized_name = 'Item Display Length Unit Of Measure';
	$this->addAttributeMapping('width', 'item_display_width')->localized_name = 'Display Width';
		$this->addAttributeMapping('dimension_unit', 'item_display_width_unit_of_measure',true,false)->localized_name = 'Item Display Width Unit Of Measure';
	$this->addAttributeMapping('height', 'item_display_height')->localized_name = 'Display Height';
		$this->addAttributeMapping('dimension_unit', 'item_display_height_unit_of_measure',true,false)->localized_name = 'Item Display Height Unit Of Measure';
	
	$this->addAttributeMapping('weight', 'website_shipping_weight',true,false)->localized_name = 'Shipping Weight';
	$this->addAttributeMapping('weight_unit', 'website_shipping_weight_unit_of_measure',true,false)->localized_name = 'Website Shipping Weight Unit Of Measure';

/*** Discovery ***/
	$this->addAttributeMapping('title', 'bullet_point1',true,true)->localized_name = 'Key Product Features1';

	for ($i = 2; $i <= 4; $i++)
	$this->addAttributeMapping('', 'bullet_point' . $i, true,false)->localized_name = 'Key Product Features' . $i;

	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'generic_keywords' . $i, true,false)->localized_name = 'Search Terms' . $i;

	//recommended browse nodes1: required by default
	$this->addAttributeMapping('recommended_browse_nodes1', 'recommended_browse_nodes1', true, true)->localized_name = 'Recommended Browse Nodes1';
	$this->addAttributeMapping('recommended_browse_nodes2', 'recommended_browse_nodes2', true)->localized_name = 'Recommended Browse Nodes2';

/*** Image ***/
	$this->addAttributeMapping('feature_imgurl', 'main_image_url', true,true)->localized_name = 'Main Image URL';
	for ($i = 1; $i < 6; $i++)
		$this->addAttributeMapping('other_image_url'.$i, 'other_image_url'.$i, true)->localized_name = 'Other Image URL' . $i;

/*** Variation ***/
	//$this->addAttributeMapping('parent_child', 'parent_child',true,false)->localized_name = 'Parentage';

?>