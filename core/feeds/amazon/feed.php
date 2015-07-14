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
		$this->fileformat = 'txt';
		$this->fields = array();
		//$this->fields = explode(',', 'Category,Title,Link,SKU,Price,Brand,Department,UPC,Image,Description,Manufacturer,Mfr part number,Other image-url1,Other image-url2,Other image-url3,Other image-url4,Other image-url5,Other image-url6,Other image-url7,Other image-url8,Weight,Shipping Cost,Shipping Weight');
		$this->fieldDelimiter = "\t";

		//Create some attributes (Mapping 3.0)
		//Required
		$this->addAttributeMapping('current_category', 'Category',true,true);
		$this->addAttributeMapping('title', 'Title',true,true);
		$this->addAttributeMapping('link', 'Link',true,true);
		$this->addAttributeMapping('sku', 'SKU',true,true);
		$this->addAttributeMapping('price', 'Price',true,true);		
		//Strongly Recommended Fields
		$this->addAttributeMapping('brand', 'Brand',false);
		$this->addAttributeMapping('department', 'Department',false);
		$this->addAttributeMapping('', 'UPC',false);		
		$this->addAttributeMapping('feature_imgurl', 'Image',true);
		$this->addAttributeMapping('description', 'Description', true,false);		
		$this->addAttributeMapping('manufacturer', 'Manufacturer',false,false);
		$this->addAttributeMapping('', 'Mfr part number');
		$this->addAttributeMapping('', 'Shipping Cost');
		//Recommended Fields
		$this->addAttributeMapping('', 'Age');
		//$this->addAttributeMapping('', 'Band'); //jewelry
		for ($i = 1; $i < 6; $i++)
			$this->addAttributeMapping("bullet_point$i", "Bullet point$i");
		$this->addAttributeMapping('color', 'Color');
		$this->addAttributeMapping('', 'Color and finish');
		$this->addAttributeMapping('', 'Computer CPU speed');
		$this->addAttributeMapping('', 'Computer memory size');
		$this->addAttributeMapping('', 'Digital Camera Resolution');
		$this->addAttributeMapping('', 'Display size');
		$this->addAttributeMapping('', 'Display technology');
		$this->addAttributeMapping('', 'Flash drive Size');
		$this->addAttributeMapping('', 'Flavor');
		$this->addAttributeMapping('', 'Gender');
		$this->addAttributeMapping('', 'Hard disk size');
		$this->addAttributeMapping('height', 'Height'); //15,3 FT
		$this->addAttributeMapping('', 'Included RAM size');
		$this->addAttributeMapping('', 'Item package quantity');
		for ($i = 1; $i < 6; $i++)
			$this->addAttributeMapping("keywords$i", "Keywords$i");	
		$this->addAttributeMapping('', 'League and Team');		
		$this->addAttributeMapping('length', 'Length');
		$this->addAttributeMapping('', 'Material');	
		$this->addAttributeMapping('', 'Maximum Age');
		$this->addAttributeMapping('', 'Memory Card type');
		$this->addAttributeMapping('', 'Minimum Age');
		$this->addAttributeMapping('', 'Model Number');
		$this->addAttributeMapping('', 'Operating System');
		$this->addAttributeMapping('', 'Optical zoom');
		for ($i = 1; $i < 9; $i++)
			$this->addAttributeMapping("other_image_url$i", "Other image-url$i");
		$this->addAttributeMapping('', 'Recommended Browse Node',false);		
		$this->addAttributeMapping('', 'Ring size');
		$this->addAttributeMapping('', 'Scent');	
		$this->addAttributeMapping('', 'Screen Resolution');
		$this->addAttributeMapping('weight', 'Shipping Weight');
		$this->addAttributeMapping('size', 'Size');
		$this->addAttributeMapping('', 'Size per Pearl'); //Indicates the size per pearl (note that unit of measure is millimeter).
		$this->addAttributeMapping('', 'Theme HPC'); //Health & Personal Care
		$this->addAttributeMapping('', 'Total Diamond Weight');
		$this->addAttributeMapping('', 'Watch movement');
		$this->addAttributeMapping('', 'Weight');
		$this->addAttributeMapping('width', 'Width');

		/* deprecated attributes?
		$this->addAttributeMapping('cuisine', 'Cuisine');
		$this->addAttributeMapping('specialty', 'Specialty');
		$this->addAttributeMapping('occasion', 'Occasion');
		$this->addAttributeMapping('metal_type', 'Metal Type');
		$this->addAttributeMapping('band_material', 'Band Material');
		$this->addAttributeMapping('sku_bid', 'Sku Bid');
		//Variations attributes
		$this->addAttributeMapping('variation_product_type', 'variation-product-type');
		$this->addAttributeMapping('parent_child', 'parent-child');
		$this->addAttributeMapping('parent_sku', 'parent-sku');
		$this->addAttributeMapping('relationship_type', 'relationship-type');
		$this->addAttributeMapping('variation_theme', 'variation-theme');	
		*/		
		$this->addRule('price_rounding','pricerounding'); //2 decimals		
	}

	function formatProduct($product) {

		$variantUPC = '';
		$variantMfr = '';
		if ($product->attributes['isVariation']) {
			//Not used in original code
			//$variantUPC = rand();
			//$variantMfr = rand();
		}

		//Prepare
		//$product->attributes['id'] = $product->attributes['id'] . $variantUPC; //Not used in original code
		//$product->attributes['mfr_part_number'] = $product->attributes['id'] . $variantMfr;

		$image_count = 1;
		foreach($product->imgurls as $imgurl) {
			$image_index = "other_image_url$image_count";
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
		if (isset($product->attributes['weight'])) {
			$product->attributes['shipping_weight'] = $product->attributes['weight'];
		 } 
		//else {
		// 	$product->attributes['shipping_weight'] = '0 kg';
		// 	$product->attributes['weight'] = '0 kg';
		// }

		return parent::formatProduct($product);
	}

}

?>