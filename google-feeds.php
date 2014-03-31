<?php

/**
 * Function to get xml file content of Google categories Product feeds 
 */
function purple_feeds_page_getGoogleFeeds($category_id, $google_category, $brand_name) {
    
    $output = NULL;
    ini_set('max_execution_time', 0);
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
            AND $wpdb->term_taxonomy.taxonomy = 'product_cat'
            AND $wpdb->term_taxonomy.term_id = " . $category_id . "
            ORDER BY post_date DESC
    		";
    $products = $wpdb->get_results($sql);
    $cat_row = get_term($category_id, 'product_cat');

    $output.= '<?xml version="1.0" encoding="iso-8859-1" ?>' . PHP_EOL;
    $output.= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">' . PHP_EOL;
    $output.= '<channel>';
    $output.= '<title>' . $cat_row->name . '</title>';
    $output.= '<link>' . get_category_link($category_id) . '</link>';
    $output.= '<description><![CDATA[' . $cat_row->description . ']]></description>';
    foreach ($products as $prod) {
        if ($brand_name == "") {
            $brand_name = $cat_row->name;
        }
        $product = get_product($prod->ID);

        $output.= '<item>' . PHP_EOL;
        $output.= '	<g:id>' . $prod->ID . '</g:id>' . PHP_EOL;
        $output.= '	<g:mpn>' . $product->sku . '</g:mpn>' . PHP_EOL;
        $output.= '	<title><![CDATA[' . $prod->post_title . ']]></title>' . PHP_EOL;
        $output.= '	<description><![CDATA[' . $prod->post_content . ']]></description>' . PHP_EOL;
        $output.= '	<g:google_product_category><![CDATA[' . $google_category . ']]></g:google_product_category>' . PHP_EOL;
        $output.= '	<g:product_type>' . $google_category . '</g:product_type>' . PHP_EOL;
        $output.= '	<link>' . get_permalink($prod->ID) . '</link>' . PHP_EOL;
        $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($prod->ID), 'small-feature');
        $imgurl = $thumb['0'];
        $output.= '	<g:image_link><![CDATA[' . $imgurl . ']]></g:image_link>' . PHP_EOL;
        $output.= '	<g:price>' . $product->price . '</g:price>' . PHP_EOL;
        $output.= '	<g:condition>new</g:condition>' . PHP_EOL;
        $output.= '	<g:brand><![CDATA[' . $brand_name . ']]></g:brand>' . PHP_EOL;
        $output.= '	<g:availability>in stock</g:availability>' . PHP_EOL;
        $output.= '</item>' . PHP_EOL;
    }
    $output.= '</channel>';
    $output.= '</rss>';
    
    return $output;
}

?>