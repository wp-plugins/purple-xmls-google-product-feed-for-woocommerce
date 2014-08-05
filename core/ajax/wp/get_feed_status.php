<?php

	/********************************************************************
	Version 2.0
		Get a feed's generation Status
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-07-02
	********************************************************************/

	require_once dirname(__FILE__) . '/../../../../../../wp-load.php';

	function safeGetPostData($index) {
		if (isset($_POST[$index]))
			return $_POST[$index];
		else
			return '';
	}

	$feedIdentifier = safeGetPostData('feed_identifier');
	
  echo get_option('cp_feedActivity_' . $feedIdentifier);

?>