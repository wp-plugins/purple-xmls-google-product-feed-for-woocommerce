<?php

  /********************************************************************
  Version 2.0
    This gets a little complex. See design-document -> ProductCategoryExport
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-14
  ********************************************************************/

class PAProductW extends PAProduct {

}

class PProductList {

	public $AttributeCategory;
	public $exclude_variable_attributes;
	public $products = null;
	public $tag_list;
  
	public function __construct() {
		$this->AttributeCategory = array();
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
  
	public function get_customfields($parent) {
		global $wpdb;
		$sql = "SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE meta_key NOT LIKE '\_%' LIMIT 0, " . $parent->max_custom_field;
		$this->custom_fields = $wpdb->get_results($sql);
	}
  
	public function get_woobrands() {
		global $wpdb;
		$sql = "
			SELECT id, post_title, post_name, $wpdb->term_taxonomy.term_taxonomy_id, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name
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
		$sql = "
			SELECT id, post_title, post_name, $wpdb->term_taxonomy.term_taxonomy_id, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name as term_name
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
		$this->get_customfields($parent);

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

		if ($parent->has_product_range)
			$limit = 'LIMIT ' . $parent->product_limit_low . ', ' . $parent->product_limit_high;
		else
			$limit = '';
		
		$sql = "
				SELECT 
				  $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name, $wpdb->term_taxonomy.taxonomy, 
					$wpdb->terms.name as category_name, $wpdb->terms.term_id as category_id, details.name as product_type,
					attributes.meta_value as attribute_list, attribute_details.attribute_details, variation_id_table.variation_ids as variation_ids 
					#, stock_levels.parent_manage_stock
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
				#LEFT JOIN
				#	(
				#		SELECT post_id, meta_value as parent_manage_stock
				#		FROM $wpdb->postmeta
				#		WHERE meta_key = '_manage_stock'
				#	) as stock_levels ON stock_levels.post_id = $wpdb->posts.ID

				$extra_sql_part

				WHERE $wpdb->posts.post_status = 'publish'
					AND $wpdb->posts.post_type = 'product'
					AND $wpdb->term_taxonomy.taxonomy = 'product_cat'
					AND $wpdb->terms.term_id IN (" . $parent->categories->asCSV() . ")
					ORDER BY $wpdb->terms.term_id, post_date DESC

				$limit

					";

		$this->products = $wpdb->get_results($sql);

		if (count($this->products) == 0) {
			$parent->message .= 'ProductList returned Zero results';
		}
	} //loadProducts

	public function getProductList($parent, $remote_category) {

		global $wpdb;
		global $pfcore;

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

			//if no category associated with parent product, skip
			if ( !$parent->categories->containsCategory( $prod->category_id ) )
				continue;

			//if parent has variations, do we need it? 
			if ( $parent->categories->containsCategory( $prod->variation_ids ) )
				continue;

			//Duplicate check
			if ( $parent->ignoreDuplicates ) {
				$skip_this_item = isset($master_product_list[$prod->ID]);
				$master_product_list[$prod->ID] = 1;
				if ($skip_this_item)
					continue;
			}

			//Prepare the item
			$item = new PAProductW(); //extends PAProduct (basicfeed.php)
			$product = get_product($prod->ID); //WooCommerce - get product by id

			//Basics
			$item->id = $prod->ID;
			$item->parent_item = null;
			$item->attributes['id'] = $prod->ID;
			$item->attributes['title'] = $prod->post_title;
			$item->taxonomy = $prod->taxonomy;
			$item->isVariable = $prod->product_type == 'variable';
			$item->description_short = substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000);
			$item->description_long = substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000);
			$item->attributes['valid'] = true;

			//Fetch any default attributes Stage 0 (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 0)
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);
			

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

			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['product_type'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $prod->category_name));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);
			$item->attributes['category_id'] = $prod->category_id;
			$item->attributes['link'] = get_permalink($prod->ID);
			$thumb_ID = get_post_thumbnail_id($prod->ID);
			$thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
			$item->feature_imgurl = $thumb['0'];
			$attachments = $product->get_gallery_attachment_ids();
			$attachments = array_diff($attachments, array($thumb_ID));
			if ($attachments) 
			{
				foreach ($attachments as $attachment) 
				{
					$thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
					$imgurl = $thumb['0'];
					if (strlen($imgurl) > 0)
						$item->imgurls[] = $imgurl;
				}
			}

			$item->attributes['condition'] = 'New';
			$item->attributes['regular_price'] = $product->regular_price;
			$item->attributes['has_sale_price'] = false;
			if ( $product->sale_price != '' ) 
			{
				$item->attributes['has_sale_price'] = true;
				$item->attributes['sale_price'] = $product->sale_price;
			}
			$item->attributes['sku'] = $product->sku;
			$item->attributes['weight'] = $product->get_weight();
			//$item->parent_manage_stock = $prod->parent_manage_stock;
			$item->parent_manage_stock = true; //Temp... testing Calvin's new code
			$item->attributes['stock_status'] = 1; //Assume in stock
			$item->attributes['stock_quantity'] = 1;
			//$item->stock_status_explicitly_set = false;
			//$item->attributes['vendor'] = $product->vendor; Suggested new attribute not found on local dev setup
			$item->attributes['product_is_in_stock'] = $product->is_in_stock(); //already checks for global manage stock and product inventory stock_status
			$item->attributes['product_stock_qty'] = $product->get_stock_quantity();
			
			//get attributes
			//attempt using WC_Products and/or WC_Variable_Product			
			//$koostis = array_shift( wc_get_product_terms( $prod->id, 'pa-screen-size', array( 'fields' => 'names' ) ) );
			//$item->attributes['condition'] = $koostis[0];				

			//Attributes (General)
			if (strlen($prod->attribute_list) == 0)
				$attributes = array();
			else
				$attributes = unserialize($prod->attribute_list);
			foreach($attributes as $this_attribute) 
			{
				if ( isset($this_attribute['name']) && (strlen($this_attribute['name']) > 0) && (strpos($this_attribute['name'], 'pa_') === false) )
				//error_log($this_attribute['name'] . ' > ' . $this_attribute['value'] . ' | ' . strpos($this_attribute['name'], 'pa_'));
					$item->attributes[$this_attribute['name']] = $this_attribute['value'];
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
				if ($custom_field->post_id == $item->id)
					$item->attributes[$custom_field->meta_key] = $custom_field->meta_value;

			//If woocommerce-google-merchant-center, we have to do extra work to extract data
			if ( ($this->gmc_active) && (strlen($prod->gmc_value) > 0) ) 
			{
				$gmc_attributes = unserialize($prod->gmc_value);
				foreach( $gmc_attributes as $key => $this_attribute ) 
				{
					//Use this_attribute if no overrides force us to use any attributes OR if there are overrides and in_array()
					if ( (count($parent->gmc_attributes) == 0) || (in_array($key, $parent->gmc_attributes)) )
						switch ($key) 
						{
							case "description": 
								if (strlen($item->description_short) == 0) $item->description_short = $this_attribute;
								if (strlen($item->description_long) == 0) $item->description_long = $this_attribute;
								break;
							case "product_type":
								$item->attributes['product_type'] = $this_attribute;
								break;
							case "category":
								$item->attributes['localCategory'] = $this_attribute;
								break;
							case "condition":
								$item->attributes['condition'] = $this_attribute;
								break;
							case "shipping_weight":
								$item->attributes['weight'] = $this_attribute;
								break;							
							default:
								$item->attributes[$key] = $this_attribute;
						}
				}
			}

			$this->expandProduct($item, $parent);
		}

		$parent->logActivity('ProductList successfully generated.');

	}

	function expandProduct($listitem, $parent) {

		global $wpdb;
		global $pfcore;

		$resultlist = array();
		
			//Check for simple variation
			if ($listitem->isVariable)
				$variable_attribute_count = $this->populateVariableAttributes($listitem, $resultlist, $parent);
			else
				$variable_attribute_count = 0;

			if ($variable_attribute_count > 0) 
			{
				//If item has variations, don't export the parent (but we still need it in the list)
				$listitem->attributes['valid'] = false;
				$listitem->parent_manage_stock = 'no';
			}
			$resultlist[] = $listitem;

		//********************************************************************
		//Meta-data iteration
		//a) Now that variations are known, we can tell if it's in-stock or out-of-stock
		//********************************************************************

		foreach($resultlist as $index => $item) {

			//Strange bug: Woocommerce says parent_manage_stock = yes even if item has no parent
			if (($item->parent_manage_stock == 'yes') && ($item->parent_item == null))
				$item->parent_manage_stock = 'no';

			//$product_availability = $product->get_availability();	

			//attempting to pull flat  shipping cost
			//Note: extra call to new WC_product means an extra trip to the database which will increase timeouts (-K)
			/*
			$slug = $product->get_shipping_class();		
			$slug_object = get_term_by('slug', $slug, 'product_shipping_class'); 
			$slug_object->name;
			error_log(var_export($slug_object, true));

			$product = new WC_product($product->id);
 			echo $product->get_price_html();

			$item->attributes['condition'] = '.'.$slug_object->id;
			*/

			//STOCK CODE
			if ( $item->attributes['product_is_in_stock'] && $item->attributes['product_stock_qty'] > 0 )
				$item->attributes['stock_status'] = 1;
			else if ($item->attributes['product_stock_qty'] == '') 
				$item->attributes['stock_status'] = $item->attributes['product_is_in_stock']; //use parent's stock status if qty is blank
			else
				$item->attributes['stock_status'] = 0;
			$item->attributes['stock_quantity'] = $item->attributes['product_stock_qty'];
		
			/*
			foreach ($metadata as $m) {

				//Stock levels
				//Global setting. If unchecked, don't handle stock. Leave everything as in-stock.
				//In the case of parent-manage-stock, handle on second pass
				if (($pfcore->manage_stock) && ($item->parent_manage_stock != 'yes')) {

					if ($m->meta_key == '_stock_status') {
						$item->stock_status_explicitly_set = true;
						if ($m->meta_value == 'outofstock') 
							$item->attributes['stock_status'] = 0;
						
					}

					if (($m->meta_key == '_stock') && !$item->stock_status_explicitly_set) {
						$item->attributes['stock_quantity'] = $m->meta_value;
						//if (($m->meta_value == '') || ($m->meta_value < 1))
						if ($m->meta_value == '')  //don't change status. use parent settings
						{
							$item->attributes['stock_status'] = $product->is_in_stock() ;
						}
						else if ($m->meta_value < 1)
							$item->attributes['stock_status'] = 0;
						else 
							$item->attributes['stock_status'] = 1;
					}
				}
			} //foreach $metadata
			*/

		}

		/*
		//Second pass: Meta-data iteration
		foreach($resultlist as $item) {

			if ($pfcore->manage_stock) {
				//do stock-levels of variations who don't want to manage themselves
				if (($item->parent_manage_stock == 'yes') && ($item->parent_item != null)) {
					$item->attributes['stock_status'] = $item->parent_item->attributes['stock_status'];
					$item->attributes['stock_quantity'] = $item->parent_item->attributes['stock_quantity'];
					$item->stock_status_explicitly_set = $item->parent_item->stock_status_explicitly_set;
					if ($item->attributes['stock_quantity'] == 0)
						$item->attributes['stock_status'] = 0;
				}
			}
		}
		*/

		//Third pass: Meta-data iteration
		foreach($resultlist as $item) {
		
			if (($pfcore->manage_stock) && ($pfcore->hide_outofstock) && ($item->attributes['stock_status'] == 0))
				$item->attributes['valid'] = false;

			//Reformat "valid" if necessary
			if (isset($item->attributes['valid']) && (strcmp($item->attributes['valid'], 'false') == 0) )
				$item->attributes['valid'] = false;

		}

		foreach($resultlist as $item) {
			//Fetch any default attributes (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 1)
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);
		}

		foreach($resultlist as $item)
			$parent->handleProduct($item);

		foreach($resultlist as &$item) {
			foreach($item->attributes as &$x)
				unset($x);
			unset($item);
		}
		
  }
  
  function populateVariableAttributes($listitem, &$resultlist, $parent) {
  
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

			$item->attributes['parent_title'] = $item->attributes['title']; //This is for eBay feed only, and could otherwise be deleted
			$item->parent_item = $listitem;

			//Special Variations settings
			$item->item_group_id = $listitem->id;
			$item->id = $variation;
			$item->isVariable = true;
			$item->attributes['id'] = $variation;
			$item->attributes['item_group_id'] = $listitem->id;

			//Some basics
			$product = get_product($item->id);
			$item->product = $product; //Save for meta-data iteration

			$item->attributes['regular_price'] = $product->regular_price;
			$item->attributes['has_sale_price'] = false;
			if ($product->sale_price != "") {
				$item->attributes['has_sale_price'] = true;
				$item->attributes['sale_price'] = $product->sale_price;
			}
			$item->attributes['sku'] = $product->sku;
			$item->attributes['weight'] = $product->get_weight();
			$item->attributes['product_is_in_stock'] = $product->is_in_stock();
			$item->attributes['product_stock_qty'] = $product->get_stock_quantity();

			//Go find the Variations' Attributes
			$sql = "SELECT meta_key, meta_value FROM $wpdb->postmeta
				WHERE post_id = " . $variation . " AND 
				meta_key LIKE 'attribute\_pa\_%'";
			$attributes = $wpdb->get_results($sql);

			//Add the variation attributes
			//meta_value takes the slug from wp_terms instead of the name
			foreach($attributes as $this_attribute) {
				if (strpos($this_attribute->meta_key, 'attribute_pa') == 0)
					$this_attribute->meta_key = substr($this_attribute->meta_key, 13);
				$item->attributes[$this_attribute->meta_key] = $this_attribute->meta_value;
			}
		}

		//If we got our Variable Attributes, Done! Get out now
		if ($count > 0)
			return $count;

		//Here, life is more difficult because the user didn't select
		//specific variations

  }
}
