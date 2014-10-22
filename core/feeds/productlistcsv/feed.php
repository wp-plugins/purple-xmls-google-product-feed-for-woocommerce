<?php

	/********************************************************************
	Version 2.1
		A Product List CSV Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-10
		2014-10 Moved to Attribute Mapping v3 (K)
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlistcsvFeed extends PCSVFeedEx {

	function __construct () {
		$this->providerName = 'Productlistcsv';
		$this->providerNameL = 'productlistcsv';
		$this->fileformat = 'csv';
		$this->descriptionStrict = true;
		$this->stripHTML = true;	
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

		return parent::formatProduct($product);

  }

  function getFeedHeader($file_name, $file_path) {
		return ''; //Skip header - We'll do it later
  }

  function getFeedFooter() {
		//Now we finally write the headers! Start by creating them
		$headers = array();
		foreach($this->attributeMappings as $thisMapping)
			if ($thisMapping->enabled && !$thisMapping->deleted)
				$headers[] = $thisMapping->mapTo;
		$headerString = implode($this->fieldDelimiter, $headers);

		$savedData = file_get_contents($this->filename);
		file_put_contents($this->filename, $headerString . "\r\n" .$savedData);

    return '';
  }

}