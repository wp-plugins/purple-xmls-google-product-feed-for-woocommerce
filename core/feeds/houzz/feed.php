<?php

  /********************************************************************
  Version 2.0
    HOUZZ Integration
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto, Calv 2015-23-02

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PHouzzFeed extends PCSVFeedEx 
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'Houzz';
		$this->providerNameL = 'Houzz';
		$this->fileformat = 'csv';
		$this->fields = array();
		$this->fieldDelimiter = ',';
		//$this->stripHTML =

		//Create some attributes (Mapping 3.0)
		//GraziaShop sequence matters
		$this->addAttributeMapping('price', 'Price',true,true);
		$this->addAttributeMapping('sku', 'SKU',true,true); //SKU, 5-100 chars
		$this->addAttributeMapping('parent_sku', 'ParentSKU',true); //grouping sku
		$this->addAttributeMapping('', 'Parentage',true);
		$this->addAttributeMapping('', 'RelationshipType',true);
		$this->addAttributeMapping('', 'VariationTheme',true);
		$this->addAttributeMapping('n/a', 'UPC',true,true); //n/a allowed
		$this->addAttributeMapping('title', 'Title',true,true);	
		$this->addAttributeMapping('link', 'ProductUrl',true,true);	
		$this->addAttributeMapping('description', 'Description',true,true);		
		$this->addAttributeMapping('current_category', 'Category',false,true); 
		$this->addAttributeMapping('Style', 'Style',true,true); //select from valid values
		$this->addAttributeMapping('stock_quantity', 'Quantity',false,true);	
		$this->addAttributeMapping('price', 'Price',true,true);
		//Your suggested retail price. If you have a higher suggested retail price that you would like to display, enter it here.
		$this->addAttributeMapping('', 'MSRP',true,false); 
		//Your standard shipping option. If your product ships via a traditional package carrier (UPS, FedEx, USPS), list the price for standard shipping here.
		$this->addAttributeMapping('', 'StandardShipping'); 
		$this->addAttributeMapping('', 'ExpeditedShipping'); 
		//The minimum number of days from the time an order is placed until it ships. You must include all days, not just business days.
		$this->addAttributeMapping('LeadTimeMin', 'LeadTimeMin',true,true);
		//The maximum number of days from the time an order is placed until it ships. You must include all days, not just business days.
		$this->addAttributeMapping('LeadTimeMax', 'LeadTimeMax',true,true); 
		$this->addAttributeMapping('color', 'Color',true,false);
		$this->addAttributeMapping('size', 'Size',true,false);
		$this->addAttributeMapping('', 'Design',true,false); 
		$this->addAttributeMapping('length', 'Width',true,true); 
		$this->addAttributeMapping('width', 'Depth',true,true);	
		$this->addAttributeMapping('height', 'Height',true,true);
		$this->addAttributeMapping('', 'Materials',true,false);
		$this->addAttributeMapping('', 'Manufacturer'); 		
		$this->addAttributeMapping('', 'Designer',true,false);	
		$this->addAttributeMapping('feature_imgurl', 'Image ',true,true);
		for ($i = 2; $i < 6; $i++) //Additional images 2 - 5
			$this->addAttributeMapping( 'Image'.$i, 'Image'.$i, true );
		$this->addAttributeMapping('', 'Bulk Item',true);
		$this->addAttributeMapping('', 'Bulk Curbside Shipping',true);
		$this->addAttributeMapping('', 'Bulk Inside Shipping',true);
		$this->addAttributeMapping('', 'Configuration',true);	
		$this->addAttributeMapping('', 'Keywords',true);	

		$this->addRule('price_rounding','pricerounding');
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		//$this->addRule('description', 'description', array('strict'));
		$this->addRule( 'csv_standard', 'CSVStandard',array('title','80') ); //Houzz product titles are limited to 80 characters
		$this->addRule( 'csv_standard', 'CSVStandard',array('description') ); //65000 max?
	}

	function formatProduct($product) {

		$variantUPC = '';
		$variantMfr = '';
		if ($product->attributes['isVariation']) {
			//Not used in original code
			//$variantUPC = rand();
			//$variantMfr = rand();
		}
//upc
		if ( !isset($product->attributes['upc']) ) 
			$product->attributes['n/a'] = 'n/a';

//ProductURL: automatically pulled from "link". If you do not have a URL for the product, include your company website.

//category
		$category = explode(":", $this->current_category);
		if (isset($category[0]))
			$product->attributes['current_category'] = trim($category[0]);
		else
			$product->attributes['current_category'] = 'no_category_selected';
//style: 14 different styles
		if ( !isset($product->attributes['Style']) )
			$product->attributes['Style'] = 'see Houzz template for acceptable entries';
//additional images	
		$image_count = 1;
		foreach($product->imgurls as $imgurl) {
			$image_index = "Image$image_count";
			$product->attributes[$image_index] = str_replace('https://','http://',$imgurl);
			$image_count++;
			if ($image_count >= 5)
				break;
		}
//result code (error) notifications
		foreach($this->attributeMappings as $thisAttributeMapping) 
		{
			if ( $thisAttributeMapping->isRequired && ($thisAttributeMapping->mapTo == 'LeadTimeMax' || $thisAttributeMapping->mapTo == 'LeadTimeMin') ) 
			{				
				if ( !isset($product->attributes[$thisAttributeMapping->attributeName]) || strlen($product->attributes[$thisAttributeMapping->attributeName]) == 0 )
				{
					$this->addErrorMessage(19000, 'Missing required: ' . $thisAttributeMapping->mapTo);
					$this->productCount--;
				}
			}
			if ( $thisAttributeMapping->isRequired && ($thisAttributeMapping->mapTo == 'Style') ) 
			{				
				if ( !isset($product->attributes[$thisAttributeMapping->attributeName]) || strlen($product->attributes[$thisAttributeMapping->attributeName]) == 0 )
				{
					$this->addErrorMessage(19001, 'Missing required: ' . $thisAttributeMapping->mapTo);			
					$this->productCount--;
				}
			}
		}
		return parent::formatProduct($product);

		//$this->productCount--;

	}//formatProduct	

}

?>