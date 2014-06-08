<?php

  /********************************************************************
  Version 2.0
    Update all the Feeds at once instead of having to wait for a Cron job
	By: Keneto 2014-05-23

  ********************************************************************/

  require_once '../../../../../wp-load.php';

  global $wpdb;
  $providerName = $_POST['service_name'];

  $sql = "
			SELECT * FROM $wpdb->options
			WHERE $wpdb->options.option_name LIKE '" . $providerName . "_cp_%'";
  $mappings = $wpdb->get_results($sql);
  foreach($mappings as $this_option) {
	delete_option($this_option->option_name);
  }



?>