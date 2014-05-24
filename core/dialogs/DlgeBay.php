<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Nextag
	By: Keneto 2014-05-05

  ********************************************************************/

include_once 'basefeeddialogs.php';

class eBayDlg extends PBaseFeedDialog {

  function __construct() {
    parent::__construct();
	$this->form_attibutes_name = 'ebay_attribute_changes';
	$this->form_attibutes_id = 'ebay_attributes_form';
    $this->service_name = 'eBay';
    $this->service_name_long = 'eBay Products XML Export';
	$this->options = array(
        'UPC',
        'EAN',
        'MPN',
        'ISBN',
        'Coupon Code',
        'Coupon Code Description',
        'Manufacturer',
        'Top Seller Rank',
        'Estimated Ship Date',
        'Gender',
        'Color',
        'Material',
        'Size',
        'Size Unit of Measure',
        'Age Range',
        'Cell Phone Plan Type',
        'Cell Phone Service Provider',
        'Stock Description',
        'Product Launch Date',
        'Product Bullet Point 1',
        'Product Bullet Point 2',
        'Product Bullet Point 3',
        'Product Bullet Point 4',
        'Product Bullet Point 5',
        'Mobile URL',
        'Related Products',
        'Merchandising Type',
        'Zip Code',
        'Format',
        'Unit Price',
        'Bundle',
        'Software Platform',
        'Watch Display Type'
	);
  }

  function convert_option($option) {
    return str_replace(" ", "_", $option);
  }

}