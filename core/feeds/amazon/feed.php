<?php

  /********************************************************************
  Version 2.0
    An Amazon Feed (Product Ads)
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAmazonFeed extends PCSVFeed {

	function __construct () {

		parent::__construct();
		$this->providerName = 'Amazon';
		$this->providerNameL = 'amazon';
		$this->fileformat = 'csv';
		$this->fields = explode(',', 'Category,Title,Link,SKU,Price,Brand,Department,UPC,Image,Description,Manufacturer,Mfr part number,Other image-url1,Other image-url2,Other image-url3,Other image-url4,Other image-url5,Other image-url6,Other image-url7,Other image-url8,Weight,Shipping Cost,Shipping Weight');
		$this->fieldDelimiter = ',';

		//Create some attributes (Mapping 3.0)
		$this->addAttributeMapping('id', 'UPC');
		$this->addAttributeMapping('mfr_part_number', 'Mfr part number');
		$this->addAttributeMapping('title', 'Title', true);
		$this->addAttributeMapping('description', 'Description', true);
		$this->addAttributeMapping('category', 'Category');
		$this->addAttributeMapping('link', 'Link');
		$this->addAttributeMapping('feature_imgurl', 'Image');
		for ($i = 0; $i < 9; $i++)
			$this->addAttributeMapping("other_image_url_$i", "Other image-url$i");
		$this->addAttributeMapping('price', 'Price');
		$this->addAttributeMapping('sku', 'SKU');
		$this->addAttributeMapping('shipping_cost', 'Shipping Cost');
		$this->addAttributeMapping('shipping_weight', 'Shipping Weight');
		$this->addAttributeMapping('weight', 'Weight');
		
	}

	function formatProduct($product) {

		$variantUPC = '';
		$variantMfr = '';
		if ($product->isVariable) {
			//Not used in original code
			//$variantUPC = rand();
			//$variantMfr = rand();
		}

		//Prepare

		//$product->attributes['id'] = $product->attributes['id'] . $variantUPC; //Not used in original code
		$product->attributes['mfr_part_number'] = $product->attributes['id'] . $variantMfr;
		$product->attributes['description'] = $product->description;
		$product->attributes['category'] = $this->current_category;
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;
		$image_count = 0;
		foreach($product->imgurls as $imgurl) {
			$image_index = "other_image_url_$image_count";
			$product->attributes[$image_index] = $imgurl;
			$image_count++;
			if ($image_count >= 9)
				break;
		}
		$product->attributes['price'] = $product->attributes['regular_price'] . ' ' . $this->currency;
		if (($product->attributes['has_sale_price']) && ($product->attributes['sale_price'] != ""))
			$product->attributes['price'] = $product->attributes['sale_price'] . ' ' . $this->currency;
		$product->attributes['shipping_cost'] = '0.00 ' . $this->currency;
		$product->attributes['shipping_weight'] = $product->attributes['weight'] . ' ' . $this->weight_unit;
		$product->attributes['weight'] = $product->attributes['weight'] . $this->weight_unit;

		return parent::formatProduct($product);
		
	}

}

?>