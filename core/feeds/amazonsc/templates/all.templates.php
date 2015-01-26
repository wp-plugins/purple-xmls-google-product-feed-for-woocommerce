<?php
	//********************************************************************
	//Amazon Seller Template
	//All (except Inventory and FoodService)
	//2014-12
	//********************************************************************

//Amazon attributes that almost all templates use.
	$this->addAttributeMapping('item_sku', 'item_sku', true, true)->localized_name = 'SKU';
	$this->addAttributeMapping('title', 'item_name', true,true)->localized_name = 'Product Name';
	$this->addAttributeMapping('-refer to BTG-', 'item_type',true,true)->localized_name = 'Item Type Keyword';

	//brand required by default
	$brandRequired = !($this->template == 'home.and.garden' || $this->template == 'software.and.video games' 
		|| $this->template == 'sports.and.outdoors' || $this->template == 'sports collectibles');
	$this->addAttributeMapping('brand_name', 'brand_name', true, $brandRequired)->localized_name = 'Brand';
	
	//manufacturer required by default
	$manufacturerRequired = !($this->template == 'gift cards' || $this->template == 'home.and.garden' 
		|| $this->template == 'musical instruments' || $this->headerTemplateType == 'Shoes' || $this->template == 'sports.and.outdoors' 
		|| $this->template == 'sports collectibles');
	$this->addAttributeMapping('manufacturer', 'manufacturer', true, $manufacturerRequired)->localized_name = 'Manufacturer';
		
	//external product id required by default
	$external_product_id_required = !($this->template == 'baby' || $this->headerTemplateType == 'LabSupplies' || $this->headerTemplateType == 'PowerTransmission' 
	|| $this->headerTemplateType == 'RawMaterials' || $this->headerTemplateType == 'Industrial' || $this->template == 'jewelry' 
	|| $this->template == 'software.and.video games' || $this->template == 'sports collectibles');
	$this->addAttributeMapping('- select UPC/EAN attribute -', 'external_product_id', true, $external_product_id_required)->localized_name = 'Product ID'; //ex: 0452011876
	
	//description optional by default
	$descriptionRequired = ($this->template == 'pet supplies' || $this->template == 'baby' || $this->template == 'gift cards');
	$this->addAttributeMapping('product_description', 'product_description', true, $descriptionRequired)->localized_name = 'Product Description';

	$this->addAttributeMapping('update_delete', 'update_delete', false,false)->localized_name = 'Update Delete';	
	$this->addAttributeMapping('standard_price', 'standard_price')->localized_name = 'Standard Price';
	
	$this->addAttributeMapping('currency', 'currency')->localized_name = 'Currency'; 
	$this->addAttributeMapping('quantity', 'quantity')->localized_name = 'Quantity';

	//need to 'set get_wc_shipping_attributes as true' to enable pulling l,w,h
	//may be a taxing call
	$this->addAttributeMapping('length', 'item_length')->localized_name = 'Item Length';
	$this->addAttributeMapping('width', 'item_width')->localized_name = 'Item Width';
	$this->addAttributeMapping('height', 'item_height')->localized_name = 'Item Height';

	$this->addAttributeMapping('dimension_unit', 'item_length_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';
	$this->addAttributeMapping('weight', 'item_weight')->localized_name = 'Item Weight';
	$this->addAttributeMapping('weight_unit', 'item_weight_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';
	
	//$this->addAttributeMapping('sale_price', 'sale_price')->localized_name = 'Sale Price';	

	//Optional
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'bullet_point' . $i, true)->localized_name = 'Key Product Features' . $i; //in camera.and.photo, localized name is Bullet Point1
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'generic_keywords' . $i, true)->localized_name = 'Search Terms' . $i;
	//Template: Images. Not required but usually clients will have this in woocommerce
	$this->addAttributeMapping('feature_imgurl', 'main_image_url', true,true)->localized_name = 'Main Image URL';
	for ($i = 1; $i < 6; $i++)
		$this->addAttributeMapping('other_image_url'.$i, 'other_image_url'.$i, true)->localized_name = 'Other Image URL' . $i;

?>