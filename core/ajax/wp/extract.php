<?php

	/********************************************************************
	Version 1.0
		RapidCart Shim
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-10
	********************************************************************/

	define ('XMLRPC_REQUEST', true);
	ob_start(null);

	require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
	ob_clean();
	global $wpdb;

	$token = safeGetPostData('token');
	$request = safeGetPostData('request');
	$product_limit_low = safeGetPostData('product_limit_low');
	$product_limit_high = safeGetPostData('product_limit_high');
	$specifier = safeGetPostData('specifier');
	$specified = safeGetPostData('specified');

	$error = 0;
	if (strlen($token) == 0)
		$error = 1;
	if (strlen($request) == 0)
		$error = 2;

	$saved_token = get_option('cp_rapidcarttoken');
	if (strlen($saved_token) == 0)
		$saved_token = get_option('cp_licensekey');
	if (strlen($saved_token) == 0)
		$error = 5;
	else if ($token != $saved_token)
		$error = 4;

	//Make Specifier safe from SQL injection
	if (strlen($specifier) > 0) {
		$specifierArray = explode(',', $specifier);
		foreach($specifierArray as &$item)
			$item = (int) $item;
		$specifier = implode(',', $specifierArray);
	}

	//Parse the request
	switch ($request) {
		case 'categories':
			$query = "
				SELECT taxo.term_id as id, term.name as title, taxo.count as tally, taxo.parent as parent_category
				FROM $wpdb->term_taxonomy taxo
				LEFT JOIN $wpdb->terms term ON taxo.term_id = term.term_id
				WHERE taxo.taxonomy = 'product_cat'
			";
			break;
		case 'taxonomies':
			$query = "
				SELECT b.object_id, c.taxonomy, d.name 
				FROM $wpdb->term_relationships b
				LEFT JOIN $wpdb->term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
				LEFT JOIN $wpdb->terms d ON (c.term_id = d.term_id)
			";
			if (strlen($specifier) > 0)
				$query .= "WHERE object_id = $specifier ";
			break;
		case 'custom_fields':
			$query = "
				SELECT meta_id, post_id, meta_key, meta_value
				FROM $wpdb->postmeta
			";
			if (strlen($specifier) > 0)
				$query .= "WHERE post_id in ($specifier) ";
			break;
		case 'products':
			$query = "
					SELECT 
						$wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name,
						variation_id_table.variation_ids as variation_ids
					FROM $wpdb->posts

					#Variations
					LEFT JOIN
						(
							SELECT GROUP_CONCAT(postvars.id) as variation_ids, postvars.post_parent
							FROM $wpdb->posts postvars
							WHERE postvars.post_type = 'product_variation'
							GROUP BY postvars.post_parent
						) as variation_id_table on variation_id_table.post_parent = $wpdb->posts.ID

					WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product'
				";
			break;
		case 'init':
			//Do nothing
			$query = '';
			break;
		case 'specified':
			$query = $specified;
			break;
		default:
			if ($error == 0)
				$error = 3;
	}

	//Parse any limits
	if ($product_limit_low + $product_limit_high > 0)
		$query .=  ' LIMIT ' . (int) $product_limit_low . ', ' . (int) $product_limit_high;

	$result = new stdClass();
	$result->version = 2;
	$result->error = $error;
	$result->endOfResults = false;
	if ($error == 0)
		$result->results = $wpdb->get_results($query);
	else
		$result->results = array();
	$result->resultCount = count($result->results); //Never count variations into this value or CloudService will error

	//For Products, add the WooCommerce Product. Note: This won't work for WP-ECommerce
	if ($request == 'products') {
		foreach($result->results as &$this_result) {
			if (isset($this_result->item_group_id))
				break;
			//WooCommerce-Product
			$product = get_product($this_result->ID);
			//Links
			$this_result->link = get_permalink($this_result->ID);
			//Image Links
			$thumb_ID = get_post_thumbnail_id($this_result->ID);
			$thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
			$this_result->feature_imgurl = $thumb['0'];
			$attachments = $product->get_gallery_attachment_ids();
			$attachments = array_diff($attachments, array($thumb_ID));
			if ($attachments)  {
				foreach ($attachments as $attachment)  {
					$thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
					$imgurl = $thumb['0'];
					if (strlen($imgurl) > 0)
						$item->imgurls[] = $imgurl;
				}
			}
			//Main
			$this_result->description_short = strip_shortcodes(strip_tags($product->post->post_excerpt));
			$this_result->regular_price = $product->regular_price;
			$this_result->sale_price = $product->sale_price;
			$this_result->sku = $product->sku;
			
			$thisResultWeight = $product->get_weight();
			if ( $thisResultWeight != '')
				$this_result->weight = $thisResultWeight;
			
			$this_result->valid = true;
			//Stock
			$this_result->stock_status = $product->is_in_stock();
			$this_result->stock_quantity = $product->get_stock_quantity();
			//Checks
			if (strlen($this_result->post_content) > 4096) 
				$this_result->post_content = substr($this_result->post_content, 0, 4096);
			if (strlen($this_result->description_short) > 2048) 
				$this_result->description_short = substr($this_result->description_short, 0, 2048);

			//Variations
			$variations = explode(',', $this_result->variation_ids);
			if (strlen($this_result->variation_ids) * count($variations) > 0) {
				$this_result->valid = false;
				foreach ($variations as $variation)
					insertVariation($variation, $this_result, $result->results);
			}
			//Done?
			//if ($product_limit_low + $product_limit_high > 0 && count($result->results) < $product_limit_high)
		}

		if (count($result->results) == 0 && $error == 0) {
			/*
			//Check if we're done
			$specifiers = explode(',', $specifier);
			$chosen = end($specifiers);
			if (finalProduct($chosen))*/
				$result->endOfResults = true;
		}
	}

	if ($request == 'init') {
		$query = "
				SELECT COUNT(*) as count_of_products
				FROM $wpdb->posts
				WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product'
			";
		$data = $wpdb->get_results($query);
		$result->productCount = $data[0]->count_of_products;
		$query = "
			SELECT COUNT(*) as count_of_taxonomies
			FROM $wpdb->term_relationships taxo
		";
		$data = $wpdb->get_results($query);
		$result->taxonomyCount = $data[0]->count_of_taxonomies;
		$query = "
			SELECT COUNT(*) as count_of_categories
			FROM $wpdb->term_taxonomy taxo
			WHERE taxo.taxonomy = 'product_cat'
		";
		$data = $wpdb->get_results($query);
		$result->categoryCount = $data[0]->count_of_categories;
	}

	echo json_encode($result);

	/*function finalProduct($chosen) {
		if (strlen($chosen) == 0) 
			return false;
		$query = "
				SELECT COUNT(*) as count_of_products
				FROM $wpdb->posts
				WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product' AND $wpdb->posts.ID > $chosen
			";
		$data = $wpdb->get_results($query);
		return ($data[0]->count_of_products == 0);

	}*/

	function insertVariation($id, $source, &$resultlist) {

		global $wpdb;

		$this_result = clone $source;
		$resultlist[] = $this_result;

		//Special Variations settings
		$this_result->item_group_id = $source->ID;
		$this_result->ID = $id;
		$this_result->isVariable = true;

		//Some variation basics
		$product = get_product($id);

		$this_result->description_short = strip_shortcodes(strip_tags($product->post->post_excerpt));
		$this_result->regular_price = $product->regular_price;
		$this_result->sale_price = $product->sale_price;
		$this_result->sku = $product->sku;
		
		$thisResultWeight = get_weight();
		if ( $thisResultWeight != '')
			$this_result->weight = $thisResultWeight;

		$this_result->valid = true;
		//Stock
		$this_result->stock_status = $product->is_in_stock();
		$this_result->stock_quantity = $product->get_stock_quantity();
		//Checks
		if (strlen($this_result->description_short) > 2048) 
			$this_result->description_short = substr($this_result->description_short, 0, 2048);

	}

	function safeGetPostData($index) {
		if (isset($_POST[$index]))
			return $_POST[$index];
		else
			return '';
	}

?>