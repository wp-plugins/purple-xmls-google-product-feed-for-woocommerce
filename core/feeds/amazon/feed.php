<?php

  /********************************************************************
  Version 2.0
    An Amazon Feed (Product Ads)
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAmazonFeed extends PCSVFeedEx 
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'Amazon';
		$this->providerNameL = 'amazon';
		$this->fileformat = 'csv';
		$this->fields = array();
		//$this->fields = explode(',', 'Category,Title,Link,SKU,Price,Brand,Department,UPC,Image,Description,Manufacturer,Mfr part number,Other image-url1,Other image-url2,Other image-url3,Other image-url4,Other image-url5,Other image-url6,Other image-url7,Other image-url8,Weight,Shipping Cost,Shipping Weight');
		$this->fieldDelimiter = "\t";

		//Create some attributes (Mapping 3.0)
		//Required
		$this->addAttributeMapping('category', 'Category');
		$this->addAttributeMapping('title', 'Title',true);
		$this->addAttributeMapping('link', 'Link');
		$this->addAttributeMapping('sku', 'SKU');
		$this->addAttributeMapping('price', 'Price');
		$this->addAttributeMapping('feature_imgurl', 'Image');
		//Strongly Recommended Fields
		$this->addAttributeMapping('upc', 'UPC');
		$this->addAttributeMapping('brand', 'Brand');
		$this->addAttributeMapping('recommended_browse_node', 'Recommended Browse Node');
		$this->addAttributeMapping('department', 'Department');
		$this->addAttributeMapping('description', 'Description', true);
		$this->addAttributeMapping('manufacturer', 'Manufacturer');
		$this->addAttributeMapping('mfr_part_number', 'Mfr part number');
		$this->addAttributeMapping('shipping_cost', 'Shipping Cost');
		//Recommended Fields
		for ($i = 1; $i < 6; $i++)
			$this->addAttributeMapping("bullet_point$i", "Bullet point$i");
		$this->addAttributeMapping('age', 'Age');
		$this->addAttributeMapping('item_package_quantity', 'Item Package Quantity');
		$this->addAttributeMapping('height', 'Height');
		$this->addAttributeMapping('length', 'Length');
		$this->addAttributeMapping('weight', 'Weight');
		$this->addAttributeMapping('width', 'Width');
		for ($i = 1; $i < 6; $i++)
			$this->addAttributeMapping("keywords$i", "Keywords$i");
		$this->addAttributeMapping('model_number', 'Model Number');
		for ($i = 1; $i < 9; $i++)
			$this->addAttributeMapping("other_image_url$i", "Other image-url$i");
		$this->addAttributeMapping('shipping_weight', 'Shipping Weight');
		$this->addAttributeMapping('size', 'Size');
		$this->addAttributeMapping('color', 'Color');
		$this->addAttributeMapping('gender', 'Gender');
		$this->addAttributeMapping('scent', 'Scent');
		$this->addAttributeMapping('color_and_finish', 'Color and finish');
		$this->addAttributeMapping('material', 'Material');
		$this->addAttributeMapping('flavor', 'Flavor');
		$this->addAttributeMapping('theme_hpc', 'Theme HPC');
		$this->addAttributeMapping('league_and_team', 'League and Team');
		$this->addAttributeMapping('watch_movement', 'Watch Movement');
		$this->addAttributeMapping('maximum_age', 'Maximum Age');
		$this->addAttributeMapping('minimum_age', 'Minimum Age');
		$this->addAttributeMapping('cuisine', 'Cuisine');
		$this->addAttributeMapping('specialty', 'Specialty');
		$this->addAttributeMapping('occasion', 'Occasion');
		$this->addAttributeMapping('memory_card_type', 'Memory Cart type');
		$this->addAttributeMapping('computer_cpu_speed', 'Computer CPU Speed');
		$this->addAttributeMapping('computer_memory_size', 'Computer Memory Size');
		$this->addAttributeMapping('digital_camera_resolution', 'Digital Camera Resolution');
		$this->addAttributeMapping('display_size', 'Display Size');
		$this->addAttributeMapping('cuisine', 'Display Technology');
		$this->addAttributeMapping('cuisine', 'Flash Drive Size');
		$this->addAttributeMapping('hard_disk_size', 'Hard Disk Size');
		$this->addAttributeMapping('included_ram_size', 'Included RAM Size');
		$this->addAttributeMapping('operating_system', 'Operating System');
		$this->addAttributeMapping('optical_zoom', 'Optical Zoom');
		$this->addAttributeMapping('screen_resolution', 'Screen Resolution');
		$this->addAttributeMapping('metal_type', 'Metal Type');
		$this->addAttributeMapping('size_per_pearl', 'Size per Pearl');
		$this->addAttributeMapping('total_diamond_weight', 'Total Diamond Weight');
		$this->addAttributeMapping('ring_size', 'Ring Size');
		$this->addAttributeMapping('band_material', 'Band Material');
		$this->addAttributeMapping('sku_bid', 'Sku Bid');
		//Variations attributes
		$this->addAttributeMapping('variation_product_type', 'variation-product-type');
		$this->addAttributeMapping('parent_child', 'parent-child');
		$this->addAttributeMapping('parent_sku', 'parent-sku');
		$this->addAttributeMapping('relationship_type', 'relationship-type');
		$this->addAttributeMapping('variation_theme', 'variation-theme');				
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
		//$product->attributes['mfr_part_number'] = $product->attributes['id'] . $variantMfr;
		//cheat
		$product->attributes['category'] = $this->current_category;
		$product->attributes['description'] = $product->description;		
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;
		$image_count = 0;
		foreach($product->imgurls as $imgurl) {
			$image_index = "other_image_url_$image_count";
			$product->attributes[$image_index] = $imgurl;
			$image_count++;
			if ($image_count >= 9)
				break;
		}
		//List the product price in US dollars, without a $ sign, commas, text, or quotation marks.
		$product->attributes['price'] = $product->attributes['regular_price'];
		if (($product->attributes['has_sale_price']) && ($product->attributes['sale_price'] != ""))
			$product->attributes['price'] = $product->attributes['sale_price'];

		//$product->attributes['shipping_cost'] = '0.00 ';
		$product->attributes['shipping_weight'] = $product->attributes['weight'];
		$product->attributes['weight'] = $product->attributes['weight'] . $this->weight_unit;

		return parent::formatProduct($product);
	}

}

?>