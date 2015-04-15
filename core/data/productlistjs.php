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
	
	public $productStart = -1;

	//********************************************************************
	//Load the products from the DB
	//********************************************************************

	public function loadProducts($parent) {

		global $pfcore;

		$shopID = $pfcore->shopID;

		$db = JFactory::getDBO();

		$parent->logActivity('Reading products...');

		if ($parent->has_product_range)
			$limit = 'LIMIT ' . $parent->product_limit_low . ', ' . ($parent->product_limit_high - $parent->product_limit_low);
		else
			$limit = '';

		if ($this->productStart > -1)
			$limit = 'LIMIT ' . $this->productStart . ', 50000';

		$sql = '
			SELECT id, shopify_id, shopify_handle, title, description, vendor, price, stock_quantity, attributes, attribute_overrides, parent_id, parent_shopify_id, categories
			FROM #__rapidcart_products
			WHERE (shop_id = ' . $shopID . ') AND (state = 1)
		' . $limit;

		$db->setQuery($sql);
		$this->products = $db->loadObjectList();


	}

	//********************************************************************
	//Convert the ProductList
	//********************************************************************

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
			SELECT id, specifier, shop_type, name
			FROM #__rapidcart_shops
			WHERE id = ' . $pfcore->shopID);
		$shop = $db->loadObject();

		//********************************************************************
		//Convert the RapidCart_Product List into a Cart-Product Product List (ListItems)
		//********************************************************************

		foreach ($this->products as $index => $prod) {

			if ($index % 100 == 0)
				$parent->logActivity('Converting master product list...' . round($index / count($this->products) * 100) . '%' );

			$skip = true;
			$cats = explode(',', $prod->categories);
			if (count($cats) == 0)
				$cats[] = $prod->category;
			foreach($cats as $cat)
				if ($parent->categories->containsCategory($cat)) {
					$skip = false;
					break;
				}
			if ($skip)
				continue;

			$item = new PAProduct();

			//Basics
			$item->id = $prod->id;

			//load attributes from db
			if (strlen($prod->attributes) > 0) {
				$attributeObj = json_decode($prod->attributes);
				$item->attributes = get_object_vars($attributeObj);
			} else
				$item->attributes = array();

			//Pre-process the attributes
			foreach($item->attributes as $attributeIndex => $attribute) {

				//For WordPress, _product_attributes is a special sub-attribute list
				//This code should be moved into jobs one day so that it doesn't slow down feed loading
				if ($attributeIndex == '_product_attributes') {
					if ($attribute[0] != 's') $attribute = ''; //Minor anti-hack protection
					$attribute = unserialize($attribute);
					if ($attribute[0] != 'a') $attribute = ''; //Minor anti-hack protection
					if (strlen($attribute) == 0)
						$list = array();
					else
						$list = unserialize($attribute);
					foreach($list as $listIndex => $listItem) {
						if (isset($item->attributes[$listIndex]) && strlen($item->attributes[$listIndex]) > 0)
							$listIndex = '@' . $listIndex; //Displaced attribute!
						$item->attributes[$listIndex] = $listItem['value'];
					}
				}

			}

			//Carry on defining product
			if (!isset($item->attributes['id']))
				$item->attributes['id'] = $prod->id;
			$item->attributes['source_id'] = $prod->shopify_id;
			$item->attributes['shopify_id'] = $item->attributes['id'];
			$item->attributes['title'] = $prod->title;
			$item->taxonomy = '';
			$item->attributes['isVariable'] = false;
			$item->attributes['isVariation'] = false;
			$item->description_short = substr(strip_tags($prod->description), 0, 1000);
			$item->description_long = substr(strip_tags($prod->description), 0, 1000);
			if (isset($item->attributes['valid'])) {
				$valid = strtolower($item->attributes['valid']);
				$item->attributes['valid'] = true;
				if ($valid == 'false') $item->attributes['valid'] = false;
				if ($valid == 'true') $item->attributes['valid'] = true;
			} else
				$item->attributes['valid'] = true;
			if ($prod->parent_id > 0)
			$item->attributes['item_group_id'] = $prod->parent_id;

			//Fetch any default attributes Stage 0 (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 0 && !$thisDefault->isRuled && !isset($item->attributes[$thisDefault->attributeName]))
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			//Deal with Product type... ensure it exists
			if (!isset($item->attributes['product_type']) || strlen($item->attributes['product_type']) == 0) {
				$item->attributes['product_type'] = '';
				$pcat = $parent->categories->idToCategory($cats[0]);
				if ($pcat != null)
					$item->attributes['product_type'] = $pcat->title;
			}

			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $item->attributes['product_type']));
			//$item->attributes['product_type'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $item->attributes['product_type']));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);
			//$item->attributes['link'] = 'https://' . $pfcore->shopName . '/collections/all/products/' . $prod->shopify_handle;

			//Shop name... or specifier override
			$shopName = $shop->name;
			if (strlen($shop->specifier) > 0)
				$shopName = $shop->specifier;

			//Settings that depend on the shop type
			switch ($shop->shop_type) {
				case 0:
					//Shoopify
					$item->attributes['link'] = 'http://' . $shop->specifier . '/collections/all/products/' . $prod->shopify_handle;
					break;
				case 1:
					//WordPress
					if (isset($item->attributes['url']) && strlen($item->attributes['url']) > 0)
						$item->attributes['link'] = $item->attributes['url'];
					elseif (isset($item->attributes['_product_url']) && strlen($item->attributes['_product_url']) > 0)
						$item->attributes['link'] = $item->attributes['_product_url'];
					//elseif (isset($item->attributes['CLEAN_URL']) && strlen($item->attributes['CLEAN_URL']) > 0)
						//$item->attributes['link'] = $shopName . '/?product=' . $item->attributes['CLEAN_URL'];
					elseif (isset($item->attributes['_shorten_url_bitly']) && strlen($item->attributes['_shorten_url_bitly']) > 0)
						$item->attributes['link'] = $item->attributes['_shorten_url_bitly'];
					//_stock should go to jobs queue
					if (isset($item->attributes['_stock']) && $item->attributes['_stock'] > 0)
						$item->attributes['stock_quantity'] = $item->attributes['_stock'];
					break;
				case 2:
					//$item->attributes['link'] = $shopName . $item->attributes['link'];
					//$item->attributes['link'] = $item->attributes['link'];
					//temp
					/*if ($shop->id == 27) {
						$a = $shopName . '/image/cache/' . $item->attributes['image_url'];
						$ext = substr($a, -3);
						$a = substr($a, 0, strlen($a) - 4) . '-600x600.' . $ext;
						$item->attributes['image_url'] = $a;
					}
					else*/
					$item->attributes['image_url'] = $shopName . '/image/' . $item->attributes['image_url'];
					break;
			}

			//Product overrides look like WordPress custom fields
			if (strlen($prod->attribute_overrides) > 0) {
				$overrides = json_decode($prod->attribute_overrides);
				$overrides = get_object_vars($overrides);
				foreach($overrides as $key => $value)
					$item->attributes[$key] = $value;
			}
			
			if (isset($item->attributes['image_url']))
				$item->attributes['feature_imgurl'] =  $item->attributes['image_url'];
			elseif (!isset($item->attributes['feature_imgurl']))
				$item->attributes['feature_imgurl'] = '';
			$item->attributes['condition'] = 'New';
			if (isset($item->attributes['price']))
				$item->attributes['regular_price'] = $item->attributes['price'];
			if (!isset($item->attributes['regular_price']) || (strlen($item->attributes['regular_price']) == 0))
				$item->attributes['regular_price'] = $prod->price;
			$item->attributes['has_sale_price'] = false;
			//if ($prod->po == 1) {
				//$item->attributes['has_sale_price'] = true;
				//$item->attributes['sale_price'] = $prod->sale_price;
			//}
			if (isset($item->attributes['grams']))
				$item->attributes['weight'] = $item->attributes['grams'] / 1000;

			//In-stock status
			$item->attributes['stock_status'] = 1;
			if (isset($item->attributes['inventory_quantity']))
			$item->attributes['stock_quantity'] = $item->attributes['inventory_quantity'];
			if ($prod->stock_quantity == 0)
				$item->attributes['stock_status'] = 0;
			//Hide out of stock
			if (($pfcore->hide_outofstock) && ($item->attributes['stock_status'] == 0))
				$item->attributes['valid'] = false;

			unset($item->attributes['price']);
			unset($item->attributes['grams']);
			unset($item->attributes['inventory_quantity']);

			if (isset($item->attributes['vendor']) && !isset($item->attributes['brand']))
				$item->attributes['brand'] = $item->attributes['vendor'];

			//Fetch any default attributes (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 1 && !$thisDefault->isRuled)
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);
	  
			$parent->handleProduct($item);
			unset($item);
		}

  }

}