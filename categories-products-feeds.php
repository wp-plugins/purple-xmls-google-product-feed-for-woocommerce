<?php
/**
 * Function to get xml file content of  categories Products feeds 
 */
function purple_feeds_page_getCategoriesProductsFeeds($category_id) {
header ("Content-Type:text/xml");  
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
    echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
	http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
//echo '<sitemap>'.PHP_EOL;
    foreach ($products as $prod) {
        $product = new WC_Product($prod->ID);
        echo '	<id>' . $prod->ID . '</id>' . PHP_EOL;
        echo '	<mpn>' . $product->sku . '</mpn>' . PHP_EOL;
        echo '	<title>' . $prod->post_title . '</title>' . PHP_EOL;
        echo '	<description><![CDATA[' . $prod->post_content . ']]></description>' . PHP_EOL;
        echo '	<link>' . get_permalink($prod->ID) . '</link>' . PHP_EOL;
        $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($prod->ID), 'small-feature');
        $imgurl = $thumb['0'];
        echo '	<image_link>' . $imgurl . '</image_link>' . PHP_EOL;
        echo '	<price>' . $product->price . '</price>' . PHP_EOL;
    }
// echo '</sitemap>'.PHP_EOL;
//echo '</sitemap>'.PHP_EOL;
    echo '</urlset>';
}
?>