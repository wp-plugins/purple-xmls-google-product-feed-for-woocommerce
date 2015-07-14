<?php

	/********************************************************************
	Version 2.0
		FeedOverride is in charge of listing Attribute Mappings and similar activities
		So <price>7.00</price> can be overridden to <g:sale_price>7.00</g:sale_price>
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-15
		2014-09 stripped out CMS interface and moved it to ancestor class
	********************************************************************/

require_once dirname(__FILE__) . '/feedoverridesbase.php';

class PFeedOverride extends PBaseFeedOverride {

	function __construct($providerName, $parent, $saved_feed) {

		//Owner allows ancestor to communicate with parent
		$this->owner = $parent;
		$this->loadDropDownMappings($providerName);
		if (($saved_feed == null) || ($saved_feed->own_overrides != 1) )
			$this->loadAdvancedCommands($providerName);
		else
			$this->advancedCommands = explode("\n", $saved_feed->feed_overrides);

		$recent_attribute = null; //Allows setParam to work

		foreach($this->advancedCommands as $this_option) {

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
				$params = $this->ptokens($this_option);
				$command = strtolower($params[0]);
			}

			//Apply command
			switch ($command) {
				case '$': //Mapping 2.0 setting
					$this->interpretSingleSetting($this_option, $parent);
					break;
				case 'deleteattribute':
					//If the Attribute doesn't exist (yet), map it first.
					//This allows variable-attribute feeds to have attributes deleted since their attribute mappings might not yet be complete
					if ($params[1] == '*') {
						foreach($parent->attributeMappings as $thisAttributeMapping)
							$thisAttributeMapping->deleted = true;
						if (isset($parent->mapAttributesOnTheFly))
							$parent->mapAttributesOnTheFly = false;
					} else {
						if ($parent->getMapping($params[1]) == null)
							$parent->addAttributeMapping($params[1], $params[1]);
						//Delete it
						$parent->getMapping($params[1])->deleted = true;
					}
					break;
				case 'deletelicensekeys':
					$reg = new PLicense();
					$reg->unregisterAll();
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
					$usesCData = false;
					if (isset($params[4]) && (strtolower($params[4]) == 'true'))
						$usesCData = true;
					if (!isset($params[3]) || strlen($params[3]) == 0)
						$params[3] = $params[1];
					$recent_attribute = $parent->addAttributeMapping($params[1], $params[3], $usesCData, false, true);
					break;
				case 'rule': case 'addrule':
					//Rule name after last ")"
					$ruleName = '';
					for($i = count($params) - 3; $i > 0; $i--)
						if ($params[$i] == ')') {
							$ruleName = $params[$i + 2];
							break;
						}
					if (strtolower($ruleName) == 'substring')
						$ruleName = 'substr';
					//Rule Parameters is everything within ()
					$start = false;
					$ruleParams = array();
					foreach($params as $param) {
						if ($param == ')')
							break;
						if ($start)
							$ruleParams[] = $param;
						if ($param == '(')
							$start = true;
					}
					$parent->addRule($ruleName, $params[1], $ruleParams, 100);
					break;
				case 'set':
					//Eg: set forceCData to off
					if (count($params) > 3) {
						if (strtolower($params[3]) == 'false') $params[3] = false;
						if (strtolower($params[3]) == 'true') $params[3] = true;
						if (strtolower($params[3]) == 'no') $params[3] = false;
						if (strtolower($params[3]) == 'yes') $params[3] = true;
						if (strtolower($params[3]) == 'off') $params[3] = false;
						if (strtolower($params[3]) == 'on') $params[3] = true;
						if ($this->validIdentifier($params[1]))
							$parent->$params[1] = $params[3];
					}
					break;
				case 'maptaxonomy':
					//Example: mapTaxonomy product_brand to brand
					$parent->relatedData[] = array($params[1], $params[3]);
					break;
				case 'setattribute':
					if (strtolower($params[2]) == 'mapto')
						if ($parent->getMapping($params[1]) != null) {
							$recent_attribute = $parent->getMapping($params[1]);
							$recent_attribute->mapTo = $params[3];
						}
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
				case 'setattributeparam':
					//For example, allows AmazonSC to set the localized name after mapAttribute [This equals setParam but allows you to specify which mapping]
					//eg: setAttributeParam description usesCData true
					$recent_attribute = $parent->getMapping($params[1]);
					if ($recent_attribute != null)
						$recent_attribute->$params[2] = $params[3];
					break;
				case 'setlicensekey':
					$reg = new PLicense();
					$reg->setLicenseKey($params[1]);
					break;
				case 'setparam':
					//For example, allows AmazonSC to set the localized name after mapAttribute
					//eg setParam localized_name "A local name"
					if ($recent_attribute != null)
						$recent_attribute->$params[1] = $params[2];
					break;
				case 'setrapidcarttoken':
					global $pfcore;
					$pfcore->settingSet('cp_rapidcarttoken', $params[1]);
					break;
				default: //Mapping 2.0 override
					$this->interpretOverride($this_option);
			}

		}

	}

	/*//determine if value should be overridden. No! This caused ghost attributes
	public function exists($value) {
		$result = false;
		foreach($this->overrides as $a)
			if ($a == $value) {
				$result = true;
				break;
			}
		return $result;
	}*/

	function interpretSingleSetting($this_option, $parent) {

		global $pfcore;

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
		if ($this_option == '$default_brand') {$parent->addErrorMessage(11003, 'Use of deprecated command: $default_brand', true);}
		if ($this_option == '$exclude_variable_attributes') {$parent->productList->exclude_variable_attributes = true;}
		if ($this_option == '$field_delimiter') {$parent->fieldDelimiter = $value;}
		if ($this_option == '$get_wc_shipping_attributes') {$parent->get_wc_shipping_attributes = true;}		
		if ($this_option == '$hide_out_of_stock') {$pfcore->hide_outofstock = true;}
		if ($this_option == '$ignore_duplicates') {$parent->ignoreDuplicates = true;}
		if ($this_option == '$max_description_length') {$parent->addErrorMessage(11001, 'Use of deprecated command: $max_description_length', true);}
		//if ($this_option == '$max_custom_field') {$parent->max_custom_field = $value;} //max_custom_field not used in recent versions
		if ($this_option == '$strip_html_markup') {$parent->stripHTML = true;}
		if ($this_option == '$system_wide_shipping_type') {$parent->addErrorMessage(11001, 'Use of deprecated command: $system_wide_shipping_type', true);} 
		if ($this_option == '$timeout') {$parent->timeout = $value;}
		if ($this_option == '$weight_unit') {$parent->weight_unit = $value;}

		if ($this_option == '$basic_feed') {
			//Minimal settings across the board
			$parent->max_custom_field = 0; //no custom fields
			$parent->allowRelatedData = false; //hunt for brand/tag/other
			$parent->allow_attributes = false; //disables even woo attributes
			$parent->allow_attribute_details = false; //disable old style attribute detection
		}

		if ($this_option == '$descriptions') {
			$parent->addErrorMessage(11001, 'Use of deprecated command: $descriptions', true);
		}
		if ($this_option == '$description') {
			$parent->addErrorMessage(11001, 'Use of deprecated command: $description', true);
		}
		if ($this_option == '$google_merchant_center') {
			$parent->gmc_enabled = true;
			if (strlen($value) > 0)
				$parent->gmc_attributes[] = $value;
		}
		if ($this_option == '$strict_description') {
			//Complain about deprecated function
			$parent->addErrorMessage(11000, 'Use of deprecated command: $strict_description', true);
		}
	
		//***********************************************************
		// System-Wide Discount
		//***********************************************************

		if ($this_option == '$discount') {
			//Complain about deprecated function
			$parent->addErrorMessage(11002, 'Use of deprecated command: $discount', true);
		}

		//Deprecated shipping code. Migrate people away from this
		if ($this_option == '$system_wide_shipping') {
			//Complain about deprecated function
			$parent->addErrorMessage(11004, 'Use of deprecated command: $system_wide_shipping', true);
		}

		if ($this_option == '$shipping') {
			//Complain about deprecated function
			$parent->addErrorMessage(11004, 'Use of deprecated command: $shipping', true);
		}

		if ($this_option == '$system_wide_tax') {
			//Complain about deprecated function
			$parent->addErrorMessage(11005, 'Use of deprecated command: $system_wide_tax', true);
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

		//NewEgg shipping: default or free
		if ($this_option == '$newegg_shipping')
			$parent->newegg_shipping = $value;

		//amazon leadtime
		if ($this_option == '$leadtime_to_ship')
			$parent->leadtime_to_ship = $value;

		//eBay business policies
		if ($this_option == '$payment_name')
			$parent->payment_name = $value;
		if ($this_option == '$shipping_name')
			$parent->shipping_name = $value;
		if ($this_option == '$return_name')
			$parent->return_name = $value;

		//Become delivery time
		if ($this_option == '$delivery-charge')
			$parent->delivery_charge = $value;
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