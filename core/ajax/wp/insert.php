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

	public function addProduct() {

		$post = array(
			'post_author' => $user_id,
			'post_content' => '',
			'post_status' => "publish",
			'post_title' => $product->part_num,
			'post_parent' => '',
			'post_type' => "product",
		);

		//Create post
		$post_id = wp_insert_post( $post, $wp_error );
		if($post_id){
			$attach_id = get_post_meta($product->parent_id, "_thumbnail_id", true);
			add_post_meta($post_id, '_thumbnail_id', $attach_id);
		}
		wp_set_object_terms( $post_id, 'Races', 'product_cat' );
		wp_set_object_terms($post_id, 'simple', 'product_type');



		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock');
		update_post_meta( $post_id, 'total_sales', '0');
		update_post_meta( $post_id, '_downloadable', 'yes');
		update_post_meta( $post_id, '_virtual', 'yes');
		update_post_meta( $post_id, '_regular_price', "1" );
		update_post_meta( $post_id, '_sale_price', "1" );
		update_post_meta( $post_id, '_purchase_note', "" );
		update_post_meta( $post_id, '_featured', "no" );
		update_post_meta( $post_id, '_weight', "" );
		update_post_meta( $post_id, '_length', "" );
		update_post_meta( $post_id, '_width', "" );
		update_post_meta( $post_id, '_height', "" );
		update_post_meta( $post_id, '_sku', "");
		update_post_meta( $post_id, '_product_attributes', array());
		update_post_meta( $post_id, '_sale_price_dates_from', "" );
		update_post_meta( $post_id, '_sale_price_dates_to', "" );
		update_post_meta( $post_id, '_price', "1" );
		update_post_meta( $post_id, '_sold_individually', "" );
		update_post_meta( $post_id, '_manage_stock', "no" );
		update_post_meta( $post_id, '_backorders', "no" );
		update_post_meta( $post_id, '_stock', "" );

		/*
		// file paths will be stored in an array keyed off md5(file path)
		//$downdloadArray =array('name'=>"Test", 'file' => $uploadDIR['baseurl']."/video/".$video);
		$file_path =md5($uploadDIR['baseurl']."/video/".$video);
		$_file_paths[  $file_path  ] = $downdloadArray;

		// grant permission to any newly added files on any existing orders for this product
		//do_action( 'woocommerce_process_product_file_download_paths', $post_id, 0, $downdloadArray );
		update_post_meta( $post_id, '_downloadable_files ', $_file_paths);
		update_post_meta( $post_id, '_download_limit', '');
		update_post_meta( $post_id, '_download_expiry', '');
		update_post_meta( $post_id, '_download_type', '');
		*/

		update_post_meta( $post_id, '_product_image_gallery', '');

		//We need to reply by returning post_id so that attributes & custom_fields can be configured

	}

	//********************************************************************
	//Main
	///********************************************************************

	public function main() {

		$token = $this->safeGetPostData('token');
		$request = $this->safeGetPostData('request');

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
				break;
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