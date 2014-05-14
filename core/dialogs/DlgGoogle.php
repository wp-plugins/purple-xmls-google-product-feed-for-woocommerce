<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for GoogleFeed
	By: Keneto 2014-05-05

  ********************************************************************/

include_once 'basefeeddialogs.php';
include_once '../classes/attributesfound.php';
require_once '../../../../../wp-load.php';

class GoogleDlg extends PBaseFeedDialog {
  
  function __construct() {
    parent::__construct();
	$this->form_attibutes_name = 'attribute_changes';
	$this->form_attibutes_id = 'google_attributes_form';
	$this->form_options_id = 'googleattr';
    $this->service_name = 'Google';
    $this->service_name_long = 'Google Products XML';
	$this->options = array('brand', 'GTIN', 'identifier exists', 'gender', 'age group', 'color', 'size', 'material', 'pattern', 
    'sale price effective date', 'Tax', 'multipack', 'adult', 'Adwords grouping', 'Adwords labels', 'Adwords redirect',
	'unit pricing measure', 'unit pricing base measure', 'energy efficiency class', 'excluded destination', 'expiration date');
  }
  
  function convert_option($option) {
    return strtolower(str_replace(" ", "_", $option));
  }

}

?>