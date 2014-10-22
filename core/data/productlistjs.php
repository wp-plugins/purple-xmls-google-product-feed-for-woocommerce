<?php

  /********************************************************************
  Version 2.0
    Joomla/RapidCart Productlist
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-16
  ********************************************************************/

class PProductList {

	public $products = null;

	public function loadProducts($parent) {

		//********************************************************************
		//Load the products
		//********************************************************************
		global $pfcore;

		$shopID = $pfcore->shopID;

		$db = JFactory::getDBO();

		$sql = '
			SELECT *
			FROM #__rapidcart_products
			WHERE (shop_id = ' . $shopID . ')
		';

		$db->setQuery($sql);
		$this->products = $db->loadObjectList();

	}

	public function getProductList($parent, $remote_category) {

		global $pfcore;

		//********************************************************************
		//Initialize
		//********************************************************************
		$parent->logActivity('Retrieving product list from database');
		if ($this->products == null)
			$this->loadProducts($parent);

		$db = JFactory::getDBO();
		$db->setQuery('
			SELECT id, specifier
			FROM #__rapidcart_shops
			WHERE id = ' . $pfcore->shopID);
		$shop = $db->loadObject();

		//********************************************************************
		//Convert the RapidCart_Product List into a Cart-Product Product List (ListItems)
		//********************************************************************

		foreach ($this->products as $index => $prod) {

			if ($index % 100 == 0)
				$parent->logActivity('Converting master product list...' . round($index / count($this->products) * 100) . '%' );

			//if (!$parent->categories->containsCategory($prod->category_id))
				//continue;

			$item = new PAProduct();
	  
			//Basics
			$item->id = $prod->id;

			//load attributes from db
			$attributeObj = json_decode($prod->attributes);
			//$attributes = get_object_vars($thisVariant);
			$item->attributes = get_object_vars($attributeObj);

			//Carry on defining product
			$item->attributes['shopify_id'] = $item->attributes['id'];
			$item->attributes['title'] = $prod->title;
			$item->taxonomy = '';
			$item->isVariable = false;
			$item->description_short = substr(strip_tags($prod->description), 0, 1000);
			$item->description_long = substr(strip_tags($prod->description), 0, 1000);
			$item->attributes['valid'] = true;

			//Cheat! (temp)
			$item->attributes['id'] = $prod->id; //replace one of the id's

			//Fetch any default attributes (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				$item->attributes[$thisDefault->attributeName] = $thisDefault->value;

			if (!isset($item->attributes['product_type']))
				$item->attributes['product_type'] = '';
			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $item->attributes['product_type']));
			//$item->attributes['product_type'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $item->attributes['product_type']));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);
			//$item->attributes['link'] = 'https://' . $pfcore->shopName . '/collections/all/products/' . $prod->shopify_handle;
			$item->attributes['link'] = 'http://' . $shop->specifier . '/collections/all/products/' . $prod->shopify_handle;
			
			if (isset($item->attributes['image_url']))
				$item->feature_imgurl =  $item->attributes['image_url'];
			else
				$item->feature_imgurl = '';
			$item->attributes['condition'] = 'New';
			$item->attributes['regular_price'] = $item->attributes['price'];
			$item->attributes['has_sale_price'] = false;
			//if ($prod->po == 1) {
				//$item->attributes['has_sale_price'] = true;
				//$item->attributes['sale_price'] = $prod->sale_price;
			//}
			$item->attributes['weight'] = $item->attributes['grams'] / 1000;

			//In-stock status
			$item->attributes['stock_status'] = 1;
			$item->attributes['stock_quantity'] = $item->attributes['inventory_quantity'];
			if ($prod->stock_quantity == 0)
				$item->attributes['stock_status'] = 0;

			unset($item->attributes['price']);
			unset($item->attributes['grams']);
			unset($item->attributes['inventory_quantity']);
	  
			$parent->handleProduct($item);
			unset($item);
		}

  }

}