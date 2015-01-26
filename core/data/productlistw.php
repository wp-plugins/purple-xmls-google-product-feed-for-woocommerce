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

class PMasterAttributeList {

	protected $children;

	public function __construct() {
		$this->children = array();
	}

	public function add($child) {
		$this->children[] = $child;
	}

	public function findByName($name) {
		foreach ($this->children as $child)
			if ($child->name == $name)
				return $child;
		return null;
	}

	public function findBySlug($slug) {
		foreach ($this->children as $mainChild)
			foreach ($mainChild->children as $termChild)
				if ($termChild->slug == $slug)
					return $termChild;
		return null;
	}

}

class PProductList {

	public $AttributeCategory;
	public $exclude_variable_attributes;
	public $masterAttributeList;
	public $products = null;
	//public $tag_list;
	public $relatedData;
  
	public function __construct() {

		$this->AttributeCategory = array();
		$this->relatedData = array();

		$this->loadMasterAttributeList();

	}

  /*function ExistsInChildList($needle, $haystack) {
    $result = false;
	foreach($haystack as $x) {
	 	if ($x->ID == $needle->id) {
	    	$result = true;
			break;
	  	}
	}
	return $result;
  }*/
  
	public function get_customfields($parent) {
		if ($parent->max_custom_field == 0)
			$this->custom_fields = array();
		else {
			global $wpdb;
			$sql = "SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE meta_key NOT LIKE '\_%' LIMIT 0, " . $parent->max_custom_field;
			$this->custom_fields = $wpdb->get_results($sql);
		}
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

		if (!isset($this->relatedData['brand']) && $parent->allowRelatedData)
			$this->relatedData['brand'] = new PProductSupplementalData('product_brand');
		if (!isset($this->relatedData['tag']) && $parent->allowRelatedData)
			$this->relatedData['tag'] = new PProductSupplementalData('product_tag');
		
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
			$limit = 'LIMIT ' . $parent->product_limit_low . ', ' . $parent->product_limit_high - $parent->product_limit_low;
		else
			$limit = '';

		if ($parent->allow_attributes) {
			$attribute_select = 'attributes.meta_value as attribute_list, attribute_details.attribute_details, ';
			$attribute_sql_part = "
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
					) as attribute_details ON attribute_details.ID = $wpdb->posts.ID";
		} else {
			$attribute_select = '';
			$attribute_sql_part = '';
		}
		
		$sql = "
				SELECT 
				  $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name, 
					tblCategories.category_names, tblCategories.category_ids,
					#$wpdb->term_taxonomy.taxonomy, $wpdb->terms.name as category_name, $wpdb->terms.term_id as category_id, 
					details.name as product_type,
					$attribute_select
					variation_id_table.variation_ids as variation_ids 
					#, stock_levels.parent_manage_stock
					$extra_sql_query
				FROM $wpdb->posts
				
				#Categories
				LEFT JOIN
					(
						SELECT postsAsTaxo.ID, GROUP_CONCAT(category_terms.name) as category_names, GROUP_CONCAT(category_terms.term_id) as category_ids
						FROM $wpdb->posts postsAsTaxo
						LEFT JOIN $wpdb->term_relationships category_relationships ON (postsAsTaxo.ID = category_relationships.object_id)
						LEFT JOIN $wpdb->term_taxonomy category_taxonomy ON (category_relationships.term_taxonomy_id = category_taxonomy.term_taxonomy_id)
						LEFT JOIN $wpdb->terms category_terms ON (category_taxonomy.term_id = category_terms.term_id)
						WHERE category_taxonomy.taxonomy = 'product_cat' # AND category_terms.term_id IN (" . $parent->categories->asCSV() . ")
						GROUP BY postsAsTaxo.ID
					) as tblCategories ON tblCategories.ID = $wpdb->posts.ID
				
				#Link in product type
				LEFT JOIN 
					(
						SELECT a.ID, d.name FROM $wpdb->posts a
						LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id)
						LEFT JOIN $wpdb->term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
						LEFT JOIN $wpdb->terms d ON (c.term_id = d.term_id)
						WHERE c.taxonomy = 'product_type'
					) as details ON details.ID = $wpdb->posts.ID
				
				$attribute_sql_part

				#variations
				LEFT JOIN
					(
						SELECT GROUP_CONCAT(postvars.id) as variation_ids, postvars.post_parent
						FROM $wpdb->posts postvars
						WHERE (postvars.post_type = 'product_variation') AND (postvars.post_status = 'publish')
						GROUP BY postvars.post_parent
					) as variation_id_table on variation_id_table.post_parent = $wpdb->posts.ID

				$extra_sql_part

				WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product'
				ORDER BY post_date ASC

				$limit

					";

		$this->products = $wpdb->get_results($sql);

		if (count($this->products) == 0) {
			$parent->message .= 'ProductList returned Zero results';
		}
	} //loadProducts

	protected function loadMasterAttributeList() {
		$this->masterAttributeList = new PMasterAttributeList();
		global $wpdb;

		//Load the main attributes
		$sql = '
			SELECT attribute_name as name, attribute_type as type
			FROM ' . $wpdb->prefix . 'woocommerce_attribute_taxonomies';
		$data = $wpdb->get_results($sql);
		foreach ($data as $datum) {
			$thisAttribute = new stdClass();
			$thisAttribute->name = $datum->name;
			$thisAttribute->type = $datum->type;
			$thisAttribute->children = array();
			$this->masterAttributeList->add($thisAttribute);
		}

		//Load the taxonomies
		$sql = "
			SELECT taxo.taxonomy, terms.name, terms.slug
			FROM $wpdb->term_taxonomy taxo
			LEFT JOIN $wpdb->terms terms ON (terms.term_id = taxo.term_id)
			WHERE taxo.taxonomy LIKE 'pa\_%'
		";
		$data = $wpdb->get_results($sql);

		//Load the taxonomies in as child terms
		foreach ($data as $datum) {
			$thisAttribute = $this->masterAttributeList->findByName(substr($datum->taxonomy, 3));
			if ($thisAttribute != null) {
				$term = new stdClass();
				$term->taxonomy = $thisAttribute->name;
				$term->name = $datum->name;
				$term->slug = $datum->slug;
				$thisAttribute->children[] = $term;
			}
		}

	}

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

			//if skip product with non-matching category
			$category_ids = explode(',', $prod->category_ids);
			$skip = true;
			foreach($category_ids as $id)
				if ($parent->categories->containsCategory($id) ) {
					$skip = false;
					break;
				}
			if ($skip)
				continue;
			$category_names = explode(',', $prod->category_names);

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
			$item->attributes['id'] = $prod->ID;
			$item->attributes['title'] = $prod->post_title;
			$item->taxonomy = $category_names;
			$item->attributes['isVariable'] = $prod->product_type == 'variable';
			$item->attributes['isVariation'] = false;
			$item->description_short = substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000);
			$item->description_long = substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000);
			$item->attributes['valid'] = true;

			//Fetch any default attributes Stage 0 (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 0 && !$thisDefault->isRuled && !isset($item->attributes[$thisDefault->attributeName]))
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			//Assign related data (like brands and tags)
			foreach($this->relatedData as $index => $relation)
				$relation->check($index, $item);

			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['product_type'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $category_names[0]));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);
			$item->attributes['category_id'] = $category_ids[0];
			$item->attributes['category_ids'] = $category_ids;
			$item->attributes['link'] = get_permalink($prod->ID);
			$thumb_ID = get_post_thumbnail_id($prod->ID);
			$thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
			$item->attributes['feature_imgurl'] = $thumb['0'];
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
			if ($parent->get_wc_shipping_attributes) {
				//WooCommerce shipping weight and unit				
				//WooCommerce shipping dimensions + unit
				$item->attributes['length'] = $product->length;
				$item->attributes['width'] = $product->width;
				$item->attributes['height'] = $product->height; 
			}
			//$item->parent_manage_stock = $prod->parent_manage_stock;
			$item->parent_manage_stock = true; //Temp... testing Calvin's new code
			$item->attributes['stock_status'] = 1; //Assume in stock
			$item->attributes['stock_quantity'] = 1;
			//$item->stock_status_explicitly_set = false;

			if ( isset($product->vendor) )
				$item->attributes['vendor'] = $product->vendor; //Suggested new attribute not found on local dev setup
			if ( isset($product->model) )
				$item->attributes['model'] = $product->model;
			if ( isset($product->size) )
				$item->attributes['size'] = $product->size;
			$item->attributes['product_is_in_stock'] = $product->is_in_stock(); //already checks for global manage stock and product inventory stock_status
			$item->attributes['product_stock_qty'] = $product->get_stock_quantity();
			
			//get attributes
			//attempt using WC_Products and/or WC_Variable_Product			
			//$koostis = array_shift( wc_get_product_terms( $prod->id, 'pa-screen-size', array( 'fields' => 'names' ) ) );
			//$item->attributes['condition'] = $koostis[0];				

			//Attributes (General)
			if (!$parent->allow_attributes || strlen($prod->attribute_list) == 0)
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
			if ($parent->allow_attributes)
				$item->attribute_details = explode(',', $prod->attribute_details);
			else
				$item->attribute_details = array();
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

			//Fetch any default attributes Stage 5 (Mapping 3.1)
			if ($parent->attributeDefaultStages[5] > 0) {
				foreach ($parent->attributeDefaults as $thisDefault)
					if ($thisDefault->stage == 5 && !$thisDefault->isRuled)
						$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);
			}

			$this->expandProduct($item, $parent);

		}

		$parent->logActivity('ProductList successfully generated.');

	}

	function expandProduct($listitem, $parent) {

		global $wpdb;
		global $pfcore;

		$resultlist = array();

		//Insert any WooCommerce "Official" Variations
		$variations = explode(",", $listitem->variation_ids);
		foreach ($variations as $variation)
			$this->insertVariation($variation, $listitem, $resultlist, $parent);
		
		if (count($resultlist) == 0)  {
			//If item has no variations, use the parent
			$listitem->parent_manage_stock = 'no';
			$resultlist[] = $listitem;
		}

		//Process the results

		foreach($resultlist as $index => $item) {

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
			unset($item->attributes['product_stock_qty']); //This was causing confusion among users
			unset($item->attributes['product_is_in_stock']); //This was causing confusion among users

			//Hide out of stock
			if (($pfcore->manage_stock) && ($pfcore->hide_outofstock) && ($item->attributes['stock_status'] == 0))
				$item->attributes['valid'] = false;

			//Reformat "valid" if necessary
			if (isset($item->attributes['valid']) && (strcmp($item->attributes['valid'], 'false') == 0) )
				$item->attributes['valid'] = false;

			//Fetch any default attributes (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 1 && !$thisDefault->isRuled)
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			//Send this item out for Feed processing
			$parent->handleProduct($item);

			//Free resources
			foreach($item->attributes as &$x)
				unset($x);
			unset($item);

			if (!$listitem->attributes['isVariable'])
				break;

		}
		
  }
  
  function insertVariation($id, $parentItem, &$resultlist, $parent) {

		//********************************************************************
		//Variable attributes occur when the user has fully defined the variations and attributes.
		//In woocommerce, a true variation appears in wp_posts.post_type = product_variation
		//with post_parent pointed to the original product. So it's like a sub-product or a sub-post.
		//********************************************************************

		if (strlen($id) == 0)
			return;

		global $wpdb;

		//Copy item
		$item = clone $parentItem;
		$resultlist[] = $item;

		$item->attributes['parent_title'] = $item->attributes['title']; //This is for eBay feed only, and could otherwise be deleted

		//Special Variations settings
		$item->item_group_id = $parentItem->id;
		$item->id = $id;
		$item->attributes['isVariation'] = true;
		$item->attributes['id'] = $id;
		$item->attributes['item_group_id'] = $parentItem->id;

		//Some basics
		$product = get_product($id);
		$item->product = $product; //Save for meta-data iteration

		$item->attributes['regular_price'] = $product->regular_price;
		$item->attributes['has_sale_price'] = false;
		if ($product->sale_price != "") {
			$item->attributes['has_sale_price'] = true;
			$item->attributes['sale_price'] = $product->sale_price;
		}
		$item->attributes['sku'] = $product->sku;
		$item->attributes['weight'] = $product->get_weight();
		if ($parent->get_wc_shipping_attributes) {
			//WooCommerce shipping dimensions + unit
			//dimension unit provided from feedcore.php
			$item->attributes['length'] = $product->length;
			$item->attributes['width'] = $product->width;
			$item->attributes['height'] = $product->height; 
		}

		$item->attributes['product_is_in_stock'] = $product->is_in_stock();
		$item->attributes['product_stock_qty'] = $product->get_stock_quantity();

		//Go find the Variations' Attributes
		$sql = "SELECT meta_key, meta_value FROM $wpdb->postmeta
			WHERE post_id = " . $id . " AND 
			meta_key LIKE 'attribute\_pa\_%'";
		$attributes = $wpdb->get_results($sql);

		//Add the variation attributes
		//meta_value takes the slug from wp_terms instead of the name
		$permutations = array();
		foreach($attributes as $this_attribute) {
			if (strpos($this_attribute->meta_key, 'attribute_pa') == 0) {
				$this_attribute->meta_key = substr($this_attribute->meta_key, 13);
				//Convert from Slug to Term
				$term = $this->masterAttributeList->findBySlug($this_attribute->meta_value);
				if ($term != null)
					$this_attribute->meta_value = $term->name;
				//If no meta_value provided it means ANY of this attribute. Mark it for future permutation
				if (strlen($this_attribute->meta_value) == 0) {
					$termcat = $this->masterAttributeList->findByName($this_attribute->meta_key);
					if ($termcat != null)
						$permutations[] = $termcat;
				}
			}
			$item->attributes[$this_attribute->meta_key] = $this_attribute->meta_value;
		}

		//If no permutations, we're done
		if (count($permutations) == 0 || !$parent->allow_variationPermutations)
			return;

		//The presence of permutations forcefully invalidate existing items
		 foreach ($resultlist as $thisItem)
		 	if ($thisItem->id == $id)
		 		$thisItem->attributes['valid'] = false;

		//Now, add the permutations
		//for ($i = 0; $i < count($permutations); $i++) //Only need this loop if counting null values into permutations
		// $i = 0;
		// $this->permute($id, $i, $permutations, $resultlist, $parent);

  }

	function permute($id, $index, $permutations, &$resultlist, $parent, $stack = array() ) {
		foreach ($permutations[$index]->children as $thisChild) 
			if ($index == count($permutations) - 1) {
				//At the end of the permutation chain, insert
				foreach($resultlist as $listitem)
					if ($listitem->id == $id && !$listitem->attributes['valid']) {
						$item = clone $listitem;
						$item->attributes['valid'] = true;
						$resultlist[] = $item;
						$newStack = $stack;
						$newStack[] = $thisChild;
						//Debug
						//$a = '';
						//foreach($newStack as $term) $a .= $term->taxonomy . '=' . $term->name . ' ';
						//error_log($id . ' end of stack ' . $a);
						foreach($newStack as $term)
							$item->attributes[$term->taxonomy] = $term->name;
					}
			} else {
				//Not the end of permutation chain means keep looking
				$newStack = $stack;
				$newStack[] = $thisChild;
				$this->permute($id, $index + 1, $permutations, $resultlist, $parent, $newStack);
			}
	}

}
