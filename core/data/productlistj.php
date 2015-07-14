<?php

  /********************************************************************
  Version 2.0
    Joomla/Virtuemart Productlist
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

		$db = JFactory::getDBO();
		$lang = JComponentHelper::getParams('com_languages')->get('site');
		if (strlen($parent->lang) > 0)
			$lang = $parent->lang;
		$lang = strtolower(str_replace('-', '_', $lang));
    $sql = '
			SELECT a.virtuemart_product_id as product_id, a.product_parent_id as parent_id, details.product_name, 
				a.product_sku as sku, details.product_s_desc as excerpt, details.product_desc as description,
				prices.price_ids,
				a.product_in_stock as stock_quantity, a.product_weight as weight, a.product_weight_uom as weight_symbol, 
				a.product_url as url, manufacturer_details.mf_name as manufacturer, 
				cat.categories as category_ids, cat.category_names,
				attributes.attribute_names, attributes.attribute_values
			FROM #__virtuemart_products a
			LEFT JOIN #__virtuemart_products_' . $lang . ' details ON a.virtuemart_product_id = details.virtuemart_product_id
			#Link prices
			LEFT JOIN
				(
					SELECT pr.virtuemart_product_id, GROUP_CONCAT(pr.virtuemart_product_price_id) as price_ids
					FROM #__virtuemart_product_prices pr
					GROUP BY pr.virtuemart_product_id
				) prices on (a.virtuemart_product_id = prices.virtuemart_product_id)
			#Link manufacturers
			LEFT JOIN #__virtuemart_product_manufacturers manufacturer ON a.virtuemart_product_id = manufacturer.virtuemart_product_id
			LEFT JOIN #__virtuemart_manufacturers_' . $lang . ' manufacturer_details ON manufacturer.virtuemart_manufacturer_id = manufacturer_details.virtuemart_manufacturer_id
			#category
			LEFT JOIN 
				(
					SELECT categories.virtuemart_product_id, GROUP_CONCAT(categories.virtuemart_category_id) as categories, GROUP_CONCAT(category_details.category_name) as category_names
					FROM #__virtuemart_product_categories as categories
					LEFT JOIN #__virtuemart_categories_' . $lang . ' as category_details ON categories.virtuemart_category_id = category_details.virtuemart_category_id
					GROUP BY categories.virtuemart_product_id
				) cat on (cat.virtuemart_product_id = a.virtuemart_product_id)
			#attributes
			LEFT JOIN
				(
					SELECT custom_fields.virtuemart_customfield_id, custom_fields.virtuemart_product_id, GROUP_CONCAT(custom_fields.custom_value) as attribute_values, GROUP_CONCAT(field_details.custom_title) as attribute_names
					FROM #__virtuemart_product_customfields as custom_fields
					LEFT JOIN #__virtuemart_customs as field_details ON field_details.virtuemart_custom_id = custom_fields.virtuemart_custom_id
					GROUP BY custom_fields.virtuemart_product_id
				) as attributes ON attributes.virtuemart_product_id = a.virtuemart_product_id
			WHERE a.published = 1';

		$db->setQuery($sql);
		$this->products = $db->loadObjectList();
	}

	public function getProductList($parent, $remote_category) {
	
		global $pfcore;

		$parent->logActivity('Retrieving product list from database');
		if ($this->products == null)
			$this->loadProducts($parent);

		$db = JFactory::getDBO();

		//********************************************************************
		//Convert the WP_Product List into a Cart-Product Master List (ListItems)
		//********************************************************************

		foreach ($this->products as $index => $prod) {
		
			if ($index % 100 == 0)
				$parent->logActivity('Converting master product list...' . round($index / count($this->products) * 100) . '%' );

			if ($prod->parent_id > 0) {
				//Seek parent...
				$my_parent = null;
				foreach ($this->products as $potential_parent)
					if ($potential_parent->product_id == $prod->parent_id) {
						$my_parent = $potential_parent;
						//$prod->category_id = $potential_parent->category_id;
						break;
					}
			}

			//if skip product with non-matching category
			$category_ids = explode(',', $prod->category_ids);
			$skip = true;
			foreach($category_ids as $id)
				if ($parent->categories->containsCategory($id) ) {
					$skip = false;
					break;
				}
			if ($parent->force_all_categories)
				$skip = false;
			if ($skip)
				continue;
			$category_names = explode(',', $prod->category_names);

			$item = new PAProduct();
	  
			//Basics
			$item->id = $prod->product_id;
			$item->attributes['title'] = $prod->product_name;
			$item->taxonomy = '';
			$item->attributes['isVariable'] = false;
			$item->attributes['isVariation'] = false;
			$item->description_short = substr(strip_tags($prod->excerpt), 0, 1000); //!Need strip_shortcodes
			$item->description_long = substr(strip_tags($prod->description), 0, 1000); //!Need strip_shortcodes
			$item->attributes['valid'] = true;
			if ( isset($item->description_short) )
				$item->attributes['description_short'] = $item->description_short;
			$item->attributes['id'] = $prod->product_id;

			//Fetch any default attributes Stage 0 (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 0 && !$thisDefault->isRuled && !isset($item->attributes[$thisDefault->attributeName]))
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['product_type'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));

			if (count($category_names) ==0)
				$item->attributes['localCategory'] = '';
			else {
				$item->attributes['localCategory'] = $category_names[0];
				foreach($category_names as $catIndex => $category_name)
					if ($catIndex > 0)
						$item->attributes['localCategory_' . $catIndex] = $category_name;
			}
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $item->attributes['localCategory']));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);
			$item->attributes['link'] = $pfcore->siteHost . 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $prod->product_id;
			$item->attributes['condition'] = 'New';

			$item->attributes['sku'] = $prod->sku;
			$item->attributes['weight'] = $prod->weight;
			$item->attributes['brand'] = $prod->manufacturer;
			$item->attributes['manufacturer'] = $prod->manufacturer;

			//Prices
			$item->attributes['regular_price'] = 0;
			$item->attributes['has_sale_price'] = false;
			$db->setQuery('
				SELECT prices.product_price, prices.override as po, prices.product_override_price as sale_price, currencies.currency_code_3 as currency_name, currencies.currency_symbol
				FROM #__virtuemart_product_prices prices
				LEFT JOIN #__virtuemart_currencies currencies ON prices.product_currency = currencies.virtuemart_currency_id
				WHERE prices.virtuemart_product_price_id in (' . $prod->price_ids . ')'
			);
			$prices = $db->loadObjectList();
			foreach($prices as $priceIndex => $price) {
				if ($priceIndex == 0) {
					$item->attributes['regular_price'] = $price->product_price;
					$item->attributes['currency'] = $price->currency_symbol;
					$item->attributes['currency_name'] = $price->currency_name;
					$item->attributes['has_sale_price'] = false;
					if ($price->po == 1) {
						$item->attributes['has_sale_price'] = true;
						$item->attributes['sale_price'] = $price->sale_price;
					}
				} else {
					$item->attributes['regular_price_' . $priceIndex] = $price->product_price;
					$item->attributes['currency_' . $priceIndex] = $price->currency_symbol;
					$item->attributes['currency_name_' . $priceIndex] = $price->currency_name;
					$item->attributes['has_sale_price_' . $priceIndex] = false;
					if ($price->po == 1) {
						$item->attributes['has_sale_price_' . $priceIndex] = true;
						$item->attributes['sale_price_' . $priceIndex] = $price->sale_price;
					}
				}
			}

	  
			//If this has a parent, use the parent's attributes if necessary
			//one day we need to figure out how to know if attributes are blank
			//on purpose (eg user hit "override parent" then left it empty
			if ($prod->parent_id > 0)
				if ((strlen($prod->attribute_names) == 0) && ($my_parent)) {
					$prod->attribute_names = $my_parent->attribute_names;
					$prod->attribute_values = $my_parent->attribute_values;
				}

			//Images
			$db->setQuery('
				SELECT media_details.file_url
				FROM #__virtuemart_product_medias as media
				LEFT JOIN #__virtuemart_medias as media_details ON media.virtuemart_media_id = media_details.virtuemart_media_id
				WHERE media.virtuemart_product_id = ' . $prod->product_id
			);

			$media = $db->loadObjectList();
			$item->imgurls = array();
			if ($media) {
				$item->attributes['feature_imgurl'] =  $pfcore->siteHost . $media[0]->file_url;
				foreach ($media as $fileIndex => $file)
					if ($fileIndex > 0)
						$item->imgurls[] = $pfcore->siteHost . $file->file_url;
			}

			//Attributes
			$attribute_names = explode(',', $prod->attribute_names);
			$attribute_values = explode(',', $prod->attribute_values);
			foreach($attribute_names as $key => $value)
				if (strlen($value) > 0)
					$item->attributes[$value] = $attribute_values[$key];

			//Variations
			if ($prod->parent_id > 0) {
				$item->item_group_id = $prod->parent_id;
				$item->attributes['isVariation'] = true;

				$item->attributes['item_group_id'] = $prod->parent_id;
				$item->attributes['parent_title'] = $item->attributes['title']; //For eBay
			}

			//In-stock status
			$item->attributes['stock_status'] = 1;
			$item->attributes['stock_quantity'] = $prod->stock_quantity;
			if ($prod->stock_quantity == 0)
				$item->attributes['stock_status'] = 0;

			//Fetch any default attributes (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 1 && !$thisDefault->isRuled)
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			$parent->handleProduct($item);
		}

  }

}

?>