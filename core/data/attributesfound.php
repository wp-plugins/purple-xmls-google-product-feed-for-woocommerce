<?php

  /********************************************************************
  Version 2.0
    Find the Attributes contained with in our DB
		(these attributes will need to be compared to some outside agency's attributes)
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05

  ********************************************************************/

class FoundAttribute {

  public $attributes;
  public $attrOptionsTableName = '';
  public $attrOptions;

  function __construct() {
	global $pfcore;
	$fetchAttributes = 'fetchAttributes' . $pfcore->callSuffix;
	$this->$fetchAttributes();
  }

  function fetchAttributesJ() {
    //From Joomla / Virtuemart
	$db = JFactory::getDBO();
	$query = 'SELECT a.custom_title as attribute_name
      FROM #__virtuemart_customs a
	  WHERE (CHAR_LENGTH(a.custom_element) > 0) AND (a.published = 1)';
	$db->setQuery($query);
	$db->query();
	$this->attributes = $db->loadObjectList();
  }

  function fetchAttributesW() {
    //From WordPress / Woocommerce
    global $wpdb;
    $attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
    $this->attrOptionsTableName = $wpdb->prefix . 'options';
    $sql = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
    $this->attributes = $wpdb->get_results($sql);
  }

  /*function fetchAttrOptions($attrVal) {
    global $wpdb;
    $sql = "SELECT option_value FROM " . $this->attrOptionsTableName . " WHERE option_name='" . $attrVal . "'";
    $this->attrOptions = $wpdb->get_results($sql);
  }*/

}

class FoundOptions {

  public $option_value = '';

  function __construct($service_name, $attribute) {
	global $pfcore;
	$internalFetch = 'internalFetch' . $pfcore->callSuffix;
	$this->$internalFetch($service_name, $attribute);
  }

  function internalFetchJ($service_name, $attribute) {
    $option_name = $service_name . '_cp_' . $attribute;
	$db = JFactory::getDBO();
	$query = "SELECT a.value
      FROM #__cartproductfeed_options a
	  WHERE (a.state = 1) AND (a.name='$option_name')";
	$db->setQuery($query);
	$db->query();
	$this->option_value = $db->loadResult();
  }
  
  function internalFetchW($service_name, $attribute) {
	$this->option_value = get_option($service_name . '_cp_' . $attribute);
  }

}

?>