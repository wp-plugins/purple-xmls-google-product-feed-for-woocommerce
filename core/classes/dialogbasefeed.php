<?php

  /********************************************************************
	Version 2.0
		Core functionality of a basic feed.
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05
		This file could still use some work
  ********************************************************************/

require_once dirname(__FILE__) . '/../data/productcategories.php';
require_once dirname(__FILE__) . '/../data/attributesfound.php';
require_once dirname(__FILE__) . '/../data/feedfolders.php';

class PBaseFeedDialog {

	public $options; //Array to be filled by constructor of descendant
	public $service_name = 'Google'; //Example only
	public $service_name_long = 'Google Products XML Export'; //Example only

	function __construct() {
		$this->options = array();
	}

  function createDropdown($thisAttribute, $index) {
    $found_options = new FoundOptions($this->service_name, $thisAttribute);
    $output = '
	<select id="attribute_select' . $index . '" onchange="setAttributeOption(\'' . $this->service_name . '\', \'' . $thisAttribute . '\', ' . $index . ')">
	  <option value=""></option>';
		foreach($this->options as $option) {
			if ($option == $found_options->option_value) {
				$selected = 'selected="selected"';
			} else $selected = '';
			$output .= '<option value="' . $this->convert_option($option) . '"' . $selected . '>' . $option . '</option>';
		}
		$output .= '
	</select>';
		return $output;
  }

  function advancedTab() {
		global $pfcore;
		$output = '
	  <div class="feed-advanced" id="feed-advanced">
	    <textarea class="feed-advanced-text" id="feed-advanced-text">' . $pfcore->settingGet($this->service_name . '-cart-product-settings') . '</textarea>
		<input class="navy_blue_button" type="submit" value="Update" id="submit" name="submit" onclick="doUpdateSetting(\'feed-advanced-text\', \'cp_advancedFeedSetting-' . $this->service_name . '\')">
		<div id="updateSettingMessage">&nbsp;</div>
	  </div>';
		return $output;
  }

  function attributeMappings() {
		$FoundAttributes = new FoundAttribute();
		$output = '
			  <h2>Attribute Mapping</h2>
			  <table>
			  <tr>
			    <td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';
		$index = 0;
		foreach($FoundAttributes->attributes as $attr)
			$output .= '
				<tr><td>' . $attr->attribute_name . '</td><td></td><td>' . $this->createDropdown($attr->attribute_name, $index++) . '</td></tr>';
		$output .= '
			  </table>';

		return $output;
  }

  function mainDialog() {
		global $pfcore;

		$servName = strtolower($this->service_name);

		$attrVal = array();
		$folders = new PFeedFolder();
		$product_categories = new PProductCategories();

    $output = '
	  <div class="attributes-mapping">
        <div id="poststuff">
		  <div class="postbox" style="width: 98%;">
		    <h3 class="hndle">' . $this->service_name_long . '</h3>
			<div class="inside export-target">
			<div class="feed-left">
			<p>This Export Feed will map your ' . $pfcore->cmsPluginName . ' Attributes to ' .$this->service_name.'\'s required ones.</p>
			' . $this->attributeMappings() . '
			</div>
			<div class="feed-right">
				<form action="' . $folders->feedURL() . '" name="' . $servName . '" id="cat-feeds-xml-' . $servName . '-form" method="' . $pfcore->form_method . '" target="_blank">
				<div class="feed-right-row">
				  <span class="label">' . $pfcore->cmsPluginName . ' Category : </span>
				  <select name="local_category" id="local_category" class="select_big">' . $product_categories->getOptionList() . '</select>
				</div>
				<div class="feed-right-row">
				  <span class="label">' . $this->service_name . ' Category : </span>
				  <span><input type="text" name="categoryDisplayText" class="text_big" id="categoryDisplayText"  onkeyup="doFetchCategory_timed(\'' . $this->service_name . '\',  this.value)" autocomplete="off"></span>
				  <div id="categoryList" class="categoryList"></div>
				  <input type="hidden" id="remote_category" name="remote_category">
				</div>
				<div class="feed-right-row">
				  <span class="label">File name for feed : </span>
				  <span ><input type="text" name="feed_filename" id="feed_filename" class="text_big" /></span>
				</div>
				<div class="feed-right-row">
				  <label>* If you use an existing file name, the file will be overwritten.</label>
				</div>
				<div class="feed-right-row">
				  <input type="hidden" name="RequestCode" value="' . $this->service_name . '" />
					<input class="cupid-green" type="button" onclick="doGetFeed(\'' . $servName . '\')" value="Get Feed" \>
					<div id="feed-error-display">&nbsp;</div>
					<div id="feed-status-display">&nbsp;</div>
				</div>
				</form>
			</div>
		  <div style="clear: both;">&nbsp;</div>
		  <div>
		    <label class="un_collapse_label" title="Advanced" id="toggleAdvancedSettingsButton" onclick="toggleAdvancedDialog()">[ Open Advanced Commands ]</label>
			<label class="un_collapse_label" title="Erase existing mappings" id="erase_mappings" onclick="doEraseMappings(\'' . $this->service_name . '\')">[ Clear Deleted Attributes ]</label>
		  </div>
		  ' . $this->advancedTab() . '
		</div>
	  </div>';
		return $output;
	}

  //!Who even call this??
	function convert_option($option) {
		//Some Feeds (like Google & eBay) need to modify this
		return $option;
  }

  //This function needs to die
	function list_categories() {
		$data = file_get_contents('categories_' . strtolower($this->service_name) . '.txt');
		$arr = explode("\n", $data);
		$key = 0;
		$result = NULL;
    foreach ($arr as $k => $value) {
			if ($value == '') {
				$value = '--- Select ' . $this->service_name . ' Category ---';
			}
			$result .= "<option value='" . str_replace(" & ", ".and.", str_replace(" / ", ".in.", trim($value))) . "'>" . htmlentities(trim($value)) . "</option>";
    }
    return $result;
  }

}


?>