<?php

  /********************************************************************
  Version 2.0
    A Feed
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/
  
class PBasicFeed {

	public $activityLogger = null; //If set, someone wants me to log what phase I'm at in feed-generation';
  public $categories;
  public $current_category; //This is the active category while in formatProduct()
  public $currency;
  public $currency_shipping = ''; //Defaults to $currency
  public $currency_format = '%1.2f';
  public $default_brand = '';
  public $descriptionFormat; //0 = short or long, 1 = long 2 = short
  public $descriptionStrict = false; //hack out ALL special characters from the description including multinational
  public $descriptionStrictReplacementChar = ' ';
  public $discount = false;
  public $discount_amount = 0;
  public $discount_sale_amount = 0;
  public $discount_multiplier = 1.00;
  public $discount_sale_multiplier = 1.00;
  public $fileformat = 'xml';
  public $fieldDelimiter = "\t"; //For CSVs
  public $fields; //For CSVs
  public $feed_category;
  public $force_currency = false;
	public $gmc_enabled = false; //Allow Google merchant centre woothemes extension (WordPress)
	public $gmc_attributes = array(); //If anything added in here, restrict GMC list to these
  public $max_description_length = 10000;
	public $message = ''; //For Error detection
  public $providerName = '';
  public $providerNameL = '';
	public $productCount = 0; //Number of products successfully exported
  public $productList;
  public $productTypeFromLocalCategory = false;
  public $shipping_amount = '0.00';
  public $shipping_multiplier = 0;
  public $shipping_sale_multiplier = 0;
	public $success = false;
  public $stripHTML = false;
  public $system_wide_shipping = true;
  public $system_wide_shipping_rate = '0.00';  //Deprecated as of v3.0.1.23: Use $shipping_amount
  public $system_wide_shipping_type = 'Ground';
  public $system_wide_tax = false;
  public $system_wide_tax_rate = 0;
  public $timeout = 0; //If >0 try to override max_execution time
  public $weight_unit;
 
  function checkFolders() {

    global $message;

    $dir = PFeedFolder::uploadRoot();
    if (!is_writable($dir)) {
      $message = $dir . ' should be writeable';
	  return false;
    }
	
    $dir = PFeedFolder::uploadFolder();
    if (!is_dir($dir)) {
	  mkdir($dir);
    }
    if (!is_writable($dir)) {
	  $message = "$dir should be writeable";
	  return false;
    }
    $dir2 = $dir . $this->providerName . '/';
	if (!is_dir($dir2)) {
	  mkdir($dir2);
	}
	
	return true;
  }

  function formatLine($attribute, $value, $cdata = false, $leader_space = '') {
	//Prep a single line for XML
	//Allow the $attribute to be overridden
	if (isset($this->feedOverrides->overrides[$attribute]) && (strlen($this->feedOverrides->overrides[$attribute]) > 0)) {
	  $attribute = $this->feedOverrides->overrides[$attribute];
	}
	$c_leader = '';
	$c_footer = '';
	if ($cdata) {
	  $c_leader = '<![CDATA[';
	  $c_footer = ']]>';
	}
	//Allow force strip HTML
	if ($this->stripHTML) {
	  $value = strip_tags(html_entity_decode($value));
	}
	//if not CData, don't allow '&'
	if (!$cdata) {
	  $value = htmlentities($value, ENT_QUOTES,'UTF-8');
	}
	//Done
	return '
	    ' . $leader_space . '<' . $attribute . '>' . $c_leader . $value . $c_footer . '</' . $attribute . '>';
  }
  
  function formatProduct($product) {
    return '';
  }
  
  function getFeedData_internal($remote_category) {
		$output = null;

		$products = $this->productList->getProductList($this, $remote_category);
		foreach($products as $this_product) {
			if (!$this->feed_category->verifyProduct($this_product)) continue;
			//********************************************************************
			//Adjust the product a little before sending it out to be Formatted
			//********************************************************************
			switch ($this->descriptionFormat) {
				case 1: //Force Long
					$this_product->description = $this_product->description_long;
					break;
				case 2: //Force Short
					$this_product->description = $this_product->description_short;
					break;
				default:
					//By default pick short... if no short, pick long (original behaviour)
					if ( strlen ( $this_product->description_short ) == 0 ) 
					$this_product->description = $this_product->description_long;
					else 
					$this_product->description = $this_product->description_short;		  
					break;
			}
			//check if description is empty
			if ( '' == ( trim($this_product->description) ) )
				//if so use title
				$this_product->description = $this_product->title;

			if (strlen($this_product->description) > $this->max_description_length) 
				$this_product->description = substr($this_product->description, 0, $this->max_description_length);

			if ($this->descriptionStrict) {
				//I really should use preg_replace here one day
				//$this_product->description = preg_replace('/[^A-Za-z0-9\-]/', '', $this_product->description);
				for($i=0;$i<strlen($this_product->description);$i++) {
					if (($this_product->description[$i] < "\x20") || ($this_product->description[$i] > "\x7E")) {
						$this_product->description[$i] = $this->descriptionStrictReplacementChar;
					}
				}
			}
			/***********************************************************
			Category & Brand
			***********************************************************/
			if ($this->productTypeFromLocalCategory)
				$this_product->product_type = $this_product->localCategory;

			if ((!isset($this_product->attributes['brand'])) && (strlen($this->default_brand) > 0))
				$this_product->attributes['brand'] = $this->default_brand;

			/***********************************************************
			Price Discount
			***********************************************************/
			if ($this->discount) {
				//Basic sale_price is a function of price
				if ($this->discount_amount > 0 || $this->discount_multiplier != 1) {
					$this_product->sale_price = $this_product->regular_price * $this->discount_multiplier - $this->discount_amount;
					$this_product->has_sale_price = true;
				}
				//Possible to do sale as a function of sale price, but IFF this_product->has_sale_price already
				if (($this->discount_sale_amount > 0 || $this->discount_sale_multiplier != 1) && $this_product->has_sale_price) {
					$this_product->sale_price = $this_product->sale_price * $this->discount_sale_multiplier - $this->discount_sale_amount;
					$this_product->has_sale_price = true;
				}
				//Disallow negative price
				if ($this_product->sale_price < 0)
					$this_product->sale_price = 0;
			}

			/***********************************************************
			Shipping
			***********************************************************/
			$this_product->shipping_amount = 
				$this->shipping_amount + 										//Base amount
				$this_product->regular_price * $this->shipping_multiplier + 		//% of Price
				($this_product->has_sale_price ? $this_product->sale_price * $this->shipping_sale_multiplier : 0);		//% of Sale Price
			/***********************************************************
			Other
			***********************************************************/
			if ($this->system_wide_tax  && (!isset($this_product->tax)))
				$this_product->tax = $this->system_wide_tax_rate;

			/***********************************************************
			Done Adjustments. Send to descendant feed-provider for formatting
			***********************************************************/

			$output .= $this->formatProduct($this_product);

			$this->productCount++;
		}

		return $output;

	}

	function getFeedData($category, $remote_category, $file_name) {

		$this->logActivity('Initializing...');
		global $message;
		global $pfcore;

		$x = new PLicense();

		if (!$this->checkFolders()) {
			return;
		}

		$file_url = PFeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
		$file_path = PFeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
			
		//Special: where admin is https and site is http, path to wp-uploads works out incorrectly as https
		//  we check the content_url() for https... if not present, patch the file_path
		if ((strpos($file_path, 'https://') !== false) && (strpos(content_url(), 'https') === false)) {
			$file_path = str_replace('https://', 'http://', $file_path);
		}

		//Redirect the client to the feed file instead of this script
		//header('Location: ' . $file_path); //Header no longer needed since AJAX

		//Figure out what categories the user wants to export
		$this->categories = new PProductCategories($category);

		//Get the ProductList ready
		if ($this->productList == null)
			$this->productList = new PProductList();
		
		//Initialize some useful data 
		//(must occur before overrides)
		$this->current_category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
		$this->weight_unit = $pfcore->weight_unit;
		$this->currency = $pfcore->currency;
		
		//Get the Feed Overrides ready
		$this->feedOverrides = new PFeedOverride($this->providerName, $this);

		//Trying to change max_execution_time will throw privilege errors on some installs
		//so it's been left as an option
		if ($this->timeout > 0) {
			ini_set('max_execution_time', $this->timeout);
		}

		if (strlen($this->currency) > 0) {
			$this->currency = ' ' . $this->currency;
		}
		if (strlen($this->currency_shipping) == 0) {
			$this->currency_shipping = $this->currency;
		}

		$this->logActivity('Creating feed data');
		//Create the Feed
		$output = 
			$this->getFeedHeader($file_name, $file_path) .
			$this->getFeedData_internal($remote_category) .
			$this->getFeedFooter();

		$this->logActivity('Writing feed to file');
		//Save the Feed
		$handle = fopen($file_url, "w");  
		fwrite($handle, $output);
		fclose($handle);

		$this->logActivity('Updating Feed List');
		PFeedActivityLog::updateFeedList($category, $remote_category, $file_name, $file_path, $this->providerName);

		if ($this->productCount == 0) {
			$this->message .= '<br>No products returned';
			return;
		}

		$this->success = true;
  }

  function getFeedFooter() {
    return '';
  }
  
  function getFeedHeader() {
    return '';
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

	function logActivity($activity) {
	  if ($this->activityLogger != null)
			$this->activityLogger->logPhase($activity);
	}
  
	function must_exit() {
		//true means exit when feed complete so the browser page will remain in place (WordPress)
		return true;
	}
  
	function __construct ($cachedProductList = null) {
		$this->feed_category = new md5y();
		if ($cachedProductList != null)
		  $this->productList = $cachedProductList;
	}

}