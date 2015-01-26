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
		//Required
		$this->addAttributeMapping('title', 'title', true,true);
		$this->addAttributeMapping('regular_price', 'price',false,true); //inc. VAT and in Euros
		$this->addAttributeMapping('link', 'product_url', true,true);
		$this->addAttributeMapping('url_image', 'url_image', true,true);	
		$this->addAttributeMapping('unique_code', 'unique_code', true,true);
		$this->addAttributeMapping('category', 'category', true,true);
		$this->addAttributeMapping('delivery_costs', 'delivery_costs', true,true);
		$this->addAttributeMapping('delivery_period', 'delivery_period', true,true);
		$this->addAttributeMapping('', 'ean', true,true);
		$this->addAttributeMapping('', 'brand', true,true);
		//optional
		$this->addAttributeMapping('sale_price', 'sale_price');
		$this->addAttributeMapping('model_code', 'model_code'); //item group id
		$this->addAttributeMapping('', 'original');		
		$this->addAttributeMapping('', 'mpn');
		$this->addAttributeMapping('', 'mpn_color', true);
		
		$this->addAttributeMapping('description', 'description', true);
		$this->addAttributeMapping('', 'colour');
		$this->addAttributeMapping('', 'size', true);
		//combo codes
		
		$this->addAttributeMapping('variant_code', 'variant_code', true);
	}
  
	function formatProduct( $product ) 
	{

		//kleur
		if ( isset($product->attributes['kleur']) )
			$product->attributes['colour'] = $product->attributes['kleur'];	
		if ( isset($product->attributes['sku']) )
			$product->attributes['mpn'] = $product->attributes['sku'];		
		//model_code is the item_group_id
		if (isset($product->attributes['item_group_id']))
			$cpf_attribute_itemgid = $product->attributes['item_group_id'];
		else
			$cpf_attribute_itemgid = '';
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
			if ( $thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && !empty($product->attributes[$thisAttributeMapping->attributeName]) ) {
				if ( $thisAttributeMapping->mapTo == 'colour' || $thisAttributeMapping->mapTo == 'color' )
						$cpf_attribute_color = $product->attributes[$thisAttributeMapping->attributeName];
				if ( $thisAttributeMapping->mapTo == 'size' )
					//if ( !empty($product->attributes[$thisAttributeMapping->attributeName]) )
						$cpf_attribute_size = $product->attributes[$thisAttributeMapping->attributeName];
				
			}

		$beslist_variant_code = $product->attributes['model_code'];
		$beslist_unique_code = $product->attributes['model_code'];
		if ( !empty($cpf_attribute_color) ) 
		{
			$beslist_variant_code .= '-'.$cpf_attribute_color;
			$beslist_unique_code .= '-'.$cpf_attribute_color;
		}
		if ( !empty($cpf_attribute_size) ) 
		{
			$beslist_unique_code .= '-'.$cpf_attribute_size;
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