<?php

  /********************************************************************
  Version 2.0
    Find the Attributes contained with in our DB 
	(these attributes will need to be compared to some outside agency's attributes)
	By: Keneto 2014-05-05

  ********************************************************************/

class FoundAttribute {

  public $attributes;
  public $attrOptionsTableName = '';
  public $attrOptions;

  function __construct() {
    if (defined('_JEXEC')) {
	} else {
	  $this->fetchWoocommerceAttributes();
	}
  }
  
  function fetchWoocommerceAttributes() {
    global $wpdb;
    $attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
    $this->attrOptionsTableName = $wpdb->prefix . 'options';
    $sql = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
    $this->attributes = $wpdb->get_results($sql);
  }
  
  function fetchAttrOptions($attrVal) {
    global $wpdb;
    $sql = "SELECT option_value FROM " . $this->attrOptionsTableName . " WHERE option_name='" . $attrVal . "'";
    $this->attrOptions = $wpdb->get_results($sql);
  }

}

class FoundOptions {

  public $option_value = '';

  function __construct($service_name, $attribute) {
	$this->option_value = get_option($service_name . '_cp_' . $attribute);
  }

}

?>