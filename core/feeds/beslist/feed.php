<?php

  /********************************************************************
  Version 2.1
    A Product List XML Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
		2014-09 Retired Attribute mapping v2

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PBeslistFeed extends PBasicFeed
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'Beslist';
		$this->providerNameL = 'beslist';		
		//Create some default attributes (Mapping 3.0)
		$this->addAttributeMapping('title', 'title', true);
		$this->addAttributeMapping('regular_price', 'regular_price'); //inc. VAT and in Euros
		$this->addAttributeMapping('sale_price', 'sale_price');
		$this->addAttributeMapping('link', 'product_url', true);
		$this->addAttributeMapping('url_image', 'url_image', true);		

		$this->addAttributeMapping('model_code', 'model_code'); //item group id
		$this->addAttributeMapping('category', 'category', true);
		$this->addAttributeMapping('delivery_period', 'delivery_period', true);
		$this->addAttributeMapping('mpn', 'mpn');
		$this->addAttributeMapping('EAN', 'ean', true);
		$this->addAttributeMapping('brand', 'brand', true);
		$this->addAttributeMapping('mpn_color', 'mpn_color', true);
		//optional
		$this->addAttributeMapping('description', 'description', true);
		$this->addAttributeMapping('colour', 'colour');
		$this->addAttributeMapping('size', 'size');
		//combo codes
		$this->addAttributeMapping('unique_code', 'unique_code');
		$this->addAttributeMapping('variant_code', 'variant_code');
	}
  
	function formatProduct( $product ) 
	{
		//Cheat: These three fields aren't ready to be attributes yet, so adding manually:
		$product->attributes['description'] = $product->description;
		$product->attributes['category'] = $this->current_category;
		$product->attributes['url_image'] = $product->feature_imgurl;
		//kleur
		$product->attributes['colour'] = $product->attributes['kleur'];	
		$product->attributes['mpn'] = $product->attributes['sku'];		
		//model_code is the item_group_id
		$cpf_attribute_itemgid = $product->attributes['item_group_id'];
		if  ( $cpf_attribute_itemgid == '' )		
			$product->attributes['model_code'] = $product->attributes['id'];
		else
			$product->attributes['model_code'] = $cpf_attribute_itemgid; 
		
	
		//********************************************************************
		//Mapping 3.0 Pre-processing
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);
			
		
		 $output = '
		 <item>';
		
		//Beslist combo codes: variant_code and unique_code. Eventually softcode to allow atribute combinations
		foreach( $this->attributeMappings as $thisAttributeMapping )
			if ( $thisAttributeMapping->enabled && !$thisAttributeMapping->deleted ) {
				if ( $thisAttributeMapping->mapTo == 'size' )
					$product->attributes['size1'] = $product->attributes[$thisAttributeMapping->attributeName];
				if ( $thisAttributeMapping->mapTo == 'colour' || $thisAttributeMapping->mapTo == 'color' )
					$product->attributes['color1'] = $product->attributes[$thisAttributeMapping->attributeName];
			}

		$beslist_variant_code = $product->attributes['model_code'];
		$beslist_unique_code = $product->attributes['model_code'];
		if ( !empty($product->attributes['color1']) ) 
		{
			$beslist_variant_code .= '-'.$product->attributes['color1'];
			$beslist_unique_code .= '-'.$product->attributes['color1'];
		}
		if ( !empty($product->attributes['size1']) ) 
		{
			$beslist_unique_code .= '-'.$product->attributes['size1'];
		}
		$product->attributes['variant_code'] = $beslist_variant_code;
		$product->attributes['unique_code'] = $beslist_unique_code;

		//********************************************************************
		//Add attributes (Mapping 3.0)
		//********************************************************************

		foreach($this->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) )
				$output .= $this->formatLine($thisAttributeMapping->mapTo, $product->attributes[$thisAttributeMapping->attributeName], $thisAttributeMapping->usesCData);

		//Images hard-coded for now
		$image_count = 0;
		foreach($product->imgurls as $imgurl) 
		{
			$output .= $this->formatLine('additional_image_link', $imgurl, true);
			$image_count++;
			if ($image_count > 9)
				break;
		}
		
		//********************************************************************
		//Mapping 3.0 post processing
		//********************************************************************

		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 3)
				$thisDefault->postProcess($product, $output);

	    $output .= '
	    </item>';
	    return $output;
	  }

  function getFeedFooter( ) 
  {   
    $output = '
    		  </items>';
	return $output;
  }

  function getFeedHeader( $file_name, $file_path ) 
  {
    $output = '<?xml version="1.0" encoding="UTF-8" ?>
    		   <items>';
	return $output;
  }
}