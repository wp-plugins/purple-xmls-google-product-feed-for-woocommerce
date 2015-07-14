<?php

  /********************************************************************
  Version 2.0
		Erase Attribute Mappings that may be hidden away in the options table
		because they were removed from Woocommerce before being removed from
		the plugin. ONE DAY these options probably need to be given their own
		table that the user can edit
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-23
  2014-06-08 feedcore now loads wp-load.php and handles other init tasks
  ********************************************************************/

  require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
  require_once dirname(__FILE__) . '/../../data/feedcore.php';

  global $wpdb;
  $providerName = $_POST['service_name'];

  $sql = $wpdb->prepare("
			SELECT * FROM $wpdb->options
			WHERE $wpdb->options.option_name LIKE '%s'", like_escape($providerName) . '_cp_%');

  $mappings = $wpdb->get_results($sql);
  foreach($mappings as $this_option)
		delete_option($this_option->option_name);

  echo "1";


?>