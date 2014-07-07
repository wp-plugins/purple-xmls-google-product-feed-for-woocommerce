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

  require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
  require_once dirname(__FILE__) . '/../../classes/cron.php';

  //Don't update here - security issue would allow any option to be updated
  //Only update within an if()

  if ($setting == 'cp_feed_delay') {

		update_option($setting, $value);

		//Is this event scheduled?
		$next_refresh = wp_next_scheduled('update_cartfeeds_hook');
		if ($next_refresh )
			wp_unschedule_event($next_refresh, 'update_cartfeeds_hook');
		wp_schedule_event(time(), 'refresh_interval', 'update_cartfeeds_hook');
  }

  //Some PHPs don't return the post correctly when it's long data
  if (strlen($setting) == 0) {
		$setting = substr(file_get_contents("php://input"), 8);
		$indexOfAmp = strpos($setting, '&');
		if ($indexOfAmp !== false)
			$setting = substr($setting, 0, $indexOfAmp);
  }

  if (strpos($setting, 'cp_advancedFeedSetting') !== false) {
  
		//$value may get truncated on an & because $_POST can't parse
		//so pull value manually
		$postdata = file_get_contents("php://input");
		$i = strpos($postdata, '&');
		if ($i !== false)
			$postdata = substr($postdata, $i + 7);

		//Strip the provider name out of the setting
		$target = substr($setting, strpos($setting, '-') + 1);

		//Save new advanced setting
		update_option($target . '-cart-product-settings', $postdata);
  }

  echo 'Updated.';


?>