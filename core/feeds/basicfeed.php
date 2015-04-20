<?php

	/********************************************************************
  Version 2.0
    A Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../data/rules.php';
 
class PBasicFeed {

	public $activityLogger = null; //If set, someone wants me to log what phase I'm at in feed-generation';
	public $aggregateProviders = array();
	public $allow_additional_images = true;
	public $allow_attributes = true;
	public $allow_attribute_details = false; //old style attribute detection = source of minor hitches
	public $allow_variationPermutations = false;
	public $allowRelatedData = true;
	public $attributeAssignments = array();
	public $attributeDefaults = array();
	public $attributeDefaultStages = array(0, 0, 0, 0, 0, 0);
	public $attribute_granularity = 5; //0=basic feed(future) 1..4=minimal_postmeta_conversion 5=all_postmeta
	public $attributeMappings = array();
	public $auto_free = true; //Allow descendants to retain productlist
	public $auto_update_feedlist = true;
	public $categories;
	public $create_attribute_slugs = false;
	public $current_category; //This is the active category while in formatProduct()
	public $currency;
	public $currency_shipping = ''; //(Currently uses currency_format)
	public $currency_format = '%1.2f';
	public $dimension_unit;
	public $errors = array();
	public $fileformat = 'xml';
	public $fieldDelimiter = "\t"; //For CSVs
	public $fields; //For CSVs
	public $feed_category;
	public $feedOverrides;
	public $forceCData = false; //Applies to ProductListXML only
	public $force_all_categories = false;
	public $force_currency = false;
	public $force_wc_api = false;
	public $get_wc_shipping_attributes = false;
	public $get_wc_shipping_class = false;
	public $get_tax_rates = false;
	public $gmc_enabled = false; //Allow Google merchant centre woothemes extension (WordPress)
	public $gmc_attributes = array(); //If anything added in here, restrict GMC list to these
	public $has_header = true;
	public $has_footer = true;
	public $has_product_range = false;
	public $ignoreDuplicates = true; //useful when products are assigned multiple categories and insufficient identifiers to distinguish them
	public $max_description_length = 10000;
	public $max_custom_field = 50000;
	public $message = ''; //For Error detection
	public $providerName = '';
	public $providerNameL = '';
	public $productCount = 0; //Number of products successfully exported
	public $productList;
	public $productTypeFromLocalCategory = false;
	public $providerType = 0;
	public $relatedData = array();
	public $rules = array();
	//public $sellerName = ''; //Required Bing attribute - Merchant/Store that provides this product
	public $success = false;
	public $stripHTML = false;
	public $updateObject = null;
	public $utf8encode = false; //Temporary until a better encoding system can be engineered
	public $timeout = 0; //If >0 try to override max_execution time
	public $weight_unit;

	public function addAttributeDefault($attributeName, $value, $defaultClass = 'PAttributeDefault') {
		if (!class_exists($defaultClass)) {
			$this->addErrorMessage(5, 'AttributeDefault class "' . $defaultClass . '" not found. Reconfigure Advanced Commands to resolve.');
			return;
		}
		$thisDefault = new $defaultClass();
		//$thisDefault = new PAttributeDefault();
		$thisDefault->attributeName = $attributeName;
		$thisDefault->value = $value;
		$thisDefault->parent_feed = $this;
		$tvalue = trim($value);
		if (strlen($tvalue) > 0 && $tvalue[0] == '$') {
			$thisDefault->value = trim($thisDefault->value);
			$thisDefault->isRuled = true;
		}
		$this->attributeDefaults[] = $thisDefault;
		$this->attributeDefaultStages[$thisDefault->stage] += 1;
		$thisDefault->initialize();
		return $thisDefault;
	}

	public function addAttributeMapping($attributeName, $mapTo, $usesCData = false, $isRequired = false, $isMapped = false) {

		$thisMapping = new stdClass();
		$thisMapping->attributeName = $attributeName;
		$thisMapping->mapTo = $mapTo;
		$thisMapping->enabled = true;
		$thisMapping->deleted = false;
		$thisMapping->usesCData = $usesCData;
		$thisMapping->isRequired = $isRequired;
		$thisMapping->systemDefined = false;
		$thisMapping->isMapped = $isMapped;
		$this->attributeMappings[] = $thisMapping;

		//Auto-delete any system defined Mappings with matching mapTo
		foreach($this->attributeMappings as $mapping)
			if ($mapping->systemDefined && $mapping->mapTo == $mapTo)
				$mapping->deleted = true;

		return $thisMapping;
	}

	public function addErrorMessage($id, $msg, $isWarning = false) {

		//Allows descendent providers to report errors
		if (!isset($this->errors[$id])) {
			$error = new stdClass();
			$error->msg = $msg;
			$error->occurrences = 0;
			$error->isWarning = $isWarning;
			$this->errors[$id] = $error;
		}
		$this->errors[$id]->occurrences++;

	}

	public function addRule($ruleName, $ruleClass, $parameters = array(), $order = 0 ) {
		$className = 'PFeedRule' . ucwords(strtolower($ruleClass));
		if (!class_exists($className)) {
			$this->addErrorMessage(5, 'Rule "' . $ruleClass . '" not found. Reconfigure Advanced Commands to resolve.');
			return null;
		}
		$thisRule = new $className();
		$thisRule->name = $ruleName;
		$thisRule->parameters = $parameters;
		$thisRule->parent_feed = $this;
		$thisRule->order = $order;
		$thisRule->initialize();
		$this->rules[] = $thisRule;
		return $thisRule;
	}
 
	function checkFolders() {

		global $message;

		$dir = PFeedFolder::uploadRoot();
		if (!is_writable($dir)) {
			$message = $dir . ' should be writeable';
			return false;
		}

		$dir = PFeedFolder::uploadFolder();
		if (!is_dir($dir))
			mkdir($dir);
		if (!is_writable($dir)) {
			$message = "$dir should be writeable";
			return false;
		}
		$dir2 = $dir . $this->providerName . '/';
		if (!is_dir($dir2)) 
			mkdir($dir2);

		return true;
	}

	protected function containsNonUTF8Character($text) {
		for($i=0; $i<strlen($text); $i++)
			//if ($text[$i] > "\xFF")
			if (ord($text[$i]) > 224)
				return true;
		return false;
	}

	protected function continueFeed($category, $file_name, $file_path, $remote_category) {
		//Note: protected function because it will be deleted in some future version -KH
		$mode = "a";
		if ($this->updateObject->startValue == 0)
			$mode = "w";
		$this->fileHandle = fopen($this->filename, $mode);
		if ($this->has_header && $this->updateObject->startValue == 0)
			fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
		$this->productList->productStart = $this->updateObject->startValue;
		$this->productList->getProductList($this, $remote_category);
		$done = false;
		if ($this->productList->products == null || count($this->productList->products) < 50000)
			$done = true;
		if (isset($this->productList->products)) {
			$this->updateObject->startValue += count($this->productList->products);
			if (!isset($this->updateObject->productCount))
				$this->updateObject->productCount = 0;
			$this->updateObject->productCount += $this->productCount;
		}
		if ($this->has_footer && $done) {
			$this->updateObject->finished = true;
			fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
			$this->productCount = $this->updateObject->productCount;
			PFeedActivityLog::updateFeedList($category, $remote_category, $file_name, $file_path, $this->providerName, $this->productCount);
		}
		fclose($this->fileHandle);
	}

	protected function createFeed($file_name, $file_path, $remote_category) {
		//$file_name is (incorrectly) a url due to an unfortunate nomenclature left over from v2.x
		$this->fileHandle = fopen($this->filename, "w");
		if ($this->has_header)
			fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
		$this->productList->getProductList($this, $remote_category);
		if ($this->has_footer)
			fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
		fclose($this->fileHandle);
	}

	function fetchProductAttribute($name, $product) {
		$thisAttributeMapping = $this->getMapping($name);
		if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) )
			return $product->attributes[$thisAttributeMapping->attributeName];
		else
			return '';
	}

	function formatLine($attribute, $value, $cdata = false, $leader_space = '') {
		//Prep a single line for XML
		//Allow the $attribute to be overridden
		if (isset($this->feedOverrides->overrides[$attribute]) && (strlen($this->feedOverrides->overrides[$attribute]) > 0))
			$attribute = $this->feedOverrides->overrides[$attribute];
		$c_leader = '';
		$c_footer = '';
		if ($cdata) {
			$c_leader = '<![CDATA[';
			$c_footer = ']]>';
		}
		//Allow force strip HTML
		if ($this->stripHTML)
			$value = strip_tags(html_entity_decode($value));

		//UTF8Encode is guaranteed to create garbled text because we don't know the source encoding type
		//However, it will create a feed that will process, so it's a good temporary measure
		if ($this->utf8encode || $this->utf8encode == 1) {
			$value = utf8_encode($value);
			$attribute = utf8_encode($attribute);
		}

		//if not CData, don't allow '&'
		if (!$cdata)
			$value = htmlentities($value, ENT_QUOTES,'UTF-8');

		if (gettype($value) == 'array')
			$value = json_encode($value);

		//Done
		return '
        ' . $leader_space . '<' . $attribute . '>' . $c_leader . $value . $c_footer . '</' . $attribute . '>';
	}

	function formatProduct($product) {
		return '';
	}

	public function getErrorMessages() {

		$error_messages = '';

		foreach($this->errors as $index => $this_error) {
			if ($this_error->isWarning)
				$prefix = 'Warning: ';
			else
				$prefix = 'Error: ';
			$error_messages .= '<br>' . $prefix . $this_error->msg . '(' . $this_error->occurrences . ') <a href="http://docs.shoppingcartproductfeed.com/error_doc.php?id=' . $index . '" target="_blank">more...</a>';
		}

		return $this->message . $error_messages;
	}
  
	function getFeedData($category, $remote_category, $file_name, $saved_feed = null) {

		$this->logActivity('Initializing...');

		global $message;
		global $pfcore;

		$x = new PLicense();
		$this->loadAttributeUserMap();

		if ($this->updateObject == null)
			$this->initializeFeed($category, $remote_category);
		else
			$this->resumeFeed($category, $remote_category, $this->updateObject);

		$this->logActivity('Loading paths...');
		if (!$this->checkFolders())
			return;

		$file_url = PFeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
		$file_path = PFeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
			
		//Special (WordPress): where admin is https and site is http, path to wp-uploads works out incorrectly as https
		//  we check the content_url() for https... if not present, patch the file_path
		if (($pfcore->cmsName == 'WordPress') && (strpos($file_path, 'https://') !== false) && (strpos(content_url(), 'https') === false))
			$file_path = str_replace('https://', 'http://', $file_path);
		$this->file_path = $file_path;

		//Shipping and Taxation systems
		$this->shipping = new PShippingData($this);
		$this->taxData = new PTaxationData($this);

		$this->logActivity('Initializing categories...');

		//Figure out what categories the user wants to export
		$this->categories = new PProductCategories($category);

		//Get the ProductList ready
		if ($this->productList == null)
			$this->productList = new PProductList();

		//Initialize some useful data 
		//(must occur before overrides)
		$this->current_category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));

		$this->initializeOverrides($saved_feed);

		//Reorder the rules
		usort($this->rules, 'sort_rule_func');

		//Load relations into ProductList
		//Note: if relation exists, we don't overwrite
		foreach($this->relatedData as $relatedData)
			if (!isset($this->productList->relatedData[$relatedData[1]]))
				$this->productList->relatedData[$relatedData[1]] = new PProductSupplementalData($relatedData[0]);

		//Trying to change max_execution_time will throw privilege errors on some installs
		//so it's been left as an option
		if ($this->timeout > 0)
			ini_set('max_execution_time', $this->timeout);

		//Add the space in pricestandard rules
		//if (strlen($this->currency) > 0)
		//	$this->currency = ' '.$this->currency;
		
		//Create the Feed
		$this->logActivity('Creating feed data');
		$this->filename = $file_url;
		if ($this->updateObject == null)
			$this->createFeed($file_name, $file_path, $remote_category);
		else
			$this->continueFeed($category, $file_name, $file_path, $remote_category);

		$this->logActivity('Updating Feed List');
		if ($this->auto_update_feedlist)
			PFeedActivityLog::updateFeedList($category, $remote_category, $file_name, $file_path, $this->providerName, $this->productCount);

		if ($this->auto_free) {
			//Free the Attribute defaults
			for($i = 0; $i < count($this->attributeDefaults); $i++)
				unset($this->attributeDefaults[$i]);
			//Free the Attribute Mapping Objects
			for($i = 0; $i < count($this->attributeMappings); $i++)
				unset($this->attributeMappings[$i]);
			//De-allocate the overrides object to prevent chain dependency that made the core unload too early
			unset($this->feedOverrides);
		}

		if ($this->productCount == 0) {
			$this->message .= '<br>No products returned';
			return;
		}

		$this->success = true;
  }

  function getFeedFooter($file_name, $file_path) {
    return '';
  }
  
  function getFeedHeader($file_name, $file_path) {
    return '';
  }

	function getMapping($name) {
		foreach($this->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->attributeName == $name)
				return $thisAttributeMapping;
		return null;
	}

	function getMappingByMapto($name) {
		foreach($this->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->mapTo == $name)
				return $thisAttributeMapping;
		return null;
	}

	function getRuleByName($name) {
		foreach($this->rules as $rule)
			if ($rule->name == $name)
				return $rule;
		return null;
	}

	public function handleProduct($this_product) {

		$this_product->attributes['current_category'] = $this->current_category;

		//********************************************************************
		//Run the rules
		//********************************************************************

		foreach($this->rules as $rule)
			if ($rule->enabled)
				$rule->clearValue();

		foreach($this->rules as $index => $rule)
			if ($rule->enabled)
				$rule->process($this_product);

		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->isRuled) {
				$rule = $this->getRuleByName($thisDefault->value);
				if ($rule != null)
					$this_product->attributes[$thisDefault->attributeName] = $rule->value;
			}


		//***********************************************************
		//Send to descendant feed-provider for formatting
		//***********************************************************
		$product_text = $this->formatProduct($this_product);
		if ($this->feed_category->verifyProduct($this_product) && $this_product->attributes['valid']) {
			$this->handleProductSave($this_product, $product_text);
			foreach($this->aggregateProviders as $thisProvider)
				$thisProvider->aggregateProductSave($this->savedFeedID, $this_product, $product_text);
			$this->productCount++;
		}

	}

	function handleProductSave($product, $product_text) {
		fwrite($this->fileHandle, $product_text);
	}

	function initializeFeed($category, $remote_category) {
		//Allow descendant to perform initialization based on category/remote category
	}

	function initializeOverrides($saved_feed) {

		$this->logActivity('Initializing overrides...');
		//Mark all existing mappings as "SystemDefined" meaning auto-delete
		foreach($this->attributeMappings as $mapping)
			$mapping->systemDefined = true;
		//Load Attribute mappings
		$this->feedOverrides = new PFeedOverride($this->providerName, $this, $saved_feed);

	}
  
	function insertField($new_field, $index_field) {
		//CSV feed providers will sometimes want to insert-field-after-this-other-field, which PHP doesn't provide
		//insertField not currently used because the feedheader is created before productlist so there's no way to
		//know if some later category will need to re-arrange the fields
		//Edit: Debug Bing Feed provider uses insertField() for now
		if (in_array($new_field, $this->fields))
			return;
		$new_array = array();
		foreach($this->fields as $key => $item) {
			$new_array[] = $item;
			if ($item == $index_field)
				$new_array[] = $new_field;
		}
		$this->fields = $new_array;
	}

	function leaveFeed($updateObject) {
		//The system is abandoning this feed.
		//updateObject will be saved in JSON format and provided again in resumeFeed() at some point in the future
	}

	function loadAttributeUserMap() {
		//Called during feed initialization to map the Attributes
		global $pfcore;
		$map_string = $pfcore->settingGet('cpf_attribute_user_map_' . $this->providerName);
		if ( $map_string == '[]') { //if map_string is not object //temp fix for backwards compatibility... true fix below
		 		$pfcore->settingSet('cpf_attribute_user_map_' . $this->providerName, '');
		 		$map_string = '';
		 }
		if (strlen($map_string) == 0)
			$map = new stdClass(); //Was array(); *true fix -K
		else {
			$map = json_decode($map_string);
			$map = get_object_vars($map);
		}

		foreach($map as $mapto => $attr) {
			$thisAttribute = $this->getMappingByMapto($mapto);
			if ($thisAttribute != null && strlen($attr) > 0)
				$thisAttribute->attributeName = $attr;
		}
	}

	function logActivity($activity) {
	  if ($this->activityLogger != null)
			$this->activityLogger->logPhase($activity);
	}
  
	function must_exit() {
		//true means exit when feed complete so the browser page will remain in place (WordPress)
		return true;
	}

	function resumeFeed($category, $remote_category, $updateObject) {
		//Allow descendant to perform initialization based on category/remote category
		//upon resuming a Feed. Note that previously saved data is available in updateObject
		$this->auto_update_feedlist = false;
	}
  
	function __construct ($saved_feed = null) {

		global $pfcore;

		$this->feed_category = new md5y();
		$this->weight_unit = $pfcore->weight_unit;
		$this->dimension_unit = $pfcore->dimension_unit;
		$this->currency = $pfcore->currency;

		$this->addRule('description', 'description');

	}

} //PBasicFeed

//********************************************************************
// PXMLFeed has functions an XML Feed would need
//********************************************************************

class PXMLFeed extends PBasicFeed {

	public $productLevelElement = 'item';
	public $topLevelElement = 'items';

	function formatProduct($product) {

		//********************************************************************
		//Mapping 3.0 Pre-processing
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);
		
		$output = '
	<' . $this->productLevelElement . '>';

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
	</' . $this->productLevelElement . '>';

    return $output;

	}

  function getFeedFooter($file_name, $file_path) {   
    $output = '
</' . $this->topLevelElement . '>';
	return $output;
  }

  function getFeedHeader($file_name, $file_path) {

    $output = '<?xml version="1.0" encoding="UTF-8" ?>
<' . $this->topLevelElement . '>';
	return $output;
  }

}

//********************************************************************
// PCSVFeed has functions a CSV Feed would need
//********************************************************************

class PCSVFeed extends PBasicFeed {

	protected function asCSVString($current_feed) {

		//Build output in order of fields
		$output = '';
		foreach($this->fields as $field) {
			if (isset($current_feed[$field]))
				$output .= $current_feed[$field] . $this->fieldDelimiter;
			else
				$output .= $this->fieldDelimiter;
		}

		//Trim trailing comma
		return substr($output, 0, -1) . "\r\n";

	}

	public function executeOverrides($product, &$current_feed) {

		/*Mapping v2.0 Deprecated
		//Run overrides
		//Note: One day, when the feed can report errors, we need to report duplicate overrides when used_so_far makes a catch
		$used_so_far = array();
		foreach($product->attributes as $key => $a)
			if (isset($this->feedOverrides->overrides[$key]) && !in_array($this->feedOverrides->overrides[$key], $used_so_far)) {
				$current_feed[$this->feedOverrides->overrides[$key]] = $a;
				$used_so_far[] = $this->feedOverrides->overrides[$key]; 
			}
		*/

	}

	function formatProduct($product) {

		//Trigger Mapping 3.0 Before-Feed Event
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);

		//Build output in order of fields
		$output = '';
		foreach($this->fields as $field) {
			$thisAttributeMapping = $this->getMappingByMapto($field);
			if (($thisAttributeMapping != null) && $thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) ) {
				if ($thisAttributeMapping->usesCData)
					$quotes = '"';
				else
					$quotes = '';
				$output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;
			}
			$output .= $this->fieldDelimiter;
		}

		//Trigger Mapping 3.0 After-Feed Event
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 3)
				$thisDefault->postProcess($product, $output);

		//Trim trailing comma
		return substr($output, 0, -1) . "\r\n";

	}

	function getFeedHeader($file_name, $file_path) {

		$output = '';
		foreach($this->fields as $field) {
			if (isset($this->feedOverrides->overrides[$field]))
				$field = $this->feedOverrides->overrides[$field];
			$output .= $field . $this->fieldDelimiter;
		}
		//Trim trailing comma
		return;

	}

}


//********************************************************************
// PCSVFeedEx has functions a CSV Feed would need
// but phasing out deprecated functions
//********************************************************************

class PCSVFeedEx extends PBasicFeed {

	function __construct () {
		parent::__construct();
		$this->addRule('description', 'description', array('strict'));
	}

	function formatProduct($product) {

		//********************************************************************
		//Trigger Mapping 3.0 Before-Feed Event
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 2)
				$product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);

		//********************************************************************
		//Build output
		//********************************************************************
		$output = '';
		foreach($this->attributeMappings as $thisAttributeMapping) {
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName]) ) 
			{
				if ($thisAttributeMapping->usesCData)
					$quotes = '"';
				else
					$quotes = '';
				
				$output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;
			}
			if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted)
				$output .= $this->fieldDelimiter;
		}

		//********************************************************************
		//Trigger Mapping 3.0 After-Feed Event
		//********************************************************************
		foreach ($this->attributeDefaults as $thisDefault)
			if ($thisDefault->stage == 3)
				$thisDefault->postProcess($product, $output);

		//********************************************************************
		//Trim trailing delimiter
		//********************************************************************
		return substr($output, 0, -1) . "\r\n";

	}

	/*function getFeedHeader($file_name, $file_path) {

		$output = '';

		foreach($this->attributeMappings as $thisMapping)
			if ($thisMapping->enabled && !$thisMapping->deleted)
				$output .= $thisMapping->mapTo . $this->fieldDelimiter;

		return substr($output, 0, -1) .  "\r\n";

	}*/

  function getFeedHeader($file_name, $file_path) {
		return ''; //Skip header - We'll do it later
  }

  function getFeedFooter($file_name, $file_path) {

		//Now we finally write the headers! Start by creating them
		$headers = array();
		foreach($this->attributeMappings as $thisMapping)
			if ($thisMapping->enabled && !$thisMapping->deleted)
				$headers[] = $thisMapping->mapTo;
		$headerString = implode($this->fieldDelimiter, $headers);

		$savedData = file_get_contents($this->filename);
		file_put_contents($this->filename, $headerString . "\r\n" .$savedData);

		//Write the footer as a header in the aggregate
		foreach($this->aggregateProviders as $thisProvider)
			$thisProvider->aggregateHeaderWrite($this->savedFeedID, $headerString);

    return '';

  }

	function initializeOverrides($saved_feed) {
		parent::initializeOverrides($saved_feed);

		/*Deprecated
		//Converting Attribute mappings v2.0 to v3.0
		foreach($this->feedOverrides->overrides as $key => $mapTo) {
			$n = $this->getMappingByMapto($mapTo);
			if ($n == null)
				$this->addAttributeMapping($key, $mapTo, true);
			else
				$n->attributeName = $key;
		}*/

	}

}

//********************************************************************
// PAggregateFeed
//********************************************************************

class PAggregateFeed extends PBasicFeed {

	function __construct () {
		parent::__construct();
	}

	function aggregateHeaderWrite($id, $headerString) {
		//Do nothing. This is used only by AggCSV
	}

  function initializeAggregateFeed($id, $file_name) {

		$this->filename = PFeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
		$this->file_url = PFeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
		$this->productCount = 0;
		$this->file_name_short = $file_name;

		if ( !isset($this->feeds) || count($this->feeds) == 0) {
			global $pfcore;
			$data = $pfcore->settingGet('cpf_aggrfeedlist_' . $id);
			$data = explode(',', $data);
			$this->feeds = array();
			foreach($data as $datum)
				$this->feeds[$datum] = true;
		}

  }

}

function sort_rule_func($a, $b) {
	if ($a->order == $b->order)
		return 0;
	else
		return ($a->order < $b->order) ? -1 : 1;
}
