<?php

  /********************************************************************
  Version 2.0
    This gets a little complex. See design-document -> ProductCategoryExport
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-14
  ********************************************************************/

class PAProductW extends PAProduct {
  
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

class PProductList {

  public $AttributeCategory;
  public $exclude_variable_attributes;
	public $products = null;
  public $tag_list;
  
  public function __construct() {
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
	if ($this->exclude_variable_attributes) {
	  return $result; //patch for huisenthuis
	}
    foreach($this->AttributeCategory as $ThisAttribute) {
	  if (($ThisAttribute->taxonomyName == $SearchTerm->taxonomy) && ($ThisAttribute->ProductID == $SearchTerm->ID)) {
	    $result = $ThisAttribute;
		break;
	  }
	}
	return $result;
  }
  
  public function get_customfields() {
    global $wpdb;
	$sql = "SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE meta_key NOT LIKE '_%'";
	$this->custom_fields = $wpdb->get_results($sql);
  }
  
  public function get_woobrands() {
    global $wpdb;
    $sql = "select id, post_title, post_name, $wpdb->term_taxonomy.term_taxonomy_id, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name
		  FROM $wpdb->posts
		  LEFT JOIN $wpdb->term_relationships on ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		  LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		  LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
          WHERE $wpdb->posts.post_type='product'
		  AND $wpdb->term_taxonomy.taxonomy = 'product_brand'
		  ";
	$this->brand_list = $wpdb->get_results($sql);
  }
  
  public function get_wootags() {
    global $wpdb;
    $sql = "select id, post_title, post_name, $wpdb->term_taxonomy.term_taxonomy_id, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name as term_name
		  FROM $wpdb->posts
		  LEFT JOIN $wpdb->term_relationships on ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		  LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		  LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
          WHERE $wpdb->posts.post_type='product'
		  AND $wpdb->term_taxonomy.taxonomy = 'product_tag'
		  ";
		$this->tag_list = $wpdb->get_results($sql);
  }
  
  public function loadProducts($parent) {

		global $wpdb;

		//********************************************************************
		//Check that the DB can handle big queries
		//********************************************************************

		$settings = $wpdb->get_results("SHOW VARIABLES LIKE 'SQL_BIG_SELECTS'");
		foreach ($settings as $this_setting)
			if (($this_setting->Variable_name == 'sql_big_selects') && ($this_setting->Value == 'OFF')) {
				$wpdb->get_results('SET SQL_BIG_SELECTS=1');
			}

		//********************************************************************
		//Initialization
		//********************************************************************

		$extra_sql_query = '';
		$extra_sql_part = '';
  
		//Try to extract the brands from wc-brands
		if (!isset($this->brand_list)) {
			$this->get_woobrands();
		}
		//If fail to extract... stop trying
		if (!isset($this->brand_list)) {
			$this->brand_list = array();
		}
		
		//Try to extract the tags from woo_commerce
		if (!isset($this->tag_list)) {
			$this->get_wootags();
		}
		//If fail to extract... stop trying
		if (!isset($this->tag_list)) {
			$this->tag_list = array();
		}
		
		//Load up the meta attributes which will tell us the custom fields
		$this->get_customfields();

		$this->gmc_active = false;
		if (($parent->gmc_enabled) && (is_plugin_active( 'woocommerce-google-merchant-center-feed/woocommerce-google-merchant-center-feed.php' )) ) {
		  //In this case, attributes may be hidden in strange places. Adjust SQL statement to catch those
			$this->gmc_active = true;
			$extra_sql_query .= ', gmc_attributes.meta_value as gmc_value';
			$extra_sql_part .= "
				#Merchant Center catches
				LEFT JOIN
					(
						SELECT post_id, meta_value
						FROM $wpdb->postmeta
						WHERE (meta_key = 'wc_gmcf')
					) as gmc_attributes ON gmc_attributes.post_id = $wpdb->posts.ID
			";
		}

		//********************************************************************
		//Load the products
		//********************************************************************
		
		$sql = "
				SELECT 
				  $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name, $wpdb->term_taxonomy.taxonomy, 
					$wpdb->terms.name as category_name, $wpdb->terms.term_id as category_id, details.name as product_type,
					attributes.meta_value as attribute_list, attribute_details.attribute_details, variation_id_table.variation_ids as variation_ids, stock_levels.parent_manage_stock
					$extra_sql_query
				FROM $wpdb->posts
				
				#Match the terms / relationships / taxonomy
				LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
				LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
				LEFT JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
				
				#Link in product type
				LEFT JOIN 
					(
						SELECT a.ID, d.name FROM $wpdb->posts a
						LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id)
						LEFT JOIN $wpdb->term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
						LEFT JOIN $wpdb->terms d ON (c.term_id = d.term_id)
						WHERE c.taxonomy = 'product_type'
					) as details ON details.ID = $wpdb->posts.ID
				
				#Attribute_list
				LEFT JOIN
					(
						SELECT post_id, meta_value
						FROM $wpdb->postmeta
						WHERE $wpdb->postmeta.meta_key = '_product_attributes'
					) as attributes ON attributes.post_id = $wpdb->posts.ID

				#Attributes in detail
				LEFT JOIN
					(
						SELECT a.ID, GROUP_CONCAT(CONCAT(c.taxonomy, '=', d.name)) as attribute_details
						FROM $wpdb->posts a
						LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id)
						LEFT JOIN $wpdb->term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
						LEFT JOIN $wpdb->terms d ON (c.term_id = d.term_id)
						WHERE c.taxonomy LIKE 'pa_%'
						GROUP BY a.ID
					) as attribute_details ON attribute_details.ID = $wpdb->posts.ID

				#variations
				LEFT JOIN
					(
						SELECT GROUP_CONCAT(postvars.id) as variation_ids, postvars.post_parent
						FROM $wpdb->posts postvars
						WHERE postvars.post_type = 'product_variation'
						GROUP BY postvars.post_parent
					) as variation_id_table on variation_id_table.post_parent = $wpdb->posts.ID

				#stock-levels
				LEFT JOIN
					(
						SELECT post_id, meta_value as parent_manage_stock
						FROM $wpdb->postmeta
						WHERE meta_key = '_manage_stock'
					) as stock_levels ON stock_levels.post_id = $wpdb->posts.ID

				$extra_sql_part

				WHERE $wpdb->posts.post_status = 'publish'
					AND $wpdb->posts.post_type = 'product'
					AND $wpdb->term_taxonomy.taxonomy = 'product_cat'
					ORDER BY $wpdb->terms.term_id, post_date DESC
					";

		$this->products = $wpdb->get_results($sql);

		if (count($this->products) == 0) {
			$parent->message .= 'ProductList returned Zero results';
		}
	}

	public function getProductList($parent, $remote_category) {

		global $wpdb;

		$parent->logActivity('Retrieving product list from database');
		if ($this->products == null)
			$this->loadProducts($parent);

		$master_product_list = array();

		//********************************************************************
		//Convert the WP_Product List into a Cart-Product Master List (ListItems)
		//********************************************************************

		foreach ($this->products as $index => $prod) {

			if ($index % 100 == 0)
				$parent->logActivity('Converting master product list...' . round($index / count($this->products) * 100) . '%' );

			if (!$parent->categories->containsCategory($prod->category_id))
				continue;

			$item = new PAProductW();
			$product = get_product($prod->ID);

			//Basics
			$item->id = $prod->ID;
			$item->parent_item = null;
			$item->title = $prod->post_title;
			$item->taxonomy = $prod->taxonomy;
			$item->isVariable = $prod->product_type == 'variable';
			$item->description_short = substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000);
			$item->description_long = substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000);

			//Assign any brands
			foreach($this->brand_list as $this_brand) {
				if ($this_brand->id == $item->id) {
					$item->attributes['brand'] = $this_brand->name;
					break;
				}
			}
			
			//Assign any tags
			foreach($this->tag_list as $this_tag) {
				if ($this_tag->id == $item->id) {
					$item->attributes['tag'] = $this_tag->term_name;
					break;
				}
			}

			$item->category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->product_type = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->localCategory = str_replace(".and.", " & ", str_replace(".in.", " > ", $prod->category_name));
			$item->localCategory = str_replace("|", ">", $item->localCategory);
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
					if (strlen($imgurl) > 0)
						$item->imgurls[] = $imgurl;
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
			$item->parent_manage_stock = $prod->parent_manage_stock;
			$item->stock_status = 1; //Assume in stock
			$item->stock_status_explicitly_set = false;
			//$item->fetch_attributes(); //Old
			
			//Attributes (General)
			//Not used yet. Will one day replace CreateAttributeCategories()
			$attributes = unserialize($prod->attribute_list);
			foreach($attributes as $this_attribute) {

			}

			//Attributes (Detailed)
			//The attribute_details in the form:
			//  pa_attribute=example,pa_attribute2=example2,pa_attribute3=example3
			//convert to:
			//  item->attributes[attribute]=example; item->attributes[attribute2]=example2; etc
			$item->attribute_details = explode(',', $prod->attribute_details);
			foreach($item->attribute_details as $this_attribute){
				$this_attribute = explode('=', $this_attribute);
				if (count($this_attribute) > 1)
				$item->attributes[substr($this_attribute[0], 3)] = $this_attribute[1];
			}

			$item->variation_ids = $prod->variation_ids;

			//$item->fetch_meta_attributes(); //Old
			foreach($this->custom_fields as $custom_field)
				if ($custom_field->post_id = $item->id)
					$item->attributes[$custom_field->meta_key] = $custom_field->meta_value;

			//If woocommerce-google-merchant-center, we have to do extra work to extract data
			if (($this->gmc_active) && (strlen($prod->gmc_value) > 0)) {
				$gmc_attributes = unserialize($prod->gmc_value);
				foreach($gmc_attributes as $key => $this_attribute) {
					//Use this_attribute if no overrides force us to use any attributes OR if there are overrides and in_array()
					if ( (count($parent->gmc_attributes) == 0) || (in_array($key, $parent->gmc_attributes)) )
						switch ($key) {
							case "description": 
								if (strlen($item->description_short) == 0) $item->description_short = $this_attribute;
								if (strlen($item->description_long) == 0) $item->description_long = $this_attribute;
								break;
							case "product_type":
								$item->product_type = $this_attribute;
								break;
							case "category":
								$item->localCategory = $this_attribute;
								break;
							case "condition":
								$item->condition = $this_attribute;
								break;
							case "shipping_weight":
								$item->weight = $this_attribute;
								break;							
							default:
								$item->attributes[$key] = $this_attribute;
						}
				}
			}

			$master_product_list[] = $item;
		}

		$resultlist = array();

		//********************************************************************
		//Iterate the master_product_list
		//If Variations exist, make sure multiple clones of the product exist
		//********************************************************************
		foreach($master_product_list as $index => $listitem) {

			if ($index % 100 == 0)
				$parent->logActivity('Applying variations...' . round($index / count($master_product_list) * 100) . '%' );
		
			//Check for simple variation
			if ($listitem->isVariable)
				$variable_attribute_count = $this->populateVariableAttributes($listitem, $resultlist);
			else
				$variable_attribute_count = 0;

			if ($variable_attribute_count == 0) {
				$item = clone $listitem;
			$resultlist[] = $item;
			}
		}

		//********************************************************************
		//Iterate the results
		//a) Now that variations are known, we can tell if it's in-stock or out-of-stock
		//********************************************************************
		$parent->logActivity('Meta-data iteration');		
		foreach($resultlist as $item) {
			if ($item->parent_manage_stock == 'yes')
				continue;
			//In Stock?
			$sql2 = "SELECT post_id, meta_key, meta_value from $wpdb->postmeta WHERE post_id=" . $item->id;
			$metadata = $wpdb->get_results($sql2);
			foreach ($metadata as $m) {
				if ($m->meta_key == '_stock_status') {
					if ($m->meta_value == 'outofstock')
						$item->stock_status = 0;
					$item->stock_status_explicitly_set = true;
				}
				if (($m->meta_key == '_stock') && !$item->stock_status_explicitly_set) {
					if (($m->meta_value == '') || ($m->meta_value < 1))
						$item->stock_status = 0;
				}
			}
		}
		//Second pass: do stock-levels of variations who don't want to manage themselves
		foreach($resultlist as $item)
			if (($item->parent_manage_stock == 'yes') && ($item->parent_item != null)) {
				$item->stock_status = $item->parent_item->stock_status;
				$item->stock_status_explicitly_set = $item->parent_item->stock_status_explicitly_set;
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

		$variations = explode(",", $listitem->variation_ids);

		$count = 0;
		foreach($variations as $variation) {

			//Copy item
			$item = clone $listitem;
			$resultlist[] = $item;
			$count++;

			$item->parent_title = $item->title; //This is for eBay feed only, and could otherwise be deleted
			$item->parent_item = $listitem;

			//Special Variations settings
			$item->item_group_id = $listitem->id;
			$item->id = $variation;
			$item->isVariable = true;

			//Some basics
			$product = get_product($item->id);
			$item->regular_price = $product->regular_price;
			$item->has_sale_price = false;
			if ($product->sale_price != "") {
				$item->has_sale_price = true;
				$item->sale_price = $product->sale_price;
			}
			$item->sku = $product->sku;

			//Go find the Variations
			$sql = "SELECT meta_key, meta_value FROM $wpdb->postmeta
				WHERE post_id = " . $variation . " AND 
				meta_key LIKE 'attribute_pa_%'";
			$attributes = $wpdb->get_results($sql);

			//Add the variations
			foreach($attributes as $this_attribute) {
				if (strpos($this_attribute->meta_key, 'attribute_pa') == 0)
					$this_attribute->meta_key = substr($this_attribute->meta_key, 13);
				$item->attributes[$this_attribute->meta_key] = $this_attribute->meta_value;
			}
		}
	
		//If we got our Variable Attributes, Done! Get out now
		if ($count > 0) {
			return $count;
		}

		//Here, life is more difficult because the user didn't select
		//specific variations

  }


}

?>