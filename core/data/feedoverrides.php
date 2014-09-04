<?php

  /********************************************************************
  Version 2.0
    FeedOverride is in charge of listing Attribute Mappings and similar activities
	So <price>7.00</price> can be overridden to <g:sale_price>7.00</g:sale_price>
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-15
  Note: FeedOverrides is also a kind of "FeedDefaults" and a "FeedAdjustments" too
    This name is getting a little overloaded and might be better organized as three
	separate classes.

  ********************************************************************/

function ptokens($source) {

	//Old: $items = explode(' ' , $source); (Couldn't account for quotes)
	$items = array();
	$index = 0;
	$used_so_far = 0;
	$this_token = '';
	while ($used_so_far < strlen($source)) {
		if ($source[$used_so_far] == ' ') {
			$items[$index] = $this_token;
			$this_token = '';
			$index++;
		} elseif ($source[$used_so_far] == '"') {
			$used_so_far++;
			while (($used_so_far < strlen($source)) && ($source[$used_so_far] != '"')) {
				$this_token .= $source[$used_so_far];
				$used_so_far++;
			}
		} else
			$this_token .= $source[$used_so_far];
		$used_so_far++;
	}
	$items[$index] = $this_token;

	return $items;

}

class PFeedOverride {

	public $overrides = array();

	function __construct($providerName, $parent, $saved_feed) {

		if (($saved_feed == null) || ($saved_feed->own_overrides != 1) ) {
			global $pfcore;
			$loadOverrides = 'loadOverrides' . $pfcore->callSuffix;
			$this->$loadOverrides($providerName);
		} else {
			$this->loadedOptions = explode("\n", $saved_feed->feed_overrides);
		}

		foreach($this->loadedOptions as $this_option) {

			//$xyz 			means single setting true or = something
			//x = y			means custom mapping (my field to given attribute)
			//x = $y			means custom mapping (my field to fixed value y)
			$this_option = trim($this_option);
			if (strlen($this_option) == 0) 
				continue;

			if ($this_option[0] == ';') continue; //is a comment
			if ($this_option[0] == '#') continue; //is a comment
			if (($this_option[0] == '/') && ($this_option[1] == '/')) continue; //is a comment

			//Try to find a command if there is one
			$command = '';
			if (substr($this_option, 0, 1) == '$')
				$command = '$';
			else {
				$params = ptokens($this_option);
				$command = strtolower($params[0]);
			}

			//Apply command
			switch ($command) {
				case '$': //Mapping 2.0 setting
					$this->interpretSingleSetting($this_option, $parent);
					break;
				case 'deleteattribute':
					if ($parent->getMapping($params[1]) != null)
						$parent->getMapping($params[1])->deleted = true;
					break;
				case 'limitoutput':
					if ((strtolower($params[1]) == 'from') && isset($params[4])) {
						$parent->has_product_range = true;
						$parent->product_limit_low = $params[2];
						$parent->product_limit_high = $params[4];
					} elseif ((strtolower($params[1]) == 'to') && isset($params[2])) {
						$parent->has_product_range = true;
						$parent->product_limit_low = 0;
						$parent->product_limit_high = $params[2];
					}
					break;
				case 'mapattribute':
					$parent->addAttributeMapping($params[1], $params[3]);
					break;
				case 'setattribute':
					if (strtolower($params[2]) == 'mapto')
						if ($parent->getMapping($params[1]) != null)
							$parent->getMapping($params[1])->mapTo = $params[3];
					if (strtolower($params[2]) == 'default') {
							if (isset($params[4]))
								$defaultClass = $params[4];
							else
								$defaultClass = 'PAttributeDefault';
							$parent->addAttributeDefault($params[1], $params[3], $defaultClass); //This option is the same as setattributedefault
						}
					break;
				case 'setattributedefault':
					if (isset($params[4]))
						$defaultClass = $params[4];
					else
						$defaultClass = 'PAttributeDefault';
					$parent->addAttributeDefault($params[1], $params[3], $defaultClass);
					break;
				default: //Mapping 2.0 override
					$this->interpretOverride($this_option);
			}

		}

	}

	private function loadOverridesJ($providerName) {

		$db = JFactory::getDBO();

		//Attribute Mappings
		$sql = "
			SELECT name, value FROM #__cartproductfeed_options
			WHERE name LIKE '" . $providerName . "_cp_%'";
		$db->setQuery($sql);
		$db->query();
		$overrides_from_options = $db->loadObjectList();
		foreach($overrides_from_options as $this_option) {
			$key = substr($this_option->name, strlen($providerName . '_cp_'));
			$this->overrides[$key] = $this_option->value;
		}

		//Advanced options
		$sql = "
			SELECT value FROM #__cartproductfeed_options
			WHERE name = '" . $providerName . "-cart-product-settings'";
		$db->setQuery($sql);
		$db->query();
		$loadedOptions = $db->loadResult();
		if (strlen($loadedOptions) > 0)
			$this->loadedOptions = explode("\n", $loadedOptions);
		else
			$this->loadedOptions = array();

	}
  
	private function loadOverridesW($providerName) {

		global $wpdb;

		//Attribute Mappings
		$sql = "
			SELECT * FROM $wpdb->options
			WHERE $wpdb->options.option_name LIKE '" . $providerName . "_cp_%'";
		$overrides_from_options = $wpdb->get_results($sql);
		foreach($overrides_from_options as $this_option) {
			$key = substr($this_option->option_name, strlen($providerName . '_cp_'));
			$this->overrides[$key] = $this_option->option_value;
		}

		//Advanced options
		$this->loadedOptions = explode("\n", get_option($providerName . '-cart-product-settings'));

	}

	private function loadOverridesWe($providerName) {
		$this->loadOverridesW($providerName);
	}

	//determine if value should be overridden. No! This caused ghost attributes
	public function exists($value) {
		$result = false;
		foreach($this->overrides as $a)
			if ($a == $value) {
				$result = true;
				break;
			}
		return $result;
	}

	function interpretSingleSetting($this_option, $parent) {

		$valueIndex = strpos($this_option, '=');
		if ($valueIndex === false) {
			$value = '';
			$this_option = trim($this_option);
		} else {
			$value = trim(substr($this_option, $valueIndex + 1));
			$this_option = trim(substr($this_option, 0, $valueIndex - 1));
		}

		//ignore comments
		if (substr($this_option, 0, 1) == ';') {return;}
		if (substr($this_option, 0, 2) == '//') {return;}
		$this_option = strtolower($this_option);
		//Some thought was given to allowing "$parent->$this_option = $value"
		//but that looks like security trouble. A chain of if-statements more secure

		if ($this_option == '$currency') {$parent->currency = $value;} //Deprecated. Use $currency_format
		if ($this_option == '$currency_shipping') {$parent->currency_shipping = $value;}
		//if ($this_option == '$bing_force_google_category') {$parent->bingForceGoogleCategory = true;} //Not IMPL
		if ($this_option == '$bing_force_price_discount') {$parent->bingForcePriceDiscount = true;} //Debug for beta testers
		if ($this_option == '$currency_format') {$parent->currency_format = $value;}
		if ($this_option == '$default_brand') {$parent->default_brand = $value;}
		if ($this_option == '$exclude_variable_attributes') {$parent->productList->exclude_variable_attributes = true;}
		if ($this_option == '$field_delimiter') {$parent->fieldDelimiter = $value;}
		if ($this_option == '$ignore_duplicates') {$parent->ignoreDuplicates = true;}
		if ($this_option == '$max_description_length') {$parent->max_description_length = $value;}
		if ($this_option == '$max_custom_field') {$parent->max_custom_field = $value;}
		if ($this_option == '$productTypeFromLocalCategory') {$parent->productTypeFromLocalCategory = true;}
		if ($this_option == '$productTypeFromWooCommerceCategory') {$parent->productTypeFromLocalCategory = true;} //Deprecated
		if ($this_option == '$strip_html_markup') {$parent->stripHTML = true;}
		if ($this_option == '$system_wide_shipping_type') {$parent->system_wide_shipping_type = $value;}  //Deprecated. Use $shipping
		if ($this_option == '$timeout') {$parent->timeout = $value;}
		if ($this_option == '$weight_unit') {$parent->weight_unit = $value;}

		if ($this_option == '$descriptions') {
			if ($value == 'long') {$parent->descriptionFormat = 1;}
			if ($value == 'short') {$parent->descriptionFormat = 2;}
		}
		if ($this_option == '$google_merchant_center') {
			$parent->gmc_enabled = true;
			if (strlen($value) > 0)
				$parent->gmc_attributes[] = $value;
		}
		if ($this_option == '$strict_description') {
			$parent->descriptionStrict = true;
			if (strlen($value) > 0)
				$parent->descriptionStrictReplacementChar = $value;
		}
	
		//***********************************************************
		// System-Wide Discount
		//***********************************************************

		if ($this_option == '$discount') {
			/*
			Note: the spaces for the explode function to work
			Note: BEDMAS means multiplier stronger than additive value
			$discount = 5			Take 5 dollars off
			$discount = 5 s			Take 5 dollars off sale price (if sale given - if sale not given, do not apply discount)
			$discount = 0.95 *		Take 95% of price (5% discount)
			$discount = 0.95 * s	Take 95% of sale price (5% discount)
			*/
			$parent->discount = true;
			$discount_parameters = explode(' ', $value);
			//Look for the number
			foreach($discount_parameters as $this_parameter)
				if (is_numeric($this_parameter)) {
					$number_value = $this_parameter;
					break;
				}
			if (in_array('*', $discount_parameters)) {
				//multiplier. Default number_value -> 1.00
				if (!isset($number_value)) $number_value = 1;
				if (in_array('s', $discount_parameters))
					$parent->discount_sale_multiplier = $number_value;
				else
					$parent->discount_multiplier = $number_value;
			} else {
				//Additive value
				if (!isset($number_value)) $number_value = 0;
				if (in_array('s', $discount_parameters))
					$parent->discount_sale = $number_value;
				else
					$parent->discount_amount = $number_value;
			}
		}

		//***********************************************************
		// System-Wide Shipping - Legacy
		//***********************************************************

		//Deprecated shipping code. Migrate people away from this
		if ($this_option == '$system_wide_shipping') {
			$parent->system_wide_shipping = true;
			$parent->system_wide_shipping_rate = $value;
			if (($value == 'false') || ($value == 'off') || ($value == 'no'))
				$parent->system_wide_shipping = false;
		}

		//***********************************************************
		//System-Wide Shipping
		//***********************************************************/

		if ($this_option == '$shipping') {
			/*
			Note: the spaces for the explode function to work
			Note: BEDMAS means multiplier stronger than additive value
			$shipping = off|false	Shipping cost is forced off
			$shipping = 5			Shipping cost is $5
			$shipping = 0.95 *		Shipping is 95% of the full price
			$shipping = 0.95 * s	Shipping is 95% of the sale price
			$shipping = grnd|air t	Type
			*/
			$parameters = explode(' ', $value);
			//Look for the number
			foreach($parameters as $this_parameter)
				if (is_numeric($this_parameter)) {
					$number_value = $this_parameter;
					break;
				}
			if (in_array('*', $parameters)) {
				//multiplier. Default number_value -> 1.00
				if (!isset($number_value)) $number_value = 1;
				if (in_array('s', $parameters))
					$parent->shipping_sale_multiplier = $number_value;
				else
					$parent->shipping_multiplier = $number_value;
			} elseif (in_array('t', $parameters))
				$parent->system_wide_shipping_type = $parameters[0];
			else {
				//Additive value
				if (!isset($number_value)) $number_value = 0;
				$parent->shipping_amount = $number_value;
			}

			//Toggle Shipping on/off
			$parent->system_wide_shipping = true;
			if (($value == 'false') || ($value == 'off') || ($value == 'no'))
			$parent->system_wide_shipping = false;
		}

		if ($this_option == '$system_wide_tax') {
			$parent->system_wide_tax = true;
			$parent->system_wide_tax_rate = $value;
		}

		//Trick to block additional_images. This may come in handy if the user is getting errors
		//from their images and it's too much effort to change them all
		if ($this_option == '$allow_additional_images')
			if (($value == 'false') || ($value == 'off') || ($value == 'no'))
				$parent->allow_additional_images = false;

		//Rakuten Feeds need seller-id
		//Note: $parent->seller_id is unset by default (not even null)
		if ($this_option == '$seller-id')
			$parent->seller_id = $value;

		//ShareASale Feeds need merchant, merchant-id
		if ($this_option == '$merchant-id')
			$parent->merchant_id = $value;
		if ($this_option == '$merchant')
			$parent->merchant = $value;

		//AmmoSeek needs retailer_name
		if ($this_option == '$retailer_name')
			$parent->retailer_name = $value;

	}

	function interpretOverride($this_option) {
		$valueIndex = strpos($this_option, '=');
		if ($valueIndex === false) {
			$value = '';
			$this_option = trim($this_option);
		} else {
			$value = trim(substr($this_option, $valueIndex + 1));
			$this_option = trim(substr($this_option, 0, $valueIndex - 1));
		}
		$this->overrides[$this_option] = $value;
	}

}