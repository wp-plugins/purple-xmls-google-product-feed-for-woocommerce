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
		$lang = strtolower(str_replace('-', '_', $lang));
    $sql = '
			SELECT a.virtuemart_product_id as product_id, a.product_parent_id as parent_id, details.product_name, 
				a.product_sku as sku, details.product_s_desc as excerpt, details.product_desc as description,
				prices.product_price, prices.override as po, prices.product_override_price as sale_price, a.product_in_stock as stock_quantity, a.product_weight as weight, a.product_weight_uom as weight_symbol, 
				a.product_url as url, manufacturer_details.mf_name as manufacturer,
				media_details.file_url, categories.virtuemart_category_id as category_id, category_details.category_name,
				attributes.attribute_names, attributes.attribute_values
			FROM #__virtuemart_products a
			LEFT JOIN #__virtuemart_products_' . $lang . ' details ON a.virtuemart_product_id = details.virtuemart_product_id
			#Link prices
			LEFT JOIN #__virtuemart_product_prices prices ON a.virtuemart_product_id = prices.virtuemart_product_id
			LEFT JOIN #__virtuemart_currencies currencies ON prices.product_currency = currencies.virtuemart_currency_id
			#Link manufacturers
			LEFT JOIN #__virtuemart_product_manufacturers manufacturer ON a.virtuemart_product_id = manufacturer.virtuemart_product_id
			LEFT JOIN #__virtuemart_manufacturers_' . $lang . ' manufacturer_details ON manufacturer.virtuemart_manufacturer_id = manufacturer_details.virtuemart_manufacturer_id
			#Link media for images
			LEFT JOIN #__virtuemart_product_medias as media ON a.virtuemart_product_id = media.virtuemart_product_id
			LEFT JOIN #__virtuemart_medias as media_details ON media.virtuemart_media_id = media_details.virtuemart_media_id
			#category
			LEFT JOIN #__virtuemart_product_categories as categories ON a.virtuemart_product_id = categories.virtuemart_product_id
			LEFT JOIN #__virtuemart_categories_' . $lang . ' as category_details ON categories.virtuemart_category_id = category_details.virtuemart_category_id
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
		$db->query();
		$this->products = $db->loadObjectList();
	}

	public function getProductList($parent, $remote_category) {
	
		global $pfcore;

		$parent->logActivity('Retrieving product list from database');
		if ($this->products == null)
			$this->loadProducts($parent);

		$master_product_list = array();

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
						$prod->category_id = $potential_parent->category_id;
						break;
					}
			}

			if (!$parent->categories->containsCategory($prod->category_id))
				continue;

			$item = new PAProduct();
	  
			//Basics
			$item->id = $prod->product_id;
			$item->attributes['title'] = $prod->product_name;
			$item->taxonomy = '';
			$item->isVariable = false;
			$item->description_short = substr(strip_tags($prod->excerpt), 0, 1000); //!Need strip_shortcodes
			$item->description_long = substr(strip_tags($prod->description), 0, 1000); //!Need strip_shortcodes
			$item->attributes['valid'] = true;

			//Cheat! (temp)
			$item->attributes['id'] = $prod->product_id;

			//Fetch any default attributes (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				$item->attributes[$thisDefault->attributeName] = $thisDefault->value;

			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['product_type'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $prod->category_name));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);
			$item->attributes['link'] = $pfcore->siteHost . '/index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $prod->product_id;
			$item->feature_imgurl = $prod->file_url;
			$item->attributes['condition'] = 'New';
			$item->attributes['regular_price'] = $prod->product_price;
			$item->attributes['has_sale_price'] = false;
			if ($prod->po == 1) {
				$item->attributes['has_sale_price'] = true;
				$item->attributes['sale_price'] = $prod->sale_price;
			}
			$item->attributes['sku'] = $prod->sku;
			$item->attributes['weight'] = $prod->weight;
	  
			//If this has a parent, use the parent's attributes if necessary
			//one day we need to figure out how to know if attributes are blank
			//on purpose (eg user hit "override parent" then left it empty
			if ($prod->parent_id > 0)
				if ((strlen($prod->attribute_names) == 0) && ($my_parent)) {
					$prod->attribute_names = $my_parent->attribute_names;
					$prod->attribute_values = $my_parent->attribute_values;
				}

			//Attributes
			$attribute_names = explode(',', $prod->attribute_names);
			$attribute_values = explode(',', $prod->attribute_values);
			foreach($attribute_names as $key => $value)
				$item->attributes[$value] = $attribute_values[$key];

			//Variations
			if ($prod->parent_id > 0) {
				$item->item_group_id = $prod->parent_id;
				$item->parent_title = $item->attributes['title']; //This is for eBay feed only, and could otherwise be deleted
				$item->isVariable = true;
				//Cheat!
				$item->attributes['item_group_id'] = $prod->parent_id;
				$item->attributes['parent_title'] = $item->attributes['title'];
			}

			//In-stock status
			$item->attributes['stock_status'] = 1;
			$item->attributes['stock_quantity'] = $prod->stock_quantity;
			if ($prod->stock_quantity == 0)
				$item->attributes['stock_status'] = 0;
	  
			$master_product_list[] = $item;
		}

		return $master_product_list;
  }

}

?>