<?php

  /********************************************************************
  Version 2.0
    This gets a little complex. See design-document -> ProductCategoryExport
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-14
  Note: One day, this needs to be moved to Joomla/VirtueMart compatibility

  ********************************************************************/

class PAProduct {

	public $id = 0;
	public $title = '';
	public $taxonomy = '';
	public $imgurls;
	public $attributes;

	function __construct() {
		$this->imgurls = array();
		$this->attributes = array();
	}

	
}

class PProductEntry {
  public $taxonomyName;
  public $ProductID;
  public $Attributes;
  
  function __construct() {
    $this->Attributes = array();
  }
  
  function GetAttributeList(){
    $result = '';
	foreach($this->Attributes as $ThisAttribute) {
	  $result .= $ThisAttribute . ', ';
	}
	return '['. $this->Name . '] ' . substr($result, 0, -2);
  }
}

global $pfcore;
$productListScript = 'productlist' . strtolower($pfcore->callSuffix) . '.php';
require_once $productListScript;