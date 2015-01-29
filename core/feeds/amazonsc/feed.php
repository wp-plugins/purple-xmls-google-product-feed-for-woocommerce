<?php

	/********************************************************************
	Version 2.0
		An Amazon Feed (Product Ads)
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08

  ********************************************************************/

/**** Changes to categories.txt should be relfected in initializeTemplateData() ***/

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
		$this->addRule('description', 'description', array('strict'));
		$this->stripHTML = true;		
	}

	function loadTemplate($template) {

		$this->initializeTemplateData($template);
	//$this->addAttributeMapping($this->headerTemplateType, 'item_sku', true, true)->localized_name = 'SKU';
		//The "All" template excludes FoodService and Inventory AND Coins 
		if ($this->headerTemplateType != 'FoodServiceAndJanSan' 
			&& $this->headerTemplateType != 'inventoryloader' 
			&& $this->headerTemplateType != 'Coins'
			&& $this->headerTemplateType != 'EntertainmentCollectibles'
			)
			include_once dirname(__FILE__) . '/templates/all.templates.php';

		//Load the individual templates
		$files = scandir(dirname(__FILE__) . '/templates');
		foreach($files as $file)
			if ($file == $this->headerTemplateType.'.php')
			//if (strpos($file, $this->headerTemplateType) !== false)
				include_once dirname(__FILE__) . '/templates/' . $file;


		//Note external_product_id_type is generated on the fly, under formatProducts function
		//$this->addAttributeMapping('external_product_id_type', 'external_product_id_type')->localized_name = 'Product ID Type';
	
		//item_type: please refer to BTG
		
		$this->templateLoaded = true;
		$this->loadAttributeUserMap();
	}

	function formatProduct($product) {

		if (!$this->templateLoaded)
			$this->loadTemplate();

		if ( $this->headerTemplateType == 'inventoryloader')
		{
			//sku auto mapped, product-id will be mapped by user
			$product->attributes['price'] = $product->attributes['regular_price'];
			if (isset($product->attributes['sale_price']))
				$product->attributes['price'] = $product->attributes['sale_price'];
			$product->attributes['quantity'] = $product->attributes['stock_quantity'];
			$product->attributes['item-condition'] = $product->attributes['condition'];

		} //if headertemplatetype == inventoryloader
		else { 
			//********************************************************************
			//Prepare the product
			//********************************************************************		
			$product->attributes['product_description'] = str_replace('"','""',$product->attributes['description']); //Needs a rule
			$product->attributes['category'] = $this->current_category;

			//format attributes (set them manually)
			//.. instead of mapping brand -> manufacturer, map manufacturer->manufacturer and format here
			//...easier for user to map using advanced commands
			$product->attributes['item_sku'] = $product->attributes['sku'];	
			$product->attributes['item_name'] = $product->attributes['title'];

			if (isset($product->attributes['brand']))
				$product->attributes['manufacturer'] = $product->attributes['brand'];
			
			if ( $product->attributes['stock_status'] == 1 ) {
				if ( !empty($product->attributes['stock_quantity']) )
					$product->attributes['quantity'] = $product->attributes['stock_quantity'];
				}
			else {
			$product->attributes['quantity'] = '0';
			}
			
			//what if regular_price is blank but sale_price is present?
			$product->attributes['standard_price'] = $product->attributes['regular_price'];	
			//sale price usually requires an effective date.
			if (isset($product->attributes['sale_price']))
				$product->attributes['standard_price'] = $product->attributes['sale_price'];	
			
			$product->attributes['item_weight'] = $product->attributes['weight'];			
			$product->attributes['website-shipping-weight'] = $product->attributes['weight'];
			$product->attributes['dimension_unit'] = $this->dimension_unit;	
			
			//modify item weight unit to fit amazon's valid weight units. ex: convert g -> GR 
			$valid_weight_unit = $this->weight_unit;
			if ( $valid_weight_unit == 'kg' ) {
				$product->attributes['item-weight-unit-of-measure'] = 'kilograms';
				$product->attributes['weight_unit'] = 'KG';
				$product->attributes['item_weight_unit'] = 'KG';
			}			
			else if ( $valid_weight_unit == 'g' ) {
				$product->attributes['item-weight-unit-of-measure'] = 'grams';
				$product->attributes['weight_unit'] = 'GR';
				$product->attributes['item_weight_unit'] = 'GR';
			}
			else if ( $valid_weight_unit == 'lbs' ) {
				$product->attributes['item-weight-unit-of-measure'] = 'pounds';
				$product->attributes['weight_unit'] = 'LB';
				$product->attributes['item_weight_unit'] = 'LB';
			}
			else {
				$product->attributes['item-weight-unit-of-measure'] = 'ounces';
				$product->attributes['weight_unit'] = 'OZ';
				$product->attributes['item_weight_unit'] = 'OZ';
			}

			switch($this->dimension_unit){
				case 'm':
					$product->attributes['item-dimensions-unit-of-measure'] = 'meters';
					break;
				case 'cm':
					$product->attributes['item-dimensions-unit-of-measure'] = 'centimeters';
					break;
				case 'mm':
					$product->attributes['item-dimensions-unit-of-measure'] = 'millimeters';
					break;
				case 'in':
					$product->attributes['item-dimensions-unit-of-measure'] = 'inches';
					break;
				case 'ft':					
					$product->attributes['item-dimensions-unit-of-measure'] = 'feet';
					break;
				default:
					$product->attributes['dimension_unit'] = $this->dimension_unit;
			}

			//fix missing brand error (customized error)
			if (isset($product->attributes['brand']))
				$product->attributes['brand_name'] = $product->attributes['brand'];
			// else
			// 	$product->attributes['brand_name'] = $product->attributes['brand_name'];

			//default values
			if ( !isset($product->attributes['feed_product_type']) )
			 	$product->attributes['feed_product_type'] = '- refer to Inventory Template -';
			if ( !isset($product->attributes['item_type']) )
				$product->attributes['item_type'] = '- refer to Template\'s BTG -';
			if ( !isset($product->attributes['item-type-keyword']) )
				$product->attributes['item-type-keyword'] = '- refer to Template\'s BTG -';
			
			//sometimes templates only have one feed_product_type.
			if ( (strlen($this->feed_product_type) > 0) || (strlen($product->attributes['feed_product_type']) == 0) )
				$product->attributes['feed_product_type'] = $this->feed_product_type;
			
			$image_count = 1;
			foreach($product->imgurls as $imgurl) {
				$image_index = "other_image_url$image_count";
				$product->attributes[$image_index] = $imgurl;
				$image_count++;
				if ($image_count >= 9)
					break;
			}

			if (!$product->attributes['has_sale_price'])
				$product->attributes['sale_price'] = '';
			if (!isset($product->attributes['currency']) || (strlen($product->attributes['currency']) == 0))
				$product->attributes['currency'] = $this->currency;
			if (!isset($product->attributes['item_package_quantity']))
				$product->attributes['item_package_quantity'] = 1;
			$product->attributes['shipping_cost'] = '0.00';
			$product->attributes['shipping_weight'] = $product->attributes['weight'];
		
		
		} //else NOT inventoryloader

		//if ($product->attributes['isVariation'])
			//$product->attributes['parent_child'] = 'Variation'; //Trying without variations for now

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************
		if (strlen($product->attributes['product_description']) > 2000) 
		{
			$product->attributes['product_description'] = substr($product->attributes['description'], 0, 2000);
			$this->addErrorMessage(8000, 'Description truncated for ' . $product->attributes['title'], true);
		}
		
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

		//********************************************************************
		//Build output in order of fields
		//********************************************************************
		$output = '';
		foreach( $this->attributeMappings as $thisAttributeMapping ) 
		{
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
					$output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;	
					$output .= $this->fieldDelimiter;
					//convert digit to string and get the length. Depending on the length, product id type can be upc, ean or gcid...
					$productId_strlen = strlen( (string)$product->attributes[$thisAttributeMapping->attributeName] );
					switch ( $productId_strlen ) 
					{
						case 10:
							$this->external_product_id_type = 'ASIN'; //10 digit Amazon number
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
					//output product id type with delimiter, continue to next column/attribute														
					$output .= $quotes . $this->external_product_id_type . $quotes;
					$output .= $this->fieldDelimiter;	
					continue;			
				}
				//attributes are 'set' if they are formated (line 38 for example) or if there is a value associated with the attribute
				if ( isset( $product->attributes[$thisAttributeMapping->attributeName] ) )
				{
					if ( $thisAttributeMapping->usesCData )
						$quotes = '"';
					else
						$quotes = '';	
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

	function getFeedHeader($file_name, $file_path) 
	{
		
		if ( $this->headerTemplateType != 'inventoryloader' ) {
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
							if ( $thisMapping->mapTo == 'product-id-number' )
								$localizedNames[] = 'product-id-type';
						}
						else
							$localizedNames[] = '';
					}
					$output .= implode($this->fieldDelimiter, $localizedNames) .  "\r\n";		
		}

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
			//versions updated Jan 7, 2015
			case 'automotive.and.powersports':
				$this->headerTemplateType = 'AutoAccessory';
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
			case 'cell phones.and.accessories':
				//does not require product type
				$this->headerTemplateType = 'Wireless';
				$this->headerTemplateVersion = '2014.0611';
				break;
			case 'clothing':
				$this->headerTemplateType = 'Clothing';
				$this->headerTemplateVersion = '2014.0409';
				break;
			case 'collectible coins':
				$this->headerTemplateType = 'Coins';
				$this->headerTemplateVersion = 'Version=2014.0807';
				break;
			case 'computers':
				//25 different product types
				//$this->feed_product_type = 'Computer';
				$this->headerTemplateType = 'Computers';
				$this->headerTemplateVersion = '2014.0611';
				break;
			case 'consumer electronics':
				$this->headerTemplateType = 'ConsumerElectronics';
				$this->headerTemplateVersion = '2014.0612';
				break;
			 case 'entertainment collectibles':
			 	//$this->feed_product_type = 'EntertainmentCollectibles';
			 	$this->headerTemplateType = 'EntertainmentCollectibles';
			 	$this->headerTemplateVersion = '2014.1031';
			 	break;	
			case 'gift cards':
				//electronicgiftcard | physicalgiftcard
				$this->headerTemplateType = 'GiftCards';
				$this->headerTemplateVersion = '2013.1025';
				break;			
			case 'grocery.and.gourmet food':
				//food or beverages
				$this->headerTemplateType = 'FoodAndBeverages';
				$this->headerTemplateVersion = '2014.1119';
				break;
			case 'health.and.personal care':
				$this->feed_product_type = 'HealthMisc'; //two types, actually..
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
			case 'jewelry':
				$this->headerTemplateType = 'Jewelry';
				$this->headerTemplateVersion = '2014.0318';
				break;
			case 'inventoryloader':
				$this->headerTemplateType = 'inventoryloader';
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
			default:
				$this->headerTemplateType = $template;
				$this->feed_product_type = '';
				$this->headerTemplateVersion = '2014.0409';
		}
	}

	function initializeFeed( $category, $remote_category ) 
	{
		$this->loadTemplate($remote_category);

		/*** Template: Basic - these are attributes that are important to buyers. Some are required to create an offer **/		
	} //initialize feed
}

?>