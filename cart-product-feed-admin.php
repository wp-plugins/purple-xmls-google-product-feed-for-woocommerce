<?php

  /********************************************************************
  Version 1.1

  Modified: 2014-05-01 Now Product Categories can export to both XML and TXT (CSV or Tabbed)
    By: Keneto

  ********************************************************************/

require_once 'cart-product-setup.php';
require_once 'core/dialogs/licensekeydialogs.php';
include_once 'core/dialogs/feedpagedialogs.php';

// Hook for adding admin menus
add_action('admin_menu', 'cart_product_admin_menu');
add_action( 'admin_enqueue_scripts', 'register_cart_product_styles' );

function register_cart_product_styles() {
  wp_register_style('cart-product-style', plugins_url('css/cartproduct.css', __FILE__));
  wp_enqueue_style('cart-product-style');
  wp_enqueue_script( 'jquery' );
  wp_register_script('cart-product-script', plugins_url('js/cartproduct.js', __FILE__));
  wp_enqueue_script('cart-product-script');
}

// action function for above hook
function cart_product_admin_menu() {

    $icon_image = plugins_url('/', __FILE__) . "/images/xml-icon.png";
    // Add a new top-level menu :
    add_menu_page(__('CartProductFeed', 'cart-product-strings'), __('CartProductFeed', 'cart-product-strings'), 'manage_options', 'cart-product-feed-admin', 'cart_product_feed_admin_page', $icon_image);

	//Add two submenus
    add_submenu_page('cart-product-feed-admin', __('Create New Feed', 'cart-product-strings'), __('Create New Feed', 'cart-product-strings'), 'manage_options', 'cart-product-feed-admin', 'cart_product_feed_admin_page');
    add_submenu_page('cart-product-feed-admin', __('Manage Feeds', 'cart-product-strings'), __('Manage Feeds', 'cart-product-strings'), 'manage_options', 'cart-product-feed-manage-page', 'cart_product_feed_manage_page');

}

//**************************************************************
// Create New Feed Page
//**************************************************************

function cart_product_feed_admin_page() {

	$iconurl = plugins_url('/', __FILE__) . '/images/cp_feed32.png';
    echo "<div class='purplefeedspage wrap'>";
    echo '<div id="icon-purple_feed" class="icon32" style="background: transparent url(' . $iconurl . ') no-repeat">
        <br />
    </div><h2>Cart Product Feed</h2><br />';
    $message2 = NULL;
    $icon_image2 = plugins_url('/', __FILE__) . "/images/BuyLicenseButton.png";
    // check for updating feed delay ID
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action == "update_license") {
            if (isset($_POST['license_key'])) {
                $licence_key = $_POST['license_key'];
                if ($licence_key != "") {
                    update_option('cp_licensekey', $licence_key);
                }
            }
        }
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
        if ($action == "reset_attributes") {
            global $wpdb, $woocommerce;
            $attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
            $sql = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
            $attributes = $wpdb->get_results($sql);
            foreach ($attributes as $attr) {
                delete_option($attr->attribute_name);
            }
        } elseif ($action == "reset_nextag_attributes") {
            global $wpdb, $woocommerce;
            $attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
            $sql = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
            $attributes = $wpdb->get_results($sql);
            foreach ($attributes as $attr) {
                delete_option("nextag_pa_" . $attr->attribute_name);
            }
        }
        echo "<script> window.location.assign('" . admin_url() . "admin.php?page=cart-product-feed-admin');</script>";
    }

  # Get Variables from storage (retrieve from wherever it's stored - DB, file, etc...)

	$reg = new PLicense();

    //Main content
	echo '
	<script type="text/javascript">
	jQuery(document).ready(function($) {
	   ajaxhost = "' . plugins_url('/', __FILE__) . '";
	   jQuery("#selectFeedType").val("&nbsp;");
	});
	</script>';

	//WordPress Header (May contain a message)
	global $message;
	if ($message != "") {
	echo '<div id="setting-error-settings_updated" class="updated settings-error"> 
		  <p><strong>' . $message . '</strong></p></div>';
	}

	//Page Header
	echo PFeedPageDialogs::pageHeader();
	//Page Body
	echo PFeedPageDialogs::pageBody();

	if (!$reg->valid) {
	  //echo PLicenseKeyDialog::large_registration_dialog('');
	}

}

//**************************************************************
// Displays the Manage Feed Page
//**************************************************************

function cart_product_feed_manage_page() {

  $reg = new PLicense();
     
  require_once 'cart-product-manage-feeds.php';

  if (!$reg->valid) {
	 //echo PLicenseKeyDialog::large_registration_dialog('');
  }
}



?>