<?php

  /********************************************************************
  Version 2.1
    A Product List XML Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2014-11-27

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PKelkooFeed extends PBasicFeed {

	public $templateLoaded = false;

	function __construct () {
		parent::__construct();
		$this->forceCData = true;
		$this->providerName = 'Kelkoo';
		$this->providerNameL = 'kelkoo';

		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
	}
  	
  	function loadTemplate($template) {

		//need to use include rather than include_once
		include dirname(__FILE__) . '/templates/all.templates.php';
		
		//Load the individual templates
		$files = scandir(dirname(__FILE__) . '/templates');		
		foreach($files as $file)
			if ($file == $template.'.php')
				include_once dirname(__FILE__) . '/templates/' . $file;

		$this->templateLoaded = true;
		$this->loadAttributeUserMap();

  	}

  	function initializeFeed( $category, $remote_category ) 
	{		
		switch ($remote_category){
			case 'Any/Other':
				$template = '';
				break;
			case 'Books':
				$template = 'books';
				break;
			case 'Video games and software':
				$template = 'videogames.software';
				break;
			case 'Fashion and Fashion accessories':
				$template = 'fashion.accessories';
				break;
			case 'Mobilephones with subscription and Prepaid cards':
				$template = 'mobilephones';
				break;	
			case 'Movies':
				$template = 'movies';
				break;	
			case 'Music':
				$template = 'music';
				break;	
			case 'New and used cars':
				$template = 'new.usedcars';
				break;	
			case 'Property':
				$template = 'property';
				break;	
			case 'Wine and Champagne':
				$template = 'wine.champagne';
				break;	
			case 'Tyres':
				$template = 'tyres';
				break;				
			default:
				$template = '';
				break;
		}
		$this->loadTemplate($template);
	}

	//Not safe to assume continueFeed will exist next version.
	//Note: continueFeed() cannot safely call initializeFeed() so I've replicated it for now -KH
	protected function continueFeed($category, $file_name, $file_path, $remote_category) {
		switch ($remote_category){
			case 'Any/Other':
				$template = '';
				break;
			case 'Books':
				$template = 'books';
				break;
			case 'Video games and software':
				$template = 'videogames.software';
				break;
			case 'Fashion and Fashion accessories':
				$template = 'fashion.accessories';
				break;
			case 'Mobilephones with subscription and Prepaid cards':
				$template = 'mobilephones';
				break;	
			case 'Movies':
				$template = 'movies';
				break;	
			case 'Music':
				$template = 'music';
				break;	
			case 'New and used cars':
				$template = 'new.usedcars';
				break;	
			case 'Property':
				$template = 'property';
				break;	
			case 'Wine and Champagne':
				$template = 'wine.champagne';
				break;	
			case 'Tyres':
				$template = 'tyres';
				break;				
			default:
				$template = '';
				break;
		}
		$this->loadTemplate($template);
		parent::continueFeed($category, $file_name, $file_path, $remote_category);
	}

	function formatProduct($product) {

		#$product->attributes['currency'] = $this->currency;

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'In Stock';
		else
			$product->attributes['stock_status'] = 'Out of Stock';

		//Price
		// if (strlen($product->attributes['regular_price']) == 0)
		// 	$product->attributes['price'] = '0.00';
		// $product->attributes['price'] = $product->attributes['regular_price'];
		// if ($product->attributes['has_sale_price'])
		// 	$product->attributes['price'] = $product->attributes['sale_price'];

		//Images now soft-coded
		foreach($product->imgurls as $image_count => $imgurl) {
			$product->attributes['additional_image_link' . $image_count] = $imgurl;
			if ($image_count > 9)
				break;
		}

		//********************************************************************
		//Mapping 3.0 Pre-processing
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);
		
		$output = '
	<item>';

		//********************************************************************
		//Add attributes (Mapping 3.0)
		//********************************************************************

		foreach($this->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) )
				$output .= $this->formatLine($thisAttributeMapping->mapTo, $product->attributes[$thisAttributeMapping->attributeName], $thisAttributeMapping->usesCData);

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

  function getFeedFooter($file_name, $file_path) {   
    $output = '
 </items>';
	return $output;
  }

  function getFeedHeader($file_name, $file_path) {

    $output = '<?xml version="1.0" encoding="UTF-8" ?>
<items>';
	return $output;
  }

}