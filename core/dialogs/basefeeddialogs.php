<?php

  /********************************************************************
  Version 2.0
    Core functionality of a basic feed. 
	This file could still use some presentation work
	By: Keneto 2014-05-05

  ********************************************************************/
  
require_once __DIR__ . '/../classes/productcategories.php';

class PBaseFeedDialog {

  public $form_attibutes_name = 'attribute_changes';
  public $form_attibutes_id = 'google_attributes_form';
  public $form_options_id = 'googleattr';
  public $options; //Array to be filled by constructor of descendant
  public $service_name = 'Google'; //Example only
  public $service_name_long = 'Google Products XML'; //Example only
  
  function __construct() {
    $this->options = array();
  }
  
  /*function attributeMappings() {
    $FoundAttributes = new FoundAttribute();
	$cnt = 0;
    $output = '
			  <h2>Select Attributes Mapping</h2>
			  <form id="' . $this->form_attibutes_id . '" name="' . $this->form_attibutes_name . '" method="post" action="">
			  <table>
			  <tr>
				<td class="title" width="120">Found Attributes</td>
				<td class="title">' . $this->service_name . ' Attributes</td>
			  </tr>';
    while ($cnt < count($FoundAttributes->attributes)) {
	  $output .= '
				<tr>
				<td><input type="hidden" name="RequestCode" value="attributeUpdate' . $this->service_name . '" />
				<select class="select_small" name="wooattr' . $cnt . '" id="wooattr' . $cnt . '">';
      $cnt2 = 0;
      foreach ($FoundAttributes->attributes as $attr) {
		if ($cnt == $cnt2) {
		  $output.= "<option value='" . $attr->attribute_name . "' selected>" . $attr->attribute_name . "</option>";
		} else {
		  $output .= "<option value='" . $attr->attribute_name . "'>" . $attr->attribute_name . "</option>";
		}
		$attrVal[$cnt2] = $attr->attribute_name;
		$cnt2++;
      }
	  $output .= '
				</select>
				</td>
				<td>';

	  //No idea what this was meant to do but it's always blank when I test on original file
	  $FoundAttributes->fetchAttrOptions($attrVal[$cnt]);
      $val = "";
      foreach ($FoundAttributes->attrOptions as $result) {
            $val = $result->option_value;
      }
	  //Google's: $sql2 = "SELECT option_value FROM " . $attr_options . " WHERE option_name='" . $attrVal[$cnt] . "'";
	  //The Nextag: $sql2 = "SELECT option_value FROM " . $attr_options . " WHERE option_name='nextag_pa_" . $attrVal[$cnt] . "'";
	  $output .= '
				<select class="select_small" name="' . $this->form_options_id . $cnt . '" id="' . $this->form_options_id . $cnt . '">;
				<option value=""></option>';
				//<option value="$val">$val</option>'; //No idea why this was here... replaced it with a blank for now
				
	  foreach($this->options as $option) {
	    $output .= '
				<option value="' . $this->convert_option($option) . '">' . $option . '</option>';
	  }
      $output .='
				</select>
				</td>
				</tr>';
      $cnt++;
    }
  $output .= '
			  <tr>
<td >
<input type="hidden" name="count" value=$cnt>
    <input style="width:100px;" class="navy_blue_button" type="submit" value="Update"  name="submit">
    </td>
    <td>
    <a href="' . admin_url() . 'admin.php?page=cart-product-feed-admin&action=reset_attributes"><input style="width:100px;" class="shiny_red" type="button" value="Reset"  name="submit"></a>
    </td>
	</table>
    </form>	';
	
	return $output;
  }*/
  
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
  
  function attributeMappings() {
    $FoundAttributes = new FoundAttribute();
    $output = '
			  <h2>Attribute Mapping</h2>
			  <table>
			  <tr>
			    <td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';
	$index = 0;
	foreach($FoundAttributes->attributes as $attr) {
	  $output .= '
				<tr><td>' . $attr->attribute_name . '</td><td></td><td>' . $this->createDropdown($attr->attribute_name, $index++) . '</td></tr>';
	}
	$output .= '
			  </table>';
	
	return $output;
  }
  
  function mainDialog()
  {
	$servName = strtolower($this->service_name);

    $attrVal = array();
    
    $output = '
	  <div class="attributes-mapping">
        <div id="poststuff">
		  <div class="postbox" style="width: 98%;">
		    <h3 class="hndle">' . $this->service_name_long . '</h3>
			<div class="inside export-target">
			<div class="feed-left">' . 
			  $this->attributeMappings() . '
			</div>
			<div class="feed-right">
				<form action="' . site_url() . '" name="' . $servName . '" id="cat-feeds-xml-' . $servName . '-form" method="get" target="_blank">
				<div class="feed-right-row">
				  <span class="label">Source Category : </span>
				  <select name="category" id="listproduct' . $servName . '" class="select_big">' . PProductCategories::getList() . '</select>
				</div>
				<div class="feed-right-row">
				  <span class="label">Target Category : </span>
				  <span id="' . $servName . 'CategoryContain"><select name="' . $servName . '_category" id="' . $servName . 'CategorySelect" class="select_big">' . $this->list_categories() . '</select></span>
				</div>
				<div class="feed-right-row">
				  <span class="label">File name for feed xml : </span>
				  <span ><input type="text" name="feed_filename" id="feed_filename" class="text_big" /></span>
				</div>
				<div class="feed-right-row">
				  <label>* If you use an existing file name, the file will be overwritten.</label>
				</div>
				<div class="feed-right-row">
				  <input type="hidden" name="RequestCode" value="' . $this->service_name . '" />
				  <input class="cupid-green" type="button" onclick="document.' . $servName . '.submit();" name="submit-' . $servName . '-xml" value="Get Feed" id="cat-feeds-xml-' . $servName . '">
				</div>
				</form>
			</div>
		  <div style="clear: both;">&nbsp;</div>
		</div>
	  </div>';
	return $output;
  }
  
  function convert_option($option) {
	//Some Feeds (like Google & eBay) need to modify this
    return $option;
  }
  
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