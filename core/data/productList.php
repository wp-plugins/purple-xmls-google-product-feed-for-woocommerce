<?php

  /********************************************************************
  Version 2.0
    This gets a little complex. See design-document -> ProductCategoryExport
	By: Keneto 2014-05-14
  Note: One day, this needs to be moved to Joomla/VirtueMart compatibility

  ********************************************************************/

class PAProduct {
  public $id = 0;
  public $title = '';
  public $taxonomy = '';
  public $imgurls;
  public $attributes;
  
  function __construct() {
    $this->imgurls = array();
	$this->attributes = array();
  }
  
  function fetch_attributes(){
  
    global $wpdb;
	
	//For non-variant products, get the attributes
	$sql = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name as Attributes
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.ID = " . $this->id . "
		AND $wpdb->term_taxonomy.taxonomy LIKE 'pa_%' ";
	$childlist = $wpdb->get_results($sql);
	
	foreach($childlist as $a){
	  $this->attributes[substr($a->taxonomy, 3)] = $a->Attributes;
	}

  }
  
  function fetch_meta_attributes(){
  
    global $wpdb;
	
	//********************************************************************
	//wp_post_meta provides some attributes in the form of custom fields
	//********************************************************************
	$sql = "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=" . $this->id;
	$meta = $wpdb->get_results($sql);
	foreach($meta as $this_meta) {
	  if ((strlen($this_meta->meta_key) > 0) && (substr($this_meta->meta_key, 0, 1) != '_')) {
	    switch (strtolower($this_meta->meta_key)) {
		  case 'tax':
		    $this->tax = $this_meta->meta_value;
			break;
		  case 'vat':
		    $this->vat = $this_meta->meta_value;
			break;
		  case 'gst':
		    $this->gst = $this_meta->meta_value;
			break;
		  case 'total_sales':
		    break;
		  default:
	        $this->attributes[$this_meta->meta_key] = $this_meta->meta_value;
		}
	  }
	}
  }
	
}

class PProductEntry {
  public $taxonomyName;
  public $ProductID;
  public $Attributes;
  
  function __construct() {
    $this->Attributes = array();
  }
  
  function GetAttributeList(){
    $result = '';
	foreach($this->Attributes as $ThisAttribute) {
	  $result .= $ThisAttribute . ', ';
	}
	return '['. $this->Name . '] ' . substr($result, 0, -2);
  }
}

class PProductList {

  public $AttributeCategory;
  
  function __construct() {
    $this->AttributeCategory = array();
  }

  function CreateAttributeCategories($list) {
    //iterate the list and build categories
	foreach($list as $listitem) {
	  //Try to find existing ProductAttribute
	  $x = $this->FindAttributeCategory($listitem);
	  if ($x == null) {

	    //Not found... make a new one
	    $x = new PProductEntry();
	    $x->taxonomyName = $listitem->taxonomy;
		$x->ProductID = $listitem->ID;
		$this->AttributeCategory[] = $x;
	  }
	  
	  //Save the Attribute
	  $x->Attributes[] = $listitem->Attributes;
	  
	}
  }

  function CreateNewProductList($list, $childlist) {

    //iterate the list of products
	foreach($list as $listitem) {
	  if ($this->ExistsInChildList($listitem, $childlist)) {
	    $this->InsertAttributes($listitem, 0, '');
	  }

	}
	//Clean up the trailing comma from the attributes
	foreach($this->resultlist as $listitem) {
	  $listitem->Attributes = substr($listitem->Attributes, 0, -2);
  
	}

  }

  function ExistsInChildList($needle, $haystack) {
    $result = false;
	foreach($haystack as $x) {
	  if ($x->ID == $needle->id) {
	    $result = true;
		break;
	  }
	}
	return $result;
  }

  function FindAttributeCategory($SearchTerm) {
    $result = null;
	//return $result; //patch for huisenthuis
    foreach($this->AttributeCategory as $ThisAttribute) {
	  if (($ThisAttribute->taxonomyName == $SearchTerm->taxonomy) && ($ThisAttribute->ProductID == $SearchTerm->ID)) {
	    $result = $ThisAttribute;
		break;
	  }
	}
	return $result;
  }

  function getProductList($category_id, $remote_category) {

    global $wpdb;
	
	//********************************************************************
    //Load the products for the given category
	//********************************************************************
	
    $sql = "
            SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name, $wpdb->term_taxonomy.taxonomy
            FROM $wpdb->posts
            LEFT JOIN $wpdb->term_relationships ON
            ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
            LEFT JOIN $wpdb->term_taxonomy ON
            ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
            WHERE $wpdb->posts.post_status = 'publish'
            AND $wpdb->posts.post_type = 'product'
            AND $wpdb->term_taxonomy.taxonomy = 'product_cat'
            AND $wpdb->term_taxonomy.term_id = " . $category_id . "
            ORDER BY post_date DESC
    		";

    $products = $wpdb->get_results($sql);
	$master_product_list = array();
	
	//********************************************************************
	//Convert the WP_Product List into a Cart-Product Master List
	//********************************************************************
	
	foreach ($products as $prod) {

	  $item = new PAProduct();
	  $product = get_product($prod->ID);
	  
	  //Basics
	  $item->id = $prod->ID;
	  $item->title = $prod->post_title;
	  $item->taxonomy = $prod->taxonomy;
	  $item->isVariable = false;
	  $item->description_short = substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000);
      $item->description_long = substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000);
	  
	  $item->category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
	  $item->product_type = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
	  $item->link = get_permalink($prod->ID);
	  $thumb_ID = get_post_thumbnail_id($prod->ID);
	  $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
	  $item->feature_imgurl = $thumb['0'];
	  $attachments = $product->get_gallery_attachment_ids();
	  $attachments = array_diff($attachments, array($thumb_ID));
	  if ($attachments) {
		foreach ($attachments as $attachment) {
			$thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
			$imgurl = $thumb['0'];
			if (strlen($imgurl) > 0) {
			  $item->imgurls[] = $imgurl;
			}
		}
	  }
	  $item->condition = 'New';
	  $item->regular_price = $product->regular_price;
	  $item->has_sale_price = false;
	  if ($product->sale_price != "") {
	    $item->has_sale_price = true;
		$item->sale_price = $product->sale_price;
	  }
	  $item->sku = $product->sku;
	  $item->weight = $product->get_weight();

	  $item->fetch_attributes();
	  $item->fetch_meta_attributes();

	  $master_product_list[] = $item;
	}

	//********************************************************************
	//Compute variations - > ChildList
	//ChildList / AttributeCategories will be used later if the user
	//  didn't set specific variations
	//********************************************************************
	$sql = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name as Attributes
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.post_type = 'product'
		AND $wpdb->term_taxonomy.taxonomy LIKE 'pa_%' ";
	$childlist = $wpdb->get_results($sql);

	//Generate Attributes Table
	$this->CreateAttributeCategories($childlist);

	$resultlist = array();

	//********************************************************************
    //Iterate the master_product_list
	//If Variations exist, make sure multiple clones of the product exist
	//********************************************************************
	
	foreach($master_product_list as $listitem) {
	
	  //Check for simple variation
	  $variable_attribute_count = $this->populateVariableAttributes($listitem, $resultlist);

	  if ($variable_attribute_count == 0) {
	    $item = clone $listitem;
		$resultlist[] = $item;
	  }
	}
	
	//********************************************************************
    //Iterate the results
	//a) Now that variations are known, we can tell if it's in-stock or out-of-stock
	//********************************************************************
	
	foreach($resultlist as $item) {
	  //In Stock?
	  $item->stock_status = 1; //Assume in stock
	  $item->stock_status_explicitly_set = false;
	  $sql2 = "SELECT post_id, meta_key, meta_value from $wpdb->postmeta WHERE post_id=" . $item->id;
	  $metadata = $wpdb->get_results($sql2);
	  foreach ($metadata as $m) {
	    if ($m->meta_key == '_stock_status') {
		  if ($m->meta_value == 'outofstock')
		    $item->stock_status = 0;
		  $item->stock_status_explicitly_set = true;
		}
		if (($m->meta_key == '_stock') && !$item->stock_status_explicitly_set) {
		  if (($m->meta_value == '') || ($m->meta_value < 1)) {
		    $item->stock_status = 0;
		  }
		}
	  }
	}

	return $resultlist;
  }
  
  function populateVariableAttributes($listitem, &$resultlist) {
  
    //********************************************************************
    //Variable attributes occur when the user has fully defined the variations and attributes.
	//In woocommerce, a true variation appears in wp_posts.post_type = product_variation
	//with post_parent pointed to the original product. So it's like a sub-product or a sub-post.
	//********************************************************************
	
	global $wpdb;
	$sql = "
		SELECT id, post_title FROM $wpdb->posts
		WHERE $wpdb->posts.post_parent = " . $listitem->id . "
		AND $wpdb->posts.post_type = 'product_variation'";
	$variations = $wpdb->get_results($sql);
	
	$count = 0;
	foreach($variations as $variation) {

	  //Copy item
	  $item = clone $listitem;
	  $resultlist[] = $item;
	  $count++;
	  
	  $item->parent_title = $item->title; //This is for eBay feed only, and could otherwise be deleted

	  //Special Variations settings
	  $item->item_group_id = $listitem->id;
	  $item->id = $variation->id;
	  $item->isVariable = true;
	  
	  //Some basics
	  $product = get_product($item->id);
	  //$item->description_short = substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000);
      //$item->description_long = substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000);
	  $item->regular_price = $product->regular_price;
	  $item->has_sale_price = false;
	  if ($product->sale_price != "") {
	    $item->has_sale_price = true;
		$item->sale_price = $product->sale_price;
	  }
	  $item->sku = $product->sku;

	  //$item->fetch_meta_attributes(); //Grab the meta attributes for this variation
	  


	  //Go find the Variations
	  $sql = "SELECT meta_key, meta_value FROM $wpdb->postmeta
			WHERE post_id = " . $variation->id . " AND 
			meta_key LIKE 'attribute_pa_%'";
	  $attributes = $wpdb->get_results($sql);

	  //Add the variations
	  foreach($attributes as $this_attribute) {
	    if (strpos($this_attribute->meta_key, 'attribute_pa') == 0) {
		  $this_attribute->meta_key = substr($this_attribute->meta_key, 13);
		}
	    $item->attributes[$this_attribute->meta_key] = $this_attribute->meta_value;
	  }

	}
	
	//If we got our Variable Attributes, Done! Get out now
	if ($count > 0) {
	  return $count;
	}
	
	//Here, life is more difficult because the user didn't select
	//specific variations
	
	  /*if ($this->ExistsInChildList($listitem, $childlist)) {
		//$this->InsertAttributes($listitem, 0, '');
		$item = clone $listitem;
		$item->attributes = 'Some';
		$resultlist[] = $item;
	  } else {*/
  }


}

?>