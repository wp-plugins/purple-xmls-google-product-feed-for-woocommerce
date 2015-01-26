<?php

  /********************************************************************
	Version 2.0
		Core functionality of a basic feed.
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05
		2014-10 Moved to template (.tpl) format for simplicity (hopefully) -Keneto
  ********************************************************************/

require_once dirname(__FILE__) . '/../data/productcategories.php';
require_once dirname(__FILE__) . '/../data/attributesfound.php';
require_once dirname(__FILE__) . '/../data/feedfolders.php';

class PBaseFeedDialog {

	public $blockCategoryList = false;
	public $options; //Array to be filled by constructor of descendant
	public $service_name = 'Google'; //Example only
	public $service_name_long = 'Google Products XML Export'; //Example only

	function __construct() {
		$this->options = array();
	}

  function createDropdown($thisAttribute, $index) {
    $found_options = new FoundOptions($this->service_name, $thisAttribute);
    $output = '
	<select class="attribute_select" id="attribute_select' . $index . '" onchange="setAttributeOption(\'' . $this->service_name . '\', \'' . $thisAttribute . '\', ' . $index . ')">
	  <option value=""></option>';
		foreach($this->options as $option) {
			if ($option == $found_options->option_value)
				$selected = 'selected="selected"';
			else
				$selected = '';
			$output .= '<option value="' . $this->convert_option($option) . '"' . $selected . '>' . $option . '</option>';
		}
		$output .= '
	</select>';
		return $output;
  }

  function createDropdownAttr($FoundAttributes, $defaultValue = '', $mapTo) {
    $output = '
	<select class="attribute_select" service_name="' . $this->service_name . '"
		mapto="' . $mapTo . '"
		onchange="setAttributeOptionV2(this)" >
	  <option value=""></option>
	  <option value="(Reset)">(Reset)</option>';
		foreach($FoundAttributes->attributes as $attr) {
			if ($defaultValue == $attr->attribute_name)
				$selected = ' selected="true"';
			else
				$selected = '';
			$output .= '<option value="' . $attr->attribute_name . '"' . $selected . '>' . $attr->attribute_name . '</option>';
		}
		$output .= '
		<option value="brand">brand (auto-detect)</option>
		<option value="id">id</option>	
		<option value="sku">sku</option>
		<option value="default1">default1</option>
		<option value="default2">default2</option>
		<option value="default3">default3</option>	
	</select>';
		return $output;
  }

  function attributeMappings() {

		global $pfcore;
		$FoundAttributes = new FoundAttribute();
		$savedAttributes = $FoundAttributes->attributes;
		$FoundAttributes->attributes = array();
		foreach($savedAttributes as $attr)
			$FoundAttributes->attributes[] = $attr;

		foreach($this->provider->attributeMappings as $thisAttributeMapping) {
			//if empty mapping, don't add to drop down list
			if ( strlen(trim($thisAttributeMapping->attributeName)) > 0 ) {
				$attr = new stdClass();
				$attr->attribute_name = $thisAttributeMapping->attributeName;
				$FoundAttributes->attributes[] = $attr;
			}
		}
		
		/*
		//patch: for google feed, ram the brand in
		if ($this->service_name == 'Google') {
			$has_brand = false;
			foreach($FoundAttributes->attributes as $attr)
				if (strtolower($attr->attribute_name) == 'brand') {
					$has_brand = true;
					break;
				}
			if (!$has_brand) {
				$thisAttribute = new stdClass();
				$thisAttribute->attribute_name = 'brand';
				$FoundAttributes->attributes[] = $thisAttribute;
			}
		}
http://www.shoppingcartproductfeed.com/tos/generate-google-merchant-feed-woocommerce/
		*/
		$output = '				
				<p><a target="blank" title="Generate Merchant Feed" href="http://shoppingcartproductfeed.com/generate-google-merchant-feed-woocommerce/">Generate your first feed</a> | 
				<a target=\'_blank\' href=\'http://www.shoppingcartproductfeed.com/tos/\' >View guides</a></p>
				<p>Use the drop downs below to re-map ' . $pfcore->cmsPluginName . ' attributes to ' .$this->service_name.'\'s required attributes.</p>
				<p>Additional attributes can also be found below by clicking the [Show] button.</p>
				<label class="attributes-label" title="Required Attributes" id="toggleRequiredAttributes" onclick="toggleRequiredAttributes()">Required Attributes</label>
				<div class="required-attributes" id=\'required-attributes\'>
				<table>
				<tr><td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';

		foreach($this->provider->attributeMappings as $thisAttributeMapping)
			if ($thisAttributeMapping->isRequired)
				$output .= '<tr><td>' . $this->createDropdownAttr($FoundAttributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
		$output .= '
			  </table>
			  </div>
			  <label class="attributes-label" title="Optional Attributes" id="toggleOptionalAttributes" onclick="toggleOptionalAttributes()">[Show] Additional Attributes</label>
			  <div class="optional-attributes" id=\'optional-attributes\'>
			  <table>
			  <tr><td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';

		foreach($this->provider->attributeMappings as $thisAttributeMapping)
			if (!$thisAttributeMapping->isRequired)
				$output .= '<tr><td>' . $this->createDropdownAttr($FoundAttributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
		$output .= '
			  </table>
			  </div>';

		return $output;
  }

	function categoryList($initial_remote_category) {
		if ($this->blockCategoryList)
			return '<input type="hidden" id="remote_category" name="remote_category" value="undefined">';
		else
			return '
				  <span class="label">' . $this->service_name . ' Category : </span>
				  <span><input type="text" name="categoryDisplayText" class="text_big" id="categoryDisplayText"  onkeyup="doFetchCategory_timed(\'' . $this->service_name . '\',  this.value)" value="' . $initial_remote_category . '" autocomplete="off" placeholder="Start typing for a category name"/></span>
				  <div id="categoryList" class="categoryList"></div>
				  <input type="hidden" id="remote_category" name="remote_category" value="' . $initial_remote_category . '">';
	}

	public function getTemplateFile() {
		$filename = dirname(__FILE__) . '/../feeds/' . strtolower($this->service_name) . '/dialog.tpl.php';
		if (!file_exists($filename))
			$filename = dirname(__FILE__) . '/dialogbasefeed.tpl.php';
		return $filename;
	}

	public function initializeProvider() {
		//Load the feed provider
		require_once dirname(__FILE__) . '/md5.php';
		require_once dirname(__FILE__) . '/../feeds/' . strtolower($this->service_name) . '/feed.php';
		$providerName = 'P' . $this->service_name . 'Feed';
		$this->provider = new $providerName;
		$this->provider->loadAttributeUserMap();
	}

	function line2() {
		global $pfcore;
		if ($pfcore->cmsPluginName != 'RapidCart')
			return '';
		$listOfShops = $pfcore->listOfRapidCartShops();
		$output = '<select class="text_big" id="edtRapidCartShop" onchange="doFetchLocalCategories()" >';
		foreach($listOfShops as $shop) {
			if ($shop->id == $pfcore->shopID)
				$selected = ' selected="selected"';
			else
				$selected = '';
			$output .= '<option value="' . $shop->id . '"' . $selected . '>' . $shop->name . '</option>';
		}
		$output .= '</select>';
		return '
				<div class="feed-right-row">
				  <span class="label">Shop : </span>
				  ' . $output . '
				</div>';
	}

  public function mainDialog($source_feed = null) {

		global $pfcore;

		$this->advancedSettings = $pfcore->settingGet($this->service_name . '-cart-product-settings');
		if ($source_feed == null) {
			$initial_local_category = '';
			$this->initial_local_category_id = '';
			$initial_remote_category = '';
			$this->initial_filename = '';
			$this->script = '';
			$this->cbUnique = '';
		} else {
			$initial_local_category = $source_feed->local_category;
			$this->initial_local_category_id = $source_feed->category_id;
			$initial_remote_category = $source_feed->remote_category;
			$this->initial_filename = $source_feed->filename;
			if ($source_feed->own_overrides == 1) {
				$strChecked = 'checked="checked" ';
				$this->advancedSettings = $source_feed->feed_overrides;
			} else
				$strChecked = '';
			$this->cbUnique = '<div><label><input type="checkbox" id="cbUniqueOverride" ' . $strChecked . '/>Advanced commands unique to this feed</label></div>';
			/*if ($source_feed->own_overrides == 1) {
				$this->advancedSettings = $source_feed->feed_overrides;
				$this->script = '
					<script type="text/javascript">
						jQuery( document ).ready( function() {
							jQuery("#cbUniqueOverride").prop("checked", true);
						});
					</script>';
			}*/
		}

		$this->servName = strtolower($this->service_name);

		$this->initializeProvider();

		$attrVal = array();
		$this->folders = new PFeedFolder();
		$this->product_categories = new PProductCategories(); //used?

		$this->localCategoryList = '
			<input type="text" name="local_category_display" class="text_big" id="local_category_display"  onclick="showLocalCategories(\'' . $this->service_name . '\')" value="' . $initial_local_category . '" autocomplete="off" readonly="true" placeholder="Click here to select your categories"/>
			<input type="hidden" name="local_category" id="local_category" value="' . $this->initial_local_category_id .'" />';
		$this->source_feed = $source_feed;


		//Pass this to the template for processing
		
		include $this->getTemplateFile();

	}

  //Strip special characters out of an option so it can safely go in a <select /> in the dialog
	function convert_option($option) {
		//Some Feeds (like Google & eBay) need to modify this
		return $option;
  }

}


?>