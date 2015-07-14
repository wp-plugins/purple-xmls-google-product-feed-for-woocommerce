<?php
/********************************************************************
	
Version 3.0
A Become (Europe) Feed
Copyright 2015 Purple Turtle Productions. All rights reserved.
license	GNU General Public License version 3 or later; see GPLv3.txt
********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PRakutenUKFeed extends PCSVFeedEx{
	
	function __construct () {
			
	parent::__construct();
			
	$this->providerName = 'RakutenUK';
	$this->providerNameL = 'RakutenUK';
	$this->fileformat = 'csv';
	$this->fieldDelimiter = ",";
	$this->fields = array();
			
//Fields
//$this->addAttributeMapping('', '', true,false); 
	
//the first true wraps the value in double quotes (CSV) or CDATA tags (XML)
//the second true/false tells the plugin weather the attribute is requried or not (see attribute table in the plugin)
	
	//required fields to add a product
	$this->addAttributeMapping('sku', 'base_sku', true,true); //req to edit; max char: 40
	$this->addAttributeMapping('', 'sku',true,false); //max chars: 40
	$this->addAttributeMapping('title', 'name', true,true);
	$this->addAttributeMapping('description', 'description_1', true,true); //req to edit
	$this->addAttributeMapping('price', 'price', true,true);
	$this->addAttributeMapping('current_category', 'rakuten_product_category_id', true,true);

	for ($i = 1; $i <= 5; $i++)
		$this->addAttributeMapping('', 'attribute_'.$i, true,false); //required if variable

	$this->addAttributeMapping('mpn', 'manufacturer_part_number', true,false);
	$this->addAttributeMapping('', 'url', true,false); //URL of the (Rakuten) product page of the product. You can only set this field after downloading an existing Product CSV file.
	
	$this->addAttributeMapping('', 'tagline', true,false);
	
	$this->addAttributeMapping('', 'legal_information', true,false);
	$this->addAttributeMapping('', 'shipping_instructions', true,false);
	$this->addAttributeMapping('', 'labels', true,false);
		
	$this->addAttributeMapping('feature_imgurl', 'image_url_1', true,false);
	for ($i = 2; $i <= 10; $i++)
		$this->addAttributeMapping('image_url_' . $i, 'image_url_' . $i, true,false);
		
	$this->addAttributeMapping('', 'video_url', true,false);		
	$this->addAttributeMapping('', 'gtin_type', true,false);		
	$this->addAttributeMapping('', 'gtin_value', true,false);		
	$this->addAttributeMapping('brand', 'brand', true,false);
		
	$this->addAttributeMapping('', 'display_start_date', true,false);		
	$this->addAttributeMapping('', 'display_end_date', true,false);	
	$this->addAttributeMapping('', 'available_start_date', true,false);		
	$this->addAttributeMapping('', 'available_end_date', true,false);		
	$this->addAttributeMapping('', 'shipping_preparation_time', true,false);
	$this->addAttributeMapping('', 'free_shipping', true,false);
		
	$this->addAttributeMapping('width', 'shipping_width', true,false); //in cm
	$this->addAttributeMapping('height', 'shipping_height', true,false); //in cm
	$this->addAttributeMapping('length', 'shipping_length', true,false); //in cm
	$this->addAttributeMapping('weight', 'weight', true,false); //in grams
		
	$this->addAttributeMapping('', 'display_quantity', true,false);
	$this->addAttributeMapping('operator_for_quantity', 'operator_for_quantity', true,false); //Sets whether you add, subtract or do nothing to change your inventory quantity
	$this->addAttributeMapping('stock_quantity', 'quantity', true,false);
	$this->addAttributeMapping('', 'return_quantity_in_cancel', true,false);
	$this->addAttributeMapping('', 'purchase_quantity_limit', true,false);
	
	for ($i = 1; $i <= 5; $i++) //15 max
		$this->addAttributeMapping('', 'shop_product_unique_identifier_'.$i, true,false); //The unique identifiers of the shop categories of the product. Ex. potsanadpans
	for ($i = 1; $i <= 5; $i++)	//20 max
		$this->addAttributeMapping('', 'shipping_option_'.$i,true,false);

	//Formatting		
	$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
	$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
	$this->addRule( 'description', 'description',array('max_length=20000','strict') );
	// $this->addRule('csv_standard', 'CSVStandard',array('title','255')); //max chars: 255
	// $this->addRule('csv_standard', 'CSVStandard',array('description')); 
	$this->addRule( 'substr','substr', array('title','0','255',true) ); //255 length

	}
		
	function formatProduct($product) {	

	$category = explode(";", $product->attributes['current_category']);
	if (isset($category[1]))
		$product->attributes['current_category'] = trim($category[1]);
	else
		$product->attributes['current_category'] = '0';

//operator for quantity; +, -, =
	if ( !isset($product->attributes['operator_for_quantity']) )
		$product->attributes['operator_for_quantity'] = '=';

//convert demension to centimeters
	$dimension_multiplier = 1;
	switch ($product->attributes['dimension_unit']) {
		case 'm': 
			$dimension_multiplier = 100;
			break;
		case 'mm':
			$dimension_multiplier = 0.1;
			break;
		case 'in':
			$dimension_multiplier = 2.54;
			break;
		case 'yd':
			$dimension_multiplier = 91.44;
			break;
		default: 
			$dimension_multiplier = 1;
			break;
	}
	//convert to cm
	$product->attributes['height'] = $product->attributes['height']*$dimension_multiplier;
	$product->attributes['width'] = $product->attributes['width']*$dimension_multiplier;
	$product->attributes['length'] = $product->attributes['length']*$dimension_multiplier;
	
//convert weight to grams
	$product->attributes['weight_unit'] = $this->weight_unit;
	$weight_multiplier = 1;
	switch ($product->attributes['dimension_unit']) {

		case 'kg':
			$weight_multiplier = 1000;
			break;
		case 'lbs':
			$weight_multiplier = 453.592;
			break;
		case 'oz':
			$weight_multiplier = 28.3495;
			break;
		default: //already in grams
			$weight_multiplier = 1;
			break;

	}
	//$product->attributes['weight'] = sprintf('%0.2f', $product->attributes['weight']*$weight_multiplier);
	$product->attributes['weight'] = $product->attributes['weight']*$weight_multiplier;

//additional images
	$image_count = 2;
		foreach($product->imgurls as $imgurl) {
			$image_index = "image_url_$image_count";
			$product->attributes[$image_index] = str_replace('https://','http://',$imgurl);
			$image_count++;
			if ($image_count >= 10)
				break;
		}
	return parent::formatProduct($product);

	}

}
?>