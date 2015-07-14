<?php
require_once 'core/classes/cron.php';

//callback function
function cart_product_activate_plugin(){

	global $wpdb;

	$table_name = $wpdb->prefix . "cp_feeds";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "
			CREATE TABLE $table_name (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`category` varchar(250) NOT NULL,
			`remote_category` varchar(1000) NOT NULL,
			`filename` varchar(250) NOT NULL,
			`url` varchar(500) NOT NULL,
			`type` varchar(50) NOT NULL DEFAULT 'google',
			`own_overrides` int(10),
			`feed_overrides` text,
			`product_count` int,
			`feed_errors` text,
			`feed_title` varchar(250),
			PRIMARY KEY (`id`)
		)";
		//)" ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
		$wpdb->query( $sql );
	}

	$table_name = $wpdb->prefix . "cp_feeds";
	$sql = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		category varchar(250) NOT NULL,
		remote_category varchar(1000) NOT NULL,
		filename varchar(250) NOT NULL,
		url varchar(500) NOT NULL,
		type varchar(50) NOT NULL,
		own_overrides int(10),
		feed_overrides text,
		product_count int,
		feed_errors text,
		feed_title varchar(250),
		PRIMARY KEY  (id)
	)";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	/* Not IMPL: Using wp_options
	$table_name = $wpdb->prefix . "cp_feed_options";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "
			CREATE TABLE $table_name (
			id int AUTO_INCREMENT,
			name varchar(255),
			value text,
			kind int,
			PRIMARY KEY (`id`)
		)";
		$wpdb->query( $sql );
	}*/

}

function cart_product_deactivate_plugin() {

	$next_refresh = wp_next_scheduled('update_cartfeeds_hook');
	if ($next_refresh )
		wp_unschedule_event($next_refresh, 'update_cartfeeds_hook');

}