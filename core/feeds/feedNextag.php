<?php

  /********************************************************************
  Version 2.0
    A Google Feed
	By: Keneto 2014-05-08

  ********************************************************************/

require_once 'basicfeed.php';

class PNextagFeed extends PBasicFeed{

  function __construct () {
	$this->providerName = 'Nextag';
	$this->providerNameL = 'nextag';
	$this->fileformat = 'csv';
	parent::__construct();
  }

  function getFeedData_internal($category_id, $remote_category, $file_name, $file_path) {
    return $this->getFeeds($category_id, $remote_category, $file_name, $file_path);
  }

function getFeeds($category_id, $nextag_category, $file_name, $file_path) {
    $output = array();
    global $wpdb;
    ini_set('max_execution_time', 0);
    $attr_options = $wpdb->prefix . 'options';
    $posts_table = $wpdb->prefix . 'posts';
    $allCats = array();
    $cntCat = 0;
    if ($category_id == 0) {
//Get all categories
        $term_table = $wpdb->prefix . 'terms';
        $taxo_table = $wpdb->prefix . 'term_taxonomy';
        $sql_cat = "SELECT taxo.term_id, taxo.parent, taxo.count, term.name FROM " . $taxo_table . " taxo 
				LEFT JOIN " . $term_table . " term ON taxo.term_id = term.term_id 
				WHERE taxo.taxonomy = 'product_cat'";
        $cats = $wpdb->get_results($sql_cat);
        foreach ($cats as $cat) {
            $allCats[$cntCat] = $cat->term_id;
            $cntCat++;
        }
    }
    $heading_fixed = array("Seller Part #" => "Seller Part #", "Manufacturer" => "Manufacturer", "Manufacturer Part #" => "Manufacturer Part #", "Product Name" => "Product Name", "Product Description" => "Product Description", "Click-Out URL" => "Click-Out URL", "Price" => "Price", "Category: Nextag Numeric ID" => "Category: Nextag Numeric ID", "Category: Other Format" => "Category: Other Format", "Category: PriceGrabber Format" => "Category: PriceGrabber Format", "Category: Shopping.com Format" => "Category: Shopping.com Format", "Category: Shopzilla Numeric ID" => "Category: Shopzilla Numeric ID", "Image URL" => "Image URL", "Ground Shipping" => "Ground Shipping", "Stock Status" => "Stock Status", "Product Condition" => "Product Condition", "ListPrice" => "ListPrice", "Weight" => "Weight", "UPC" => "UPC", "MUZE ID" => "MUZE ID", "ISBN" => "ISBN");
    $init_nextag_feed_fixed = array("Seller Part #" => "", "Manufacturer" => "", "Manufacturer Part #" => "", "Product Name" => "", "Product Description" => "", "Click-Out URL" => "", "Price" => "", "Category: Nextag Numeric ID" => "", "Category: Other Format" => "", "Category: PriceGrabber Format" => "", "Category: Shopping.com Format" => "", "Category: Shopzilla Numeric ID" => "", "Image URL" => "", "Ground Shipping" => "", "Stock Status" => "", "Product Condition" => "", "ListPrice" => "", "Weight" => "", "UPC" => "", "MUZE ID" => "", "ISBN" => "");
    $sql2 = "SELECT option_value FROM " . $attr_options . " WHERE option_name like'nextag_pa_%' ORDER BY `option_id` ASC";
    $result2 = $wpdb->get_results($sql2);
    $val = "";
    $heading_variable = array();
    $init_nextag_feed_attr = array();
    foreach ($result2 as $result) {
        $val = $result->option_value;
        if ($val != "") {
            $init_nextag_feed_attr[$val] = "";
            $heading_variable[$val] = $val;
        }
    }
    $output[] = array_merge($heading_fixed, $heading_variable);
    $init_nextag_feed = array_merge($init_nextag_feed_fixed, $init_nextag_feed_attr);
    if ($category_id == 0) {
        for ($i = 0; $i < $cntCat; $i++) {
            $outputs = $this->getXml($allCats[$i], $nextag_category, $init_nextag_feed);
            $output = array_merge($output, $outputs);
        }
    } else {
        $outputs = $this->getXml($category_id, $nextag_category, $init_nextag_feed);
        $output = array_merge($output, $outputs);
    }
    return $output;
}

/**
 * Function to get csv content of  Product feeds based categories
 */
function getXml($id, $nextag_category, $init_nextag_feed) {
    global $wpdb;
    $output = array();
    $option_table = $wpdb->prefix . 'options';
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
            AND $wpdb->term_taxonomy.term_id = " . $id . "
            ORDER BY post_date DESC
    		";

    $products = $wpdb->get_results($sql);


    foreach ($products as $prod) {
        $product = get_product($prod->ID);
        $attributes = $product->get_attributes();
		if (!$this->feed_category->verifyProduct($product)) break;
        if ($product->product_type == "variable" && count($attributes) >= 1) {
            $args2 = array('post_type' => 'product_variation', 'post_parent' => $prod->ID, 'posts_per_page' => 99999);
            $variationloop = new WP_Query($args2);
            while ($variationloop->have_posts()) : $variationloop->the_post();
                $sub_prod = $variationloop->post;
                $sub_product = get_product($sub_prod->ID);

                $attributes = $sub_product->get_attributes();
                $attributes2 = array();
                $parent_attributes = array();
                $attrNames = NULL;
                if ($attributes) {
                    $i = 0;
                    foreach ($attributes as $attr) {
                        $attrNames = $attr['name'];
                        $atribute_name = get_option("nextag_" . $attrNames);
                        $i++;
                        if ($attr['is_variation']) {
                            if ($atribute_name != "") {
                                $atribute_name2 = "attribute_" . $attrNames;
                                $attr_value = get_post_meta($sub_prod->ID, $atribute_name2, true);
                                $attributes2[$atribute_name] = $attr_value;
                            }
                        } else {
                            if ($atribute_name != "") {
                                $parent_attributes[$attrNames] = "";
                            }
                        }
                    }
                    if (count($parent_attributes) > 0) {
                        $i = 0;
                        foreach ($parent_attributes as $key => $value) {
                            $terms[$i] = get_the_terms($prod->ID, $key);
                            $i++;
                        }
                        $outputs = $this->getAtttributesCombinationWithVariation(count($parent_attributes), 0, 0, $terms, array(), $prod, $product, $nextag_category, $sub_prod, $sub_product, $attributes2, $init_nextag_feed);
                        $output = array_merge($output, $outputs);
                    } else {
                        $outputs = $this->getVariationProduct($sub_prod, $sub_product, $prod, $product, $nextag_category, $attributes2, $init_nextag_feed);
                        $output = array_merge($output, $outputs);
                    }
                }
            endwhile;
        } else {
            $attrNames = array();
            $attributes_count = 0;
            if ($attributes) {
                $i = 0;
                foreach ($attributes as $attr) {
                    $attrNames[$i] = $attr['name'];
                    $terms[$i] = get_the_terms($prod->ID, $attrNames[$i]);
                    $i++;
                }

                $attributes_count = $i;
                $attrNames2 = implode(",", $attrNames);
                $sql2 = "SELECT option_name, option_value FROM " . $option_table . " WHERE option_name IN (" . $attrNames2 . ") and option_value <>''";
                $result2 = $wpdb->get_results($sql2);
                if (count($result2) != count($attrNames)) {
                    $i = 0;
                    $attrNames = array();
                    $attributes_count = 0;
                    foreach ($result2 as $attr) {
                        $attrNames[$i] = $attr->option_name;
                        $terms[$i] = get_the_terms($prod->ID, "pa_" . $attrNames[$i]);
                        $i++;
                    }
                    $attributes_count = $i;
                }
            }

            if (count($attrNames) == 0) {
                $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
                $currency = get_woocommerce_currency();
                $current_feed = $init_nextag_feed;
                $current_feed['Seller Part #'] = $prod->ID;
                $current_feed['Product Name'] = $prod->post_title;
                if (strip_shortcodes(strip_tags($product->post->post_excerpt)) != "") {
                    $current_feed['Product Description'] = substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000);
                } else {
                    $current_feed['Product Description'] = substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000);
                }
                $current_cat = explode(":", str_replace(".and.", " & ", str_replace(".in.", " > ", $nextag_category)));
                $current_feed['Category: Other Format'] = $current_cat[1];
                $current_feed['Category: Nextag Numeric ID'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $nextag_category));
                $current_feed['Category: PriceGrabber Format'] = str_replace("/", ">", $current_cat[1]);
                $current_feed['Category: Shopping.com Format'] = str_replace("/", "->", str_replace(">", "->", $current_cat[1]));
                $current_feed['Category: Shopzilla Numeric ID'] = $current_cat[0];
                $current_feed['Click-Out URL'] = get_permalink($prod->ID);
                $thumb_ID = get_post_thumbnail_id($prod->ID);
                $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
                $feature_imgurl = $thumb['0'];
                $current_feed['Image URL'] = $feature_imgurl;
                $current_feed['Product Condition'] = "New";
                $current_feed['Stock Status'] = "AVAILABLE";
                $current_feed['Price'] = $product->regular_price . " $currency";
                if ($product->sale_price != "") {
                    $current_feed['ListPrice'] = $product->sale_price . " $currency";
                }
                $current_feed['Ground Shipping'] = "0.00 $currency";
                $current_feed['Weight'] = $product->get_weight() . $weight_unit;
                $output[] = $current_feed;
            } else {
                $outputs = $this->getAtttributesCombination($attributes_count, 0, 0, $terms, array(), $prod, $product, $nextag_category, $init_nextag_feed);
                $output = array_merge($output, $outputs);
            }
        }
    }
    return $output;
}

/**
 * Function to get attribute combination of each product (not variable)
 */
function getAtttributesCombination($count, $current_count, $current_terms_count, $terms, $attributes, $prod, $product, $nextag_category, $init_nextag_feed) {
    $output = array();
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    if ($current_count >= $count) {
        return array();
    }
    foreach ($terms[$current_count] as $terms_item) {
        $key = get_option("nextag_" . $terms_item->taxonomy);
        $current_terms_count++;
        $attributes[$key] = $terms_item->name;
        $outputs = $this->getAtttributesCombination($count, $current_count + 1, $current_terms_count, $terms, $attributes, $prod, $product, $nextag_category, $init_nextag_feed);
        $output = array_merge($output, $outputs);
        if (count($attributes) == $count) {
            $current_feed = $init_nextag_feed;
            if ($prod->product_type != "variable" && $count > 0) {
                $current_feed['Seller Part #'] = $prod->ID . rand();
//                $current_feed[''] = $product->sku ;
//                $current_feed[''] = $prod->ID ;
            } else {
                $current_feed['Seller Part #'] = $prod->ID;
            }
            $current_feed['Product Name'] = $prod->post_title;
            if (strip_shortcodes(strip_tags($product->post->post_excerpt)) != "") {
                $current_feed['Product Description'] = substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000);
            } else {
                $current_feed['Product Description'] = substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000);
            }
            $current_cat = explode(":", str_replace(".and.", " & ", str_replace(".in.", " > ", $nextag_category)));
            $current_feed['Category: Other Format'] = $current_cat[1];
            $current_feed['Category: Nextag Numeric ID'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $nextag_category));
            $current_feed['Category: PriceGrabber Format'] = str_replace("/", ">", $current_cat[1]);
            $current_feed['Category: Shopping.com Format'] = str_replace("/", "->", str_replace(">", "->", $current_cat[1]));
            $current_feed['Category: Shopzilla Numeric ID'] = $current_cat[0];
            $current_feed['Click-Out URL'] = get_permalink($prod->ID);
            $thumb_ID = get_post_thumbnail_id($prod->ID);
            $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
            $feature_imgurl = $thumb['0'];
            $current_feed['Image URL'] = $feature_imgurl;
            $current_feed['Product Condition'] = "New";
            $current_feed['Stock Status'] = "AVAILABLE";
            $current_feed['Price'] = $product->regular_price . " $currency";
            if ($product->sale_price != "") {
                $current_feed['ListPrice'] = $product->sale_price . " $currency";
            }
            $current_feed['Weight'] = $product->get_weight() . $weight_unit;
            $current_feed['Ground Shipping'] = "0.00 $currency";
            foreach ($attributes as $key => $value) {
                $current_feed[$key] = $value;
            }
            $output[] = $current_feed;
        }
    }
    return $output;
}

/**
 * Function to get attribute combination of each product (variable product)
 */
function getVariationProduct($prod, $product, $parent, $parent_product, $nextag_category, $attributes, $init_nextag_feed) {
    $output = array();
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    $current_feed = $init_nextag_feed;
    $current_feed['Seller Part #'] = $prod->ID;
    $current_feed['Product Name'] = $parent->post_title;
    if (strip_shortcodes(strip_tags($parent_product->post->post_excerptS)) != "") {
        $current_feed['Product Description'] = substr(strip_shortcodes(strip_tags($parent_product->post->post_excerpt)), 0, 1000);
    } else {
        $current_feed['Product Description'] = substr(strip_shortcodes(strip_tags($parent->post_content)), 0, 1000);
    }
    $current_cat = explode(":", str_replace(".and.", " & ", str_replace(".in.", " > ", $nextag_category)));
    $current_feed['Category: Other Format'] = $current_cat[1];
    $current_feed['Category: Nextag Numeric ID'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $nextag_category));
    $current_feed['Category: PriceGrabber Format'] = str_replace("/", ">", $current_cat[1]);
    $current_feed['Category: Shopping.com Format'] = str_replace("/", "->", str_replace(">", "->", $current_cat[1]));
    $current_feed['Category: Shopzilla Numeric ID'] = $current_cat[0];
    $current_feed['Click-Out URL'] = get_permalink($parent->ID);
    $thumb_ID = get_post_thumbnail_id($prod->ID);
    $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
    $feature_imgurl = $thumb['0'];
    if ($feature_imgurl == "") {
        $thumb_ID = get_post_thumbnail_id($parent->ID);
        $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
        $feature_imgurl = $thumb['0'];
    }
    $current_feed['Image URL'] = $feature_imgurl;
    $current_feed['Product Condition'] = "New";
    $current_feed['Stock Status'] = "AVAILABLE";
    if ($product->price != "") {
        $current_feed['Price'] = $product->regular_price . " $currency";
    } else {
        $current_feed['Price'] = $parent_product->regular_price . " $currency";
    }
    if ($product->sale_price != "") {
        $current_feed['ListPrice'] = $product->sale_price . " $currency";
    } elseif ($parent_product->sale_price != "") {
        $current_feed['ListPrice'] = $parent_product->sale_price . " $currency";
    }
    $current_feed['Ground Shipping'] = "0.00 $currency";
    $current_feed['Weight'] = $product->get_weight() . $weight_unit;
    foreach ($attributes as $key => $value) {
        $current_feed[$key] = $value;
    }
    $output[] = $current_feed;
    return $output;
}

/**
 * Function to get attribute compination of each product (variable attribute + common attribute)
 */
function getAtttributesCombinationWithVariation($count, $current_count, $current_terms_count, $terms, $attributes, $parent, $parent_product, $nextag_category, $prod, $product, $variation_attributes, $init_nextag_feed) {

    $output = array();
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    if ($current_count >= $count) {
        return array();
    }
    foreach ($terms[$current_count] as $terms_item) {
        $key = get_option("nextag_" . $terms_item->taxonomy);
        $current_terms_count++;
        $attributes[$key] = $terms_item->name;
        $outputs = $this->getAtttributesCombinationWithVariation($count, $current_count + 1, $current_terms_count, $terms, $attributes, $parent, $parent_product, $nextag_category, $prod, $product, $variation_attributes, $init_nextag_feed);
        $output = array_merge($output, $outputs);
        if (count($attributes) == $count) {

            $current_feed = $init_nextag_feed;
            if (count($terms) > 1) {
                $current_feed['Seller Part #'] = $prod->ID . rand();
            } else {
                $current_feed['Seller Part #'] = $prod->ID;
            }
//            $current_feed[''] =  $product->sku ;
//            $current_feed[''] =$parent->ID ;
            $current_feed['Product Name'] = $parent->post_title;
            if (strip_shortcodes(strip_tags($parent_product->post->post_excerptS)) != "") {
                $current_feed['Product Description'] = substr(strip_shortcodes(strip_tags($parent_product->post->post_excerpt)), 0, 1000);
            } else {
                $current_feed['Product Description'] = substr(strip_shortcodes(strip_tags($parent->post_content)), 0, 1000);
            }
            $current_cat = explode(":", str_replace(".and.", " & ", str_replace(".in.", " > ", $nextag_category)));
            $current_feed['Category: Other Format'] = $current_cat[1];
            $current_feed['Category: Nextag Numeric ID'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $nextag_category));
            $current_feed['Category: PriceGrabber Format'] = str_replace("/", ">", $current_cat[1]);
            $current_feed['Category: Shopping.com Format'] = str_replace("/", "->", str_replace(">", "->", $current_cat[1]));
            $current_feed['Category: Shopzilla Numeric ID'] = $current_cat[0];
            $current_feed['Click-Out URL'] = get_permalink($parent->ID);
            $thumb_ID = get_post_thumbnail_id($prod->ID);
            $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
            $feature_imgurl = $thumb['0'];
            if ($feature_imgurl == "") {
                $thumb_ID = get_post_thumbnail_id($parent->ID);
                $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
                $feature_imgurl = $thumb['0'];
            }
            $current_feed['Image URL'] = $feature_imgurl;
            $current_feed['Product Condition'] = "New";
            $current_feed['Stock Status'] = "AVAILABLE";
            if ($product->regular_price != "") {
                $current_feed['Price'] = $product->regular_price . " $currency";
            } else {
                $current_feed['Price'] = $parent_product->regular_price . " $currency";
            }
            if ($product->sale_price != "") {
                $current_feed['ListPrice'] = $product->sale_price . " $currency";
            } elseif ($parent_product->sale_price != "") {
                $current_feed['ListPrice'] = $parent_product->sale_price . " $currency";
            }
            $current_feed['Ground Shipping'] = "0.00 $currency";
            $current_feed['Weight'] = $product->get_weight() . $weight_unit;
            foreach ($variation_attributes as $key => $value) {
                $current_feed[$key] = $value;
            }
            foreach ($attributes as $key => $value) {
                $current_feed[$key] = $value;
            }
            $output[] = $current_feed;
        }
    }
    return $output;
}

}
?>