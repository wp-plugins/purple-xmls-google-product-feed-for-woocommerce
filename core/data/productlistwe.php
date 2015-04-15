<?php

  /********************************************************************
  Version 2.0
    Product List from WP E-Commerce
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08
  ********************************************************************/

class PProductList {

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
		//Load the products
		//********************************************************************

		$sql = "
			SELECT posts.id, variations.id as variation_id, posts.post_title, posts.post_content, sku_main.meta_value as sku_main, sku_var.meta_value as sku_var, 
				posts.post_excerpt, posts.post_content as description,
				#price
				price_main.meta_value as price_main, price_var.meta_value as price_var, sale_price_main.meta_value as sale_price_main, sale_price_var.meta_value as sale_price_var,
				#stock
				stock_main.meta_value as stock_main, stock_var.meta_value as stock_var,
				#meta
				#metadata_main.meta_value as metadata_main, metadata_var.meta_value as metadata_var
				category.term_id as category_id, category.name as category_name,
				attributes.attribute_names as attribute_names, attributes.attribute_values as attribute_values,
				images.image_link
				#custom_fields.cfnames, custom_fields.cfvalues
			FROM wp_posts posts
			#Link Variations
			LEFT JOIN 
				(
					SELECT id, post_parent
					FROM wp_posts
					WHERE post_type = 'wpsc-product'
				) as variations ON variations.post_parent = posts.id
			#SKU: Main
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_sku'
				) as sku_main ON sku_main.post_id = posts.id
			#SKU: Variation
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_sku'
				) as sku_var ON sku_var.post_id = variations.id
			#Price: Main
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_price'
				) as price_main ON price_main.post_id = posts.id
			#Price: Variation
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_price'
				) as price_var ON price_var.post_id = variations.id
			#SalePrice: Main
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_special_price'
				) as sale_price_main ON sale_price_main.post_id = posts.id
			#SalePrice: Variation
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_special_price'
				) as sale_price_var ON sale_price_var.post_id = variations.id
			#Stock: Main
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_stock'
				) as stock_main ON stock_main.post_id = posts.id
			#Stock: Variation
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_stock'
				) as stock_var ON stock_var.post_id = variations.id
			#MetaData: Main
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_product_metadata'
				) as metadata_main ON metadata_main.post_id = posts.id
			#MetaData: Variation
			LEFT JOIN
				(
					SELECT post_id, meta_key, meta_value
					FROM wp_postmeta
					WHERE meta_key = '_wpsc_product_metadata'
				) as metadata_var ON metadata_var.post_id = variations.id
			#Category
			LEFT JOIN
				(
					SELECT a.id, d.term_id, d.name
					FROM wp_posts a
					LEFT JOIN wp_term_relationships b ON (a.id = b.object_id)
					LEFT JOIN wp_term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
					LEFT JOIN wp_terms d ON (c.term_id = d.term_id)
					WHERE c.taxonomy = 'wpsc_product_category'
				) as category ON category.id = posts.id
			#Variation Attributes
			LEFT JOIN
				(
					SELECT a.id, GROUP_CONCAT(parent_taxonomy_name.name) as attribute_names, GROUP_CONCAT(d.name) as attribute_values
					FROM wp_posts a
					LEFT JOIN wp_term_relationships b ON (a.id = b.object_id)
					LEFT JOIN wp_term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
					LEFT JOIN wp_terms d ON (c.term_id = d.term_id)
					LEFT JOIN wp_term_taxonomy parent_taxonomy on (c.parent = parent_taxonomy.term_taxonomy_id)
					LEFT JOIN wp_terms parent_taxonomy_name on (parent_taxonomy.term_id = parent_taxonomy_name.term_id)
					WHERE c.taxonomy = 'wpsc-variation'
					GROUP BY a.id
				) as attributes ON attributes.id = variations.id
			#Link Images
			LEFT JOIN 
				(
					SELECT id, post_parent, GROUP_CONCAT(guid) as image_link
					FROM wp_posts a
					WHERE (post_status = 'inherit') and (post_type = 'attachment') and (post_mime_type LIKE 'image%')
					GROUP BY a.post_parent
				) as images ON images.post_parent = posts.id
			#Custom Fields
			#LEFT JOIN
			#	(
			#		SELECT post_id, GROUP_CONCAT(meta_key) as cfnames, GROUP_CONCAT(meta_value) as cfvalues
			#		FROM wp_postmeta 
			#		WHERE meta_key NOT LIKE '\_%'
			#		GROUP BY post_id
			#	) as custom_fields ON custom_fields.post_id = posts.id
			WHERE (posts.post_type = 'wpsc-product') AND (posts.post_status = 'publish')";

		$this->products = $wpdb->get_results($sql);

		if (count($this->products) == 0)
			$parent->message .= 'ProductList returned Zero results';

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

			if (!$parent->categories->containsCategory($prod->category_id))
				continue;

			//Duplicate check
			if ( $parent->ignoreDuplicates ) {
				$skip_this_item = (isset($master_product_list[$prod->id]) && isset($master_product_list[$prod->variation_id]));
				if ($prod->id > 0)
					$master_product_list[$prod->id] = 1;
				if ($prod->variation_id > 0)
					$master_product_list[$prod->variation_id] = 1;
				if ($skip_this_item)
					continue;
			}

			$item = new PAProduct();

			$isVariation = strlen($prod->variation_id) > 0;

			//Basics
			if (!$isVariation) {
				$item->id = $prod->id;
				$item->attributes['isVariable'] = false;
				$item->attributes['isVariation'] = false;
				$item->attributes['sku'] = $prod->sku_main;
				$item->attributes['regular_price'] = $prod->price_main;
				$item->attributes['has_sale_price'] = false;
				if ($prod->sale_price_main > 0) {
					$item->attributes['has_sale_price'] = true;
					$item->attributes['sale_price'] = $prod->sale_price_main;
				}
				$item->attributes['stock_quantity'] = $prod->stock_main;
			} else {
				$item->id = $prod->variation_id;
				$item->attributes['isVariable'] = false;
				$item->attributes['isVariation'] = true;
				$item->attributes['sku'] = $prod->sku_var;
				$item->attributes['parent_sku'] = $prod->sku_main;
				$item->attributes['regular_price'] = $prod->price_var;
				$item->attributes['has_sale_price'] = false;
				if ($prod->sale_price_var > 0) {
					$item->attributes['has_sale_price'] = true;
					$item->attributes['sale_price'] = $prod->sale_price_var;
				}
				$item->attributes['stock_quantity'] = $prod->stock_var;
				$item->item_group_id = $prod->id;
				$item->attributes['parent_title'] = '';
			}

			$item->attributes['id'] = $item->id;
			$item->attributes['title'] = $prod->post_title;
			$item->taxonomy = '';
			
			$item->description_short = substr(strip_shortcodes(strip_tags($prod->post_excerpt)), 0, 5000);
			$item->description_long = substr(strip_shortcodes(strip_tags($prod->description)), 0, 5000);
			$item->attributes['valid'] = true;

			//Fetch any default attributes Stage 0 (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 0 && !$thisDefault->isRuled && !isset($item->attributes[$thisDefault->attributeName]))
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['product_type'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $prod->category_name));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);
			$item->attributes['link'] = $pfcore->siteHost . '?wpsc-product=' . rawurlencode($item->attributes['title']);
			$images = explode(',', $prod->image_link);
			if (count($images) == 0)
				$item->attributes['feature_imgurl'] = '';
			else
				$item->attributes['feature_imgurl'] = $images[0];
			for ($i = 1; $i < count($images); $i++)
				$item->imgurls[] = $images[$i];
			$item->attributes['condition'] = 'New';
			
			//$item->attributes['weight'] = $prod->weight;

			//Attributes
			$attribute_names = explode(',', $prod->attribute_names);
			$attribute_values = explode(',', $prod->attribute_values);
			foreach($attribute_names as $key => $value)
				if (strlen($value) > 0)
					$item->attributes[$value] = $attribute_values[$key];

			//In-stock status
			//a null $item->attributes['stock_quantity'] means no stock tracking, so in stock
			$item->attributes['stock_status'] = 1;
			if (strlen($item->attributes['stock_quantity']) == 0)
				$item->attributes['stock_quantity'] = 1;
			elseif ($item->attributes['stock_quantity'] == 0)
				$item->attributes['stock_status'] = 0;

			//Fetch any default attributes (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 1 && !$thisDefault->isRuled)
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			//Custom Fields: Old
			/*$cfnames = explode(',', $prod->cfnames);
			$cfvalues = explode(',', $prod->cfvalues);
			foreach ($cfnames as $idx => $name) {
				if (isset($cfvalues[$idx]) && (!isset($item->attributes[$name]) || strlen($item->attributes[$name]) == 0) )
					$item->attributes[$name] = $cfvalues[$idx];
			}*/
			$sql = "SELECT meta_key, meta_value FROM $wpdb->postmeta
				WHERE post_id = " . $item->attributes['id'] . " AND meta_key NOT LIKE '\_%'";
			$attributes = $wpdb->get_results($sql);
			foreach($attributes as $this_attribute)
				$item->attributes[$this_attribute->meta_key] = $this_attribute->meta_value;
	  
			//Send this item out for Feed processing
			$parent->handleProduct($item);

		}

	}

}

?>