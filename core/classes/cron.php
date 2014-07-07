<?php

	/********************************************************************
	Version 2.0
	Handle the cron items
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-12

	********************************************************************/

//Create a custom refresh_interval so that scheduled events will be able to display
//  in Cron job manager
function add_xml_refresh_interval() {
	$current_delay = get_option('cp_feed_delay');
	return array(
		'refresh_interval' => array('interval' => $current_delay, 'display' => 'XML refresh interval'),
	);
}

class PCPCron {

	public static function doSetup() {
		add_filter('cron_schedules', 'add_xml_refresh_interval');
		//Delete old (faulty) scheduled cron job from prior versions
		$next_refresh = wp_next_scheduled('the_name_of_my_custom_interval');
		if ($next_refresh)
			wp_unschedule_event($next_refresh, 'the_name_of_my_custom_interval');
		$next_refresh = wp_next_scheduled('purple_xml_updatefeeds_hook');
		if ($next_refresh)
			wp_unschedule_event($next_refresh, 'purple_xml_updatefeeds_hook');
	}

	public static function scheduleUpdate () {
		//Set the Cron job here. Params are (when, display, hook)
		$next_refresh = wp_next_scheduled('update_cartfeeds_hook');
		if (!$next_refresh )
			wp_schedule_event(time(), 'refresh_interval', 'update_cartfeeds_hook');
	}

}