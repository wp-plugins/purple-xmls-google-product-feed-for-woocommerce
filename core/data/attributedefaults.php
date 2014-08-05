<?php

  /********************************************************************
  Version 2.0
    Attribute defaults
		Can by value or expression
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/

//The base class occurs before item loads from database
//These values will be overwritten by db-values if found
//Only Title and ID exist at this moment
class PAttributeDefault {

		public $attributeName;
		public $enabled = true;
		public $stage = 0;
		public $value;

	public function getValue($item) {
		return $this->value;
	}

}

//Occurs after item loads from database
//Most attributes exist but calc fields like price do not
class PActionAfterLoad extends PAttributeDefault {

	public function __construct() {
		$this->stage = 1;
	}

}

//The feed is about to be generated. 
//all calc fields exist
class PActionBeforeFeed extends PAttributeDefault {

	public function __construct() {
		$this->stage = 2;
	}

}

//The feed has been generated
//Descendent may modify the feed output
class PActionAfterFeed extends PAttributeDefault {

	public function __construct() {
		$this->stage = 3;
	}

	public function postProcess($product, &$output) {
	}

}

class PFirstFoundColor extends PAttributeDefault {

	public $colorwords = array(
		'amber', 'amethyst', 'aqua', 'aquamarine', 'auburn', 'azure', 'beige', 'black', 'blue', 'bronze', 
		'brown', 'cerise', 'cerulean', 'charcoal', 'copper', 'coral', 'cream', 'crimson', 'crystal', 'cyan',
		'diamond', 'denim', 'ebony', 'ecru', 'emerald', 'fuchsia', 'gold', 'gray', 'green', 'grey', 'indigo', 
		'ivory', 'jade', 'jet', 'lavender', 'lemon', 'lilac', 'lime', 'magenta', 'mahogany', 'maroon', 'mauve', 
		'ocher', 'olive', 'orange', 'orchid', 'pastel', 'peach', 'peridot', 'periwinkle', 'persimmon', 'pearl', 
		'pewter', 'pink', 'purple', 'red', 'rhodium', 'rose', 'ruby', 'saffron', 'sapphire', 'scarlet', 'silver', 
		'tan', 'taupe', 'teal', 'topaz', 'turquoise', 'ultramarine', 'vermilion', 'violet', 'white', 'yellow');
 

	function firstColorWord($text) {
		$options = explode(' ', $text);
		foreach ($options as $option) {
			$searchterm = preg_replace("/[^a-zA-Z 0-9]+/", "", $option);
			if (in_array(strtolower($searchterm), $this->colorwords))
				return $searchterm;
		}
		return '';
	}
		/* Inaccurate:
		$textl = strtolower($text);
		foreach ($this->colorwords as $color)
			if (strpos($textl, $color) !== false)
				return $color;*/

	public function getValue($item) {

		$color = $this->firstColorWord($item->description_short);
		if (strlen($color) > 0) return $color;

		$color = $this->firstColorWord($item->description_long);
		if (strlen($color) > 0) return $color;

		$color = $this->firstColorWord($item->attributes['title']);
		if (strlen($color) > 0) return $color;

		return '';
	}

}

class PSalePriceIfDefined extends PActionBeforeFeed {

	public function getValue($item) {
		if ($item->attributes['has_sale_price'])
			return $item->attributes['sale_price'];
		else
			return $item->attributes['regular_price'];
	}

}

?>