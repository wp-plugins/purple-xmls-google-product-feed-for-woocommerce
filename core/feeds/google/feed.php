<?php

	/********************************************************************
	Version 2.1
		A Google Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
		2014-09 Retired Attribute Mapping v2.0 (Keneto)
		2014-11 All required & optional parameters now show
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PGoogleFeed extends PXMLFeed
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'Google';
		$this->providerNameL = 'google';
		//Create some attributes (Mapping 3.0) in the form (title, Google-title, CData, isRequired)
		//  Note that isRequired is just to direct the plugin on where on the dialog to display
		$this->addAttributeMapping('id', 'g:id', false, true);
		$this->addAttributeMapping('item_group_id', 'g:item_group_id', false, true);
		$this->addAttributeMapping('title', 'title', true, true);
		$this->addAttributeMapping('description', 'description', true, true);
		$this->addAttributeMapping('link', 'link', true, true);
		$this->addAttributeMapping('product_type', 'g:product_type', true, true);	
		$this->addAttributeMapping('current_category', 'g:google_product_category', true, true);
		$this->addAttributeMapping('condition', 'g:condition', false, true);
		$this->addAttributeMapping('stock_status', 'g:availability', false, true);
		$this->addAttributeMapping('sku', 'g:mpn', false, true);
		$this->addAttributeMapping('regular_price', 'g:price', false, true);
		$this->addAttributeMapping('sale_price', 'g:sale_price', false, false);
		$this->addAttributeMapping('brand', 'g:brand', false, true);
		$this->addAttributeMapping('weight', 'g:shipping_weight', false, false);
		$this->addAttributeMapping('feature_imgurl', 'g:image_link', true, true);

		//Optional Attributes
		$this->addAttributeMapping('gender', 'g:gender', false, false);
		$this->addAttributeMapping('age_group', 'g:age_group', false, false);
		$this->addAttributeMapping('color', 'g:color', false, false);
		$this->addAttributeMapping('size', 'g:size', false, false);
		$this->addAttributeMapping('material', 'g:material', false, false);
		$this->addAttributeMapping('pattern', 'g:pattern', false, false);
		$this->addAttributeMapping('', 'g:sale_price_effective_date', false, false);
		$this->addAttributeMapping('tax', 'g:tax', false, false);
		$this->addAttributeMapping('', 'g:multipack', false, false);
		$this->addAttributeMapping('adult', 'g:adult', false, false);
		$this->addAttributeMapping('adwords_grouping', 'g:adwords_grouping', false, false);
		$this->addAttributeMapping('adwords_labels', 'g:adwords_labels', false, false);
		$this->addAttributeMapping('adwords_redirect', 'g:adwords_redirect', false, false);
		$this->addAttributeMapping('', 'g:unit_pricing_measure', false, false);
		$this->addAttributeMapping('', 'g:unit_pricing_base_measure', false, false);
		$this->addAttributeMapping('', 'g:energy_efficiency_class', false, false);
		$this->addAttributeMapping('', 'g:excluded_destination', false, false);
		$this->addAttributeMapping('', 'g:expiration_date', false, false);
		$this->addAttributeMapping('', 'g:custom_label_0', false, false);
		$this->addAttributeMapping('', 'g:custom_label_1', false, false);
		$this->addAttributeMapping('', 'g:custom_label_2', false, false);
		$this->addAttributeMapping('', 'g:custom_label_3', false, false);
		$this->addAttributeMapping('', 'g:custom_label_4', false, false);

		$this->google_exact_title = false;
		$this->google_combo_title = false;
		$this->productLevelElement = 'item';

		$this->addAttributeDefault('additional_images', 'none', 'PGoogleAdditionalImages');
		$this->addAttributeDefault('tax_country', 'US');

		$this->addRule('price_standard', 'pricestandard');
		$this->addRule('status_standard', 'statusstandard');
		$this->addRule('weight_unit', 'weightunit');
		$this->addRule('google_exact_title', 'googleexacttitle');
		$this->addRule('google_combo_title', 'googlecombotitle');

	}
 
  function formatProduct($product) 
  {
		//********************************************************************
		//Prepare the Product Attributes
		//********************************************************************

//PLA
//$product->attributes['link'] .= '?source=googleproduct&cvars='. rawurlencode($product->attributes['title']);
		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************

		if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
			if ($this->getMappingByMapto('g:identifier_exists') == null)
				$this->addErrorMessage(2000, 'Missing brand for ' . $product->attributes['title']);

		return parent::formatProduct($product);

	}

	function getFeedFooter( ) 
	{   
    	$output = '
  </channel>
</rss>';
		return $output;
	}

	function getFeedHeader( $file_name, $file_path ) 
	{
		$output = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">
  <channel>
    <title>' . $file_name . '</title>
    <link><![CDATA[' . $file_path . ']]></link>
    <description>' . $file_name . '</description>';
		return $output;
  }

}