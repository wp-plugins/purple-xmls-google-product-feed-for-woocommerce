<?php

  /********************************************************************
  Version 2.1
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
  
  function formatProduct($product) {
    $output = '
      <item>';
	$output .= $this->formatLine('g:id', $product->id);
	if (isset($product->item_group_id)) {
	  $output .= $this->formatLine('g:item_group_id', $product->item_group_id);
	}
	
    $output .= $this->formatLine('title', $product->title, true);
	$output .= $this->formatLine('description', $product->description, true);
	$output .= $this->formatLine('g:google_product_category', $this->current_category, true);
	$output .= $this->formatLine('g:product_type', $product->product_type, true);
	$output .= $this->formatLine('link', $product->link, true);
	$output .= $this->formatLine('g:image_link', $product->feature_imgurl, true);
	
	$image_count = 0;
	foreach($product->imgurls as $imgurl) {
	  $output .= $this->formatLine('g:additional_image_link', $imgurl, true);
	  $image_count++;
	  if ($image_count > 9)
	    break;
	}
	$output.= $this->formatLine('g:condition', $product->condition);
	
	if ($product->stock_status == 1) {
	  $product->stock_status = 'in stock';
	} else {
	  $product->stock_status = 'out of stock';
	}
	$output.= $this->formatLine('g:availability', $product->stock_status);
	
	if (strlen($product->regular_price) == 0) {
	  $product->regular_price = '0.00';
	}
	$output.= $this->formatLine('g:price', $product->regular_price);
	if ($product->has_sale_price) {
	  $output.= $this->formatLine('g:sale_price', $product->sale_price/* . ' ' . $this->currency*/);
	}
	$output.= $this->formatLine('g:mpn', $product->sku);
	
	if ($product->weight != "") {
	  $output.= $this->formatLine('g:shipping_weight', $product->weight . ' ' . $this->weight_unit);
	}

	$output.= '
	    <g:shipping>' .
		  $this->formatLine('g:service', 'Ground', false, '  ') .
		  $this->formatLine('g:price', '0.00', false, '  ') . '
        </g:shipping>';
	
	if (isset($product->tax)) {
	  $output.= '
	    <g:tax>' .
		  $this->formatLine('g:country', 'US', false, '  ') .
		  $this->formatLine('g:rate', $product->tax, false, '  ') . '
        </g:tax>';
	}

	foreach($product->attributes as $key => $a) {
	  if (isset($this->feedOverrides->overrides[$key])) {
	    $output .= $this->formatLine($key, $a);
	  }
	}
    $output .= '
	  </item>';
    return $output;
  }

  function getFeedFooter() {
    $output = null;
    $output.= '
  </channel>
  </rss>';
	return $output;
  }

  function getFeedHeader($file_name, $file_path) {
    $output = '<?xml version="1.0" encoding="UTF-8" ?>
  <rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">
  <channel>
    <title>' . $file_name . '</title>
    <link><![CDATA[' . $file_path . ']]></link>
    <description>' . $file_name . '</description>';
	return $output;
  }

}
?>