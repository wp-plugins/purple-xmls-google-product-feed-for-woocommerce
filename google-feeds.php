<?php

/**
 * Function to get xml file content of Google categories Product feeds 
 */
function purple_feeds_page_getGoogleFeeds($category_id, $google_category, $brand_name) {
    
    header("Content-Type:text/xml");
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

    echo '<?xml version="1.0"?>' . PHP_EOL;
    echo '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">' . PHP_EOL;
    echo '<channel>';
    echo '<title>' . $cat_row->name . '</title>';
    echo '<link>' . get_category_link($category_id) . '</link>';
    echo '<description>' . $cat_row->description . '</description>';
    foreach ($products as $prod) {
        if ($brand_name == "") {
            $brand_name = $cat_row->name;
        }
        $product = get_product($prod->ID);

        echo '<item>' . PHP_EOL;
        echo '	<g:id>' . $prod->ID . '</g:id>' . PHP_EOL;
        echo '	<g:mpn>' . $product->sku . '</g:mpn>' . PHP_EOL;
        echo '	<title>' . $prod->post_title . '</title>' . PHP_EOL;
        echo '	<description><![CDATA[' . $prod->post_content . ']]></description>' . PHP_EOL;
        echo '	<g:google_product_category>' . $google_category . '</g:google_product_category>' . PHP_EOL;
        echo '	<g:product_type>' . $google_category . '</g:product_type>' . PHP_EOL;
        echo '	<link>' . get_permalink($prod->ID) . '</link>' . PHP_EOL;
        $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($prod->ID), 'small-feature');
        $imgurl = $thumb['0'];
        echo '	<g:image_link>' . $imgurl . '</g:image_link>' . PHP_EOL;
        echo '	<g:price>' . $product->price . '</g:price>' . PHP_EOL;
        echo '	<g:condition>new</g:condition>' . PHP_EOL;
        echo '	<g:brand>' . $brand_name . '</g:brand>' . PHP_EOL;
        echo '	<g:availability>in stock</g:availability>' . PHP_EOL;
        // echo '	<g:quantity>1</g:quantity>'.PHP_EOL;
        echo '</item>' . PHP_EOL;
    }
    echo '</channel>';
    echo '</rss>';
}

?>