<?php

  /********************************************************************
  Version 1.2
		Modified: 2014-05-01 Now Product Categories can export to both XML and TXT ( CSV or Tabbed )
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
    By: Keneto
  ********************************************************************/


/**
 * Required admin files
 *
 */
require_once 'cart-product-setup.php';

/**
 * Hooks for adding admin specific styles and scripts
 *
 */
function register_cart_product_styles_and_scripts() 
{
	wp_register_style( 'cart-product-style', plugins_url( 'css/cart-product_v3.60.css', __FILE__ ) );
	wp_enqueue_style( 'cart-product-style' );

	wp_register_style( 'cart-product-colorstyle', plugins_url( 'css/colorbox.css', __FILE__ ) );
	wp_enqueue_style( 'cart-product-colorstyle' );

	wp_enqueue_script( 'jquery' );

	wp_register_script( 'cart-product-script', plugins_url( 'js/cart-product.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'cart-product-script' );

	wp_register_script( 'cart-product-colorbox', plugins_url( 'js/jquery.colorbox-min.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'cart-product-colorbox' );

}
add_action( 'admin_enqueue_scripts', 'register_cart_product_styles_and_scripts' );

/**
 * Add menu items to the admin
 *
 */
function cart_product_admin_menu() {

    /* add new top level */
    add_menu_page(
		__( 'CartProductFeed', 'cart-product-strings' ),
		__( 'CartProductFeed', 'cart-product-strings' ),
		'manage_options',
		'cart-product-feed-admin',
		'cart_product_feed_admin_page',
		plugins_url( '/', __FILE__ ) . '/images/xml-icon.png'
	);

	/* add the submenus */
    add_submenu_page(
		'cart-product-feed-admin',
		__( 'Create New Feed', 'cart-product-strings' ),
		__( 'Create New Feed', 'cart-product-strings' ),
		'manage_options',
		'cart-product-feed-admin',
		'cart_product_feed_admin_page'
	);

    add_submenu_page(
		'cart-product-feed-admin',
		__( 'Manage Feeds', 'cart-product-strings' ),
		__( 'Manage Feeds', 'cart-product-strings' ),
		'manage_options',
		'cart-product-feed-manage-page',
		'cart_product_feed_manage_page'
	);

}

add_action( 'admin_menu', 'cart_product_admin_menu' );
add_action( 'cpf_init_pageview', 'cart_product_feed_admin_page_action' );

function cart_product_feed_admin_page() {

	require_once 'cart-product-wpincludes.php';
	require_once 'core/classes/dialoglicensekey.php';
	include_once 'core/classes/dialogfeedpage.php';
	require_once 'core/feeds/basicfeed.php';

	global $pfcore;
	$pfcore->trigger('cpf_init_feeds');

	do_action('cpf_init_pageview');
}

//include_once('cart-product-version-check.php');
/**
 * Create news feed page
 */
function cart_product_feed_admin_page_action() 
{


	echo 	"<div class='wrap'>";
	//echo 	"<div class='cpf-header'>";
	echo 	'<h2>Create Product Feed';
	$url = site_url() . '/wp-admin/admin.php?page=cart-product-feed-manage-page';
    echo '<input style="margin-top:12px;" type="button" class="add-new-h2" onclick="document.location=\'' . $url . '\';" value="' . __( 'Manage Feeds', 'cart-product-strings' ) . '" />
    </h2>';
	//echo    '</div>';
	//prints logo/links header info: also version number/check
	CPF_print_info();
	$action = '';
	$source_feed_id = -1;

	$message2 = NULL;
	$icon_image2 = plugins_url( '/', __FILE__ ) . "/images/BuyLicenseButton.png";
	
	//check action
	if ( isset( $_POST['action'] ) )
		$action = $_POST['action'];
	if ( isset( $_GET['action'] ) )
		$action = $_GET['action'];
				
	switch ($action) {
		case 'update_license':
			//I think this is AJAX only now -K
			//No... it is still used (2014/08/25) -K
			if ( isset( $_POST['license_key'] ) ) {
				$licence_key = $_POST['license_key'];
				if ( $licence_key != '' )
					update_option( 'cp_licensekey', $licence_key );
			}
			break;
		case 'reset_attributes':
			//I don't think this is used -K
			global $wpdb, $woocommerce;
			$attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
			$sql = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
			$attributes = $wpdb->get_results( $sql );
			foreach ( $attributes as $attr )
				delete_option( $attr->attribute_name );
			break;
		case 'edit':
			$action = '';
			$source_feed_id = $_GET['id'];
			break;
	}

	if (isset($action) && (strlen($action) > 0) )
		echo "<script> window.location.assign( '" . admin_url() . "admin.php?page=cart-product-feed-admin' );</script>";

	if (isset( $_GET['debug'])) {
		$debug = $_GET['debug'];
		if ($debug == 'phpinfo') {
			phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES);
			return;
		}
		if ($debug == 'reg') {
			echo "<pre>\r\n";
			new PLicense(true);
			echo "</pre>\r\n";
		}
	}

  # Get Variables from storage ( retrieve from wherever it's stored - DB, file, etc... )

	$reg = new PLicense();

	//Main content
	echo '
	<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		ajaxhost = "' . plugins_url( '/', __FILE__ ) . '";
		jQuery( "#selectFeedType" ).val( "&nbsp;" );
		doFetchLocalCategories();
		feed_id = ' . $source_feed_id . ';
	} );
	</script>';

	//WordPress Header ( May contain a message )

	global $message;
	if ( strlen($message) > 0 && strlen($reg->error_message) > 0 )
		$message .= '<br>'; //insert break after local message (if present)
	$message .= $reg->error_message;
	if ( strlen($message) > 0 ) 
	{
		//echo '<div id="setting-error-settings_updated" class="error settings-error">'
		echo '<div id="setting-error-settings_updated" class="updated settings-error">
			  <p>' . $message . '</p>
			  </div>';
	}

	if ($source_feed_id == -1) {
		//Page Header
		echo PFeedPageDialogs::pageHeader();
		//Page Body
		echo PFeedPageDialogs::pageBody();
	} else {
		require_once dirname(__FILE__) . '/core/classes/dialogeditfeed.php';
		echo PEditFeedDialog::pageBody($source_feed_id);
	}

	if ( !$reg->valid ) {
	  //echo PLicenseKeyDialog::large_registration_dialog( '' );
	}

}

/**
 * Display the manage feed page
 *
 */

add_action( 'cpf_init_pageview_manage', 'cart_product_feed_manage_page_action' );

function cart_product_feed_manage_page() {

	require_once 'cart-product-wpincludes.php';
	require_once 'core/classes/dialoglicensekey.php';
	include_once 'core/classes/dialogfeedpage.php';

	global $pfcore;
	$pfcore->trigger('cpf_init_feeds');

	do_action('cpf_init_pageview_manage');

}

function cart_product_feed_manage_page_action() {

	$reg = new PLicense();

	require_once 'cart-product-manage-feeds.php';

	//if ( !$reg->valid )
		//echo PLicenseKeyDialog::large_registration_dialog( '' );

}
