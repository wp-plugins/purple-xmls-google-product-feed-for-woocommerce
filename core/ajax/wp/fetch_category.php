<?php

	/********************************************************************
	Version 2.0
		Go get the category
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-18
	********************************************************************/

	define ('XMLRPC_REQUEST', true);

	require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
	require_once dirname(__FILE__) . '/../../data/feedcore.php';

	$service_name = $_POST['service_name'];

	$data = '';
	if (class_exists('CPF_Taxonomy'))
		$data = CPF_Taxonomy::onLoadTaxonomy(strtolower($_POST['service_name']));

	if (strlen($data) == 0)
		$data = file_get_contents(dirname(__FILE__) . '/../../feeds/' . strtolower($service_name) . '/categories.txt');

	$data = explode("\n", $data);
	$searchTerm = strtolower($_POST['partial_data']);
	$count = 0;
	$canDisplay = true;
	foreach($data as $this_item) {

		if (strlen($this_item) * strlen($searchTerm) == 0)
			continue;

		if (strpos(strtolower($this_item), $searchTerm) !== false) {

			//Transform item from chicken-scratch into something the system can recognize later
			$option = str_replace(" & ", ".and.", str_replace(" / ", ".in.", trim($this_item)));
			$option = str_replace("'", '', $option);

			//Transform a category from chicken-scratch into something the user can read
			$text = htmlentities(trim($this_item));

			if ($canDisplay)
				echo '<div class="categoryItem" onclick="doSelectCategory(this, \'' . $option . '\', \'' . $service_name . '\')">' . $text . '</div>';
			$count++;
			if ((strlen($searchTerm) < 3) && ($count > 15))
				$canDisplay = false;

		}
	}

	if ($count == 0) {
		//echo 'No matching categories found';
	}

	if (!$canDisplay)
		echo '<div class="categoryItem">(' . $count . ' results)</div>';

?>