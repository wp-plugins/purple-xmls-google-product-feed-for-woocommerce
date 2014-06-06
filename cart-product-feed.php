<?php

/***********************************************************
  Plugin Name: Cart Product Feed
  Plugin URI: http://www.w3bdesign.ca/
  Description: Product XML Data Feeds for WooCommerce :: Update permalinks after activating/deactivating the plugin :: <a href="http://www.youtube.com/watch?v=9VVipmBI4rk">Instruction Video</a>
  Author: Purple Turtle Productions
  Version: 3.0.1.22
  Author URI: http://www.w3bdesign.ca/
  Authors: Haris, Keneto (May2014)
  Note: Unversioned files arrived before me (Keneto). I've assigned them a version number of 1.0
    When I move the code closer to a class-based design, I assign higher version numbers until
	I hit 2.x which will be entirely class-based
  Note: The "core" folder is to be shared to the (future) Joomla component.
	This should be factored in when designing... making a CMS/shopping-system-independent core
  Note: "purple" term exists from legacy plugin name. Classnames in "P" for the same reason
 ***********************************************************/

//action hook for plugin activation
register_activation_hook(__FILE__, 'cart_product_activate_plugin');
register_deactivation_hook(__FILE__, 'cart_product_deactivate_plugin');
global $cp_feed_order, $cp_feed_order_reverse;

require_once 'cart-product-functions.php';
require_once 'core/classes/cron.php';
require_once 'core/data/feedFolders.php';
require_once 'core/registration.php';

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

add_action('init', 'init_cart_product_feed');

function init_cart_product_feed() {

	require_once 'core/classes/md5.php';
	require_once 'core/data/feedCategories.php';
	require_once 'core/data/productList.php';
	require_once 'core/data/feedOverrides.php';

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
	$providerFile = 'core/feeds/feed' . $requestCode . '.php';

	if (!file_exists(dirname(__FILE__) . '/' . $providerFile)) {
	  return;
	}

	require_once $providerFile;

	$providerClass = 'P' . $requestCode . 'Feed';
	$x = new $providerClass;
	$x->getFeedData();

	//Some of the feeds need to exit() so the page doesn't forward
	if ($x->must_exit())
	  exit();
}

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

function update_all_cart_feeds() {

	require_once 'core/classes/md5.php';
	require_once 'core/data/feedCategories.php';
	require_once 'core/data/feedOverrides.php';
	require_once 'core/data/productList.php';

    # Get Variables from storage (retrieve from wherever it's stored - DB, file, etc...)

	$reg = new PLicense();
    if ($reg->results["status"] == "Active") {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'cp_feeds';
		$sql = 'SELECT * FROM ' . $feed_table;
        $feed_settings = $wpdb->get_results($sql);

        foreach ($feed_settings as $feed_setting) {

			//Make sure someone exists in the core who can provide the feed
			$providerName = $feed_setting->type;
			$providerFile = 'core/feeds/feed' . $providerName . '.php';
			if (!file_exists(dirname(__FILE__) . '/' . $providerFile)) {
			  continue;
			}
			require_once $providerFile;

			//Initialize provider data
			$category = $feed_setting->category;
			$google_category = $feed_setting->remote_category;

			$providerClass = 'P' . $providerName . 'Feed';
			$x = new $providerClass;
			$x->updateFeed($feed_setting->category, $feed_setting->remote_category, $feed_setting->filename);

        }
    }
}

//***********************************************************
// Links From the Install Plugins Page (WordPress)
//***********************************************************

if (defined('WP_ADMIN')) {

    require_once 'cart-product-feed-admin.php';
    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_" . $plugin, 'cart_product_manage_feeds_link');
}

//Function to create feed generation link  in installed plugin page
function cart_product_manage_feeds_link($links) {
    $settings_link = '<a href="admin.php?page=cart-product-feed-manage-page">Manage Feeds</a>';
    array_unshift($links, $settings_link);
    return $links;
}