<?php

  /********************************************************************
  Version 2.0
    Product List from WP E-Commerce
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08
  ********************************************************************/

class PProductList {

	public function __construct() {

		$this->relatedData = array();

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

		if (!isset($this->relatedData['tag']) && $parent->allowRelatedData)
			$this->relatedData['tag'] = new PProductSupplementalData('product_tag');

		//********************************************************************
		//Load the products
		//********************************************************************

		$sql = "
			SELECT 
				posts.ID, posts.post_title, posts.post_content, posts.post_excerpt, posts.post_content as description,
				tblCategories.category_names, tblCategories.category_ids,
				images.image_link,
				variations.variation_ids
			FROM $wpdb->posts posts
			#Categories
			LEFT JOIN
				(
					SELECT postsAsTaxo.ID, GROUP_CONCAT(category_terms.name) as category_names, GROUP_CONCAT(category_terms.term_id) as category_ids
					FROM $wpdb->posts postsAsTaxo
					LEFT JOIN $wpdb->term_relationships category_relationships ON (postsAsTaxo.ID = category_relationships.object_id)
					LEFT JOIN $wpdb->term_taxonomy category_taxonomy ON (category_relationships.term_taxonomy_id = category_taxonomy.term_taxonomy_id)
					LEFT JOIN $wpdb->terms category_terms ON (category_taxonomy.term_id = category_terms.term_id)
					WHERE category_taxonomy.taxonomy = 'wpsc_product_category'
					GROUP BY postsAsTaxo.ID
				) as tblCategories ON tblCategories.ID = posts.ID
			#Link Images
			LEFT JOIN 
				(
					SELECT a.ID, a.post_parent, GROUP_CONCAT(a.guid) as image_link
					FROM $wpdb->posts a
					WHERE (post_status = 'inherit') and (post_type = 'attachment') and (post_mime_type LIKE 'image%')
					GROUP BY a.post_parent
				) as images ON images.post_parent = posts.ID
			#Variations
			LEFT JOIN
				(
					SELECT post_variations.post_parent, GROUP_CONCAT(post_variations.ID) as variation_ids
					FROM $wpdb->posts post_variations
					WHERE (post_variations.post_type = 'wpsc-product')
					GROUP BY post_variations.post_parent
				) as variations ON (variations.post_parent = posts.ID)
			WHERE (posts.post_parent = 0) AND (posts.post_type = 'wpsc-product') AND (posts.post_status = 'publish')";

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

			//if skip product with non-matching category
			$category_ids = explode(',', $prod->category_ids);
			$skip = true;
			foreach($category_ids as $id)
				if ($parent->categories->containsCategory($id) ) {
					$skip = false;
					break;
				}
			if ($parent->force_all_categories)
				$skip = false;
			if ($skip)
				continue;
			$category_names = explode(',', $prod->category_names);

			$item = new PAProduct();

			//Basic Attributes
			$item->id = $prod->ID;
			$item->attributes['id'] = $prod->ID;
			$item->attributes['title'] = $prod->post_title;
			$item->attributes['regular_price'] = 0;
			$item->attributes['sale_price'] = 0;
			$item->attributes['has_sale_price'] = false;
			$item->taxonomy = '';
			$item->description_short = substr(strip_shortcodes(strip_tags($prod->post_excerpt)), 0, 8192);
			$item->description_long = substr(strip_shortcodes(strip_tags($prod->description)), 0, 8192);
			if ( isset($item->description_short) )
				$item->attributes['description_short'] = $item->description_short;
			$item->attributes['weight'] = 0;
			$item->attributes['isVariable'] = false;
			$item->attributes['isVariation'] = false;
			$item->attributes['valid'] = true;

			//Fetch any default attributes Stage 0 (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 0 && !$thisDefault->isRuled && !isset($item->attributes[$thisDefault->attributeName]))
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			//Assign related data (like brands and tags)
			foreach($this->relatedData as $index => $relation)
				$relation->check($index, $item);

			//Extended Attributes
			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['product_type'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $category_names[0]));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);
			$item->attributes['category_names'] = $prod->category_names;
			$item->attributes['link'] = $pfcore->siteHost . '?wpsc-product=' . rawurlencode($item->attributes['title']);
			$images = explode(',', $prod->image_link);
			if (count($images) == 0)
				$item->attributes['feature_imgurl'] = '';
			else
				$item->attributes['feature_imgurl'] = $images[0];
			for ($i = 1; $i < count($images); $i++)
				$item->imgurls[] = $images[$i];
			$item->attributes['condition'] = 'New';

			//Fetch any default attributes Stage 1 (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 1 && !$thisDefault->isRuled)
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			//Custom Fields
			$this->postmetaLookup($item, $parent);

			if (!isset($prod->variation_ids)) {
				//Send this item out for Feed processing
				$parent->handleProduct($item);
				continue;
			}

			//********************************************************************
			//Variations
			//********************************************************************

			$parentItem = $item;

			$variation_ids = explode(',', $prod->variation_ids);

			foreach($variation_ids as $variation_id) {

				$item = clone($parentItem);
				$item->id = $variation_id;
				$item->attributes['id'] = $variation_id;
				$item->attributes['isVariable'] = false;
				$item->attributes['isVariation'] = true;
				$item->item_group_id = $prod->ID;
				$item->attributes['parent_title'] = $parentItem->attributes['title'];
				$item->attributes['parent_sku'] = $parentItem->attributes['sku']; //get parent sku

				//Attributes
				/*$sql = "
					SELECT posts.ID, parent_taxonomy_name.name as name, d.name as value
					FROM $wpdb->posts posts
					LEFT JOIN $wpdb->term_relationships b ON (posts.id = b.object_id)
					LEFT JOIN $wpdb->term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
					LEFT JOIN $wpdb->terms d ON (c.term_id = d.term_id)
					LEFT JOIN $wpdb->term_taxonomy parent_taxonomy on (c.parent = parent_taxonomy.term_taxonomy_id)
					LEFT JOIN $wpdb->terms parent_taxonomy_name on (parent_taxonomy.term_id = parent_taxonomy_name.term_id)
					WHERE (c.taxonomy = 'wpsc-variation') AND (posts.ID = $variation_id)";*/
				//if (version == ??)
					$sql = "
						SELECT posts.ID, parent_taxonomy_name.name as name, d.name as value
						FROM $wpdb->posts posts
						LEFT JOIN $wpdb->term_relationships b ON (posts.id = b.object_id)
						LEFT JOIN $wpdb->term_taxonomy c ON (b.term_taxonomy_id = c.term_taxonomy_id)
						LEFT JOIN $wpdb->terms d ON (c.term_id = d.term_id)
						LEFT JOIN $wpdb->terms parent_taxonomy_name on (c.parent = parent_taxonomy_name.term_id)
						WHERE (c.taxonomy = 'wpsc-variation') AND (posts.ID = $variation_id)";
				$attributes = $wpdb->get_results($sql);

				foreach($attributes as $attribute)
					$item->attributes[$attribute->name] = $attribute->value;

				//Custom Fields
				$this->postmetaLookup($item, $parent);

				//Send this item out for Feed processing
				$parent->handleProduct($item);
					

			}

		}

	}

	public function postmetaLookup($item, $parent) {

		global $wpdb;

		$sql = "
			SELECT meta_key, meta_value 
			FROM $wpdb->postmeta
			WHERE post_id = " . $item->attributes['id'];
		$attributes = $wpdb->get_results($sql);
		foreach($attributes as $this_attribute) {

			switch ($this_attribute->meta_key) {
				case '_wpsc_price':
					$item->attributes['regular_price'] = $this_attribute->meta_value;
					break;
				case '_wpsc_product_metadata':
					if ($parent->attribute_granularity > 5)
						$item->attributes['product_metadata'] = $this_attribute->meta_value;
					break;
				case '_wpsc_special_price':
					$item->attributes['sale_price'] = $this_attribute->meta_value;
					break;
				case '_wpsc_sku':
					$item->attributes['sku'] = $this_attribute->meta_value;
					break;
				case '_wpsc_stock':
					$item->attributes['stock_quantity'] = $this_attribute->meta_value;
					break;
				default:
					if (strlen($this_attribute->meta_key) > 1 && $this_attribute->meta_key[0] != '_')
						$item->attributes[$this_attribute->meta_key] = $this_attribute->meta_value;
			}
	
		}

		//Price
		if ($item->attributes['sale_price'] > 0)
			$item->attributes['has_sale_price'] = true;

		//In-stock status
		//a null $item->attributes['stock_quantity'] means no stock tracking, so in stock
		$item->attributes['stock_status'] = 1;
		if (strlen($item->attributes['stock_quantity']) == 0)
			$item->attributes['stock_quantity'] = 1;
		elseif ($item->attributes['stock_quantity'] == 0)
			$item->attributes['stock_status'] = 0;

	}

}

?>