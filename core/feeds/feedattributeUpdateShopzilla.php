<?php

  /********************************************************************
  Version 2.0
    Update Attributes
	By: Keneto 2014-05-08

  ********************************************************************/

//Join attribute and corresponding remote attributes together(attributes mapping)

require_once 'basicAttributeUpdate.php';

class PattributeUpdateShopzillaFeed extends PattributeUpdate {

  function __construct() {
    $this->attribute_tag = 'shopzillaattr';
	$this->serviceName = 'Shopzilla';
  }

}

?>