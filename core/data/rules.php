<?php

	/********************************************************************
	Version 2
		Rules collect all the attribute adjustments that used to be all over the place
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-12
	********************************************************************/

class PFeedRule {

	public $enabled = true;
	public $name = '';
	public $order = 0; //200 ~ PActionBeforeFeed, therefore don't assign order > 299 (300 ~ PActionAfterFeed)
	public $parameters = array();
	public $parametersV = null; //Virtual parameters... or "Interpreted" params
	public $parent_feed = null; //points to feed provider that owns this rule
	public $value = '';

	function __destruct() {
		unset($this->parent_feed);
	}

	public function clearValue() {
		$this->value = '';
		$this->parametersV = $this->parameters;
	}

	public function initialize() {
		if (strlen($this->name) > 0 && $this->name[0] != '$')
			$this->name = '$' . $this->name;
	}

	public function makeUnique() {

		//Unique rule -> Only one rule with this name allowed to be enabled at a given time
		//Note that at the time of the makeUnique call (within initialize), this rule
		//(the caller) does not exist in the rules list
		foreach($this->parent_feed->rules as $rule)
			if ($rule->name == $this->name)
				$rule->enabled = false;

	}

	public function process($product) {
	}

	public function resolveVirtualParameters() {
		$busy = true;
		while ($busy) {
			$busy = false;
			foreach ($this->parametersV as &$param)
				if (strlen($param) > 0 && $param[0] == '$') {
					$rule = $this->parent_feed->getRuleByName($param);
					if ($rule != null) {
						$param = $rule->value;
						$busy = true;
					}
				}
		}
	}

}

//***********************************************************
//Concatenate
//***********************************************************

class PFeedRuleConcat extends PFeedRule {

	public function process($product) {
		$this->resolveVirtualParameters();
		foreach ($this->parametersV as $arg)
			if (isset($product->attributes[$arg]))
				$this->value .= $product->attributes[$arg];
			else
				$this->value .= $arg;
	}

}

//***********************************************************
//Description: Format & Length 
//***********************************************************

class PFeedRuleDescription extends PFeedRule {

	public $allow_empty_title = false;
	public $max_description_length = 10000;
	public $descriptionStrict = false;
	public $descriptionStrictReplacementChar = ' ';

	public function initialize() {

		parent::initialize();
		$this->makeUnique();
		$this->format = 0;

		foreach ($this->parameters as $param) {
			$cmd = explode('=', $param);
			$original_cmd = $cmd;
			foreach ($cmd as &$item)
				$item = trim($item);
			switch (strtolower($cmd[0])) {
				case 'long':
					$this->format = 1;
					break;
				case 'short':
					$this->format = 2;
					break;
				case 'length': case 'max_length': case 'maximum_length':
					$this->max_description_length = $cmd[1];
					break;
				case 'replacement_character':
					$this->descriptionStrictReplacementChar = $original_cmd[1];
					break;
				case 'strict':
					if (!isset($cmd[1]) || strtolower($cmd[1]) == 'true')
						$cmd[1] = true;
					else
						$cmd[1] = false;
					$this->descriptionStrict = $cmd[1];
					break;
				case 'allow_empty_title':
					if (!isset($cmd[1]) || strtolower($cmd[1]) == 'true')
						$cmd[1] = true;
					else
						$cmd[1] = false;
					$this->allow_empty_title = $cmd[1];
					break;
			}
		}
				
	}

	public function process($product) {

		switch ($this->format) {
			case 1: //Force Long
				$product->attributes['description'] = $product->description_long;
				break;
			case 2: //Force Short
				$product->attributes['description'] = $product->description_short;
				break;
			default:
				//By default pick short... if no short, pick long (original behaviour)
				if ( strlen ( $product->description_short ) == 0 ) 
					$product->attributes['description'] = $product->description_long;
				else
					$product->attributes['description'] = $product->description_short;		  
				break;
		}

		if (!$this->allow_empty_title)
			//If description is empty, use title
			if (strlen(trim($product->attributes['description']) ) == 0 )
				$product->attributes['description'] = $product->attributes['title'];

		if ($this->descriptionStrict) {
			$description = $product->attributes['description'];
			//I really should use preg_replace here one day
			//$product->description = preg_replace('/[^A-Za-z0-9\-]/', '', $product->description);
			for($i=0;$i<strlen($description);$i++) {
				if (($description[$i] < "\x20") || ($description[$i] > "\x7E")) {
					$description[$i] = $this->descriptionStrictReplacementChar;
				}
			}
			$product->attributes['description'] = $description;
		}

		if (strlen($product->attributes['description']) > $this->max_description_length) 
			$product->attributes['description'] = substr($product->attributes['description'], 0, $this->max_description_length);

	}

}

//***********************************************************
//Discount
//***********************************************************

class PFeedRuleDiscount extends PFeedRule {

	public $discount_amount = 0;
	public $discount_sale_amount = 0;
	public $discount_multiplier = 1.00;
	public $discount_sale_multiplier = 1.00;

	public function initialize() {

		/*
		Note: BEDMAS means multiplier stronger than additive value
		rule discount(5)						Take 5 dollars off
		rule discount(5, s)					Take 5 dollars off sale price (if sale given - if sale not given, do not apply discount)
		rule discount(0.95, *)			Take 95% of price (5% discount)
		rule discount(0.95, *, s)		Take 95% of sale price (5% discount)
		*/

		parent::initialize();

		foreach($this->parameters as $this_parameter)
			if (is_numeric($this_parameter)) {
				$number_value = $this_parameter;
				break;
			}

		if (in_array('*', $this->parameters)) {
			//multiplier. Default number_value -> 1.00
			if (!isset($number_value)) $number_value = 1;
			if (in_array('s', $this->parameters))
				$this->discount_sale_multiplier = $number_value;
			else
				$this->discount_multiplier = $number_value;
		} else {
			//Additive value
			if (!isset($number_value)) $number_value = 0;
			if (in_array('s', $this->parameters))
				$this->discount_sale = $number_value;
			else
				$this->discount_amount = $number_value;
		}

	}

	public function process($product) {

		//discount_multiplier should act on regular_price (used to be sale_price)
		if ($this->discount_amount > 0 || $this->discount_multiplier != 1) {
			$product->attributes['regular_price'] = $product->attributes['regular_price'] * $this->discount_multiplier - $this->discount_amount;
			//$product->attributes['has_sale_price'] = true;
		}
		//Possible to do sale as a function of sale price, but IFF product->has_sale_price already
		if (($this->discount_sale_amount > 0 || $this->discount_sale_multiplier != 1) && $product->attributes['has_sale_price']) {
			$product->attributes['sale_price'] = $product->attributes['sale_price'] * $this->discount_sale_multiplier - $this->discount_sale_amount;
			$product->attributes['has_sale_price'] = true;
		}
		//Disallow negative price
		if ($product->attributes['has_sale_price'] && $product->attributes['sale_price'] < 0)
			$product->attributes['sale_price'] = 0;

	}

}

//***********************************************************
//Google-Specific
//***********************************************************

class PFeedRuleGooglecombotitle extends PFeedRule {

	public function process($product) {

		if ( $this->parent_feed->google_combo_title ) {
			$title_dash = " - ";
			$title_combo = "";
			//Modify Title to include Brand - Title - Flavour (if exists) - Size (if exists)		
			$title_combo = $product->attributes['brand'].$title_dash.$product->attributes['title'];
			if ( !empty($product->attributes['flavour']) ) {
				$title_combo = $title_combo.$title_dash.$product->attributes['flavour'];
			}
			if ( !empty($product->attributes['size']) ) {
				$title_combo = $title_combo.$title_dash.$product->attributes['size'];
			}
			$product->attributes['title'] = $title_combo;
		}

	}

}

class PFeedRuleGoogleexacttitle extends PFeedRule {

	public function process($product) {

		//Upper case the first character of each word in the title:
		//Google doesn't like block letters
		if ( !$this->parent_feed->google_exact_title )
			$product->attributes['title'] = ucwords(strtolower( $product->attributes['title'] ));

	}

}

//***********************************************************
//Price
//***********************************************************

class PFeedRulePricestandard extends PFeedRule {

	public $unit = '';

	public function initialize() {

		parent::initialize();

		if (count($this->parameters) > 2)
			$this->unit = $this->parameters[1];

		$this->order = 200;

	}

	public function process($product) {

		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';
		$product->attributes['regular_price'] = sprintf($this->parent_feed->currency_format, $product->attributes['regular_price']) . $this->parent_feed->currency;
		$this->parent_feed->getMapping('sale_price')->enabled = $product->attributes['has_sale_price'];
		if ($product->attributes['has_sale_price'])
			$product->attributes['sale_price'] = sprintf($this->parent_feed->currency_format, $product->attributes['sale_price']) . $this->parent_feed->currency;

	}

}

//***********************************************************
//Shipping
//***********************************************************

class PFeedRuleShipping extends PFeedRule {

	public $shipping_amount = '0.00';
	public $shipping_multiplier = 0;
	public $shipping_sale_multiplier = 0;
	public $shipping_type = 'Ground';

	public function initialize() {

			/*
			Note: BEDMAS means multiplier stronger than additive value
			rule shipping(5)						Shipping cost is $5
			rule shipping(0.95, *)			Shipping is 95% of the full price
			rule shipping(0.95, *, s)		Shipping is 95% of the sale price
			rule shipping(grnd|air, t)	Type
			*/

		parent::initialize();

		foreach($this->parameters as $this_parameter)
			if (is_numeric($this_parameter)) {
				$number_value = $this_parameter;
				break;
			}

		if (in_array('*', $this->parameters)) {
			//multiplier. Default number_value -> 1.00
			if (!isset($number_value)) $number_value = 1;
			if (in_array('s', $this->parameters))
				$this->shipping_sale_multiplier = $number_value;
			else
				$this->shipping_multiplier = $number_value;
		} elseif (in_array('t', $this->parameters))
			$this->shipping_type = $this->parameters[0];
		else {
			//Additive value
			if (!isset($number_value)) $number_value = 0;
			$this->shipping_amount = $number_value;
		}

		if ($this->parent_feed->providerName == 'Google')
			$this->parent_feed->addAttributeDefault('shipping', 'none', 'PGoogleShipping');

	}

	public function process($product) {

		$product->attributes['shipping_amount'] = 
			$this->shipping_amount + 										//Base amount
			$product->attributes['regular_price'] * $this->shipping_multiplier + 		//% of Price
			($product->attributes['has_sale_price'] ? $product->attributes['sale_price'] * $this->shipping_sale_multiplier : 0);		//% of Sale Price
		$product->attributes['shipping_type'] = $this->shipping_type;

	}

}

//***********************************************************
//Status
//***********************************************************

class PFeedRuleStatusstandard extends PFeedRule {

	public function process($product) {

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'in stock';
		else
			$product->attributes['stock_status'] = 'out of stock';

	}

}

//***********************************************************
//Pos
//***********************************************************

class PFeedRulePos extends PFeedRule {

	public function process($product) {
	
		if (!isset($product->attributes[$this->parametersV[0]]))
			return;
		if (count($this->parametersV) < 2)
			return;

		$this->value = strpos($product->attributes[$this->parametersV[0]], $this->parametersV[1]);

	}

}

//***********************************************************
//Strlen
//***********************************************************

class PFeedRuleStrlen extends PFeedRule {

	public function process($product) {
	
		if (!isset($product->attributes[$this->parametersV[0]])) {
			$this->value = 0;
			return;
		}

		$this->value = strlen($product->attributes[$this->parametersV[0]]);

	}

}

//***********************************************************
//SubString
//***********************************************************

class PFeedRuleSubstr extends PFeedRule {

	public function process($product) {
		$this->resolveVirtualParameters();
		if (!isset($product->attributes[$this->parametersV[0]]))
			return;
		if (count($this->parametersV) == 2)
			$this->value = substr($product->attributes[$this->parametersV[0]], (int) $this->parametersV[1]);
		if (count($this->parametersV) == 3)
			$this->value = substr($product->attributes[$this->parametersV[0]], $this->parametersV[1], $this->parametersV[2]);

	}

}

//***********************************************************
//Tax
//***********************************************************

class PFeedRuleTax extends PFeedRule {

	public $rate = 0;

	public function initialize() {

		parent::initialize();

		foreach($this->parameters as $this_parameter)
			if (is_numeric($this_parameter)) {
				$this->rate = $this_parameter;
				break;
			}

		if ($this->parent_feed->providerName == 'Google')
			$this->parent_feed->addAttributeDefault('tax', 'none', 'PGoogleTax');
	}

	public function process($product) {

		if (!isset($product->attributes['tax']))
			$product->attributes['tax'] = $this->rate;
		if (!isset($product->attributes['tax_country']))
			$product->attributes['tax_country'] = 'US';

	}

}

//***********************************************************
//Weight Unit
//***********************************************************

class PFeedRuleWeightunit extends PFeedRule {

	public $unit = '';

	public function initialize() {

		parent::initialize();

		if (count($this->parameters) > 2)
			$this->unit = $this->parameters[1];

	}

	public function process($product) {

		if (!isset($product->attributes['weight']) || strlen($product->attributes['weight']) == 0 ) {
			$this->parent_feed->getMapping('weight')->enabled = false;
			return;
		}

		$this->parent_feed->getMapping('weight')->enabled = true;
		if (strlen($this->unit) > 0)
			$product->attributes['weight'] = $product->attributes['weight'] . ' ' . $this->unit;
		else
			$product->attributes['weight'] = $product->attributes['weight'] . ' ' . $this->parent_feed->weight_unit;

	}

}

?>