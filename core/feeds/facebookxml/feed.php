<?php

/********************************************************************
 * Version 2.1
 * A Google Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-08
 * 2014-09 Retired Attribute Mapping v2.0 (Keneto)
 * 2014-11 All required & optional parameters now show
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PFacebookXMLFeed extends PXMLFeed {
    function __construct() {
        parent::__construct();
        $this->providerName  = 'FacebookXML';
        $this->providerNameL = 'facebookxml';
        //Create some attributes (Mapping 3.0) in the form (title, Google-title, CData, isRequired)
        //Note that isRequired is just to direct the plugin on where on the dialog to display
        $this->addAttributeMapping('id', 'g:id', false, true);
        $this->addAttributeMapping('item_group_id', 'g:item_group_id', false, false);
        $this->addAttributeMapping('stock_status', 'g:availability', false, true);
        $this->addAttributeMapping('condition', 'g:condition', false, true);
        $this->addAttributeMapping('description', 'g:description', true, true);
        $this->addAttributeMapping('feature_imgurl', 'g:image_link', true, true);
        $this->addAttributeMapping('link', 'g:link', true, true);
        $this->addAttributeMapping('title', 'g:title', true, true);
        $this->addAttributeMapping('regular_price', 'g:price', false, true);
        $this->addAttributeMapping('sku', 'g:mpn', false, true);
		$this->addAttributeMapping('brand', 'g:brand', false, true);

        //Optional Attributes
        $this->addAttributeMapping('upc', 'g:gtin', true, false);
        $this->addAttributeMapping('age_group', 'g:age_group', true, false);
        $this->addAttributeMapping('size', 'g:size', true, false);
        $this->addAttributeMapping('color', 'g:color', true, false);
        $this->addAttributeMapping('', 'g:expiration_date', true, false);
        $this->addAttributeMapping('', 'g:gender', false, false);
        $this->addAttributeMapping('current_category', 'g:google_product_category', true, false);
        $this->addAttributeMapping('', 'g:material', true, false);
        $this->addAttributeMapping('', 'g:pattern', true, false);
        $this->addAttributeMapping('', 'g:product_type', true, false);        
        $this->addAttributeMapping('sale_price', 'g:sale_price', false, false);
        $this->addAttributeMapping('sale_price_effective_date', 'g:sale_price_effective_date', false, false);
       
        //Optional Shipping attributes
       // $this->addAttributeMapping('', 'g:shipping', true, false);
        $this->addAttributeMapping('weight', 'g:shipping_weight', true, false);
        $this->addAttributeMapping('shipping_size', 'g:shipping_size', true, false);

//		$this->addAttributeMapping('tax', 'g:tax', false, false);
//		$this->addAttributeMapping('', 'g:multipack', false, false);
//		$this->addAttributeMapping('adult', 'g:adult', false, false);
//		$this->addAttributeMapping('adwords_grouping', 'g:adwords_grouping', false, false);
//		$this->addAttributeMapping('adwords_labels', 'g:adwords_labels', false, false);
//		$this->addAttributeMapping('adwords_redirect', 'g:adwords_redirect', false, false);
//		$this->addAttributeMapping('', 'g:unit_pricing_measure', false, false);
//		$this->addAttributeMapping('', 'g:unit_pricing_base_measure', false, false);
//		$this->addAttributeMapping('', 'g:energy_efficiency_class', false, false);
//		$this->addAttributeMapping('', 'g:excluded_destination', false, false);
//		$this->addAttributeMapping('', 'g:custom_label_0', false, false);
//		$this->addAttributeMapping('', 'g:custom_label_1', false, false);
//		$this->addAttributeMapping('', 'g:custom_label_2', false, false);
//		$this->addAttributeMapping('', 'g:custom_label_3', false, false);
//		$this->addAttributeMapping('', 'g:custom_label_4', false, false);

		$this->google_exact_title = false;
		$this->google_combo_title = false;
        $this->addRule('google_exact_title', 'googleexacttitle');
        $this->addRule('google_combo_title', 'googlecombotitle');

        $this->productLevelElement = 'item';

        $this->addAttributeDefault('additional_images', 'none', 'PGoogleAdditionalImages');
//		$this->addAttributeDefault('tax_country', 'US');

		$this->addRule('price_standard', 'pricestandard');
		$this->addRule('status_standard', 'statusstandard');
        $this->addRule('price_rounding','pricerounding'); //2 decimals
		$this->addRule('weight_unit', 'weightunit');	

    }

    function formatProduct($product) {
        global $pfcore;
        //********************************************************************
        //Prepare the Product Attributes
        //********************************************************************

        //Format is "LxWxH units"
        $product->attributes['shipping_size'] = null;
        //length
        if ( isset($product->attributes['length']) )
            $product->attributes['shipping_size'] = $product->attributes['length'];
        else 
            $product->attributes['shipping_size'] = 0;
        //width
        if ( isset($product->attributes['width']) )
            $product->attributes['shipping_size'] .= 'x' . $product->attributes['width'];
        else
            $product->attributes['shipping_size'] .= 'x' . 0;
        //height
        if ( isset($product->attributes['height']) )
            $product->attributes['shipping_size'] .= 'x' . $product->attributes['height'];
        else
            $product->attributes['shipping_size'] .= 'x'. 0;
        //format units if shipping_size 'exists'
        if ( $product->attributes['shipping_size'] == '0x0x0')
             $product->attributes['shipping_size'] = null;
        else 
            $product->attributes['shipping_size'] .= ' ' . $this->dimension_unit;
//PLA
//$product->attributes['link'] .= '?source=googleproduct&cvars='. rawurlencode($product->attributes['title']);
       if (isset($product->attributes['sale_price_dates_from']) && isset($product->attributes['sale_price_dates_to'])) 
        {   
            $product->attributes['sale_price_dates_from'] = $pfcore->localizedDate( 'Y-m-d\TH:i:sO', $product->attributes['sale_price_dates_from'] );
            $product->attributes['sale_price_dates_to'] = $pfcore->localizedDate( 'Y-m-d\TH:i:sO', $product->attributes['sale_price_dates_to'] );

            if ( strlen($product->attributes['sale_price_dates_from']) > 0 && strlen($product->attributes['sale_price_dates_to']) > 0 )
                $product->attributes['sale_price_effective_date'] = $product->attributes['sale_price_dates_from'].'/'.$product->attributes['sale_price_dates_to'];
        }

        //********************************************************************
        //Validation checks & Error messages
        //********************************************************************
		if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			if (($this->getMappingByMapto('g:identifier_exists') == null) && ($this->getMappingByMapto('`in') == null) && ($this->getMappingByMapto('g:brand') == null))
				$this->addErrorMessage(2000, 'Missing brand for ' . $product->attributes['title']);

        return parent::formatProduct($product);

    }

    function getFeedFooter($file_name, $file_path) {
        $output = '
  </channel>
</rss>';

        return $output;
    }

    function getFeedHeader($file_name, $file_path) {
        $output = '<?xml version="1.0" ?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
  <channel>
    <title>' . $file_name . '</title>
    <link><![CDATA[' . $file_path . ']]></link>
    <description>' . $file_name . '</description>';

        return $output;
    }

}