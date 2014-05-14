<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Nextag
	By: Keneto 2014-05-05

  ********************************************************************/

include_once 'basefeeddialogs.php';
include_once '../classes/attributesfound.php';
require_once '../../../../../wp-load.php';

class NextagDlg extends PBaseFeedDialog {

  function __construct() {
    parent::__construct();
	$this->form_attibutes_name = 'nextag_attribute_changes';
	$this->form_attibutes_id = 'nextag_attributes_form';
    $this->service_name = 'Nextag';
    $this->service_name_long = 'Nextag Products XML';
	$this->options = array(
        'Category: PriceGrabber Format',
        'Category: Shopping.com Format',
        'Category: Shopzilla Numeric ID',
        'Manufacturer',
        'Manufacturer Part #',
        'Marketing Message',
        'Cost-per-Click',
        'UPC',
        'Distributor ID',
        'MUZE ID',
        'Muze Prelrefnum',
        'ISBN',
        'Techdata Part #',
        'Ingram Part #',
        'Standard Marketing Message Bid',
        'Enhanced Marketing Message Bid',
        'Premium Marketing Message Bid',
        'Top Placement Bid',
        'Promo Type',
        'Promo Text',
        'Promo Text Start',
        'Promo Text End',
        'unit_pricing_base_measure',
        'Promo Discount Amount',
        'Promo Discount Percent',
        'List Price Start',
        'List Price End',
        'Promo Discount Is Exclusive',
        'Promo Free Shipping',
        'Promo Free Shipping Start',
        'Promo Free Shipping End',
        'Promo Free Shipping Is Exclusive',
        'Coupon Code Text',
        'Coupon URL Text',
        'Coupon Code Free Shipping',
        'Coupon URL Free Shipping',
        'Coupon Code Discount',
        'Coupon URL Discount',
        'Promo Tax Discount',
        'Promo Tax Discount Start',
        'Promo Tax Discount End',
        'Coupon Code Tax Discount',
        'Coupon Code URL Discount',
        'Promo Tax Discount Is Exclusive',
        'Promo Rebate List Price',
        'Promo Rebate Discount Amount',
        'Promo Rebate Discount Start',
        'Promo Rebate Discount End',
        'Coupon Code Rebate Discount',
        'Coupon Rebate URL Discount',
        'Promo Rebate Discount Is Exclusive');
  }
  
}