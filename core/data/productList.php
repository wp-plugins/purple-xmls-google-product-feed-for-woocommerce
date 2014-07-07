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
  
  function fetch_attributes(){
  
    global $wpdb;
	
	//For non-variant products, get the attributes
	$sql = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name as Attributes
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.ID = " . $this->id . "
		AND $wpdb->term_taxonomy.taxonomy LIKE 'pa_%' ";
	$childlist = $wpdb->get_results($sql);
	
	foreach($childlist as $a){
	  $this->attributes[substr($a->taxonomy, 3)] = $a->Attributes;
	}

  }
  
  function fetch_meta_attributes(){
  
    global $wpdb;
	
	//********************************************************************
	//wp_post_meta provides some attributes in the form of custom fields
	//********************************************************************
	$sql = "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=" . $this->id;
	$meta = $wpdb->get_results($sql);
	foreach($meta as $this_meta) {
	  if ((strlen($this_meta->meta_key) > 0) && (substr($this_meta->meta_key, 0, 1) != '_')) {
	    switch (strtolower($this_meta->meta_key)) {
		  case 'tax':
		    $this->tax = $this_meta->meta_value;
			break;
		  case 'vat':
		    $this->vat = $this_meta->meta_value;
			break;
		  case 'gst':
		    $this->gst = $this_meta->meta_value;
			break;
		  case 'total_sales':
		    break;
		  default:
	        $this->attributes[$this_meta->meta_key] = $this_meta->meta_value;
		}
	  }
	}
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