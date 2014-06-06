<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for GoogleFeed
	By: Keneto 2014-05-05

  ********************************************************************/

include_once 'basefeeddialogs.php';

class GoogleDlg extends PBaseFeedDialog {

  function __construct() {
    parent::__construct();
    $this->service_name = 'Google';
    $this->service_name_long = 'Google Products XML Export';
	$this->options = array('g:brand', 'g:GTIN', 'g:identifier exists', 'g:gender', 'g:age_group', 'g:color', 'g:size', 'g:material', 'g:pattern',
    'g:sale_price_effective_date', 'g:tax', 'g:multipack', 'g:adult', 'g:adwords_grouping', 'g:adwords_labels', 'g:adwords_redirect',
	'g:unit_pricing_measure', 'g:unit_pricing_base_measure', 'g:energy_efficiency_class', 'g:excluded_destination', 'g:expiration_date');
  }

  function convert_option($option) {
    return strtolower(str_replace(" ", "_", $option));
  }

}

?>