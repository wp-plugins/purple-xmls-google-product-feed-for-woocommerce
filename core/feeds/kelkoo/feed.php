<?php

  /********************************************************************
  Version 2.1
    A Product List XML Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2014-11-27

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PkelkooFeed extends PBasicFeed {

	public $templateLoaded = false;

	function __construct () {
		parent::__construct();
		$this->forceCData = true;
		$this->providerName = 'Kelkoo';
		$this->providerNameL = 'kelkoo';
	}
  	
  	function loadTemplate($template) {

		//$this->kelkoo_category = $template;
		include_once dirname(__FILE__) . '/templates/all.templates.php';
		
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
		$this->loadTemplate($remote_category);
	}

	function formatProduct($product) {

		$product->attributes['image-url'] = $product->attributes['feature_imgurl'];
		if ($product->attributes['stock_status'] == 1)
			$product->attributes['availability'] = 'In Stock';
		else
			$product->attributes['availability'] = 'Out of Stock';

		//Price
		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['price'] = '0.00';
		$product->attributes['price'] = $product->attributes['regular_price'];
		if ($product->attributes['has_sale_price'])
			$product->attributes['price'] = $product->attributes['sale_price'];

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

  function getFeedFooter() {   
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