<?php

  /********************************************************************
  Version 2.0
    AJAX script updates a setting
	By: Keneto 2014-05-09

  ********************************************************************/
  
  if (!isset($_POST['setting']) || !isset($_POST['value'])) {
    echo 'Error in setting';
	return;
  }
  
  $setting = $_POST['setting'];
  $value = $_POST['value'];

  require_once '../../../../../wp-load.php';
  require_once 'cron.php';
  
  //Don't update here - security issue would allow any option to be updated
  //Only update within an if()
  
  if ($setting == 'cp_feed_delay') {
  
    update_option($setting, $value);
	
    //Is this event scheduled?
	$next_refresh = wp_next_scheduled('update_cartfeeds_hook');
	if ($next_refresh ) {
	  wp_unschedule_event($next_refresh, 'update_cartfeeds_hook');
	}
	wp_schedule_event(time(), 'refresh_interval', 'update_cartfeeds_hook');
  }
  
  echo 'Updated.';


?>