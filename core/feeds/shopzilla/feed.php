<?php

	/********************************************************************
	Version 3.0
	A Shopzilla Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
		2014-09 Moved to Mapping v3
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PShopzillaFeed extends PCSVFeedEx{

	function __construct () {
		parent::__construct();
		$this->providerName = 'Shopzilla';
		$this->providerNameL = 'shopzilla';
		$this->fileformat = 'csv';
		$this->fields = array();
		$this->descriptionStrict = true;

		$this->addAttributeMapping('id', 'Unique ID');
		$this->addAttributeMapping('item_group_id', 'Item Group ID');
		$this->addAttributeMapping('title', 'Title');
		$this->addAttributeMapping('description', 'Description', true);
		$this->addAttributeMapping('category', 'Category', true);
		$this->addAttributeMapping('link', 'Product URL');
		$this->addAttributeMapping('feature_imgurl', 'Image URL');
		$this->addAttributeMapping('additional_images', 'Additional Image URL');

		$this->addAttributeMapping('condition', 'Condition');
		$this->addAttributeMapping('availability', 'Availability');
		$this->addAttributeMapping('current_price', 'Current Price');
		$this->addAttributeMapping('original_price', 'Original Price');
		$this->addAttributeMapping('weight', 'Ship Weight');
	}

  function formatProduct($product) {

		//cheat: Remap these
		$product->attributes['category'] = $this->current_category;
		$product->attributes['description'] = $product->description;		
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;

		//prepare
    if ($product->isVariable)
			$product->attributes['item_group_id'] = $product->item_group_id;
		$product->attributes['category'] = str_replace(',', '', $product->attributes['category']);

		//Max 9 Additional Images
		$product->attributes['additional_images'] = '';
		$image_count = 0;
		foreach($product->imgurls as $imgurl) {
			$product->attributes['additional_images'] .= $imgurl . ',';
			$image_count++;
			if ($image_count >= 9)
				break;
		}
		if (strlen($product->attributes['additional_images']) > 0)
			$product->attributes['additional_images'] = substr($product->attributes['additional_images'], 0, -1);

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['availability'] = 'In Stock';
		else
			$product->attributes['availability'] = 'Out of Stock';
	
		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';

		$product->attributes['current_price'] = $product->attributes['regular_price'];
		$product->attributes['original_price'] = $product->attributes['regular_price'];
		if ($product->attributes['has_sale_price'])
			$product->attributes['current_price'] = $product->attributes['sale_price'];

		return parent::formatProduct($product);

  }

}

?>