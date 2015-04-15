<?php

/***********************************************************
  Plugin Name: Cart Product Feed
  Plugin URI: www.shoppingcartproductfeed.com
  Description: WooCommerce Shopping Cart Export :: <a target="_blank" href="http://shoppingcartproductfeed.com/tos/">How-To Click Here</a>
  Author: ShoppingCartProductFeed.com
  Version: 3.1.1.10
  Author URI: www.shoppingcartproductfeed.com
  Authors: Haris, Keneto (May2014)
  Note: The "core" folder is shared to the Joomla component.
	Changes to the core, especially /core/data, should be considered carefully
  Note: "purple" term exists from legacy plugin name. Classnames in "P" for the same reason
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
 ***********************************************************/

require_once dirname(__FILE__) . '/../../../wp-admin/includes/plugin.php';
$plugin_version_data = get_plugin_data( __FILE__ );
//current version: used to show version throughout plugin pages
define('FEED_PLUGIN_VERSION', $plugin_version_data[ 'Version' ] ); 
define('CPF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); //cart-product-feed/cart-product-feed.php

//functions to display cart-product-feed version and checks for updates
include_once ('cart-product-information.php');

//action hook for plugin activation
register_activation_hook( __FILE__, 'cart_product_activate_plugin' );
register_deactivation_hook( __FILE__, 'cart_product_deactivate_plugin' );

global $cp_feed_order, $cp_feed_order_reverse;

require_once 'core/classes/cron.php';
require_once 'core/data/feedfolders.php';

if (get_option('cp_feed_order_reverse') == '')
    add_option('cp_feed_order_reverse', false);

if (get_option('cp_feed_order') == '')
    add_option('cp_feed_order', "id");

if (get_option('cp_feed_delay') == '')
    add_option('cp_feed_delay', "43200");

if (get_option('cp_licensekey') == '')
    add_option('cp_licensekey', "none");

if (get_option('cp_localkey') == '')
    add_option('cp_localkey', "none");

//***********************************************************
// Basic Initialization
//***********************************************************

/* Due to AJAX, this function can no longer be called

add_action('init', 'init_cart_product_feed');

function init_cart_product_feed() {

	require_once 'cart-product-wpincludes.php'; //The rest of the required-files moved here

	global $message;

	// Check if form was posted and select task accordingly
	$dir = PFeedFolder::uploadRoot();
	if (!is_writable($dir)) {
		$message = $dir . ' should be writeable';
		return;
	}
	$dir = PFeedFolder::uploadFolder();
	if (!is_dir($dir)) {
		mkdir($dir);
	}
	if (!is_writable($dir)) {
		$message = "$dir should be writeable";
		return;
	}

	if (!isset($_REQUEST['RequestCode'])) {
		//No request means do nothing
		return;
	}

	$requestCode = $_REQUEST['RequestCode'];
	$providerFile = 'core/feeds/' . strtolower($requestCode) . '/feed.php';

	if (!file_exists(dirname(__FILE__) . '/' . $providerFile)) {
	  return;
	}

	require_once $providerFile;

	//Load form data
	$category = $_REQUEST['local_category'];
	$remote_category = $_REQUEST['remote_category'];
	$file_name = sanitize_title_with_dashes($_REQUEST['feed_filename']);
	if ($file_name == '') {
		$file_name = 'feed' . rand(10, 1000);
	}

	$providerClass = 'P' . $requestCode . 'Feed';
	$x = new $providerClass;
	$x->getFeedData($category, $remote_category, $file_name);

	//Some of the feeds need to exit() so the page doesn't forward
	if ($x->must_exit())
	  exit();
}
*/

//***********************************************************
// cron schedules for Feed Updates
//***********************************************************

PCPCron::doSetup();
PCPCron::scheduleUpdate();

//***********************************************************
// Update Feeds (Cron)
//   2014-05-09 Changed to now update all feeds... not just Google Feeds
//***********************************************************

add_action('update_cartfeeds_hook', 'update_all_cart_feeds');

function update_all_cart_feeds($doRegCheck = true) {

	require_once 'cart-product-wpincludes.php'; //The rest of the required-files moved here
	require_once 'core/data/savedfeed.php';

	$reg = new PLicense();
	if ($doRegCheck && ($reg->results["status"] != "Active"))
		return;

	do_action('load_cpf_modifiers');
	add_action( 'get_feed_main_hook', 'update_all_cart_feeds_step_2' );
	do_action('get_feed_main_hook');
}

function update_all_cart_feeds_step_2() {
	global $wpdb;
	$feed_table = $wpdb->prefix . 'cp_feeds';
	$sql = 'SELECT id, type, filename FROM ' . $feed_table;
	$feed_ids = $wpdb->get_results($sql);
	$savedProductList = null;

	//***********************************************************
	//Build stack of aggregate providers
	//***********************************************************
	$aggregateProviders = array();
	foreach ($feed_ids as $this_feed_id) {

		if ($this_feed_id->type == 'AggXml' || $this_feed_id->type == 'AggCsv' || $this_feed_id->type == 'AggTxt') {
			$providerName = $this_feed_id->type;
			$providerFile = 'core/feeds/' . strtolower($providerName) . '/feed.php';
			if (!file_exists(dirname(__FILE__) . '/' . $providerFile))
				continue;
			require_once $providerFile;

			//Initialize provider data
			$providerClass = 'P' . $providerName . 'Feed';
			$x = new $providerClass(null);
			$x->initializeAggregateFeed($this_feed_id->id, $this_feed_id->filename);
			$aggregateProviders[] = $x;
		};
	}

	//***********************************************************
	//Main
	//***********************************************************
	foreach ($feed_ids as $index => $this_feed_id) {

		$saved_feed = new PSavedFeed($this_feed_id->id);

		$providerName = $saved_feed->provider;

		//Skip any Aggregate Types
		if ($providerName == 'AggXml' || $providerName == 'AggCsv' || $providerName == 'AggTxt')
			continue;

		//Make sure someone exists in the core who can provide the feed
		$providerFile = 'core/feeds/' . strtolower($providerName) . '/feed.php';
		if (!file_exists(dirname(__FILE__) . '/' . $providerFile))
			continue;
		require_once $providerFile;

		//Initialize provider data
		$providerClass = 'P' . $providerName . 'Feed';
		$x = new $providerClass();
		$x->aggregateProviders = $aggregateProviders;
		$x->savedFeedID = $saved_feed->id;

		$x->productList = $savedProductList;
		$x->getFeedData($saved_feed->category_id, $saved_feed->remote_category, $saved_feed->filename, $saved_feed);
		
		$savedProductList = $x->productList;
		$x->products = null;

	}

	foreach ($aggregateProviders as $thisAggregateProvider)
		$thisAggregateProvider->finalizeAggregateFeed();

}

//***********************************************************
// Links From the Install Plugins Page (WordPress)
//***********************************************************

if (is_admin()) {

	require_once 'cart-product-feed-admin.php';
	$plugin = plugin_basename(__FILE__);
	add_filter("plugin_action_links_" . $plugin, 'cart_product_manage_feeds_link');

}

//***********************************************************
//Function to create feed generation link  in installed plugin page
//***********************************************************
function cart_product_manage_feeds_link($links) {

	$settings_link = '<a href="admin.php?page=cart-product-feed-manage-page">Manage Feeds</a>';
	array_unshift($links, $settings_link);
	return $links;

}