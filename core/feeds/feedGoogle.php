<?php

  /********************************************************************
  Version 2.0
    A Google Feed
	By: Keneto 2014-05-08

  ********************************************************************/

require_once 'basicfeed.php';

class PGoogleFeed extends PBasicFeed{

  function __construct () {
	$this->providerName = 'Google';
	$this->providerNameL = 'google';
	parent::__construct();
  }

  function getFeedData_internal($category_id, $remote_category, $file_name, $file_path) {
    return $this->getFeeds($category_id, $remote_category, $file_name, $file_path);
  }

/**
 * Function to get xml file content of Google categories Product feeds 
 */
function getFeeds($category_id, $google_category, $file_name, $file_path) {
    $output = NULL;
	//    header("Content-Type:text/xml");
	//ini_set('max_execution_time', 0);
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
    $output.= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">' . PHP_EOL;
    $output.= '<channel>';
    $output.= '<title>' . $file_name . '</title>';
    $output.= '<link><![CDATA[' . $file_path . ']]></link>';
    $output.= '<description>' . $file_name . '</description>';

    if ($category_id == 0 or $category_id == "") {
        for ($i = 0; $i < $cntCat; $i++) {
            $output.= $this->getXml($allCats[$i], $google_category);
        }
    } else {
        $output.= $this->getXml($category_id, $google_category);
    }
    $output.= '</channel>';
    $output.= '</rss>';

    return $output;
}

/**
 * Function to get xml content of  Product feeds based categories
 */
function getXml($id, $google_category) {
    global $wpdb;
    $output = NULL;
    $option_table = $wpdb->prefix . 'options';
	
	$products = $this->loadProductList($id);

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
                        $atribute_name = get_option($this->providerName . '_cp_' . str_replace("pa_", "", $attrNames));
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
                        $output.= $this->getAtttributesCombinationWithVariation(count($parent_attributes), 0, 0, $terms, array(), array(), 0, $prod, $product, $google_category, $sub_prod, $sub_product, $attributes2);
                    } else {
                        $output.= $this->getVariationProduct($sub_prod, $sub_product, $prod, $product, $google_category, $attributes2);
                    }
                }
            endwhile;
        } else {
			//Non-Variant Product
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
                    $attrNames2.="'" . str_replace("pa_", "", $atribute_name) . "'";
                }
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
                $output.= '<item>' . PHP_EOL;
                $output.= '	<g:id>' . $prod->ID . '</g:id>' . PHP_EOL;
                $output.= '	<title><![CDATA[' . $prod->post_title . ']]></title>' . PHP_EOL;
                if (strip_shortcodes(strip_tags($product->post->post_excerpt)) != "") {
                    $output.= '	<description><![CDATA[' . substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000) . ']]></description>' . PHP_EOL;
                } else {
                    $output.= '	<description><![CDATA[' . substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000) . ']]></description>' . PHP_EOL;
                }
                $output.= '	<g:google_product_category><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $google_category)) . ']]></g:google_product_category>' . PHP_EOL;
                $output.= '	<g:product_type><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $google_category)) . ']]></g:product_type>' . PHP_EOL;
                $output.= '	<link><![CDATA[' . get_permalink($prod->ID) . ']]></link>' . PHP_EOL;
                $thumb_ID = get_post_thumbnail_id($prod->ID);
                $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
                $feature_imgurl = $thumb['0'];
                $output.= '    <g:image_link><![CDATA[' . $feature_imgurl . ']]></g:image_link>' . PHP_EOL;
                $attachments = $product->get_gallery_attachment_ids();
                $attachments = array_diff($attachments, array($thumb_ID));
                if ($attachments) {
                    $image_count = 0;
                    foreach ($attachments as $attachment) {
                        $thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
                        $imgurl = $thumb['0'];
                        $image_count++;
                        $output.= '    <g:additional_image_link><![CDATA[' . $imgurl . ']]></g:additional_image_link>' . PHP_EOL;
                    }
                }
                $output.= '	<g:condition>New</g:condition>' . PHP_EOL;
				if ($prod->stock_status == 1)
                  $output.= '	<g:availability>in stock</g:availability>' . PHP_EOL;
				else
				  $output.= '	<g:availability>out of  stock</g:availability>' . PHP_EOL;
                $output.= '	<g:price>' . $product->regular_price . " $currency" . '</g:price>' . PHP_EOL;
                if ($product->sale_price != "") {
                    $output.= '	<g:sale_price>' . $product->sale_price . " $currency" . '</g:sale_price>' . PHP_EOL;
                }
                $output.= '	<g:mpn>' . $product->sku . '</g:mpn>' . PHP_EOL;
                $wgt = $product->get_weight();
                if ($wgt != "") {
                    $output.= '	<g:shipping_weight>' . $wgt . ' ' . $weight_unit . '</g:shipping_weight>' . PHP_EOL;
                }
                $output.= '	<g:shipping>' . PHP_EOL;
                $output.= '	    <g:service>Ground</g:service>' . PHP_EOL;
                $output.= "	    <g:price>0.00 $currency</g:price>" . PHP_EOL;
                $output.= '	</g:shipping>' . PHP_EOL;
                $output.= '</item>' . PHP_EOL;
            } else {
                $output.= $this->getAtttributesCombination($attributes_count, 0, 0, $terms, array(),array(),0, $prod, $product, $google_category);
            }
        }
    }
    return $output;
}

/**
 * Function to get attribute combination of each product (not variable)
 */
function getAtttributesCombination($count, $current_count, $current_terms_count, $terms, $attributes, $attributes_key, $attributes_index, $prod, $product, $google_category) {
    $output = NULL;
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    if ($current_count >= $count) {
        return;
    }
    foreach ($terms[$current_count] as $terms_item) {
        $key = get_option($this->providerName . '_cp_' . str_replace("pa_", "", $terms_item->taxonomy));
        $current_terms_count++;
        $attributes[$attributes_index] = $terms_item->name;
        $attributes_key[$attributes_index] = $key;
        $attributes_index++;
        $output.= $this->getAtttributesCombination($count, $current_count + 1, $current_terms_count, $terms, $attributes, $attributes_key, $attributes_index, $prod, $product, $google_category);
        if (count($attributes) == $count) {
            $output.= '<item>' . PHP_EOL;
            if ($prod->product_type != "variable" && $count > 0) {
                $output.= '	<g:id>' . $prod->ID . rand() . '</g:id>' . PHP_EOL;
                $output.= '	<g:item_group_id>' . $prod->ID . '</g:item_group_id>' . PHP_EOL;
            } else {
                $output.= '	<g:id>' . $prod->ID . '</g:id>' . PHP_EOL;
            }
            $output.= '	<title><![CDATA[' . $prod->post_title . ']]></title>' . PHP_EOL;
            if (strip_shortcodes(strip_tags($product->post->post_excerpt)) != "") {
                $output.= '	<description><![CDATA[' . substr(strip_shortcodes(strip_tags($product->post->post_excerpt)), 0, 1000) . ']]></description>' . PHP_EOL;
            } else {
                $output.= '	<description><![CDATA[' . substr(strip_shortcodes(strip_tags($prod->post_content)), 0, 1000) . ']]></description>' . PHP_EOL;
            }
            $output.= '	<g:google_product_category><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $google_category)) . ']]></g:google_product_category>' . PHP_EOL;
            $output.= '	<g:product_type><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $google_category)) . ']]></g:product_type>' . PHP_EOL;
            $output.= '	<link><![CDATA[' . get_permalink($prod->ID) . ']]></link>' . PHP_EOL;
            $thumb_ID = get_post_thumbnail_id($prod->ID);
            $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
            $feature_imgurl = $thumb['0'];
            $output.= '	<g:image_link><![CDATA[' . $feature_imgurl . ']]></g:image_link>' . PHP_EOL;
            $attachments = $product->get_gallery_attachment_ids();
            $attachments = array_diff($attachments, array($thumb_ID));
            if ($attachments) {
                $image_count = 0;
                foreach ($attachments as $attachment) {
                    $thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
                    $imgurl = $thumb['0'];
                    $image_count++;
                    $output.= '	<g:additional_image_link><![CDATA[' . $imgurl . ']]></g:additional_image_link>' . PHP_EOL;
                }
            }
            $output.= '	<g:condition>New</g:condition>' . PHP_EOL;
			if ($prod->stock_status == 1)
			  $output.= '	<g:availability>in stock</g:availability>' . PHP_EOL;
			else
			  $output.= '	<g:availability>out of  stock</g:availability>' . PHP_EOL;
            $output.= '	<g:price>' . $product->regular_price . " $currency" . '</g:price>' . PHP_EOL;
            if ($product->sale_price != "") {
                $output.= '	<g:sale_price>' . $product->sale_price . " $currency" . '</g:sale_price>' . PHP_EOL;
            }
            $output.= '	<g:mpn>' . $product->sku . '</g:mpn>' . PHP_EOL;
            $wgt = $product->get_weight();
            if ($wgt != "") {
                $output.= '	<g:shipping_weight>' . $wgt . ' ' . $weight_unit . '</g:shipping_weight>' . PHP_EOL;
            }
            $output.= '	<g:shipping>' . PHP_EOL;
            $output.= '	    <g:service>Ground</g:service>' . PHP_EOL;
            $output.= "	    <g:price>0.00 $currency</g:price>" . PHP_EOL;
            $output.= '	</g:shipping>' . PHP_EOL;
            for ($temp_index = 0; $temp_index < count($attributes); $temp_index++) {
                $output.= '	<g:' . $attributes_key[$temp_index] . '>' . $attributes[$temp_index] . '</g:' . $attributes_key[$temp_index] . '>' . PHP_EOL;
            }

            $output.= '</item>' . PHP_EOL;
        }
    }
    return $output;
}

/**
 * Function to get attribute combination of each product (variable product)
 */
function getVariationProduct($prod, $product, $parent, $parent_product, $google_category, $attributes) {

    $output = NULL;
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    $output.= '<item>' . PHP_EOL;
    $output.= '	<g:id>' . $prod->ID . '</g:id>' . PHP_EOL;
    $output.= '	<g:item_group_id>' . $parent->ID . '</g:item_group_id>' . PHP_EOL;
    $output.= '	<title><![CDATA[' . $parent->post_title . ']]></title>' . PHP_EOL;
    if (strip_shortcodes(strip_tags($parent_product->post->post_excerpt)) != "") {
        $output.= '	<description><![CDATA[' . substr(strip_shortcodes(strip_tags($parent_product->post->post_excerpt)), 0, 1000) . ']]></description>' . PHP_EOL;
    } else {
        $output.= '	<description><![CDATA[' . substr(strip_shortcodes(strip_tags($parent->post_content)), 0, 1000) . ']]></description>' . PHP_EOL;
    }
    $output.= '	<g:google_product_category><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $google_category)) . ']]></g:google_product_category>' . PHP_EOL;
    $output.= '	<g:product_type><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $google_category)) . ']]></g:product_type>' . PHP_EOL;
    $output.= '	<link><![CDATA[' . get_permalink($parent->ID) . ']]></link>' . PHP_EOL;
    $thumb_ID = get_post_thumbnail_id($prod->ID);
    $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
    $feature_imgurl = $thumb['0'];
    if ($feature_imgurl == "") {
        $thumb_ID = get_post_thumbnail_id($parent->ID);
        $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
        $feature_imgurl = $thumb['0'];
    }
    $output.= '	<g:image_link><![CDATA[' . $feature_imgurl . ']]></g:image_link>' . PHP_EOL;
    $attachments = $parent_product->get_gallery_attachment_ids();
    $attachments = array_diff($attachments, array($thumb_ID));
    if ($attachments) {
        $image_count = 0;
        foreach ($attachments as $attachment) {
            $thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
            $imgurl = $thumb['0'];
            $image_count++;
            $output.= '	<g:additional_image_link><![CDATA[' . $imgurl . ']]></g:additional_image_link>' . PHP_EOL;
        }
    }
    $output.= '	<g:condition>New</g:condition>' . PHP_EOL;
	if ($prod->stock_status == 1)
	  $output.= '	<g:availability>in stock</g:availability>' . PHP_EOL;
	else
	  $output.= '	<g:availability>out of  stock</g:availability>' . PHP_EOL;
    if ($product->price != "") {
        $output.= '	<g:price>' . $product->regular_price . " $currency" . '</g:price>' . PHP_EOL;
    } else {
        $output.= '	<g:price>' . $parent_product->regular_price . " $currency" . '</g:price>' . PHP_EOL;
    }
    if ($product->sale_price != "") {
        $output.= '	<g:sale_price>' . $product->sale_price . " $currency" . '</g:sale_price>' . PHP_EOL;
    } elseif ($parent_product->sale_price != "") {
        $output.= '	<g:sale_price>' . $parent_product->sale_price . " $currency" . '</g:sale_price>' . PHP_EOL;
    }
    $output.= '	<g:mpn>' . $product->sku . '</g:mpn>' . PHP_EOL;
    $wgt = $product->get_weight();
    if ($wgt != "") {
        $output.= '	<g:shipping_weight>' . $wgt . ' ' . $weight_unit . '</g:shipping_weight>' . PHP_EOL;
    }
    $output.= '	<g:shipping>' . PHP_EOL;
    $output.= '	    <g:service>Ground</g:service>' . PHP_EOL;
    $output.= "	    <g:price>0.00 $currency</g:price>" . PHP_EOL;
    $output.= '	</g:shipping>' . PHP_EOL;
    foreach ($attributes as $key => $value) {
        $output.= '	<g:' . $key . '>' . $value . '</g:' . $key . '>' . PHP_EOL;
    }
    $output.= '</item>' . PHP_EOL;
    return $output;
}

/**
 * Function to get attribute combination of each product (variable attribute + common attribute)
 */
function getAtttributesCombinationWithVariation($count, $current_count, $current_terms_count, $terms, $attributes, $attributes_key, $attributes_index, $parent, $parent_product, $google_category, $prod, $product, $variation_attributes) {
    $output = NULL;
    $weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
    $currency = get_woocommerce_currency();
    if ($current_count >= $count) {
        return;
    }
    foreach ($terms[$current_count] as $terms_item) {
        $key = get_option($this->providerName . '_cp_' . str_replace("pa_", "", $terms_item->taxonomy));
        $current_terms_count++;
        $attributes[$attributes_index] = $terms_item->name;
        $attributes_key[$attributes_index] = $key;
        $attributes_index++;
        $output.= $this->getAtttributesCombinationWithVariation($count, $current_count + 1, $current_terms_count, $terms, $attributes, $attributes_key, $attributes_index, $parent, $parent_product, $google_category, $prod, $product, $variation_attributes);
        if (count($attributes) == $count) {
            $output.= '<item>' . PHP_EOL;
            $output.= '	<g:id>' . $prod->ID . rand(100, 1000) . '</g:id>' . PHP_EOL;
            $output.= '	<g:item_group_id>' . $parent->ID . '</g:item_group_id>' . PHP_EOL;
            $output.= '	<title><![CDATA[' . $parent->post_title . ']]></title>' . PHP_EOL;
            if (strip_shortcodes(strip_tags($parent_product->post->post_excerpt)) != "") {
                $output.= '	<description><![CDATA[' . substr(strip_shortcodes(strip_tags($parent_product->post->post_excerpt)), 0, 1000) . ']]></description>' . PHP_EOL;
            } else {
                $output.= '	<description><![CDATA[' . substr(strip_shortcodes(strip_tags($parent->post_content)), 0, 1000) . ']]></description>' . PHP_EOL;
            }
            $output.= '	<g:google_product_category><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $google_category)) . ']]></g:google_product_category>' . PHP_EOL;
            $output.= '	<g:product_type><![CDATA[' . str_replace(".and.", " & ", str_replace(".in.", " > ", $google_category)) . ']]></g:product_type>' . PHP_EOL;
            $output.= '	<link><![CDATA[' . get_permalink($parent->ID) . ']]></link>' . PHP_EOL;
            $thumb_ID = get_post_thumbnail_id($prod->ID);
            $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
            $feature_imgurl = $thumb['0'];
            if ($feature_imgurl == "") {
                $thumb_ID = get_post_thumbnail_id($parent->ID);
                $thumb = wp_get_attachment_image_src($thumb_ID, 'small-feature');
                $feature_imgurl = $thumb['0'];
            }
            $output.= '	<g:image_link><![CDATA[' . $feature_imgurl . ']]></g:image_link>' . PHP_EOL;
            $attachments = $parent_product->get_gallery_attachment_ids();
            $attachments = array_diff($attachments, array($thumb_ID));
            if ($attachments) {
                $image_count = 0;
                foreach ($attachments as $attachment) {
                    $thumb = (wp_get_attachment_image_src($attachment, 'small-feature'));
                    $imgurl = $thumb['0'];
                    $image_count++;
                    $output.= '	<g:additional_image_link><![CDATA[' . $imgurl . ']]></g:additional_image_link>' . PHP_EOL;
                }
            }
            $output.= '	<g:condition>New</g:condition>' . PHP_EOL;
			if ($prod->stock_status == 1)
			  $output.= '	<g:availability>in stock</g:availability>' . PHP_EOL;
			else
			  $output.= '	<g:availability>out of  stock</g:availability>' . PHP_EOL;
            if ($product->regular_price != "") {
                $output.= '	<g:price>' . $product->regular_price . " $currency" . '</g:price>' . PHP_EOL;
            } else {
                $output.= '	<g:price>' . $parent_product->regular_price . " $currency" . '</g:price>' . PHP_EOL;
            }
            if ($product->sale_price != "") {
                $output.= '	<g:sale_price>' . $product->sale_price . " $currency" . '</g:sale_price>' . PHP_EOL;
            } elseif ($parent_product->sale_price != "") {
                $output.= '	<g:sale_price>' . $parent_product->sale_price . " $currency" . '</g:sale_price>' . PHP_EOL;
            }
            $output.= '	<g:mpn>' . $product->sku . '</g:mpn>' . PHP_EOL;
            $wgt = $product->get_weight();
            if ($wgt != "") {
                $output.= '	<g:shipping_weight>' . $wgt . ' ' . $weight_unit . '</g:shipping_weight>' . PHP_EOL;
            }
            $output.= '	<g:shipping>' . PHP_EOL;
            $output.= '	    <g:service>Ground</g:service>' . PHP_EOL;
            $output.= "	    <g:price>0.00 $currency</g:price>" . PHP_EOL;
            $output.= '	</g:shipping>' . PHP_EOL;
            foreach ($variation_attributes as $key => $value) {
                $output.= '	<g:' . $key . '>' . $value . '</g:' . $key . '>' . PHP_EOL;
            }
            for ($temp_index = 0; $temp_index < count($attributes); $temp_index++) {
                $output.= '	<g:' . $attributes_key[$temp_index] . '>' . $attributes[$temp_index] . '</g:' . $attributes_key[$temp_index] . '>' . PHP_EOL;
            }
            $output.= '</item>' . PHP_EOL;
        }
    }
    return $output;
}

}
?>