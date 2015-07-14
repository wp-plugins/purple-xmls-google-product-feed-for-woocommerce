<?php

  /********************************************************************
  Version 2.0
    GraziaShop Merchant Integration
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2015-23-02

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PGraziaShopFeed extends PCSVFeedEx 
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'GraziaShop';
		$this->providerNameL = 'GraziaShop';
		$this->fileformat = 'csv';
		$this->fields = array();
		$this->fieldDelimiter = ",";

		//Create some attributes (Mapping 3.0)
		//GraziaShop sequence matters
		$this->addAttributeMapping('sku', 'product_code',true,true); //SKU, 5-100 chars
		$this->addAttributeMapping('item_group_id', 'group_code',true,true); //grouping sku
		$this->addAttributeMapping('', 'barcode',true);
		$this->addAttributeMapping('', 'mpn',true); //reserved for future use
		$this->addAttributeMapping('brand', 'brand',true,true);
		$this->addAttributeMapping('current_category', 'category',true,true);
		$this->addAttributeMapping('title', 'title',true,true);	//5 - 200 chars
		$this->addAttributeMapping('description', 'description',true,true);		
		$this->addAttributeMapping('', 'material',true); //100% cotton
		$this->addAttributeMapping('', 'pattern',true);
		$this->addAttributeMapping('', 'care',true);	//hand wash only
		$this->addAttributeMapping('feature_imgurl', 'image_link',true);
		$this->addAttributeMapping('additional_image_links', 'additional_image_links',true,false);
		$this->addAttributeMapping('', 'gender'); //reserved for future use
		$this->addAttributeMapping('', 'age_group'); //reserved for future use
		$this->addAttributeMapping('', 'product_type'); //reserved for future use
		$this->addAttributeMapping('', 'availability'); //reserved for future use
		$this->addAttributeMapping('sale_price', 'sale_price',true,true);
		$this->addAttributeMapping('sale_price_effective_date', 'sale_price_effective_date',true,false);
		$this->addAttributeMapping('', 'condition'); //reserved for future use
		$this->addAttributeMapping('', 'shipping_weight'); //reserved for future use
		$this->addAttributeMapping('', 'colour',true,true);	
		$this->addAttributeMapping('', 'size',true,true);
		$this->addAttributeMapping('', 'fit'); //Slim fit	
		$this->addAttributeMapping('regular_price', 'price',true,true);		
		$this->addAttributeMapping('currency', 'currency',true,true);	
		$this->addAttributeMapping('stock_quantity', 'stock',true,true);
		$this->addAttributeMapping('', 'season'); //reserved for future use
		$this->addAttributeMapping('language', 'language',true,true);		

//List the product price in US dollars, without a $ sign, commas, text, or quotation marks.
		$this->addRule('price_rounding','pricerounding');
		$this->addRule( 'description', 'description',array('max_length=6500','strict') ); 
		//$this->addRule( 'csv_standard', 'CSVStandard',array('description') ); 
		//$this->addRule( 'csv_standard', 'CSVStandard',array('title','200') ); //200 title char limit
		$this->addRule( 'substr','substr', array('title','0','200',true) ); //200 length
	
	}

	function getFeedHeader($file_name, $file_path) 
	{
		//GraziaShop header line 1
		$output = implode(
			$this->fieldDelimiter, 
			array('version,1')
		) .  "\r\n";

		//GraziaShop header line 2
		foreach($this->attributeMappings as $thisMapping)
		{
			if ($thisMapping->enabled && !$thisMapping->deleted)
			{
				$output .= $thisMapping->mapTo . $this->fieldDelimiter;
			}
		}
	    return substr($output, 0, -1) .  "\r\n";
	}

	function getFeedFooter($file_name, $file_path) {
		//Override parent and do nothing
	}

	function formatProduct($product) {

		global $pfcore;

		$variantUPC = '';
		$variantMfr = '';
		if ($product->attributes['isVariation']) {
			//Not used in original code
			//$variantUPC = rand();
			//$variantMfr = rand();
		}

/*
* This is an essential requirement from Grazia and gives the store owner the ability to simply 
* select 'Yes' or 'No' at the product level.
* GraziaShop Business rule: setAttributeDefualt business-attribute as none PGraziaBusinessRule
* where business-attribute is the custom field containing 'yes' or 'no' (if the custom attribute has spaces, enclose it in double quotes)
*/

//additional image links 
		if ( $this->allow_additional_images && (count($product->imgurls) > 0) )
	 		$product->attributes['additional_image_links'] = implode(',', $product->imgurls); 
//sale price from/to dates
	 	//if (($product->attributes['has_sale_price']) {
		if (isset($product->attributes['sale_price_dates_from']) && isset($product->attributes['sale_price_dates_to'])) 
		{
			$product->attributes['sale_price_dates_from'] = $pfcore->localizedDate( 'Y-m-d H:i:s', $product->attributes['sale_price_dates_from'] );
			$product->attributes['sale_price_dates_to'] = $pfcore->localizedDate( 'Y-m-d H:i:s', $product->attributes['sale_price_dates_to'] );

			if ( strlen($product->attributes['sale_price_dates_from']) > 0 && strlen($product->attributes['sale_price_dates_to']) > 0 )
				$product->attributes['sale_price_effective_date'] = $product->attributes['sale_price_dates_from'].' '.$product->attributes['sale_price_dates_to'];
		}
//currency
		// if (!isset($product->attributes['currency']) || (strlen($product->attributes['currency']) == 0))
		//$product->attributes['currency'] = $this->currency;
//stock
		// if ( !isset($product->attributes['quantity']) )
		// 	$product->attributes['quantity'] = $product->attributes['stock_quantity'];

//langauge: EN, FR, IT
		$language = get_locale();
		if (strpos($language,'_') !== false) {
			$language= substr($language, 0, strpos($language, '_'));
			$product->attributes['language'] = strtoupper($language);
		}

		return parent::formatProduct($product);

	}//formatProduct	

}

?>