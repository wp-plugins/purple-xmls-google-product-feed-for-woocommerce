<?php

	/********************************************************************
	Version 2.1
		A Product List TXT Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-11
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProductlisttxtFeed extends PCSVFeedEx {

	public $mapAttributesOnTheFly = true;

	function __construct () {
		parent::__construct();
		$this->providerName = 'Productlisttxt';
		$this->providerNameL = 'productlisttxt';
		$this->fileformat = 'txt';
		$this->stripHTML = true;

		$this->addAttributeMapping('description', 'description', true);
	}
  
	function formatProduct($product) {

		//Cheat: These three fields aren't ready to be attributes yet, so adding manually:
		$product->attributes['description'] = str_replace('"','""',$product->attributes['description']);

		//********************************************************************
		//Make sure all the fields for this product are mapped
		//********************************************************************
		if ($this->mapAttributesOnTheFly)
			foreach($product->attributes as $key => $value)
				if ($this->getMappingByMapto($key) == null)
					if ($key != 'category_ids')
						$this->addAttributeMapping($key, $key);

		return parent::formatProduct($product);

  }

}