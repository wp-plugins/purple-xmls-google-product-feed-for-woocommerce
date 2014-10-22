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

	$token = safeGetPostData('token');
	$request = safeGetPostData('request');
	$product_limit_low = safeGetPostData('product_limit_low');
	$product_limit_high = safeGetPostData('product_limit_high');

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
	if ($token != $saved_token)
		$error = 4;

	//Parse the request
	if ($request == 'taxonomies')
		$query = "
			SELECT a.ID, c.taxonomy, d.name 
			FROM $wpdb->posts a
			LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id)
			LEFT JOIN $wpdb->term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
			LEFT JOIN $wpdb->terms d ON (c.term_id = d.term_id)";
	else if ($request == 'custom_fields')
		$query = "
						SELECT post_id, meta_key, meta_value
						FROM $wpdb->postmeta";
	else if ($request == 'products')
		$query = "
				SELECT 
				  $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name
					FROM $wpdb->posts
					WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product'
				";
	else if ($error == 0)
		$error = 3;

	//Parse any limits
	if ($product_limit_low + $product_limit_high > 0)
		$query .=  ' LIMIT ' . $product_limit_low . ', ' . $product_limit_high;

	$result = new stdClass();
	$result->error = $error;
	if ($error == 0)
		$result->results = $wpdb->get_results($query);

	//For Products, add the WooCommerce Product. Note: This won't work for WP-ECommerce
	if ($request == 'products')
		foreach($result->results as &$this_result) {
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
			$this_result->weight = $product->get_weight();
			//Stock
			$this_result->stock_status = $product->is_in_stock();
			$this_result->stock_quantity = $product->get_stock_quantity();
		}
	
	echo json_encode($result);

	function safeGetPostData($index) {
		if (isset($_POST[$index]))
			return $_POST[$index];
		else
			return '';
	}

?>