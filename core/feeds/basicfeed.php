<?php

  /********************************************************************
  Version 2.0
    A Feed
	By: Keneto 2014-05-08

  ********************************************************************/

require_once __DIR__ . '../../classes/md5.php';
require_once __DIR__ . '/../registration.php';

class PBasicFeed {

  public $providerName = '';
  public $providerNameL = '';
  public $fileformat = 'xml';
  public $feed_category;
  
  function add_feed_selection($category, $remote_category, $filename, $url, $type) {
    global $wpdb;
    $feed_table = $wpdb->prefix . 'cp_feeds';
    $sql = "INSERT INTO $feed_table(`category`, `remote_category`, `filename`, `url`, `type`) VALUES ('$category','$remote_category','$filename','$url','$type')";
    $wpdb->query($sql);
  }
  
  function checkFolders() {

    global $message;

    $dir = WP_CONTENT_DIR . "/uploads/cart_product_feeds/";
    if (!is_writable(WP_CONTENT_DIR . "/uploads/")) {
      $message = WP_CONTENT_DIR . "/uploads/ should be writeable";
	  return false;
    }
	
    $dir = WP_CONTENT_DIR . "/uploads/cart_product_feeds/";
    if (!is_dir($dir)) {
	  mkdir($dir);
    }
    if (!is_writable($dir)) {
	  $message = "$dir should be writeable";
	  return false;
    }
    $dir2 = $dir . $this->providerNameL . '/';
	if (!is_dir($dir2)) {
	  mkdir($dir2);
	}
	
	return true;
  }
  
  function get_feed_selection_by_filename($filename) {
    global $wpdb;
    $feed_table = $wpdb->prefix . 'cp_feeds';
    $sql = "SELECT * from $feed_table WHERE `filename`='$filename' AND `type`='" . $this->providerNameL . "'"; //! Move Type to ProviderName to eliminate case issue
    $list_of_feeds = $wpdb->get_results($sql, ARRAY_A);
    if ($list_of_feeds) {
        return $list_of_feeds[0];
    } else {
        return false;
    }
  }
  
  function getFeedData_internal() {
    return '';
  }

  function getFeedData() {

    global $message;

	new PLicense();

	if (!$this->checkFolders()) {
	  return;
	}

	$old_flag = FALSE;
	$file_name = sanitize_title_with_dashes($_REQUEST['feed_filename']);
	if ($file_name == "") {
		$file_name = "feed" . rand(10, 1000);
	}
	$file_url = WP_CONTENT_DIR . "/uploads/cart_product_feeds/" . $this->providerNameL . '/' . $file_name . '.' . $this->fileformat;
	if (file_exists($file_url)) {
		$old_feed = $this->get_feed_selection_by_filename($file_name);
		if ($old_feed) {
			$old_flag = TRUE;
		}
	}
	$category = $_REQUEST['category'];
	$remote_category = $_REQUEST[$this->providerNameL . '_category'];
	

	$file_path = site_url() . '/wp-content/uploads/cart_product_feeds/' . $this->providerNameL . '/' . $file_name . '.' . $this->fileformat;
	header('Location: ' . $file_path);
	// Generate xml file for download
	$output = $this->getFeedData_internal($category, $remote_category, $file_name, $file_path);

	$handle = fopen($file_url, "w");
	if ($this->fileformat == 'xml') {
	  
	  fwrite($handle, $output);
	  
	} else {
		foreach ($output as $fields) {
			fputcsv($handle, $fields, ',', '"');
		}
	}
	fclose($handle);

	if ($old_flag) {
		$this->update_feed_selection($old_feed['id'], $category, $remote_category, $file_name, $file_path, $this->providerName);
	} else {
		$this->add_feed_selection($category, $remote_category, $file_name, $file_path, $this->providerName );
	}

  }
  
  function updateFeed($category, $remote_category, $file_name) {
    //called by the automatic updater (Cron job), so this doesn't need as much overhead as getFeedData()
	//Note: getFeedData_Internal being fed incorrect file_path/url, but it shouldn't need it. Fix one day

	if (!$this->checkFolders()) {
	  return;
	}

	//$file_path = site_url() . '/wp-content/uploads/cart_product_feeds/' . $this->providerNameL . '/' . $file_name . '.' . $this->fileformat;
	$file_url = WP_CONTENT_DIR . "/uploads/cart_product_feeds/" . $this->providerNameL . '/' . $file_name . '.' . $this->fileformat;

	$output = $this->getFeedData_internal($category, $remote_category, $file_name, $file_url);

	$handle = fopen($file_url, "w");
	if ($this->fileformat == 'xml') {
	  
	  fwrite($handle, $output);
	  
	} else {
		foreach ($output as $fields) {
			fputcsv($handle, $fields, ',', '"');
		}
	}
	fclose($handle);
  }
  
  function must_exit() {
    //Yes: exit so the page will remain in place
    return true;
  }
  
  function update_feed_selection($old_feed_id, $category, $remote_category, $file_name, $file_path, $type) {

    global $wpdb;
    $feed_table = $wpdb->prefix . 'cp_feeds';
    $sql = "UPDATE $feed_table SET `category`='$category',`remote_category`='$remote_category',`filename`='$file_name',`url`='$file_path',`type`='$type' WHERE `id`=$old_feed_id";
    $wpdb->query($sql);
  }
  
  function __construct () {
    $this->feed_category = new md5y();
  }
  
  function loadProductList($id) {
    global $wpdb;
    //Load the products for the given category
    $sql = "
            SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name
            FROM $wpdb->posts
            LEFT JOIN $wpdb->term_relationships ON
            ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
            LEFT JOIN $wpdb->term_taxonomy ON
            ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
            WHERE $wpdb->posts.post_status = 'publish'
            AND $wpdb->posts.post_type = 'product'
            AND $wpdb->term_taxonomy.taxonomy = 'product_cat'
            AND $wpdb->term_taxonomy.term_id = " . $id . "
            ORDER BY post_date DESC
    		";

    $products = $wpdb->get_results($sql);
	
	//Find other details
	foreach ($products as $prod) {
	  $prod->stock_status = 1; //Assume in stock
	  $sql2 = "SELECT post_id, meta_key, meta_value from $wpdb->postmeta WHERE post_id=" . $prod->ID;
	  $metadata = $wpdb->get_results($sql2);
	  foreach ($metadata as $m) {
	    if ($m->meta_key == '_stock_status') {
		  if ($m->meta_value == 'outofstock')
		    $prod->stock_status = 0;
		}
	  }
	}
	
	return $products;
  }

}

?>