<?php

  /********************************************************************
  Version 2.0
    A Feed
	By: Keneto 2014-05-08

  ********************************************************************/

class PBasicFeed {

  public $categories;
  public $current_category; //This is the active category while in formatProduct()
  public $currency;
  public $descriptionFormat; //0 = short or long, 1 = long 2 = short
  public $descriptionStrict = false; //hack out ALL special characters from the description including multinational
  public $descriptionStrictReplacementChar = ' ';
  public $fileformat = 'xml';
  public $feed_category;
  public $max_description_length = 10000;
  public $providerName = '';
  public $providerNameL = '';
  public $productList;
  public $stripHTML = false;
  public $weight_unit;
  
  
  function add_feed_selection($category, $remote_category, $filename, $url, $type) {
    global $wpdb;
    $feed_table = $wpdb->prefix . 'cp_feeds';
    $sql = "INSERT INTO $feed_table(`category`, `remote_category`, `filename`, `url`, `type`) VALUES ('$category','$remote_category','$filename','$url','$type')";
    $wpdb->query($sql);
  }
  
  function checkFolders() {

    global $message;

    $dir = PFeedFolder::uploadRoot();
    if (!is_writable($dir)) {
      $message = $dir . ' should be writeable';
	  return false;
    }
	
    $dir = PFeedFolder::uploadFolder();
    if (!is_dir($dir)) {
	  mkdir($dir);
    }
    if (!is_writable($dir)) {
	  $message = "$dir should be writeable";
	  return false;
    }
    $dir2 = $dir . $this->providerName . '/';
	if (!is_dir($dir2)) {
	  mkdir($dir2);
	}
	
	return true;
  }

  function formatLine($attribute, $value, $cdata = false, $leader_space = '') {
	//Prep a single line for XML
	//Allow the $attribute to be overridden
	if (isset($this->feedOverrides->overrides[$attribute]) && (strlen($this->feedOverrides->overrides[$attribute]) > 0)) {
	  $attribute = $this->feedOverrides->overrides[$attribute];
	}
	$c_leader = '';
	$c_footer = '';
	if ($cdata) {
	  $c_leader = '<![CDATA[';
	  $c_footer = ']]>';
	}
	//Allow force strip HTML
	if ($this->stripHTML) {
	  $value = strip_tags(html_entity_decode($value));
	}
	//if not CData, don't allow '&'
	if (!$cdata) {
	  $value = htmlentities($value, ENT_QUOTES,'UTF-8');
	}
	//Done
	return '
	    ' . $leader_space . '<' . $attribute . '>' . $c_leader . $value . $c_footer . '</' . $attribute . '>';
  }
  
  function formatProduct($product) {
    return '';
  }
  
  //What does this even do? Need to trace it one day
  function get_feed_selection_by_filename($filename) {
    global $wpdb;
    $feed_table = $wpdb->prefix . 'cp_feeds';
    $sql = "SELECT * from $feed_table WHERE `filename`='$filename' AND `type`='" . $this->providerName . "'";
    $list_of_feeds = $wpdb->get_results($sql, ARRAY_A);
    if ($list_of_feeds) {
        return $list_of_feeds[0];
    } else {
        return false;
    }
  }
  
  function getFeedData_internal($remote_category) {
    $output = null;

	foreach($this->categories->items as $this_category) {
	  $products = $this->productList->getProductList($this_category, $remote_category);
	  foreach($products as $this_product) {
	    if (!$this->feed_category->verifyProduct($this_product)) break;
		//Adjust the product a little before sending it out to be Formatted
		switch ($this->descriptionFormat) {
		  case 1: //Force Long
		    $this_product->description = $this_product->description_long;
			break;
		  case 2: //Force Short
		    $this_product->description = $this_product->description_short;
			break;
		  default:
		    //By default pick short... if no short, pick long (original behaviour)
			if (strlen($this_product->description_short) == 0) {
		      $this_product->description = $this_product->description_long;
			} else {
			  $this_product->description = $this_product->description_short;
			}
			break;
		}
		if (strlen($this_product->description) > $this->max_description_length) {
		  $this_product->description = substr($this_product->description, 0, $this->max_description_length);
		}
		if ($this->descriptionStrict) {
		  //I really should use preg_replace here one day
		  //$this_product->description = preg_replace('/[^A-Za-z0-9\-]/', '', $this_product->description);
		  for($i=0;$i<strlen($this_product->description);$i++) {
		    if (($this_product->description[$i] < "\x20") || ($this_product->description[$i] > "\x7E")) {
			  $this_product->description[$i] = $this->descriptionStrictReplacementChar;
			}
		  }
		}
	    $output .= $this->formatProduct($this_product);
	  }
	}

    return $output;

  }

  function getFeedData() {

    global $message;

	$x = new PLicense();

	if (!$this->checkFolders()) {
	  return;
	}
	
	//Old, uncommented code

	$old_flag = FALSE;
	$file_name = sanitize_title_with_dashes($_REQUEST['feed_filename']);
	if ($file_name == "") {
		$file_name = "feed" . rand(10, 1000);
	}
	$file_url = PFeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
	if (file_exists($file_url)) {
		$old_feed = $this->get_feed_selection_by_filename($file_name);
		if ($old_feed) {
			$old_flag = TRUE;
		}
	}
	$category = $_REQUEST['local_category'];
	$remote_category = $_REQUEST['remote_category'];


	$file_path = site_url() . '/wp-content/uploads/cart_product_feeds/' . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
	header('Location: ' . $file_path);


	//Figure out what categories the user wants to export
	$this->categories = new PCategoryList($category);

	//Get the ProductList ready
	$this->productList = new PProductList();

	//Get the Feed Overrides ready
	$this->feedOverrides = new PFeedOverride($this->providerName, $this);

	//Initialize some useful data
	$this->current_category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
	$this->weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
	$this->currency = get_woocommerce_currency();

	//Create the Feed
	$output = 
	  $this->getFeedHeader($file_name, $file_path) .
	  $this->getFeedData_internal($remote_category) .
	  $this->getFeedFooter();
	  
	//Save the Feed
	$handle = fopen($file_url, "w");  
	fwrite($handle, $output);
	fclose($handle);
	
	//Not too sure / Old / Uncommented

	if ($old_flag) {
		$this->update_feed_selection($old_feed['id'], $category, $remote_category, $file_name, $file_path, $this->providerName);
	} else {
		$this->add_feed_selection($category, $remote_category, $file_name, $file_path, $this->providerName );
	}

  }

  function getFeedFooter() {
    return '';
  }
  
  function getFeedHeader() {
    return '';
  }
  
  function updateFeed($category, $remote_category, $file_name) {
    //called by the automatic updater (Cron job), so this doesn't need as much overhead as getFeedData()
	//Note: getFeedData_Internal being fed incorrect file_path/url, but it shouldn't need it. Fix one day

	if (!$this->checkFolders()) {
	  return;
	}

	$file_url = PFeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
	$file_path = site_url() . '/wp-content/uploads/cart_product_feeds/' . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
	
	$old_flag = false;
	if (file_exists($file_url)) {
		$old_feed = $this->get_feed_selection_by_filename($file_name);
		if ($old_feed) {
			$old_flag = true;
		}
	}

	//Figure out what categories the user wants to export
	$this->categories = new PCategoryList($category);

	//Get the ProductList ready
	$this->productList = new PProductList();

	//Get the Feed Overrides ready
	$this->feedOverrides = new PFeedOverride($this->providerName, $this);

	//Initialize some useful data
	$this->current_category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
	$this->weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
	$this->currency = get_woocommerce_currency();

	//Create the Feed
	$output = 
	  $this->getFeedHeader($file_name, $file_path) .
	  $this->getFeedData_internal($remote_category) .
	  $this->getFeedFooter();

	//Save the Feed
	$handle = fopen($file_url, "w");  
	fwrite($handle, $output);
	fclose($handle);
	
	//Not too sure / Old / Uncommented

	if ($old_flag) {
		$this->update_feed_selection($old_feed['id'], $category, $remote_category, $file_name, $file_path, $this->providerName);
	} else {
		$this->add_feed_selection($category, $remote_category, $file_name, $file_path, $this->providerName );
	}
  }
  
  function must_exit() {
    //true means exit so the browser page will remain in place
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

  //!This function needs to die
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