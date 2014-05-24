<?php

  /********************************************************************
  Version 2.0
    List the categories the user wants to export
	By: Keneto 2014-05-14

  ********************************************************************/

class PCategoryList {

  function __construct($category_id) {
  
    global $wpdb;
  
    $this->items = array();
	
	//Case of user wants specific category
	if (($category_id != "") && ($category_id > 0)) {
	  $this->items[] = $category_id;
	  return;
	}

	//Case of user wants all categories
	$term_table = $wpdb->prefix . 'terms';
	$taxo_table = $wpdb->prefix . 'term_taxonomy';
	$sql_cat = "SELECT taxo.term_id, taxo.parent, taxo.count, term.name FROM " . $taxo_table . " taxo 
			LEFT JOIN " . $term_table . " term ON taxo.term_id = term.term_id 
			WHERE taxo.taxonomy = 'product_cat'";

	$categories = $wpdb->get_results($sql_cat);

	foreach ($categories as $this_category) {
		$this->items[] = $this_category->term_id;
	}
  }

}

?>