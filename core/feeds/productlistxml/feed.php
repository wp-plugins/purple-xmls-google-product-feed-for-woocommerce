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
		$this->providerName = 'Productlistxml';
		$this->providerNameL = 'productlistxml';
		parent::__construct();
	}
  
	function formatProduct($product) {

		//Cheat: These three fields aren't ready to be attributes yet, so adding manually:
		$product->attributes['description'] = $product->description;
		$product->attributes['current_category'] = $this->current_category;
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;

		//********************************************************************
		//Make sure all the fields for this product are mapped
		//********************************************************************
		foreach($product->attributes as $key => $value)
			if ($this->getMappingByMapto($key) == null)
				$this->addAttributeMapping($key, $key);

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

		//Images hard-coded for now
		$image_count = 0;
		foreach($product->imgurls as $imgurl) {
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