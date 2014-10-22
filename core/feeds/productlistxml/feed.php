<?php

  /********************************************************************
  Version 2.1
    A Product List XML Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-09

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlistxmlFeed extends PBasicFeed{

	function __construct () {
		parent::__construct();
		$this->forceCData = true;
		$this->providerName = 'Productlistxml';
		$this->providerNameL = 'productlistxml';
	}
  
	function formatProduct($product) {

		//Cheat: These three fields aren't ready to be attributes yet, so adding manually:
		$product->attributes['description'] = $product->description;
		$product->attributes['current_category'] = $this->current_category;
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;

		//Price
		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';
		$product->attributes['regular_price'] = sprintf($this->currency_format, $product->attributes['regular_price']) . $this->currency;
		$sale_price = $this->getMapping('sale_price');
		if ($sale_price != null)
			$sale_price->enabled = $product->attributes['has_sale_price'];
		if ($product->attributes['has_sale_price'])
			$product->attributes['sale_price'] = sprintf($this->currency_format, $product->attributes['sale_price']) . $this->currency;

		//Images now soft-coded
		foreach($product->imgurls as $image_count => $imgurl) {
			$product->attributes['additional_image_link' . $image_count] = $imgurl;
			if ($image_count > 9)
				break;
		}

		//********************************************************************
		//Make sure all the fields for this product are mapped
		//********************************************************************
		foreach($product->attributes as $key => $value)
			if ($this->getMappingByMapto($key) == null)
				$this->addAttributeMapping($key, $key, $this->forceCData);

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