<?php

  /********************************************************************
  Version 2.0
    An Amazon Feed (Product Ads)
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08

  ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAmazonPAUKFeed extends PCSVFeedEx 
{
	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'AmazonPAUK';
		$this->providerNameL = 'AmazonPAUK';
		$this->fileformat = 'txt';
		$this->fields = array();
		//$this->fields = explode(',', 'Category,Title,Link,SKU,Price,Brand,Department,UPC,Image,Description,Manufacturer,Mfr part number,Other image-url1,Other image-url2,Other image-url3,Other image-url4,Other image-url5,Other image-url6,Other image-url7,Other image-url8,Weight,Shipping Cost,Shipping Weight');
		$this->fieldDelimiter = "\t";

		//Create some attributes (Mapping 3.0)
//Required
		$this->addAttributeMapping('current_category', 'Product Type',true,true);		
		$this->addAttributeMapping('link', 'Link',true,true);
		$this->addAttributeMapping('sku', 'SKU',true,true);
		$this->addAttributeMapping('title', 'Title',true,true);		
		$this->addAttributeMapping('price', 'Price',true,true);		
		$this->addAttributeMapping('feature_imgurl', 'Image',true);
//Strongly Recommended Fields
		$this->addAttributeMapping('upc', 'Standard Product ID',false);		
		$this->addAttributeMapping('', 'Product ID Type',false);		
		$this->addAttributeMapping('brand', 'Brand',false);
		$this->addAttributeMapping('manufacturer', 'Manufacturer',false,false);
		//should allow user to select from BTG (just like category/product type)
		$this->addAttributeMapping('', 'Recommended Browse Node',false); 		
		$this->addAttributeMapping('department', 'Department',false);		
		$this->addAttributeMapping('description', 'Description', true,false);		
		$this->addAttributeMapping('', 'Mfr part number');
		$this->addAttributeMapping('', 'Shipping Cost');
		$this->addAttributeMapping('quantity', 'Quantity'); //ed if you have a shared Selling on Amazon and Product Ads account. Enter the quantity of the item you are advertising.
//Recommended Fields
		$this->addAttributeMapping('local_category', 'Category',true,false); 
			$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category

		//$this->addAttributeMapping('', 'Band'); //jewelry
		for ($i = 1; $i < 6; $i++)
			$this->addAttributeMapping("bullet_point$i", "Bullet point$i");
		for ($i = 1; $i < 6; $i++)
			$this->addAttributeMapping("keywords$i", "Keywords$i");	
		for ($i = 1; $i < 9; $i++)
			$this->addAttributeMapping("other_image_url$i", "Other image-url$i");
		$this->addAttributeMapping('', 'product subtype'); //describes the class of product, chosen from valid values
		$this->addAttributeMapping('', 'Outer Material Type');
		$this->addAttributeMapping('', 'is adult product');
		$this->addAttributeMapping('', 'Season');
		$this->addAttributeMapping('', 'display type');
		$this->addAttributeMapping('', 'Watch movement');
		$this->addAttributeMapping('', 'model year');
		//target audience keywords1 - target audience keywords3
		$this->addAttributeMapping('', 'volume capacity name');
		//target_audience_base1 - 5
		$this->addAttributeMapping('', 'collection name');
		$this->addAttributeMapping('', 'style name');
		$this->addAttributeMapping('height', 'Height'); //15,3 FT
		$this->addAttributeMapping('length', 'Length');
		$this->addAttributeMapping('weight', 'Weight');
		$this->addAttributeMapping('width', 'Width');
		$this->addAttributeMapping('', 'Model Number');
		$this->addAttributeMapping('', 'Item package quantity'); //a six-pack of tree-shaped air fresheners would have an item-package-quantity of 6.
		$this->addAttributeMapping('', 'Shipping Weight');
		$this->addAttributeMapping('material', 'Material');	
		$this->addAttributeMapping('', 'fabric type');
		$this->addAttributeMapping('', 'Sport'); //name of the game or activity	
		$this->addAttributeMapping('', 'Computer CPU speed');
		$this->addAttributeMapping('', 'Included RAM size');
		$this->addAttributeMapping('', 'Operating System');
		$this->addAttributeMapping('', 'Hard disk size');
		$this->addAttributeMapping('', 'Flash drive Size');
		$this->addAttributeMapping('', 'Optical zoom');
		$this->addAttributeMapping('', 'Digital Camera Resolution');
		$this->addAttributeMapping('', 'Screen Resolution');
		$this->addAttributeMapping('', 'Memory Card type');
		$this->addAttributeMapping('', 'Minimum Age');
		$this->addAttributeMapping('', 'Maximum Age');
		$this->addAttributeMapping('gender', 'Gender');
		//specific_uses_keywords1 - 5
		//special_features1 - 5
		$this->addAttributeMapping('', 'water resistance depth');
		$this->addAttributeMapping('flavor', 'Flavor');	
		$this->addAttributeMapping('', 'League and Team');
		$this->addAttributeMapping('', 'Occasion');
		$this->addAttributeMapping('', 'Band material');
		$this->addAttributeMapping('', 'Metal type');
		$this->addAttributeMapping('', 'Size per pearl');
		$this->addAttributeMapping('', 'Color and finish');
		$this->addAttributeMapping('', 'Total Diamond Weight');
		$this->addAttributeMapping('', 'Scent');
		$this->addAttributeMapping('', 'Specialty');
		$this->addAttributeMapping('', 'Cuisine');
		$this->addAttributeMapping('', 'Display technology');	
		$this->addAttributeMapping('', 'Display size');
		$this->addAttributeMapping('', 'Computer memory size');		
//optional fields
		$this->addAttributeMapping('update_delete', 'Update Delete');		
//price per unit	
		$this->addAttributeMapping('', 'item display weight'); //The weight of the item (excluding packaging), for comparison with other items.
		$this->addAttributeMapping('', 'item display volume');
		$this->addAttributeMapping('', 'item display length');
		$this->addAttributeMapping('', 'unit count'); //used to calculate price per unit
//compliance - attributes used to comply with consumer laws int he country or region where the item is sold
		$this->addAttributeMapping('', 'safety warning');
		$this->addAttributeMapping('', 'country of origin');
		$this->addAttributeMapping('', 'legal disclaimer description');
		$this->addAttributeMapping('', 'eu toys safety directive age warning');
		//eu-language & warnings...
		$this->addAttributeMapping('', 'fedas id');		

		/*Variations attributes
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

		$product->attributes['current_category'] = $this->current_category;
		
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
		if ( isset($product->attributes['weight']) ) {
			$product->attributes['shipping_weight'] = $product->attributes['weight'];
			$product->attributes['weight'] = $product->attributes['weight'] . $this->weight_unit;
		} 
		return parent::formatProduct($product);
	}

}

?>