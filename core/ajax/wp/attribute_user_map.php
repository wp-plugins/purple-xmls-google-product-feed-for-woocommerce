<?php

	/********************************************************************
	Version 2.0
		Save a change in attribute mappings. This one saves the mappings as an array of strings
		Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-11
	********************************************************************/

	require_once dirname(__FILE__) . '/../../../../../../wp-load.php';

	$map_string = get_option('cpf_attribute_user_map_' . $_POST['service_name']);

	if (strlen($map_string) == 0)
		$map = array();
	else {
		$map = json_decode($map_string);
		$map = get_object_vars($map);
	}

	$attr = $_POST['attribute'];
	$mapto = $_POST['mapto'];
	$map[$mapto] = $attr;

	if ($attr == '(Reset)') {
		$new_map = array();
		foreach($map as $index => $item)
			if ($index != $mapto)
				$new_map[$index] = $item;
		$map = $new_map;
	}

	update_option('cpf_attribute_user_map_' . $_POST['service_name'], json_encode($map));

?>