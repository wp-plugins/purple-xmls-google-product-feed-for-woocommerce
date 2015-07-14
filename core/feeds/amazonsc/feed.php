<?php

/********************************************************************
Version 2.0
	An Amazon Feed (Product Ads)
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
By: Keneto 2014-08

********************************************************************/

/*** Changes to categories.txt should be relfected in initializeTemplateData() ***/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAmazonSCFeed extends PCSVFeed
{
	public $feed_product_type = '';
	public $headerTemplateType; //Attached to the top of the feed
	public $headerTemplateVersion;
	public $templateLoaded = false;

	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'AmazonSC';
		$this->providerNameL = 'amazonsc';
		$this->fileformat = 'txt';
		$this->fields = array();
		$this->fieldDelimiter = "\t";

		$this->external_product_id_type = '';		
		$this->stripHTML = true;	

		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule('price_rounding','pricerounding');	
		
		// below is applied in basicfeed.php
		// $this->addRule('description', 'description', array('strict'));
		// $this->addRule( 'csv_standard', 'CSVStandard',array('title') ); 
		// $this->addRule( 'csv_standard', 'CSVStandard',array('description') ); 
	}

	function loadTemplate($template) {

	$this->initializeTemplateData($template);

	//lower case headertemplatetype to match templateType.php
	$thisHeaderTemplateType = strtolower($this->headerTemplateType);

	//load Italian templates
	if ( strpos(strtolower($template), 'it/') !== false ) {

		include dirname(__FILE__) . '/templates/it-IT/all.templates.it.php';

		$files = scandir(dirname(__FILE__) . '/templates/it-IT');
		foreach($files as $file)
			if (strtolower($file) == $thisHeaderTemplateType.'.php')
			//if (strpos($file, $this->headerTemplateType) !== false)
					include_once dirname(__FILE__) . '/templates/it-IT/' . $file;
	}

	//load Amazon UK templates
	elseif ( strpos(strtolower($template), 'uk/') !== false ) {
		if ( $thisHeaderTemplateType != 'offer' &&
			 $thisHeaderTemplateType != 'priceinventory' &&
			 $thisHeaderTemplateType != 'inventoryloader')
			include dirname(__FILE__) . '/templates/en-UK/all.templates.uk.php';
		$files = scandir(dirname(__FILE__) . '/templates/en-UK');
		foreach($files as $file)
			if (strtolower($file) == $thisHeaderTemplateType.'.php')
			//if (strpos($file, $this->headerTemplateType) !== false)
					include_once dirname(__FILE__) . '/templates/en-UK/' . $file;
	}

	//load Amazon ES templates
	elseif ( strpos(strtolower($template), 'es/') !== false ) {
		include dirname(__FILE__) . '/templates/es-ES/all.templates.es.php';
		
		$files = scandir(dirname(__FILE__) . '/templates/es-ES');
		foreach($files as $file)
			if (strtolower($file) == $thisHeaderTemplateType.'.php')
			//if (strpos($file, $this->headerTemplateType) !== false)
					include_once dirname(__FILE__) . '/templates/es-ES/' . $file;
	}
	
	//load Amazon FR templates
	elseif ( strpos(strtolower($template), 'fr/') !== false ) {}

	//load Amazon DE templates
	elseif ( strpos(strtolower($template), 'de/') !== false ) {}
	
	//Load US Amazon templates
	else {
		//load the "all.template" file (excludes some categories)
		if ( $thisHeaderTemplateType != 'foodserviceandjansan' &&
			 $thisHeaderTemplateType != 'inventoryloader' &&
			 $thisHeaderTemplateType != 'coins' &&
			 $thisHeaderTemplateType != 'entertainmentcollectibles' &&
			 $thisHeaderTemplateType != 'offer' &&
			 $thisHeaderTemplateType != 'priceinventory'
			)
			include dirname(__FILE__) . '/templates/all.templates.php';

		//Load the individual templates
		$files = scandir(dirname(__FILE__) . '/templates');
		foreach($files as $file)
			if ( strcasecmp($file, $thisHeaderTemplateType .'.php') == 0 ) 
				include_once dirname(__FILE__) . '/templates/' . $file;
		}

		//Note external_product_id_type is generated on the fly, under formatProducts function
		//$this->addAttributeMapping('external_product_id_type', 'external_product_id_type')->localized_name = 'Product ID Type';
		//item_type: please refer to BTG
		
		$this->templateLoaded = true;
		$this->loadAttributeUserMap();
	}

	function formatProduct($product) {
		
		global $pfcore; //required to set sale_date_from/to
		
		if (!$this->templateLoaded)
			$this->loadTemplate();

		//all templates require stock
		if ( !isset($product->attributes['quantity']) )
				$product->attributes['quantity'] = $product->attributes['stock_quantity'];
			
		if ( $this->headerTemplateType == 'inventoryloader' || $this->headerTemplateType == 'priceinventory' )
		{
			//sku auto mapped, product-id will be mapped by user
			// $product->attributes['price'] = $product->attributes['regular_price'];
			// if (isset($product->attributes['sale_price']))
			// 	$product->attributes['price'] = $product->attributes['sale_price'];
			$product->attributes['leadtime_to_ship'] = $this->leadtime_to_ship;
			
		} //if headertemplatetype == inventoryloader
		
		else if ( $this->headerTemplateType == 'Offer') //listing loader
		{
			if ( !isset($product->attributes['condition']) ); 
				$product->attributes['condition'] = 'New'; //Refurbished, UsedLikeNew, UsedVeryGood, UsedGood, UsedAcceptable
			
			//remove s from https
			$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);
			$image_count = 1;
			foreach($product->imgurls as $imgurl) {
				$image_index = "other_image_url$image_count";
				$product->attributes[$image_index] = str_replace('https://','http://',$imgurl);
				$image_count++;
				if ($image_count >= 9)
					break;
			}
	
			/*** sale price and sale price dates ***/			
			if ( $product->attributes['has_sale_price'] )
			{
				if ( isset( $product->attributes['sale_price_dates_from'] ) && isset( $product->attributes['sale_price_dates_to'] ) ) 
				{	
					$product->attributes['sale_price_dates_from'] = $pfcore->localizedDate( 'Y-m-d', $product->attributes['sale_price_dates_from'] );
					$product->attributes['sale_price_dates_to'] = $pfcore->localizedDate( 'Y-m-d', $product->attributes['sale_price_dates_to'] );
				}
				else //sale price is set, but no schedule. Amazon requires schedule.
				{
					$product->attributes['regular_price'] = $product->attributes['sale_price'];
					$product->attributes['sale_price'] = '';
				}
			}

		}// if headerTemplateType == offer (listing loader)
		
		else {
			
			//********************************************************************
			//Prepare the product
			//********************************************************************		
			$product->attributes['category'] = $this->current_category;

			//sometimes templates only have one feed_product_type.
			if (isset($this->feed_product_type) && strlen($this->feed_product_type) > 0)
				//if (isset($product->attributes['feed_product_type']) && (strlen($product->attributes['feed_product_type']) == 0) )
					$product->attributes['feed_product_type'] = $this->feed_product_type;

			if (!isset($product->attributes['currency']) || (strlen($product->attributes['currency']) == 0))
				$product->attributes['currency'] = $this->currency;

			//what if regular_price is blank but sale_price is present?
			//$product->attributes['standard_price'] = $product->attributes['regular_price'];	
		
			if ( isset($product->attributes['item_weight']) ) 
				$product->attributes['weight'] = $product->attributes['item_weight'];		
			
			$product->attributes['weight_unit_word'] = $product->attributes['weight'];
			//modify item weight unit to fit amazon's valid weight units. ex: convert g -> GR 
			$valid_weight_unit = $this->weight_unit;
			if ( $valid_weight_unit == 'kg' ) {
				$product->attributes['weight_unit_word'] = 'kilograms';
				$product->attributes['weight_unit'] = 'KG';
			}			
			else if ( $valid_weight_unit == 'g' ) {
				$product->attributes['weight_unit_word'] = 'grams';
				$product->attributes['weight_unit'] = 'GR';
			}
			else if ( $valid_weight_unit == 'lbs' ) {
				$product->attributes['weight_unit_word'] = 'pounds';
				$product->attributes['weight_unit'] = 'LB';
			}
			else {
				$product->attributes['weight_unit_word'] = 'ounces';
				$product->attributes['weight_unit'] = 'OZ';
			}

			$product->attributes['dimension_unit'] = $this->dimension_unit;	
			switch($this->dimension_unit){
				case 'm':
					$product->attributes['dimension_unit_word'] = 'meters';
					break;
				case 'cm':
					$product->attributes['dimension_unit_word'] = 'centimeters';
					break;
				case 'mm':
					$product->attributes['dimension_unit_word'] = 'millimeters';
					break;
				case 'in':
					$product->attributes['dimension_unit_word'] = 'inches';
					break;
				case 'ft':					
					$product->attributes['dimension_unit_word'] = 'feet';
					break;
				default:
					$product->attributes['dimension_unit_word'] = '';
			}

			//fix missing brand error (customized error)
			//if (isset($product->attributes['brand']))
			//	$product->attributes['brand_name'] = $product->attributes['brand'];

			//default values
			if (!isset($product->attributes['item_package_quantity'])) //The number of individiually packaged units/distinct items in a package
				$product->attributes['item_package_quantity'] = 1;
			if ( !isset($product->attributes['number_of_items']) )
					$product->attributes['number_of_items'] = 1; //Number of items included in a single package labeled for individual sale
			if ( !isset($product->attributes['handling_time']) )
					$product->attributes['handling_time'] = 2; //Indicates the time, in days, between when you receive an order for an item and when you can ship the item.
			if ( !isset($product->attributes['feed_product_type']) )
			 	$product->attributes['feed_product_type'] = 'Feed Product Type value required. Refer to Inventory Template.';
			if ( !isset($product->attributes['item_type']) )
				$product->attributes['item_type'] = 'Item Type Keyword required. Please refer to Template\'s BTG.';
			//if ( !isset($product->attributes['item-type-keyword']) )
			//	$product->attributes['item-type-keyword'] = 'Item Type Keyword required. Please refer to Template\'s BTG.';
			
			//remove s from https
			if (strpos($product->attributes['feature_imgurl'], 'https') !== false) {
			$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);
			//Warn user because server might not be listening for http connections
			//$this->addErrorMessage(<Shopzilla Range Warning + 1>, 'Converted an https image url http ' . $product->attributes['title'] . image url);
			}
			$image_count = 1;
			foreach($product->imgurls as $imgurl) {
				$image_index = "other_image_url$image_count";
				$product->attributes[$image_index] = str_replace('https://','http://',$imgurl);
				$image_count++;
				if ($image_count >= 9)
					break;
			}

			/*** sale price and sale price dates ***/			
			if ( $product->attributes['has_sale_price'] )
			{		
				if ( isset( $product->attributes['sale_price_dates_from'] ) && isset( $product->attributes['sale_price_dates_to'] ) ) 
				{				
					$product->attributes['sale_from_date'] = $pfcore->localizedDate( 'Y-m-d', $product->attributes['sale_price_dates_from'] );
					$product->attributes['sale_end_date'] = $pfcore->localizedDate( 'Y-m-d', $product->attributes['sale_price_dates_to'] );
				}
				else //sale price is set, but no schedule. 
				{
					$product->attributes['regular_price'] = $product->attributes['sale_price'];
					$product->attributes['sale_price'] = '';
				}
			}
			
			$product->attributes['shipping_cost'] = '0.00';
			$product->attributes['shipping_weight'] = $product->attributes['weight'];
		
		
		} //else NOT inventoryloader (ie category inventory file template)

		//if ($product->attributes['isVariation'])
			//$product->attributes['parent_child'] = 'Variation'; //Trying without variations for now

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************

		if (isset($product->attributes['description']) && strlen($product->attributes['description']) > 2000) 
		{
			$product->attributes['description'] = substr($product->attributes['description'], 0, 2000);
			$this->addErrorMessage(8000, 'Description truncated for ' . $product->attributes['title'], true);
		}
		if (!isset($product->attributes['product_description']))
			$product->attributes['product_description'] = '';
		
		/*if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			$this->addErrorMessage(8001, 'Brand not set for ' . $product->attributes['title'], true);
		if (($this->external_product_id_type == 'UPC') && (strlen($product->attributes['external_product_id']) == 0))
			$this->addErrorMessage(8002, 'external_product_id not set for ' . $product->attributes['title'], true);
		if (($this->template == 'health') && (strlen($product->attributes['manufacturer']) == 0))
			$this->addErrorMessage(8003, 'Manufacturer not set for ' . $product->attributes['title'], true);*/
		//8004 seems a bit too aggressive
		//if ($product->attributes['has_sale_price'] && (!isset($product->attributes['sale_from_date']) || !isset($product->attributes['sale_end_date'])))
			//$this->addErrorMessage(8004, 'Sale price set for ' . $product->attributes['title'] . ' but no sale_from_date and/or sale_end_date provided', true);

		//********************************************************************
		//Trigger Mapping 3.0 Before-Feed Event
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);

		//$parent_sku = $product->attributes['parent_sku']; 

		//********************************************************************
		//Build output in order of fields
		//********************************************************************
		$output = '';
		foreach( $this->attributeMappings as $thisAttributeMapping ) 
		{
			if ( $thisAttributeMapping->usesCData )
				$quotes = '"';
			else
				$quotes = '';	
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted ) 
			{
				//Special case: if the current attribute is external_product_id, we need to do the following
				//Depending on the number of digits, assign external_product_id_type as UPC/EAN/GCID
				if ( $thisAttributeMapping->mapTo == 'external_product_id' || 
					 $thisAttributeMapping->mapTo == 'product-id-number'  ||
					 $thisAttributeMapping->mapTo == 'product-id' )
				{
					if (!isset($product->attributes[$thisAttributeMapping->attributeName]))
						$product->attributes[$thisAttributeMapping->attributeName] = ''; //Should probably warn the user there's a bad product here -KH:2014-12
					//output external_product_id (12,13 digit code)
					//$output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;	
					//$output .= $this->fieldDelimiter;
					//convert digit to string and get the length. Depending on the length, product id type can be upc, ean or gcid...
					$productId_strlen = strlen( (string)$product->attributes[$thisAttributeMapping->attributeName] );
					switch ( $productId_strlen ) 
					{
						case 10:
							$this->external_product_id_type = 'ASIN'; //10 digit Amazon number
							break;
						case 11:
							$this->external_product_id_type = 'UPC'; //11 digit UPC (start with 0)
							$product->attributes[$thisAttributeMapping->attributeName] = '0'.$product->attributes[$thisAttributeMapping->attributeName];
							break;
						case 12:
							$this->external_product_id_type = 'UPC'; //12 digit UPC
							break;
						case 13:
							$this->external_product_id_type = 'EAN'; //13 digit EAN
							break;
						case 14:
							$this->external_product_id_type = 'EAN'; //14 digit EAN
							break;
						case 16:
							$this->external_product_id_type = 'GCID'; //16 digit GCID
							break;
						default:
							$this->external_product_id_type = ''; //valid ASIN, UPC or EAN required for product id
					}		

					$output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;	
					$output .= $this->fieldDelimiter;
				
					//output product id type with delimiter, continue to next column/attribute														
					$output .= $quotes . $this->external_product_id_type . $quotes;
					$output .= $this->fieldDelimiter;	
					continue;			
				}
				//attributes are 'set' if they are formated (line 38 for example) or if there is a value associated with the attribute
				if ( isset( $product->attributes[$thisAttributeMapping->attributeName] ) )
				{
					$output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;			
					//$output .= 'c'.$this->fieldDelimiter;
				}
				//output delimiter is attribute is enabled and not deleted
				$output .= $this->fieldDelimiter;
			}
		}

		//********************************************************************
		//Trigger Mapping 3.0 After-Feed Event
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 3)
				$thisDefault->postProcess($product, $output);

		//Trim trailing comma
		return substr($output, 0, -1) . "\r\n";	

	} //format Product

//First row of txt/csv. Requires getFeedFooter otherwise attributeMappings will output twice
	function getFeedHeader($file_name, $file_path) 
	{

		if ( $this->headerTemplateType != 'inventoryloader' && $this->headerTemplateType != 'priceinventory' ) {
			//Amazon header line 1
			$output = implode(
				$this->fieldDelimiter, 
				array('TemplateType=' . $this->headerTemplateType,  'Version=' . $this->headerTemplateVersion, 'The top 3 rows are for Amazon.com use only. Do not modify or delete the top 3 rows.')
			) .  "\r\n";

			//Amazon header line 2
				$localizedNames = array();
				foreach($this->attributeMappings as $thisMapping) 
					//check if mapping is enabled/disabled
					if ($thisMapping->enabled && !$thisMapping->deleted) 
					{
					    if ( isset($thisMapping->localized_name) )
						{								
							$localizedNames[] = $thisMapping->localized_name;
							//look for external_product_id... if exists add external_product_id beside it
							if ( $thisMapping->mapTo == 'external_product_id' )
								$localizedNames[] = 'Product ID Type';
							if ( $thisMapping->mapTo == 'product-id-number' || $thisMapping->mapTo == 'product-id' )
								$localizedNames[] = 'product-id-type';
						}
						else
							$localizedNames[] = '';
					}
					$output .= implode($this->fieldDelimiter, $localizedNames) .  "\r\n";		
		} else
			$output = ''; //InventoryLoader Output

		//Amazon header line 3
		foreach($this->attributeMappings as $thisMapping)
		{
			if ($thisMapping->enabled && !$thisMapping->deleted)
			{
				$output .= $thisMapping->mapTo . $this->fieldDelimiter;
				//look for external_product_id... if exists add external_product_id beside it
				if ( $thisMapping->mapTo == 'external_product_id' )
					$output .= 'external_product_id_type' . $this->fieldDelimiter;
				if ( $thisMapping->mapTo == 'product-id-number' || $thisMapping->mapTo == 'product-id')
					$output .= 'product-id-type' . $this->fieldDelimiter;
			}				
		}

		return substr($output, 0, -1) .  "\r\n";
	}

	function initializeTemplateData($template) {
		$this->template = strtolower( $template );
		switch ($this->template) {
//US template versions updated Jan 7, 2015
			case 'automotive.and.powersports (parts.and.accessories)':
				$this->headerTemplateType = 'AutoAccessory';
				$this->headerTemplateVersion = '2014.0611';
				break;	
			case 'automotive.and.powersports (tires.and.wheels)':
				$this->headerTemplateType = 'Tiresandwheels';
				$this->headerTemplateVersion = '2014.0611';
				break;		
			case 'baby':
				$this->feed_product_type = 'BabyProducts'; //this template version only has one feed_product_type
				$this->headerTemplateType = 'Baby';
				$this->headerTemplateVersion = '2014.0124';
				break;
			case 'beauty':
				$this->feed_product_type = 'BeautyMisc'; //this template version only has one feed_product_type
				$this->headerTemplateType = 'Beauty';
				$this->headerTemplateVersion = '2014.0312';
				break;
			case 'camera.and.photo':
				//22 different product types (from template file)
				$this->headerTemplateType = 'CameraAndPhoto';
				$this->headerTemplateVersion = '2014.0616';
				break;
			case 'cell phones.and.accessories (wireless)':
				//does not require product type
				$this->headerTemplateType = 'Wireless';
				$this->headerTemplateVersion = '2014.0611';
				break;
			case 'clothing.and.accessories':
				$this->headerTemplateType = 'Clothing';
				$this->headerTemplateVersion = '2014.0812'; //lite version
				break;
			case 'collectible coins':
				$this->headerTemplateType = 'Coins';
				$this->headerTemplateVersion = '2014.0807';
				break;
			case 'computers':
				//25 different product types				
				$this->headerTemplateType = 'Computers';
				$this->headerTemplateVersion = '2014.0611';
				break;
			case 'consumer electronics':
				$this->headerTemplateType = 'ConsumerElectronics';
				$this->headerTemplateVersion = '2014.0612';
				break;
			 case 'entertainment collectibles':
			 	$this->headerTemplateType = 'EntertainmentCollectibles';
			 	$this->headerTemplateVersion = '2014.1031';
			 	break;	
			case 'gift cards':
				//electronicgiftcard | physicalgiftcard
				$this->headerTemplateType = 'GiftCards';
				$this->headerTemplateVersion = '2013.1025';
				break;			
			case 'grocery.and.gourmet food':
				//Food or Beverages
				$this->headerTemplateType = 'FoodAndBeverages';
				$this->headerTemplateVersion = '2014.1119';
				break;
			case 'health.and.personal care':
				$this->headerTemplateType = 'Health';
				$this->headerTemplateVersion = '2014.1119';
				break;
			case 'home.and.garden':
				$this->headerTemplateType = 'Home';
				$this->headerTemplateVersion = '2014.1119';
				break;
			case 'home improvement (tools)':
				$this->headerTemplateType = 'HomeImprovement';
				$this->headerTemplateVersion = '2014.0611';
				break;	
			case 'industrial.and.scientific > fasteners':
				$this->headerTemplateType = 'MechanicalFasteners';
				$this->headerTemplateVersion = '2013.0903';
				break;	
			 case 'industrial.and.scientific > food service and janitorial, sanitation.and.safety':
			 	$this->headerTemplateType = 'FoodServiceAndJanSan';
			 	$this->headerTemplateVersion = '2012.0913';
				break;	
			case 'industrial.and.scientific > lab.and.scientific supplies':
				$this->headerTemplateType = 'LabSupplies';
				$this->headerTemplateVersion = '2013.0903';
				break;
			case 'industrial.and.scientific > power transmission':
				$this->headerTemplateType = 'PowerTransmission';
				$this->headerTemplateVersion = '2014.0415';
				break;
			case 'industrial.and.scientific > raw materials':
				$this->headerTemplateType = 'RawMaterials';
				$this->headerTemplateVersion = '2014.0220';
				break;
			case 'industrial.and.scientific > other':
				$this->headerTemplateType = 'Industrial';
				$this->headerTemplateVersion = '2014.0219';
				break;	
			case 'inventoryloader':
				$this->headerTemplateType = 'inventoryloader';
				break;																		
			case 'jewelry':
				$this->headerTemplateType = 'Jewelry';
				$this->headerTemplateVersion = '2014.0318';
				break;
			case 'listingloader':
				$this->headerTemplateType = 'Offer';
				$this->headerTemplateVersion = '2014.0703';
				break;
			case 'musical instruments':
				$this->headerTemplateType = 'MusicalInstruments';
				$this->headerTemplateVersion = '2014.1119';
				break;
			case 'office products':
				//14 product feed types
				$this->headerTemplateType = 'Office';
				$this->headerTemplateVersion = '2014.0611';
				break;
			case 'pet supplies':
				$this->feed_product_type = 'PetSuppliesMisc'; //just one feed product type :)
				$this->headerTemplateType = 'PetSupplies';
				$this->headerTemplateVersion = '2014.0619';
				break;
			case 'priceinventory':
				$this->headerTemplateType = 'priceinventory';			
				break;
			case 'shoes, handbags.and.sunglasses':
				$this->headerTemplateType = 'Shoes';
				$this->headerTemplateVersion = '2014.1119';
				break;	
			case 'software.and.video games':
				$this->headerTemplateType = 'SoftwareVideoGames';
				$this->headerTemplateVersion = '2014.0513';
				break;			
			case 'sports.and.outdoors':
				$this->headerTemplateType = 'Sports';
				$this->headerTemplateVersion = '2014.0611';
				break;
			case 'sports collectibles':
				$this->headerTemplateType = 'SportsMemorabilia';
				$this->headerTemplateVersion = '2014.0305';
				break;
			case 'toys.and.games':
				$this->headerTemplateType = 'Toys';
				$this->headerTemplateVersion = '2014.0612';
				break;
			case 'watches':
				$this->headerTemplateType = 'Watches';
				$this->headerTemplateVersion = '2014.0909';
				break;	
//Spanish template versions updated June 26, 2015
			case 'es/ sports and outdoors':
			    $this->feed_product_type = 'SportingGoods'; //1 product type
				$this->headerTemplateType = 'Sports';
				$this->headerTemplateVersion = '2014.1013';
				break;
			case 'es/ clothing and accessories':
				$this->headerTemplateType = 'Clothing';
				$this->headerTemplateVersion = '2015.0227';
				break;
				
//Italian template versions updated Mar 1, 2015
			case 'it/ luggage':
				$this->feed_product_type = 'luggage'; //1 product type
				$this->headerTemplateType = 'Luggage';
				$this->headerTemplateVersion = '2014.1228';
				break;
//UK template versions updated Mar 1, 2015
			case 'uk/ amazon device accessories':
				$this->feed_product_type = 'KindleAccessories'; //1 product type
				$this->headerTemplateType = 'KindleAccessories';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ baby':
				$this->feed_product_type = 'BabyProducts'; //1 product type
				$this->headerTemplateType = 'Baby';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ beauty':
				$this->feed_product_type = 'BeautyMisc'; //1 product type
				$this->headerTemplateType = 'Beauty';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ car and motorbike':				
				$this->headerTemplateType = 'AutoAccessory';
				$this->headerTemplateVersion = '2015.0210';
				break;	
			case 'uk/ clothing':				
				$this->headerTemplateType = 'Clothing';
				$this->headerTemplateVersion = '2014.1013';
				break;
			case 'uk/ computers.and.accessories':				
				$this->headerTemplateType = 'Computers';
				$this->headerTemplateVersion = '2014.1223';
				break;
			case 'uk/ consumer electronics':				
				$this->headerTemplateType = 'ConsumerElectronics';
				$this->headerTemplateVersion = '2014.1223';
				break;
			case 'uk/ food.and.beverages':				
				$this->headerTemplateType = 'FoodAndBeverages';
				$this->headerTemplateVersion = '2014.1202';
				break;		
			case 'uk/ health and personal care':
				$this->feed_product_type = 'HealthMisc'; //1 product type
				$this->headerTemplateType = 'Health';
				$this->headerTemplateVersion = '2015.0116';
				break;	
			case 'uk/ home':
				//3 product types: BedAndBath, FurnitureAndDecor, Home
				$this->headerTemplateType = 'Home';
				$this->headerTemplateVersion = '2014.1119';
				break;
			case 'uk/ home improvement':
				//3 product types: BedAndBath, FurnitureAndDecor, Home
				$this->headerTemplateType = 'HomeImprovement';
				$this->headerTemplateVersion = '2013.1109';
				break;	
			case 'uk/ inventoryloader':
				$this->headerTemplateType = 'inventoryloader';
				break;
			case 'uk/ jewelry':
				//8 product types
				$this->headerTemplateType = 'Jewelry';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ large appliances':				
				$this->headerTemplateType = 'LargeAppliances';
				$this->headerTemplateVersion = '2014.1223';
				break;
			case 'uk/ lawn.and.garden':				
				$this->headerTemplateType = 'LawnAndGarden';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ lighting':				
				$this->headerTemplateType = 'Lighting';
				$this->headerTemplateVersion = '2014.1223';
				break;
			case 'uk/ listingloader':				
				$this->headerTemplateType = 'Offer';
				$this->headerTemplateVersion = '2014.0703';
				break;
			case 'uk/ luggage':	
				$this->feed_product_type = 'Luggage'; //1 product type
				$this->headerTemplateType = 'Luggage';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ musical instruments':				
				$this->headerTemplateType = 'MusicalInstruments';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ office':
				$this->headerTemplateType = 'Office';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ personal care appliances':
				$this->feed_product_type = 'PersonalCareAppliances'; //1 product type
				$this->headerTemplateType = 'Personalcareappliances';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ pet supplies':
				$this->headerTemplateType = 'PetSupplies';
				$this->headerTemplateVersion = '2014.1228';
				break;
			case 'uk/ priceinventory':
				$this->headerTemplateType = 'priceinventory';			
				break;
			case 'uk/ shoes.and.accessories':
				//3 product types
				$this->headerTemplateType = 'Shoes';
				$this->headerTemplateVersion = '2015.0226';
				break;
			case 'uk/ small domestic appliances (kitchen)':
				$this->feed_product_type = 'Kitchen'; //1 product type
				$this->headerTemplateType = 'Kitchen';
				$this->headerTemplateVersion = '2014.1223';
				break;
			case 'uk/ software.and.video games':
				//5 product types
				$this->headerTemplateType = 'SoftwareVideoGames';
				$this->headerTemplateVersion = '2014.1228';
				break;	
			case 'uk/ sports':
				$this->feed_product_type = 'SportingGoods'; //1 product type
				$this->headerTemplateType = 'Sports';
				$this->headerTemplateVersion = '2014.1013';
				break;
			case 'uk/ watches':
				//no feed product type
				$this->headerTemplateType = 'Watches';
				$this->headerTemplateVersion = '2015.0122';
				break;
			case 'uk/ toys':
				$this->feed_product_type = 'ToysAndGames'; //1 product type
				$this->headerTemplateType = 'Toys';
				$this->headerTemplateVersion = '2014.1228';
				break;								
			default:
				$this->headerTemplateType = $template;
				$this->feed_product_type = '';
				$this->headerTemplateVersion = '2014.0409';
		}
	}

	function initializeFeed( $category, $remote_category ) 
	{
		$this->loadTemplate($remote_category);
	} //initialize feed

	//Not safe to assume continueFeed will exist next version
	protected function continueFeed($category, $file_name, $file_path, $remote_category) {
		$this->loadTemplate($remote_category);
		parent::continueFeed($category, $file_name, $file_path, $remote_category);
	}

}

?>