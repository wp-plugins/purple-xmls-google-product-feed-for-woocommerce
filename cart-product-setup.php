<?php

require_once 'core/classes/cron.php';

//callback function
function cart_product_activate_plugin(){
global $wpdb;
$table_name = $wpdb->prefix . "cp_feeds";
if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	$sql = "CREATE TABLE $table_name (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`category` varchar(250) NOT NULL,
	`remote_category` varchar(1000) NOT NULL,
	`filename` varchar(250) NOT NULL,
	`url` varchar(500) NOT NULL,
	`type` varchar(50) NOT NULL DEFAULT 'google',
	PRIMARY KEY (`id`)
	)";
	//)" ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
	$wpdb->query( $sql );
  }
}

function cart_product_deactivate_plugin(){
  $next_refresh = wp_next_scheduled('update_cartfeeds_hook');
  if ($next_refresh ) {
	wp_unschedule_event($next_refresh, 'update_cartfeeds_hook');
  }
}