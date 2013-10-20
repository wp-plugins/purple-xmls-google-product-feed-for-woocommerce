<?php
/*
  Plugin Name: Google XML Product Feed for WooCommerce
Plugin URI: http://www.purpleturtle.pro/
  Description: Update permalinks after activating/deactivating the plugin :: <a href="/wp-admin/options-general.php?page=purple-feeds-xmls">Settings</a> :: <b><a href="">Get Pro Version</a></b> - Includes Attribute and Variable Product Support + Easy to use Interface <img style="border:1px #ccc solid;" src="http://www.purpleturtle.pro/wp-content/uploads/2013/07/screenshot-e1373150699517.png" />
  Author: Purple Turtle Productions
  Version: 1.2
  Author URI: http://www.purpleturtle.pro/

 */
global $logger, $purplexml_plugin_dir, $purplexml_plugin_url;

$purplexml_plugin_dir = WP_PLUGIN_DIR . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__));
$purplexml_plugin_url = plugins_url() . "/purple-xmls/";

/**
 * Function to create xml file accourding to user selection
 */
function purple_xmls_init() {
    // Check if form was posted and select task accordingly
    if (isset($_REQUEST['purplexmls'])) {
        $purplexmls = $_REQUEST['purplexmls'];
        switch ($purplexmls) {
            case 'google':
                $inc = 'google-feeds.php';
                include_once $inc;
                $category = $_REQUEST['category'];
                $google_category = $_REQUEST['google_category'];
                $brand_name = $_REQUEST['brand_name'];

                // Generate xml file for download
                purple_feeds_page_getGoogleFeeds($category, $google_category, $brand_name);
//                return call_user_func('purple_feeds_page_getGoogleFeeds');
                break;
            case 'categories':
                $inc = 'categories-feeds.php';
                include_once $inc;
                // Generate xml file for download
                purple_feeds_page_getCategoriesFeeds();
                break;
            case 'category_products':
                $category = $_REQUEST['category'];
                $inc = 'categories-products-feeds.php';
                include_once $inc;
                // Generate xml file for download
                purple_feeds_page_getCategoriesProductsFeeds($category);
                break;
            default: $inc = '';
                break;
        }

        if ($inc != "") {
            exit();
        }
    }
}

add_action('init', 'purple_xmls_init');
// Add shortcode
//add_shortcode("purplexmls", "purple_xmls_init");

if (is_admin()) {
    add_action('admin_menu', 'setup_purple_feeds_admin_menu');

    $plugin = plugin_basename(__FILE__);

    add_filter("plugin_action_links_" . $plugin, 'purple_xmls_add_settings_link');

    /**
     * Function to create settings link  in installed plugin page
     */
    function purple_xmls_add_settings_link($links) {

        $settings_link = '<a href="options-general.php?page=purple-feeds-xmls">Settings</a>';

        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Function to create a menu for plugin under setting tab
     */
    function setup_purple_feeds_admin_menu() {

        add_submenu_page('options-general.php', 'Purple XMLs', 'Purple XMLs', 'manage_options', 'purple-feeds-xmls', 'purple_feeds_page_settings');
    }

    /**
     * Function to create xml file accourding to user selection
     */
    function purple_xmls_admin_init() {
        // Check if form was posted and select task accordingly
        if (isset($_REQUEST['purplexmls'])) {
            $purplexmls = $_REQUEST['purplexmls'];
            switch ($purplexmls) {
                case 'google':
                    $inc = 'google-feeds.php';
                    include_once $inc;
                    $category = $_REQUEST['category'];
                    $google_category = $_REQUEST['google_category'];
                    $brand_name = $_REQUEST['brand_name'];

                    // Generate xml file for download
                    purple_feeds_page_getGoogleFeeds($category, $google_category, $brand_name);
                    break;
                case 'categories':
                    $inc = 'categories-feeds.php';
                    include_once $inc;
                    // Generate xml file for download
                    purple_feeds_page_getCategoriesFeeds();
                    break;
                case 'category_products':
                    $category = $_REQUEST['category'];
                    $inc = 'categories-products-feeds.php';
                    include_once $inc;
                    // Generate xml file for download
                    purple_feeds_page_getCategoriesProductsFeeds($category);
                    break;
                default: $inc = '';
                    break;
            }

            if ($inc != "") {
                exit();
            }
        }
    }

    add_action('admin_init', 'purple_xmls_admin_init');

    function get_content($URL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $URL);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}

/**
 * Function to list google categories
 */
function listGoogleCategories() {
    $data = get_content('http://www.google.com/basepages/producttype/taxonomy.en-US.txt');

    $arr = explode("\n", $data);
    $key = 0;
    $result = NULL;
    foreach ($arr as $k => $value) {
        if ($value == '# Google_Product_Taxonomy_Version: 2013-01-17') {
            $value = '--- Select Google Category ---';
        }
        $result .= "<option value='" . str_replace("&", "and", str_replace(">", "-", $value)) . "'>" . htmlentities($value) . "</option>";
    }
    return $result;
}

/**
 * Function to list product categoriesD
 */
function listProductCats() {

    global $wpdb;

    $term_table = $wpdb->prefix . 'terms';

    $taxo_table = $wpdb->prefix . 'term_taxonomy';

    $sql = "SELECT taxo.term_id, taxo.parent, taxo.count, term.name FROM " . $taxo_table . " taxo 

    		LEFT JOIN " . $term_table . " term ON taxo.term_id = term.term_id 

    		WHERE taxo.taxonomy = 'product_cat'";

    $cats = $wpdb->get_results($sql);

    $allCats = array();

    $mainCats = array();

    $subOneCats = array();

    $subTwoCats = array();

    $subThreeCats = array();

    $subFourCats = array();

    foreach ($cats as $cat) {

        if ($cat->parent == 0) {

            $mainCats[$cat->term_id]['cat'] = $cat;
        }

        $allCats[$cat->term_id]['cat'] = $cat;
    }

    foreach ($cats as $cat) {

        if ($cat->parent != 0 && isset($mainCats[$cat->parent])) {

            $mainCats[$cat->parent]['subs'][] = $cat;

            $subOneCats[$cat->term_id]['cat'] = $cat;
        } else if ($cat->parent != 0) {

            $allCats[$cat->parent]['subs'][] = $cat;
        }
    }

    foreach ($cats as $cat) {

        if ($cat->parent != 0 && isset($subOneCats[$cat->parent])) {

            $subOneCats[$cat->parent]['subs'][] = $cat;

            $subTwoCats[$cat->term_id]['cat'] = $cat;
        }
    }

    foreach ($cats as $cat) {

        if ($cat->parent != 0 && isset($subTwoCats[$cat->parent])) {

            $subTwoCats[$cat->parent]['subs'][] = $cat;

            $subThreeCats[$cat->term_id]['cat'] = $cat;
        }
    }

    foreach ($cats as $cat) {

        if ($cat->parent != 0 && isset($subThreeCats[$cat->parent])) {

            $subThreeCats[$cat->parent]['subs'][] = $cat;

            $subFourCats[$cat->term_id]['cat'] = $cat;
        }
    }

    $opts = '<option value="">----- Select a Category -----</option>';

    foreach ($mainCats as $mcat) {

        $opts .= '<option value="' . $mcat['cat']->term_id . '">' . $mcat['cat']->name . ' (' . $mcat['cat']->count . ') :::::::::::: (Main Category)::::::::::' . PHP_EOL;

        $opts .= '</option>' . PHP_EOL;

        if (isset($mcat['subs'])) {

            foreach ($mcat['subs'] as $scat) {

                $opts .= '		<option value="' . $scat->term_id . '"> - ' . $scat->name . ' (' . $scat->count . ')' . PHP_EOL;

                $opts .= '		</option>' . PHP_EOL;

                if (isset($allCats[$scat->term_id]['subs'])) {

                    foreach ($allCats[$scat->term_id]['subs'] as $scat) {

                        $opts .= '				<option value="' . $scat->term_id . '"> --- ' . $scat->name . ' (' . $scat->count . ')' . PHP_EOL;

                        $opts .= '				</option>' . PHP_EOL;

                        if (isset($allCats[$scat->term_id]['subs'])) {

                            foreach ($allCats[$scat->term_id]['subs'] as $scat) {

                                $opts .= '					<option value="' . $scat->term_id . '"> ----- ' . $scat->name . ' (' . $scat->count . ')' . PHP_EOL;

                                $opts .= '					</option>' . PHP_EOL;

                                if (isset($allCats[$scat->term_id]['subs'])) {

                                    foreach ($allCats[$scat->term_id]['subs'] as $scat) {

                                        $opts .= '						<option value="' . $scat->term_id . '"> ------- ' . $scat->name . ' (' . $scat->count . ')' . PHP_EOL;

                                        $opts .= '						</option>' . PHP_EOL;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }



    return $opts;
}

/**
 * Function to show the Plugin admin page 
 */
function purple_feeds_page_settings() {
    global $wpdb;
    echo "<br/>";
    echo "<div style='float:right;padding:0 26px 0 0;'>Work for you?<br /><a href='http://wordpress.org/plugins/purple-xmls-google-product-feed-for-woocommerce/'>Rate this plugin!</a></div>";
    echo "<h1>Dashboard - Purple XMLs -</h1>";
    echo "<br/>";
    echo "<h2>Select what to export as XML</h2>";
    echo "<label>When you click the button's below, the plugin will create a XML file for you to save in a new window.</label>";
    echo "<br/>";
    echo " <div id='poststuff'><div class='postbox' style='width: 98%;'><form action='" . site_url() . "' name='google' id='cat-feeds-xml-google-form' method='get' target='_blank'>
		<h3 class='hndle'>Google Products XML</h3>
		<div class='inside export-target'>
		 <table class='form-table' style='width:95%;'>
		  <tbody><tr>";
    echo "<th><label >Category : </label></th>";
    echo '<td><select name="category" id="listproductgoogle">' . listProductCats() . '</select></td>';
    echo "<th><label>Google Category : </label></th>";
    echo '<td style="float:right;"><select name="google_category" id="googleCategorySelect" style="width: 300px;">' . listGoogleCategories() . '</select></td>';
    echo "</tr><tr>";
    echo "<th><label >Brand Name : </label></th>";
    echo '<td><input type="text" name="brand_name" id="brand_name" style="width: 238px;" /></td>';
    echo "<td colspan='2'><input type='hidden' name='purplexmls' value='google' /><input style='float:right;' type='button' onclick='document.google.submit();' name='submit-google-xml' value='Get Google Products Feed' id='cat-feeds-xml-google' ></td>";
    echo "</tr></tbody></table></div></form></div></div>";


    echo "<br/>";
    echo " <div id='poststuff'><div class='postbox' style='width: 98%;'><form name='categories' action='" . site_url() . "' id='categories-xml-form' method='get' target='_blank'>
		<h3 class='hndle'>Categories XML</h3>
		<div class='inside export-target'>
		 <table class='form-table' style='width:95%;'>
		  <tbody><tr>";
    echo "<td colspan='3'><h5 for='startdate'>Category Product Count</h5></td>";
    echo "<td ><input type='hidden' name='purplexmls' value='categories' /><input style='float:right;' type='button' onclick='document.categories.submit();' name='submit-categories-xml' value='Get Categories Feed' id='cat-feeds-xml' ></td>";
    echo "</tr></tbody></table></div></form></div></div>";

    echo "<br/>";
    echo " <div id='poststuff'><div class='postbox' style='width: 98%;'><form name='products'  action='" . site_url() . "' id='cat-product-feeds-xml-form' method='get' target='_blank'>
		<h3 class='hndle'> XML Category Product Feed</h3>
		<div class='inside export-target'>
		 <table class='form-table' style='width:95%;'>
		  <tbody><tr>";
    echo "<th><label >Category : </label></th>";
    echo '<td ><select name="category" colspan="2" id="listproductcategory" >' . listProductCats() . '</select></td>';
    echo "<td   ><input type='hidden' name='purplexmls' value='category_products' /><input style='float:right;' type='button' onclick='document.products.submit();' name='submit-google-xml' value='Get Products Feed' id='cat-feeds-xml-products' ></td>";
    echo "</tr></tbody></table></div></form></div></div>";

    echo "<br/>";
    echo "<br/>";
    echo '<h1>Why Upgrade to PRO?</h1>
<ul style="padding-left:30px;">
<li style="list-style-type: disc;">Export All Categories</li>

<li style="list-style-type: disc;">Support the Development of the Plugin</li>

<li style="list-style-type: disc;">Add attribute Mapping</li>

<li style="list-style-type: disc;">Add Variable Product Mapping</li>

<li style="list-style-type: disc;">Fully Supported and Guaranteed</li></ul> 

<h3>WooCommerce Google Merchant Feed &#8211; PurpleXMLs V2.1</h3>

<h3><strong>The long awaited WooCommerce Plugin is Now Here!</strong></h3>

<p>The Purple XMLs V2.1 Google Merchant Feed for WooCommerce has been reinvented to accommodate both <strong>Variable Products</strong> as well as <strong>Attributes</strong> that are now <span style="text-decoration: underline;color: #ff0000"><strong>REQUIRED</strong></span> for Google Merchant Center and Google Shopping. The selection is a one-time thing and only takes a few minutes of matching your existing Attributes and  Product Variations (Sizes and Colors)</p>

<p>After your attributes and variations are selected and saved, simply select your <em><strong>Product Category</strong></em> and <em><strong>Google Product Category </strong></em>and click "Get Feed" and your in business!</p><br /><h3>Screenshot of updated Admin:</h3><a href="http://www.purpleturtle.pro/woocommerce-google-merchant-feed/"><img style="border:1px #ccc solid;" src="http://www.purpleturtle.pro/wp-content/uploads/2013/07/screenshot-e1373150699517.png" /></a>

<h3><strong>Current Version Information: V2.1 Release Candidate (RC) 2.1.0 &#8211; Attributes and Variable Product Support</strong></h3><br /><a href="http://www.purpleturtle.pro/woocommerce-google-merchant-feed/"><img src="http://rememberingourmagic.com/wp-content/uploads/2013/01/button_upgrade_now.jpg" /></a>';
    echo "<br/>";
    echo "<br/>";
}

?>