<?php

	/********************************************************************
	Version 2.0
		An Amazon Feed (Product Ads)
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08

  ********************************************************************/

/**** DO NOT CHANGE CATEGORY NAMES (categories.txt) UNLESS YOU MAKE CHANGES HERE AS WELL ***/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAmazonSCFeed extends PCSVFeed 
{
	public $headerTemplateType; //Attached to the top of the feed
	public $headerTemplateVersion;

	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'AmazonSC';
		$this->providerNameL = 'amazonsc';
		$this->fileformat = 'csv';
		$this->fields = array();
		$this->fieldDelimiter = "\t";

		$this->external_product_id_type = '';
		$this->descriptionStrict = true;
		$this->stripHTML = true;		
	}

	function formatProduct($product) {

		//********************************************************************
		//Prepare the product
		//********************************************************************		
		$product->attributes['product_description'] = str_replace('"', '', $product->description);
		$product->attributes['category'] = $this->current_category;

		//format attributes (set them manually)
		//.. instead of mapping brand -> manufacturer, map manufacturer->manufacturer and format here
		//...easier for user to map using advanced commands
		$product->attributes['item_sku'] = $product->attributes['sku'];	
		$product->attributes['manufacturer'] = $product->attributes['brand'];
		$product->attributes['quantity'] = $product->attributes['stock_quantity'];
		$product->attributes['standard_price'] = $product->attributes['regular_price'];			

		//fix missing brand error (customized error)
		if (isset($product->attributes['brand']))
			$product->attributes['brand_name'] = $product->attributes['brand'];
		else
			$product->attributes['brand_name'] = '';

		if ( !isset($product->attributes['item_sku']) )
			$product->attributes['item_sku'] = $product->id;
		if ( !isset($product->attributes['manufacturer']) )
			$product->attributes['manufacturer'] = '';
		if ( !isset($product->attributes['feed_product_type']) )
			$product->attributes['feed_product_type'] = '';
		
		//sometimes templates only have one feed_product_type.
		if ( (strlen($this->feed_product_type) > 0) || (strlen($product->attributes['feed_product_type']) == 0) )
			$product->attributes['feed_product_type'] = $this->feed_product_type;
		
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;
		$image_count = 0;
		foreach($product->imgurls as $imgurl) {
			$image_index = "other_image_url_$image_count";
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
		$product->attributes['item_weight_unit_of_measure'] = $this->weight_unit;
		
		//if ($product->isVariable)
			//$product->attributes['parent_child'] = 'Variation'; //Trying without variations for now

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************
		if (strlen($product->attributes['product_description']) > 500) 
		{
			$product->attributes['product_description'] = substr($product->attributes['description'], 0, 500);
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
				if ( $thisAttributeMapping->mapTo == 'external_product_id' )				
				{				
					//output external_product_id (12,13 digit code)
					$output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;	
					$output .= $this->fieldDelimiter;
					//convert digit to string and get the length. Depending on the length, product id type can be upc, ean or gcid...
					$productId_strlen = strlen( (string)$product->attributes[$thisAttributeMapping->attributeName] );
					switch ( $productId_strlen ) 
					{
						case 12:
							$this->external_product_id_type = 'UPC'; //12 digit UPC
							break;
						case 13:
							$this->external_product_id_type = 'EAN'; //13 digit EAN
							break;
						case 16:
							$this->external_product_id_type = 'GCID'; //16 digit GCID
							break;
						deafult:
							$this->external_product_id_type = '';
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
	}

	function getFeedHeader($file_name, $file_path) 
	{
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
					}
					else
						$localizedNames[] = '';
				}
				$output .= implode($this->fieldDelimiter, $localizedNames) .  "\r\n";		
	

		//Amazon header line 3
		foreach($this->attributeMappings as $thisMapping)
		{
			if ($thisMapping->enabled && !$thisMapping->deleted)
			{
				$output .= $thisMapping->mapTo . $this->fieldDelimiter;
				//look for external_product_id... if exists add external_product_id beside it
				if ( $thisMapping->mapTo == 'external_product_id' )
					$output .= 'external_product_id_type' . $this->fieldDelimiter;
			}				
		}

		return substr($output, 0, -1) .  "\r\n";
	}

	function initializeFeed( $category, $remote_category ) 
	{
		$this->template = strtolower( $remote_category );
		switch ($this->template) {
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
			 	$this->feed_product_type = 'EntertainmentCollectibles';
			 	$this->headerTemplateType = 'EntertainmentCollectibles';
			 	$this->headerTemplateVersion = '2012.1004';
			 	break;	
			case 'gift cards':
				//electronicgiftcard | physicalgiftcard
				$this->headerTemplateType = 'GiftCards';
				$this->headerTemplateVersion = '2013.1025';
				break;			
			case 'grocery.and.gourmet food':
				//food or beverages
				$this->headerTemplateType = 'FoodAndBeverages';
				$this->headerTemplateVersion = '2014.0729';
				break;
			case 'health':
				$this->feed_product_type = 'HealthMisc'; //two types, actually..
				$this->headerTemplateType = 'Health';
				$this->headerTemplateVersion = '2014.0115';
				break;
			case 'home.and.garden';
				$this->headerTemplateType = 'Home';
				$this->headerTemplateVersion = '2014.0905';
				break;
			case 'home improvement (tools)';
				$this->headerTemplateType = 'HomeImprovement';
				$this->headerTemplateVersion = '2014.0611';
				break;	
			case 'industrial.and.scientific > fasteners';
				$this->headerTemplateType = 'MechanicalFasteners';
				$this->headerTemplateVersion = '2013.0903';
				break;	
			 case 'industrial.and.scientific > food service and janitorial, sanitation.and.safety';
			 	$this->headerTemplateType = 'FoodServiceAndJanSan';
			 	$this->headerTemplateVersion = '2012.0913';
				break;	
			case 'industrial.and.scientific > lab.and.scientific supplies';
				$this->headerTemplateType = 'LabSupplies';
				$this->headerTemplateVersion = '2013.0903';
				break;
			case 'industrial.and.scientific > power transmission';
				$this->headerTemplateType = 'PowerTransmission';
				$this->headerTemplateVersion = '2014.0415';
				break;
			case 'industrial.and.scientific > raw materials';
				$this->headerTemplateType = 'RawMaterials';
				$this->headerTemplateVersion = '2014.0220';
				break;	
			case 'industrial.and.scientific > raw materials';
				$this->headerTemplateType = 'RawMaterials';
				$this->headerTemplateVersion = '2014.0220';
				break;
			case 'industrial.and.scientific > other';
				$this->headerTemplateType = 'Industrial';
				$this->headerTemplateVersion = '2014.0219';
				break;																			
			case 'jewelry';
				$this->headerTemplateType = 'Jewelry';
				$this->headerTemplateVersion = '2014.0318';
				break;
			case 'musical instruments';
				$this->headerTemplateType = 'MusicalInstruments';
				$this->headerTemplateVersion = '2014.0619';
				break;
			case 'office products';
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
				$this->headerTemplateVersion = '2014.0707';
				break;	
			case 'software.and.video games';
				$this->headerTemplateType = 'SoftwareVideoGames';
				$this->headerTemplateVersion = '2014.0513';
				break;			
			case 'sports.and.outdoors';
				$this->headerTemplateType = 'Sports';
				$this->headerTemplateVersion = '2014.0611';
				break;
			case 'sports collectibles';
				$this->headerTemplateType = 'SportsMemorabilia';
				$this->headerTemplateVersion = '2014.0305';
				break;
			case 'toys.and.games';
				$this->headerTemplateType = 'Toys';
				$this->headerTemplateVersion = '2014.0612';
				break;
			case 'watches';
				$this->headerTemplateType = 'Watches';
				$this->headerTemplateVersion = '2014.0909';
				break;								
			default:
				$this->headerTemplateType = $remote_category;
				$this->feed_product_type = '';
				$this->headerTemplateVersion = '2014.0409';
	}

		/*** Template: Basic - these are attributes that are important to buyers. Some are required to create an offer **/		
		
		//Shared by all templates
		$this->addAttributeMapping('item_sku', 'item_sku')->localized_name = 'SKU'; 

		if ( !($this->headerTemplateType == 'SoftwareVideoGames' || $this->headerTemplateType == 'SportsMemorabilia') )
			$this->addAttributeMapping('external_product_id', 'external_product_id')->localized_name = 'Product ID'; //a valid upc/gcid/ean
		//Note external_product_id_type is generated on the fly, under formatProducts function
		//$this->addAttributeMapping('external_product_id_type', 'external_product_id_type')->localized_name = 'Product ID Type';
		$this->addAttributeMapping('product_description', 'product_description', true)->localized_name = 'Product Description'; //required in petsupplies
		//$this->addAttributeMapping('update_delete', 'update_delete')->localized_name = 'Update Delete'; //preferred or optional
		
		//Title local name differs
		if ( 
			$this->template == 'jewelry' || $this->template == 'computers' || $this->template == 'consumer electronics' || 
			$this->template == 'cell phones.and.accessories' ||	$this->template == 'baby' || $this->template == 'camera.and.photo' || 
			$this->headerTemplateType == 'EntertainmentCollectibles'
			) 
			 $this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Title'; //jewelry/comp local label name differs..
		else 
			$this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Product Name'; //..from all others				
		
		if ( 
			$this->template == 'computers' || $this->template == 'cell phones.and.accessories' || 
			$this->template == 'baby' || $this->template == 'camera.and.photo' || $this->template == 'shoes, handbags.and.sunglasses'
			) 
			 $this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand'; //mattel, samsung
		else $this->addAttributeMapping('brand_name', 'brand_name', true)->localized_name = 'Brand Name';

		//item_type: please refer to BTG
		if ( 
			$this->template == 'computers' || $this->template == 'consumer electronics' || $this->template == 'cell phones.and.accessories' ||
			$this->template == 'camera.and.photo' || $this->template == 'shoes, handbags.and.sunglasses'
			) 
			 $this->addAttributeMapping('item_type', 'item_type')->localized_name = 'Item Type';
		else if ( $this->template == 'office products' || $this->template == 'gift cards' ) 
			 $this->addAttributeMapping('item_type', 'item_type')->localized_name = 'Category (item-type)'; //office local label name differs..
		else $this->addAttributeMapping('item_type', 'item_type')->localized_name = 'Item Type Keyword'; //..from all others
		
		if ( $this->template == 'clothing' )
			$this->addAttributeMapping('model', 'model')->localized_name = 'Style Number';
		if ( $this->template == 'jewelry' )
			$this->addAttributeMapping('model', 'model')->localized_name = 'Model Number';

		if ( $this->template == 'clothing' || $this->headerTemplateType == 'Shoes' || 
			$this->headerTemplateType == 'Sports' || $this->headerTemplateType == 'SportsMemorabilia' ) { } //don't generate below headings
		else
		{
			if ( !(
				$this->template == 'cell phones.and.accessories' || $this->headerTemplateType == 'MechanicalFasteners' || 
				$this->headerTemplateType == 'LabSupplies' || $this->headerTemplateType == 'PowerTransmission' || 
				$this->headerTemplateType == 'Toys' || $this->template == 'watches' ||
				$this->headerTemplateType == 'FoodServiceAndJanSan'
				) )
			{
			$this->addAttributeMapping('feed_product_type', 'feed_product_type')->localized_name = 'Product Type'; //not in clothing, wireless...			
			}
			$this->addAttributeMapping('manufacturer', 'manufacturer')->localized_name = 'Manufacturer'; //not in clothing 
			//part_number is OPTIONAL in baby, and its localized name is 'Part Number'
			if ( $this->template == 'camera.and.photo' || $this->template == 'consumer electronics' ) 
				$this->addAttributeMapping('part_number', 'part_number')->localized_name = 'Mfr Part Number'; //not in clothing	
			else if ( $this->headerTemplateType == 'MechanicalFasteners' || $this->headerTemplateType == 'EntertainmentCollectibles' )
				$this->addAttributeMapping('part_number', 'part_number')->localized_name = 'Part Number'; //not in clothing	
			else if ( $this->template == 'grocery.and.gourmet food' ) {} //grocery doesn't have part number
			else
				$this->addAttributeMapping('part_number', 'part_number')->localized_name = 'Manufacturer Part Number'; //not in clothing			
		}
		//optional in: office
		// if ( $this->template == 'office' ) {
		// 	$this->addAttributeMapping('gtin_exemption_reason', 'gtin_exemption_reason')->localized_name = 'Product Exemption Reason'; 
		// 	$this->addAttributeMapping('related_product_id', 'related_product_id')->localized_name = 'Related Product Identifier'; 
		// 	$this->addAttributeMapping('related_product_id_type', 'related_product_id_type')->localized_name = 'Related Product Identifier Type'; 
		// }

		/*** Template: Offer - required to make your item buyable for customers on site **/
		// if ( $this->template == 'cell phones.and.accessories' || $this->template == 'camera.and.photo' )
		// 	$this->addAttributeMapping('list_price', 'list_price')->localized_name = 'MSRP';
		// else
		// 	$this->addAttributeMapping('list_price', 'list_price')->localized_name = 'Manufacturer\'s Suggested Retail Price';
		if ( $this->headerTemplateType == 'RawMaterials' )
			$this->addAttributeMapping('list_price', 'list_price')->localized_name = 'Manufacturer\'s Suggested Retail Price';
		//optional/preferred
		if ( $this->headerTemplateType == 'FoodServiceAndJanSan' ) {
			$this->addAttributeMapping('item-price', 'item-price')->localized_name = 'Item Price';
			$this->addAttributeMapping('item-leadtime-to-ship', 'leadtime-to-ship')->localized_name = 'Leadtime To Ship';
		}
		else 
			$this->addAttributeMapping('standard_price', 'standard_price')->localized_name = 'Standard Price';
		$this->addAttributeMapping('currency', 'currency')->localized_name = 'Currency'; 
		$this->addAttributeMapping('quantity', 'quantity')->localized_name = 'Quantity';
		//$this->addAttributeMapping('sale_price', 'sale_price')->localized_name = 'Sale Price';		

		if ( $this->template == 'automotive.and.powersports' || $this->template == 'grocery.and.gourmet food' || $this->template == 'health')
			$this->addAttributeMapping('item_package_quantity', 'item_package_quantity')->localized_name = 'Package Quantity'; //items in package
		//else $this->template == 'computers' || $this->template == 'consumer electronics' || $this->template == 'cell phones.and.accessories' ) 
		
		if ( $this->template == 'pet supplies' || $this->headerTemplateType == 'SoftwareVideoGames' || $this->headerTemplateType == 'EntertainmentCollectibles' )
			$this->addAttributeMapping('condition_type', 'condition_type')->localized_name = 'Item Condition'; //required for pet supplies

		/** Template: Dimensions - These attributes specify the size and weight of a product */
		if ( $this->template == 'jewelry' )
			$this->addAttributeMapping('display_dimensions_unit_of_measure', 'display_dimensions_unit_of_measure')->localized_name = 'Display Dimensions Unit Of Measure'; //MM,CM,M,IN,FT
		else if ( $this->headerTemplateType == 'SportsMemorabilia' )
		{
			$this->addAttributeMapping('display_dimensions_unit_of_measure', 'display_dimensions_unit_of_measure')->localized_name = 'Display Dimensions Unit Of Measure'; //MM,CM,M,IN,FT
			$this->addAttributeMapping('item_display_weight_unit_of_measure', 'item_display_weight_unit_of_measure')->localized_name = 'Item Display Weight Unit Of Measure'; //LB	
		}
		else
		{
			$this->addAttributeMapping('item_weight', 'item_weight')->localized_name = 'Item Weight';
			$this->addAttributeMapping('item_weight_unit_of_measure', 'item_weight_unit_of_measure')->localized_name = 'Item Weight Unit Of Measure';
		}

		if ( $this->headerTemplateType == 'MechanicalFasteners' || $this->headerTemplateType == 'LabSupplies' || $this->headerTemplateType == 'PowerTransmission' 
			|| $this->headerTemplateType == 'RawMaterials' || $this->headerTemplateType == 'Industrial' || $this->headerTemplateType == 'FoodServiceAndJanSan' )	
		{
			$this->addAttributeMapping('number_of_items', 'number_of_items')->localized_name = 'Number Of Items'; //# of identical fasteners, ex 32
			if ( !($this->headerTemplateType == 'PowerTransmission' || $this->headerTemplateType == 'FoodServiceAndJanSan') )
				$this->addAttributeMapping('fulfillment_latency', 'fulfillment_latency')->localized_name = 'Fulfillment Latency'; //days: when you receive till ship
		}
		//*** Template: Discovery
		if ( $this->template == 'automotive.and.powersports' )
		{
			//for both these, select from valid values list
			for ($i = 1; $i < 4; $i++)
				$this->addAttributeMapping( 'style_keywords' . $i, 'style_keywords' . $i, true )->localized_name = 'Style-specific Terms' . $i;
			for ($i = 1; $i < 4; $i++)
				$this->addAttributeMapping( 'department_name' . $i, 'department_name' . $i, true )->localized_name = 'Department' . $i;
		}
		//Browse node - a positive integer, example: 243826011
		if ( $this->template == 'baby' )
		{
			for ($i = 1; $i <= 2; $i++)
				$this->addAttributeMapping( 'recommended_browse_nodes' . $i, 'recommended_browse_nodes' . $i, true )->localized_name = 'Recommended Browse Node' . $i;
		}
		//optional
		for ($i = 1; $i <= 3; $i++)
			$this->addAttributeMapping('bullet_point' . $i, 'bullet_point' . $i, true)->localized_name = 'Key Product Features' . $i; //in camera.and.photo, localized name is Bullet Point1
		for ($i = 1; $i <= 3; $i++)
			$this->addAttributeMapping('generic_keywords' . $i, 'generic_keywords' . $i, true)->localized_name = 'Search Terms' . $i;
		
		if ( $this->template == 'pet supplies' || $this->template == 'gift cards' || $this->headerTemplateType == 'Toys' || $this->template == 'watches')
		{
			//Use this to specify the target audience for your product. Example: Amphibians. Refer to BTG
			for ($i = 1; $i <= 3; $i++)
				$this->addAttributeMapping('target_audience_keywords' . $i, 'target_audience_keywords' . $i, true)->localized_name = 'Target Audience' . $i;
		}

		//Template: Images. Not required but usually clients will have this in woocommerce
		$this->addAttributeMapping('feature_imgurl', 'main_image_url', true)->localized_name = 'Main Image URL';
		for ($i = 1; $i < 5; $i++)
			$this->addAttributeMapping("other_image_url_$i", "other_image_url$i", true)->localized_name = 'Other Image URL' . $i;

		//*** Template: Variation
		//$this->addAttributeMapping('item_group_id', 'parent_sku');
		if ( $this->template== 'jewelry' )
		{
			$this->addAttributeMapping('parent_child', 'parent_child')->localized_name = 'Parentage'; //parent or child
			$this->addAttributeMapping('department_name', 'department_name')->localized_name = 'Gender'; //ex: womens
			$this->addAttributeMapping('metal_type', 'metal_type', true)->localized_name = 'Metal Type'; //ex: white-gold
			$this->addAttributeMapping('metal_stamp', 'metal_stamp')->localized_name = 'Metal Stamp'; //18k
			for ($i = 1; $i <= 3; $i++)
				$this->addAttributeMapping("gem_type$i", "gem_type$i", true)->localized_name = 'Gem Type' . $i; //gems present: carnelian
		}
		//

		//*** Template: Compliance - attribues used to comply with consumer laws in the country or region where the item is sold
		if ( $this->template == 'gift cards' )
		{
			$this->addAttributeMapping('legal_disclaimer_description', 'legal_disclaimer_description')->localized_name = 'Legal Disclaimer'; //string max 50 chars
			for ($i = 1; $i < 5; $i++)
				$this->addAttributeMapping("state_string$i", "state_string$i", true)->localized_name = 'State' . $i; //state code..
			$this->addAttributeMapping('format', 'format')->localized_name = 'Format'; //ex: email, plastic, facebook, print at home or multi-pack
			for ($i = 1; $i < 5; $i++)
			$this->addAttributeMapping("genre$i", "genre$i", true)->localized_name = 'Category' . $i; //state code..
		}
		//*** Template: Ungrouped - these attributes create rich product listings for your buyers
		if ( $this->template == 'automotive.and.powersports' )
		{
			$this->addAttributeMapping('color_name', 'color_name')->localized_name = 'Color'; //ex: Navy Blue	
			$this->addAttributeMapping('model_name', 'model_name')->localized_name = 'Series'; //ex: Aspire (series/chassis of the product)
			$this->addAttributeMapping('size_name', 'size_name')->localized_name = 'Size'; //ex: Kids, Husky
		}
		//clothing only
		if ( $this->template == 'clothing'  )
		{
			$this->addAttributeMapping('color_name', 'color_name')->localized_name = 'Color'; //ex: Navy Blue
			$this->addAttributeMapping('department_name', 'department_name')->localized_name = 'Department'; //ex: womens
			$this->addAttributeMapping('size_name', 'size_name')->localized_name = 'Size'; //ex: X-Large, One Size
 		}
 		//baby product only. ALL required
 		if ( $this->template == 'baby' || $this->headerTemplateType == 'Toys' ) 
 		{ 			
 			$this->addAttributeMapping('mfg_minimum','mfg_minimum')->localized_name = 'Minimum Manufacturer Age Recommended'; //positive integer: 12
			$this->addAttributeMapping('mfg_minimum_unit_of_measure','mfg_minimum_unit_of_measure')->localized_name = 'Mfg Minimum Unit Of Measure'; //months or years
			if ( $this->template == 'baby' )
			{
			$this->addAttributeMapping('mfg_maximum','mfg_maximum')->localized_name = 'Maximum Manufacturer Age Recommended'; //positive integer: 8
			$this->addAttributeMapping('mfg_maximum_unit_of_measure','mfg_maximum_unit_of_measure')->localized_name = 'Mfg Maximum Unit Of Measure'; //years
 			}
 		}  
 		//RawMaterials only
 		if ( $this->headerTemplateType == 'RawMaterials' )
 		{
 			$this->addAttributeMapping('material_type', 'material_type')->localized_name = 'Material Type'; //material: Gold
 			$this->addAttributeMapping('measurement_system', 'measurement_system')->localized_name = 'System of Measurement'; //English or Metric
 		}
 		//Shoes, handbags & sunglasses
 		if ( $this->headerTemplateType == 'Shoes' )
 		{
 			//Eyewear
 			$this->addAttributeMapping('lens_color', 'lens_color')->localized_name = 'Lens Color'; //black,gold,clear
 			$this->addAttributeMapping('lens_color_map', 'lens_color_map')->localized_name = 'Lens Color Map'; //valid value from worksheet
 			$this->addAttributeMapping('magnification_strength', 'magnification_strength')->localized_name = 'Magnification Strength';
 			$this->addAttributeMapping('frame_material_type', 'frame_material_type')->localized_name = 'Frame Material Type';
 			$this->addAttributeMapping('lens_material_type', 'lens_material_type')->localized_name = 'Lens Material Type';
 			$this->addAttributeMapping('item_shape', 'item_shape')->localized_name = 'Item Shape';
 			$this->addAttributeMapping('polarization_type', 'polarization_type')->localized_name = 'Polarization Type'; //iridium
 			$this->addAttributeMapping('lens_width', 'lens_width')->localized_name = 'Lens Width';
 			$this->addAttributeMapping('eyewear_unit_of_measure', 'eyewear_unit_of_measure')->localized_name = 'Eyewear Unit Of Measure';
 			//Eyewear, handbag, shoe accessory, shoes
 			$this->addAttributeMapping('department_name', 'department_name')->localized_name = 'Department'; //ex: womens
 			//Handbag, Shoes
 			$this->addAttributeMapping('color_name', 'color_name')->localized_name = 'Color'; //ex: Navy Blue
 			for ($i = 1; $i <= 3; $i++)
				$this->addAttributeMapping("material_type$i", "material_type$i", true)->localized_name = 'Material Fabric' . $i; //90% cotton/10% rayon
			//Shoes
			$this->addAttributeMapping('size_name', 'size_name')->localized_name = 'Size'; //ex: X-Large, One Size
 		}
 		if ( $this->headerTemplateType == 'SportsMemorabilia' )
 		{
 			$this->addAttributeMapping('fulfillment_center_id', 'fulfillment_center_id')->localized_name = 'Fulfillment Center ID'; //AMAZON_NA
 			$this->addAttributeMapping('package_dimensions_unit_of_measure', 'package_dimensions_unit_of_measure')->localized_name = 'Package Dimensions Unit Of Measure'; //IN
 			$this->addAttributeMapping('authenticated_by', 'authenticated_by')->localized_name = 'Authentication Provided By'; //PSA
 			$this->addAttributeMapping('grade_rating', 'grade_rating')->localized_name = 'Condition Type'; //mint, excellent
 			$this->addAttributeMapping('graded_by', 'graded_by')->localized_name = 'Grading Provided By'; //BEckett, PSA
 			$this->addAttributeMapping('item_thickness_unit_of_measure', 'item_thickness_unit_of_measure')->localized_name = 'Item Thickness Unit Of Measure'; //mm, in, ft
 		}
 		if ( $this->headerTemplateType == 'EntertainmentCollectibles' )
 		{
 			$this->addAttributeMapping('entertainment_type', 'entertainment_type')->localized_name = 'Entertainment Type'; //Movies, Music, Theatre
 			$this->addAttributeMapping('condition_by', 'condition_by')->localized_name = 'Condition Provided By'; //PSA
 			$this->addAttributeMapping('authenticated_by', 'authenticated_by')->localized_name = 'Authentication By'; //PSA
 			$this->addAttributeMapping('collectible_type', 'collectible_type')->localized_name = 'Collectible Type'; //original, reproduced
 		}
	} //initialize feed
}

?>