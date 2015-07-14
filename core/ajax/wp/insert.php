<?php

	/********************************************************************
	Version 1.0
		RapidCart Shim for importing products
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-11
	********************************************************************/

define ('XMLRPC_REQUEST', true);
ob_start(null);

require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_clean();

$handler = new PRapidCartImporter();
$handler->main();

class PRapidCartImporter {

	//********************************************************************
	//Add Product
	///********************************************************************

	//The user can choose what parts of a product will be updated.
	//For example they could update stock_quantity only.
	public function allowProductUpdate($product, $field) {
		if (!isset($product->attributes[$field]))
			return false;
		if (count($product->update_fields) == 1 && $product->update_fields[0] == '*')
			return true;
		return (in_array($field, $product->update_fields));
	}

	public function findProductByRCID($id) {
		global $wpdb;
		$query = "
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_rc_id' AND meta_value='$id'
			";
		$data = $wpdb->get_results($query);
		if (count($data) > 0)
			return $data[0]->post_id;
		else
			return 0;
	}

	public function findProductBySku($sku) {
		global $wpdb;
		$query = "
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_rc_id' AND meta_value='$sku'
			";
		$data = $wpdb->get_results($query);
		if (count($data) > 0)
			return $data[0]->post_id;
		else
			return 0;
	}

	public function findProduct($product) {

		//Does product exist?
		switch ($product->match_algorithm) {
			case 'default':
				$post_id = $this->findProductByRCID($product->id);
				break;
			case 'sku':
				//Means try RcID but if fail, use sku
				$post_id = $this->findProductByRCID($product->id);
				if ($post_id == 0)
					$post_id = $this-> findProductBySku($product->sku);
				break;
			case 'sku-only':
				//Means only ever use sku
				$post_id = $this-> findProductBySku($product->sku);
				break;
			case 'native':
				//The product originated from this database, to that means
				//it must still exist by the original remote id
				$post_id = $product->remote_id;
				break;
			default:
				//No matching at all (user does not want the product inserted
				$post_id = 0;
		}
		return $post_id;

	}

	public function updateProduct($product) {

		global $wpdb;
		$post_id = 0;

		//Does product exist?
		switch ($product->match_algorithm) {
			case 'default':
				$post_id = $this->findProductByRCID($product->id);
				break;
			case 'sku':
				//Means try RcID but if fail, use sku
				$post_id = $this->findProductByRCID($product->id);
				if ($post_id == 0)
					$post_id = $this-> findProductBySku($product->sku);
				break;
			case 'sku-only':
				//Means only ever use sku
				$post_id = $this-> findProductBySku($product->sku);
				break;
			case 'native':
				//The product originated from this database, to that means
				//it must still exist by the original remote id
				$post_id = $product->remote_id;
				break;
		}

		//Skip or insert?
		if ($post_id == 0 && $product->insert_algorithm == 'ignore')
			return;

		//What fields to update?
		$product->update_fields = explode(',', $product->update_fields);
		//Unpack Attributes
		$product->attributes = get_object_vars($product->attributes);

		if ($post_id == 0) {

			//Create post
			$post = array(
				'post_author' => get_current_user_id(),
				'post_content' => $product->description,
				'post_status' => 'publish',
				'post_title' => $product->title,
				'post_parent' => '',
				'post_type' => 'product',
				//'tags_input' => array($tags)
			);

			$post_id = wp_insert_post( $post, $wp_error );
			if($post_id){
				//$attach_id = get_post_meta($product->parent_id, "_thumbnail_id", true);
				//add_post_meta($post_id, '_thumbnail_id', $attach_id);
			}
			wp_set_object_terms( $post_id, 'Races', 'product_cat' );
			wp_set_object_terms($post_id, 'simple', 'product_type');

			update_post_meta( $post_id, '_visibility', 'visible' );
			update_post_meta( $post_id, 'total_sales', '0');
			update_post_meta( $post_id, '_purchase_note', '' );
			update_post_meta( $post_id, '_featured', 'no' );
			update_post_meta( $post_id, '_product_attributes', array());
			update_post_meta( $post_id, '_stock_status', 'instock');
			update_post_meta( $post_id, '_manage_stock', 'no' );
			update_post_meta( $post_id, '_sold_individually', '' );
			update_post_meta( $post_id, '_sale_price_dates_from', '' );
			update_post_meta( $post_id, '_sale_price_dates_to', '' );
			update_post_meta( $post_id, '_backorders', 'no' );
			update_post_meta( $post_id, '_product_image_gallery', '');
			update_post_meta( $post_id, '_rc_id', $product->id );
			$product->update_fields = array('*');

		} else {
			$post = array(
				'ID' => $post_id,
				'post_content' => $product->description,
				'post_status' => $product->attributes['post_status'],
				'post_title' => $product->title,
				//'post_parent' => '',
			);
			if ($this->allowProductUpdate($product, 'post'))
				wp_update_post($post);
		}

		//$attributes = 
		if ($this->allowProductUpdate($product, 'regular_price'))
			update_post_meta( $post_id, '_regular_price', $product->attributes['price'] );
		if ($this->allowProductUpdate($product, 'sale_price'))
			update_post_meta( $post_id, '_sale_price', $product->attributes['sale_price'] );
		if ($this->allowProductUpdate($product, 'weight'))
			update_post_meta( $post_id, '_weight', $product->attributes['weight'] );
		if ($this->allowProductUpdate($product, 'length'))
			update_post_meta( $post_id, '_length', $product->attributes['length'] );
		if ($this->allowProductUpdate($product, 'width'))
			update_post_meta( $post_id, '_width', $product->attributes['width'] );
		if ($this->allowProductUpdate($product, 'height'))
			update_post_meta( $post_id, '_height', $product->attributes['height'] );
		if ($this->allowProductUpdate($product, 'sku'))
			update_post_meta( $post_id, '_sku', $product->attributes['sku']);
		if ($this->allowProductUpdate($product, 'price'))
			update_post_meta( $post_id, '_price', $product->attributes['price'] );
		if ($this->allowProductUpdate($product, 'stock'))
			update_post_meta( $post_id, '_stock', $product->stock_quantity );

		if ($this->allowProductUpdate($product, 'variations') && $product->parent_id == 0) {

			wp_set_object_terms ($post_id, 'variable', 'product_type');

			if (strlen($product->attributes['options']) > 0)
				$options = explode(',', $product->attributes['options']);
			else
				$options = array();
			$attributeData = array();
			foreach($options as $option) {
				$option = trim(strtolower($option));
				if (strpos($option, 'pa_') === false || strpos($option, 'pa_') > 0)
					$option = 'pa_' . $option;
				//$avail_attributes = array(
				//	'black',
				//	'red',
				//	'yellow'
				//);
				//wp_set_object_terms( $post_id, $avail_attributes, $option );
				$attributeData[$option] = array(
					'name'=> $option,
					'value'=>'',
					'is_visible' => '1',
					'is_variation' => '1',
					'is_taxonomy' => '1'
					);
			}
			update_post_meta( $post_id, '_product_attributes', $attributeData );

		}

		//We need to reply by returning post_id so that attributes & custom_fields can be configured

	}

	public function updateProductAttributes($options) {
		global $wpdb;
		foreach($options as $option) {
			//Apply this attribute to the given product (This idea didn't work: Variation dropdowns were not filled in)
			$post_id = $this->findProductByRCID($option->product_id);
			if ($post_id > 0)
				wp_set_object_terms( $post_id, $option->contents, 'pa_' . $option->name );
			//Check if Attribute exists in WooCommerce
			$query = '
					SELECT attribute_id
					FROM ' . $wpdb->prefix . 'woocommerce_attribute_taxonomies
					WHERE attribute_name = \'' . $option->name . '\'';
			$data = $wpdb->get_results($query);
			//If Attribute does not exist, create it (This idea had no effect, though it made the tabular entry)
			if (count($data) == 0) {
				$data = array(
					'attribute_name' => sanitize_title($option->name), 
					'attribute_label' => $option->name, 
					'attribute_type' => 'select', 
					'attribute_orderby' => 'menu)_order',
				);
				$wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $data);
			}
		}

	}

	public function updateVariation($product) {

		global $wpdb;
		$post_id = $this->findProduct($product);

		//Skip or insert?
		if ($post_id == 0 && $product->insert_algorithm == 'ignore')
			return;

		//What fields to update?
		$product->update_fields = explode(',', $product->update_fields);
		//Unpack Attributes
		$product->attributes = get_object_vars($product->attributes);

		$post = array(
			'guid' =>  home_url() . '/?product_variation=product-' . $product->parent_id . '-variation-' . $product->variation_index,
			'post_author' => get_current_user_id(),
			'post_name' => 'product-' . $product->parent_id . '-variation-' . $product->variation_index,
			'post_status' => 'publish',
			'post_title' => 'Variation #' . $product->variation_index . ' of ' . esc_attr($product->title),
			'post_parent' => $this->findProductByRCID($product->parent_id),
			'post_type' => 'product_variation',
			//'tags_input' => array($tags)
		);

		if ($post_id == 0) {
			$post_id = wp_insert_post( $post, $wp_error );
			update_post_meta( $post_id, '_rc_id', $product->id );
			$product->update_fields = array('*');
		} else {
			$post['ID'] = $post_id;
			wp_update_post($post);
		}

		//$attributes = 
		$options = explode(',', $product->options);

		foreach($options as $option) {
			update_post_meta( $post_id, 'attribute_pa_' . $option, $product->attributes[$option]);
//error_log($post_id . ' attribute_pa_' . $option . ' = ' . $product->attributes[$option]);
		}

		if ($this->allowProductUpdate($product, 'regular_price'))
			update_post_meta( $post_id, '_regular_price', $product->attributes['price'] );
		if ($this->allowProductUpdate($product, 'sale_price'))
			update_post_meta( $post_id, '_sale_price', $product->attributes['sale_price'] );
		if ($this->allowProductUpdate($product, 'sku'))
			update_post_meta( $post_id, '_sku', $product->attributes['sku']);
		if ($this->allowProductUpdate($product, 'price'))
			update_post_meta( $post_id, '_price', $product->attributes['price'] );
		if ($this->allowProductUpdate($product, 'stock'))
			update_post_meta( $post_id, '_stock', $product->stock_quantity );

		//We need to reply by returning post_id so that attributes & custom_fields can be configured

	}

	//********************************************************************
	//Main
	///********************************************************************

	public function main() {

		$token = $this->safeGetPostData('token');
		$request = $this->safeGetPostData('request');
		$data = $this->safeGetPostData('data');

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

		$result = new stdClass();
		$result->error = $error;

		if ($result->error > 0) {
			echo json_encode($result);
			return;
		}

		//Parse the request
		switch ($request) {
			case 'taxonomies':
				break;
			case 'custom_fields':
				break;
			case 'products':
				//error_log('Data: ' . $data);
				$data = str_replace('\\', '', $data);
				$data1 = json_decode($data);
				//error_log('Data1: ' . json_encode($data1));
				if ($data1->parent_id == 0)
					$this->updateProduct($data1);
				else
					$this->updateVariation($data1);
				break;
			case 'product_attributes':
				$data = str_replace('\\', '', $data);
				$data1 = json_decode($data);
				$this->updateProductAttributes($data1);
			default:
				if ($error == 0)
					$error = 3;
		}
		
		echo json_encode($result);

	}

	function safeGetPostData($index) {
		if (isset($_POST[$index]))
			return $_POST[$index];
		else
			return '';
	}

}

?>