<?php

	/********************************************************************
	Version 2.0
		List of local categories
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-15
	********************************************************************/

	define ('XMLRPC_REQUEST', true);
	ob_start(null);

	require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
	require_once dirname(__FILE__) . '/../../data/feedcore.php';
	require_once dirname(__FILE__) . '/../../data/productcategories.php';

	ob_clean();

	$categoryList = new PProductCategories();
	$result = new stdClass();
	$result->children = array();

	foreach($categoryList->categories as $this_category)
		if (!isset($this_category->parent_category))
			process_category($result->children, $this_category);
	
	echo json_encode($result);

	function process_category(&$target_list, $this_category) {
		$new_category = new stdClass();
		$new_category->id = $this_category->id;
		$new_category->title = $this_category->title;
		$new_category->tally = $this_category->tally;
		$new_category->children = array();
		$target_list[] = $new_category;
		foreach($this_category->children as $child)
			process_category($new_category->children, $child);
	}

?>