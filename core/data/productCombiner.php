<?php

  /********************************************************************
  Version 2.0
    This gets a little complex. See design-document -> ProductCategoryExport
	By: Keneto 2014-05-07
  Note: One day, this needs to be moved to Joomla/VirtueMart compatibility

  ********************************************************************/
  
require_once __DIR__.'/../productCategory/productCombiner.php';
  
class ProductsByCategory {

  function ProductList($category_id) {
  
	global $wpdb;
	$posts_table = $wpdb->prefix . 'posts';

	$sql = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON
		($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON
		($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		WHERE $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.post_type = 'product'
		AND $wpdb->term_taxonomy.taxonomy = 'product_cat'";
	
	if ($category_id != 0) {
	  $sql .=" AND $wpdb->term_taxonomy.term_id = " . $category_id ;
	}
	$sql .=" ORDER BY post_date DESC";

	return $wpdb->get_results($sql);
	}
	
  function ProductCombinations($sourcelist, $category_id) {

	global $wpdb;

	$sql = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_name, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name as Attributes
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.post_type = 'product'
		AND $wpdb->term_taxonomy.taxonomy LIKE 'pa_%' ";

	if ($category_id != 0) {
	//$sql .=" AND $wpdb->term_taxonomy.term_id = " . $category_id ; //See Documentation Note "N1"
	}
	$sql .=" ORDER BY post_date DESC";

	//ChildList contains 
	//	taxonomy (eg: pa_ProductQuality) for Product Attribute and
	//	Attributes (eg: Excellent / Good / Poor) the value for the Product Attribute
	$childlist = $wpdb->get_results($sql);

	//Initialize
	$combiner = new ProductCombiner();
	
	//Attribute Categories house the categories (eg: name: pa_ProductQuality)
	//with child items for the specific attributes
	$combiner->CreateAttributeCategories($childlist);
	
	//Now fetch back a mammoth list of products using original list
	$combiner->CreateNewProductList($sourcelist, $childlist);

	return $combiner->resultlist;
  }

}

?>