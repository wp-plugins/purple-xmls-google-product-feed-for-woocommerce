<?php

  /********************************************************************
  Version 2.0
    An eBay Feed
	By: Keneto 2014-05-08

  ********************************************************************/

require_once 'basicfeed.php';

class PeBayFeed extends PBasicFeed{

  function __construct () {
	$this->providerName = 'eBay';
	$this->providerNameL = 'ebay';
	parent::__construct();
  }

  function getFeedData_internal($category_id, $remote_category, $file_name, $file_path) {
    return $this->getFeeds($category_id, $remote_category, $file_name, $file_path);
  }

function getFeeds($category_id, $ebay_category, $file_name, $file_path) {
    $output = NULL;
//    header("Content-Type:text/xml");
    ini_set('max_execution_time', 0);
    global $wpdb;
    $posts_table = $wpdb->prefix . 'posts';
    $allCats = array();
    $cntCat = 0;

    if ($category_id == 0 or $category_id == "") {

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
    $output.= '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
//    $output.= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">' . PHP_EOL;
    $output.= '<Products>';
//    $output.= '<title>' . $file_name . '</title>';
//    $output.= '<link><![CDATA[' . $file_path . ']]></link>';
//    $output.= '<description>' . $file_name . '</description>';

    if ($category_id == 0 or $category_id == "") {
        for ($i = 0; $i < $cntCat; $i++) {
            $output.= $this->getXml($allCats[$i], $ebay_category);
        }
    } else {
        $output.= $this->getXml($category_id, $ebay_category);
    }
    $output.= '</Products>';
//    $output.= '</rss>';
    return $output;
}

/**
 * Function to get xml content of  Product feeds based categories
 */
function getXml($id, $ebay_category) {
    global $wpdb;
    $output = NULL;
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
                        $atribute_name = get_option(str_replace("ebay_pa_", "", $attrNames));
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
                        $output.= $this->getAtttributesCombinationWithVariation(count($parent_attributes), 0, 0, $terms, array(), array(), 0, $prod, $product, $ebay_category, $sub_prod, $sub_product, $attributes2);
                    } else {
                        $output.= $this->getVariationProduct($sub_prod, $sub_product, $prod, $product, $ebay_category, $attributes2);
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
                $attrNames2 = NULL;
                foreach ($attrNames as $atribute_name) {
                    if ($attrNames2 != "") {
                        $attrNames2.=",";
                    }
                    $attrNames2.="'" . str_replace("ebay_pa_", "", $atribute_name) . "'";
                }
                $sql2 = "SELECT option_name, option_value FROM " . $option_table . " WHERE option_name IN (" . $attrNames2 . ") and option_value <>''";
                $result2 = $wpdb->get_results($sql2);
                if (count($result2) != count($attrNames)) {
                    $i = 0;
                    $attrNames = array();
                    $attributes_count = 0;
                    foreach ($result2 as $attr) {
                        $attrNames[$i] = $attr->option_name;
                        $terms[$i] = get_the_terms($prod->ID, "ebay_pa_" . $attrNames[$i]);
                        $i++;
                    }
                    $attributes_count = $i;
                }
            }

            if (count($attrNames) == 0) {
                $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
                $currency = get_woocommerce_currency();
                $output.= '<Product>' . PHP_EOL;
//                $output.= '	<UPC>' . $prod->ID . '</UPC>' . PHP_EOL;
                $output.= '	<Product_Name><![CDATA[' . $prod->post_title . ']]></Product_Name>' . PHP_EOL;
                if (strip_shortcodes(strip_tags($product->post->post_excerpt)) != "") {
                    $output.= '	<Product_Description><![CDATA[' . substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000) . ']]></Product_Description>' . PHP_EOL;
                } else {
                    $output.= '	<Product_Description><![CDATA[' . substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000) . ']]></Product_Description>' . PHP_EOL;
                }
                $category = explode(":", $ebay_category);
				if (isset($category[1])) {$this_category = $category[1];} else {$this_category = '';}
                $output.= '	<Category><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $this_category)) . ']]></Category>' . PHP_EOL;
                $output.= '	<Product_Type><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $this_category)) . ']]></Product_Type>' . PHP_EOL;
                $output.= '	<Category_ID>' . $category[0] . '</Category_ID>' . PHP_EOL;
                $output.= '	<Product_URL><![CDATA[' . get_permalink($prod->ID) . ']]></Product_URL>' . PHP_EOL;
                $thumb_ID = get_post_thumbnail_id($prod->ID);
                $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
                $feature_imgurl = $thumb['0'];
                $output.= ' <Image_URL><![CDATA[' . $feature_imgurl . ']]></Image_URL>' . PHP_EOL;
                $attachments = $product->get_gallery_attachment_ids();
                $attachments = array_diff($attachments, array($thumb_ID));
                if ($attachments) {
                    $image_count = 0;
                    foreach ($attachments as $attachment) {
                        $thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
                        $imgurl = $thumb['0'];
                        $image_count++;
                        $output.= '	<Alternative_Image_URL_' . $image_count . '><![CDATA[' . $imgurl . ']]></Alternative_Image_URL_' . $image_count . '>' . PHP_EOL;
                    }
                }
                $output.= '	<Condition>New</Condition>' . PHP_EOL;
                $output.= '	<Stock_Availability>In Stock</Stock_Availability>' . PHP_EOL;
                $output.= '	<Original_Price>' . $product->regular_price . " $currency" . '</Original_Price>' . PHP_EOL;
                if ($product->sale_price != "") {
                    $output.= '	<Current_Price>' . $product->sale_price . " $currency" . '</Current_Price>' . PHP_EOL;
                }
                $output.= '	<Merchant_SKU>' . $product->sku . '</Merchant_SKU>' . PHP_EOL;
                $wgt = $product->get_weight();
                if ($wgt != "") {
                    $output.= '	<Product_Weight>' . $wgt . ' ' . $weight_unit . '</Product_Weight>' . PHP_EOL;
                    $output.= '	<Shipping_Weight>' . $wgt . '</Shipping_Weight>' . PHP_EOL;
                    $output.= '	<Weight_Unit_of_Measure>' . $weight_unit . '</Weight_Unit_of_Measure>' . PHP_EOL;
                }
                $output.= "	    <Shipping_Rate>0.00 $currency</Shipping_Rate>" . PHP_EOL;
                $output.= '</Product>' . PHP_EOL;
            } else {
                $output .= $this->getAtttributesCombination($attributes_count, 0, 0, $terms, array(), array(), 0, $prod, $product, $ebay_category);
            }
        }
    }
    return $output;
}

/**
 * Function to get attribute compination of each product (not variable)
 */
function getAtttributesCombination($count, $current_count, $current_terms_count, $terms, $attributes, $attributes_key, $attributes_index, $prod, $product, $ebay_category) {
    $output = NULL;
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    if ($current_count >= $count) {
        return;
    }
    foreach ($terms[$current_count] as $terms_item) {
        $key = get_option(str_replace("ebay_pa_", "", $terms_item->taxonomy));
        $current_terms_count++;
        $attributes[$attributes_index] = $terms_item->name;
        $attributes_key[$attributes_index] = $key;
        $attributes_index++;
        $output.= $this->getAtttributesCombination($count, $current_count + 1, $current_terms_count, $terms, $attributes, $attributes_key, $attributes_index, $prod, $product, $ebay_category);
        if (count($attributes) == $count) {
            $output.= '<Product>' . PHP_EOL;
//            if ($prod->product_type != "variable" && $count > 0) {
//                $output.= '	<UPC>' . $prod->ID . rand() . '</UPC>' . PHP_EOL;
//                $output.= '	<item_group_id>' . $prod->ID . '</item_group_id>' . PHP_EOL;
//            } else {
//                $output.= '	<UPC>' . $prod->ID . '</UPC>' . PHP_EOL;
//            }
            $output.= '	<Product_Name><![CDATA[' . $prod->post_title . ']]></Product_Name>' . PHP_EOL;
            if (strip_shortcodes(strip_tags($product->post->post_excerpt)) != "") {
                $output.= '	<Product_Description><![CDATA[' . substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000) . ']]></Product_Description>' . PHP_EOL;
            } else {
                $output.= '	<Product_Description><![CDATA[' . substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000) . ']]></Product_Description>' . PHP_EOL;
            }
            $category = explode(":", $ebay_category);
            $output.= '	<Category><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $category[1])) . ']]></Category>' . PHP_EOL;
            $output.= '	<Product_Type><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $category[1])) . ']]></Product_Type>' . PHP_EOL;
            $output.= '	<Category_ID>' . $category[0] . '</Category_ID>' . PHP_EOL;
            $output.= '	<Product_URL><![CDATA[' . get_permalink($prod->ID) . ']]></Product_URL>' . PHP_EOL;
            $thumb_ID = get_post_thumbnail_id($prod->ID);
            $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
            $feature_imgurl = $thumb['0'];
            $output.= '	<Image_URL><![CDATA[' . $feature_imgurl . ']]></Image_URL>' . PHP_EOL;
            $attachments = $product->get_gallery_attachment_ids();
            $attachments = array_diff($attachments, array($thumb_ID));
            if ($attachments) {
                $image_count = 0;
                foreach ($attachments as $attachment) {
                    $thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
                    $imgurl = $thumb['0'];
                    $image_count++;
                    $output.= '	<Alternative_Image_URL_' . $image_count . '><![CDATA[' . $imgurl . ']]></Alternative_Image_URL_' . $image_count . '>' . PHP_EOL;
                }
            }
            $output.= '	<Condition>New</Condition>' . PHP_EOL;
            $output.= '	<Stock_Availability>In Stock</Stock_Availability>' . PHP_EOL;
            $output.= '	<Original_Price>' . $product->regular_price . " $currency" . '</Original_Price>' . PHP_EOL;
            if ($product->sale_price != "") {
                $output.= '	<Current_Price>' . $product->sale_price . " $currency" . '</Current_Price>' . PHP_EOL;
            }
            $output.= '	<Merchant_SKU>' . $product->sku . '</Merchant_SKU>' . PHP_EOL;
            $wgt = $product->get_weight();
            if ($wgt != "") {
                $output.= '	<Product_Weight>' . $wgt . ' ' . $weight_unit . '</Product_Weight>' . PHP_EOL;
                $output.= '	<Shipping_Weight>' . $wgt . '</Shipping_Weight>' . PHP_EOL;
                $output.= '	<Weight_Unit_of_Measure>' . $weight_unit . '</Weight_Unit_of_Measure>' . PHP_EOL;
            }
            $output.= "	    <Shipping_Rate>0.00 $currency</Shipping_Rate>" . PHP_EOL;
            for ($temp_index = 0; $temp_index < count($attributes); $temp_index++) {
                $output.= '	<' . $attributes_key[$temp_index] . '>' . $attributes[$temp_index] . '</' . $attributes_key[$temp_index] . '>' . PHP_EOL;
            }

            $output.= '</Product>' . PHP_EOL;
        }
    }
    return $output;
}

/**
 * Function to get attribute compination of each product (variable product)
 */
function getVariationProduct($prod, $product, $parent, $parent_product, $ebay_category, $attributes) {
    $output = NULL;
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    $output.= '<Product>' . PHP_EOL;
//    $output.= '	<UPC>' . $prod->ID . '</UPC>' . PHP_EOL;
//    $output.= '	<item_group_id>' . $parent->ID . '</item_group_id>' . PHP_EOL;
    $output.= '	<Product_Name><![CDATA[' . $parent->post_title . ']]></Product_Name>' . PHP_EOL;
    $output.= '	<Parent_Name><![CDATA[' . $parent->post_title . ']]></Parent_Name>' . PHP_EOL;
    if (strip_shortcodes(strip_tags($parent_product->post->post_excerpt)) != "") {
        $output.= '	<Product_Description><![CDATA[' . substr(strip_shortcodes(strip_tags($parent_product->post->post_excerpt)), 0, 1000) . ']]></Product_Description>' . PHP_EOL;
    } else {
        $output.= '	<Product_Description><![CDATA[' . substr(strip_shortcodes(strip_tags($parent->post_content)), 0, 1000) . ']]></Product_Description>' . PHP_EOL;
    }
    $category = explode(":", $ebay_category);
	if (isset($category[1])) {$this_category = $category[1];} else {$this_category = '';}
    $output.= '	<Category><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $this_category)) . ']]></Category>' . PHP_EOL;
    $output.= '	<Product_Type><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $this_category)) . ']]></Product_Type>' . PHP_EOL;
    $output.= '	<Category_ID>' . $category[0] . '</Category_ID>' . PHP_EOL;
    $output.= '	<Product_URL><![CDATA[' . get_permalink($parent->ID) . ']]></Product_URL>' . PHP_EOL;
    $thumb_ID = get_post_thumbnail_id($prod->ID);
    $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
    $feature_imgurl = $thumb['0'];
    if ($feature_imgurl == "") {
        $thumb_ID = get_post_thumbnail_id($parent->ID);
        $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
        $feature_imgurl = $thumb['0'];
    }
    $output.= '	<Image_URL><![CDATA[' . $feature_imgurl . ']]></Image_URL>' . PHP_EOL;
    $attachments = $parent_product->get_gallery_attachment_ids();
    $attachments = array_diff($attachments, array($thumb_ID));
    if ($attachments) {
        $image_count = 0;
        foreach ($attachments as $attachment) {
            $thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
            $imgurl = $thumb['0'];
            $image_count++;
            $output.= '	<Alternative_Image_URL_' . $image_count . '><![CDATA[' . $imgurl . ']]></Alternative_Image_URL_' . $image_count . '>' . PHP_EOL;
        }
    }
    $output.= '	<Condition>New</Condition>' . PHP_EOL;
    $output.= '	<Stock_Availability>In Stock</Stock_Availability>' . PHP_EOL;
    if ($product->price != "") {
        $output.= '	<Original_Price>' . $product->regular_price . " $currency" . '</Original_Price>' . PHP_EOL;
    } else {
        $output.= '	<Original_Price>' . $parent_product->regular_price . " $currency" . '</Original_Price>' . PHP_EOL;
    }
    if ($product->sale_price != "") {
        $output.= '	<Current_Price>' . $product->sale_price . " $currency" . '</Current_Price>' . PHP_EOL;
    } elseif ($parent_product->sale_price != "") {
        $output.= '	<Current_Price>' . $parent_product->sale_price . " $currency" . '</Current_Price>' . PHP_EOL;
    }
    $output.= '	<Merchant_SKU>' . $product->sku . '</Merchant_SKU>' . PHP_EOL;
    $output.= '	<Parent_SKU>' . $parent_product->sku . '</Parent_SKU>' . PHP_EOL;
    $wgt = $product->get_weight();
    if ($wgt != "") {
        $output.= '	<Product_Weight>' . $wgt . ' ' . $weight_unit . '</Product_Weight>' . PHP_EOL;
        $output.= '	<Shipping_Weight>' . $wgt . '</Shipping_Weight>' . PHP_EOL;
        $output.= '	<Weight_Unit_of_Measure>' . $weight_unit . '</Weight_Unit_of_Measure>' . PHP_EOL;
    }
    $output.= "	    <Shipping_Rate>0.00 $currency</Shipping_Rate>" . PHP_EOL;
    foreach ($attributes as $key => $value) {
        $output.= '	<' . $key . '>' . $value . '</' . $key . '>' . PHP_EOL;
    }
    $output.= '</Product>' . PHP_EOL;
    return $output;
}

/**
 * Function to get attribute compination of each product (variable attribute + common attribute)
 */
function getAtttributesCombinationWithVariation($count, $current_count, $current_terms_count, $terms, $attributes, $attributes_key, $attributes_index, $parent, $parent_product, $ebay_category, $prod, $product, $variation_attributes) {
    $output = NULL;
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    if ($current_count >= $count) {
        return;
    }
    foreach ($terms[$current_count] as $terms_item) {
        $key = get_option(str_replace("ebay_pa_", "", $terms_item->taxonomy));
        $current_terms_count++;
        $attributes[$attributes_index] = $terms_item->name;
        $attributes_key[$attributes_index] = $key;
        $attributes_index++;
        $output.= $this->getAtttributesCombinationWithVariation($count, $current_count + 1, $current_terms_count, $terms, $attributes, $attributes_key, $attributes_index, $parent, $parent_product, $ebay_category, $prod, $product, $variation_attributes);
        if (count($attributes) == $count) {
            $output.= '<Product>' . PHP_EOL;
//            $output.= '	<UPC>' . $prod->ID . rand(100, 1000) . '</UPC>' . PHP_EOL;
//            $output.= '	<item_group_id>' . $parent->ID . '</item_group_id>' . PHP_EOL;
            $output.= '	<Product_Name><![CDATA[' . $parent->post_title . ']]></Product_Name>' . PHP_EOL;
            $output.= '	<Parent_Name><![CDATA[' . $parent->post_title . ']]></Parent_Name>' . PHP_EOL;
            if (strip_shortcodes(strip_tags($parent_product->post->post_excerpt)) != "") {
                $output.= '	<Product_Description><![CDATA[' . substr(strip_shortcodes(strip_tags($parent_product->post->post_excerpt)), 0, 1000) . ']]></Product_Description>' . PHP_EOL;
            } else {
                $output.= '	<Product_Description><![CDATA[' . substr(strip_shortcodes(strip_tags($parent->post_content)), 0, 1000) . ']]></Product_Description>' . PHP_EOL;
            }
            $category = explode(":", $ebay_category);
            $output.= '	<Category><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $category[1])) . ']]></Category>' . PHP_EOL;
            $output.= '	<Product_Type><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $category[1])) . ']]></Product_Type>' . PHP_EOL;
            $output.= '	<Category_ID>' . $category[0] . '</Category_ID>' . PHP_EOL;
            $output.= '	<Product_URL><![CDATA[' . get_permalink($parent->ID) . ']]></Product_URL>' . PHP_EOL;
            $thumb_ID = get_post_thumbnail_id($prod->ID);
            $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
            $feature_imgurl = $thumb['0'];
            if ($feature_imgurl == "") {
                $thumb_ID = get_post_thumbnail_id($parent->ID);
                $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
                $feature_imgurl = $thumb['0'];
            }
            $output.= '	<Image_URL><![CDATA[' . $feature_imgurl . ']]></Image_URL>' . PHP_EOL;
            $attachments = $parent_product->get_gallery_attachment_ids();
            $attachments = array_diff($attachments, array($thumb_ID));
            if ($attachments) {
                $image_count = 0;
                foreach ($attachments as $attachment) {
                    $thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
                    $imgurl = $thumb['0'];
                    $image_count++;
                    $output.= '	<Alternative_Image_URL_' . $image_count . '><![CDATA[' . $imgurl . ']]></Alternative_Image_URL_' . $image_count . '>' . PHP_EOL;
                }
            }
            $output.= '	<Condition>New</Condition>' . PHP_EOL;
            $output.= '	<Stock_Availability>In Stock</Stock_Availability>' . PHP_EOL;
            if ($product->regular_price != "") {
                $output.= '	<Original_Price>' . $product->regular_price . " $currency" . '</Original_Price>' . PHP_EOL;
            } else {
                $output.= '	<Original_Price>' . $parent_product->regular_price . " $currency" . '</Original_Price>' . PHP_EOL;
            }
            if ($product->sale_price != "") {
                $output.= '	<Current_Price>' . $product->sale_price . " $currency" . '</Current_Price>' . PHP_EOL;
            } elseif ($parent_product->sale_price != "") {
                $output.= '	<Current_Price>' . $parent_product->sale_price . " $currency" . '</Current_Price>' . PHP_EOL;
            }
            $output.= '	<Merchant_SKU>' . $product->sku . '</Merchant_SKU>' . PHP_EOL;
            $output.= '	<Parent_SKU>' . $parent_product->sku . '</Parent_SKU>' . PHP_EOL;
            $wgt = $product->get_weight();
            if ($wgt != "") {
                $output.= '	<Product_Weight>' . $wgt . ' ' . $weight_unit . '</Product_Weight>' . PHP_EOL;
                $output.= '	<Shipping_Weight>' . $wgt . '</Shipping_Weight>' . PHP_EOL;
                $output.= '	<Weight_Unit_of_Measure>' . $weight_unit . '</Weight_Unit_of_Measure>' . PHP_EOL;
            }
            $output.= "	    <Shipping_Rate>0.00 $currency</Shipping_Rate>" . PHP_EOL;
            foreach ($variation_attributes as $key => $value) {
                $output.= '	<' . $key . '>' . $value . '</' . $key . '>' . PHP_EOL;
            }
            for ($temp_index = 0; $temp_index < count($attributes); $temp_index++) {
                $output.= '	<' . $attributes_key[$temp_index] . '>' . $attributes[$temp_index] . '</' . $attributes_key[$temp_index] . '>' . PHP_EOL;
            }
            $output.= '</Product>' . PHP_EOL;
        }
    }
    return $output;
}

}
?>