<?php

	/********************************************************************
	Version 2.0
		A Bing Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-04
		2014-08 Moved to Attribute Mapping v3
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PBingFeed extends PCSVFeedEx 
{

	public $bingForceGoogleCategory = false;
	public $bingForcePriceDiscount = false;

	function __construct () 
	{
		parent::__construct();
		$this->providerName = 'Bing';
		$this->providerNameL = 'bing';
		$this->fileformat = 'csv';
		$this->fields = array();
		$this->fieldDelimiter = ",";
		$this->stripHTML = true;	
		//Create some attributes (Mapping 3.0)

		//required
		$this->addAttributeMapping('id', 'MPID',true,true);
		$this->addAttributeMapping('title', 'Title', true, true);
		$this->addAttributeMapping('brand', 'Brand',true,true);
		$this->addAttributeMapping('link', 'Link',true,true);
		$this->addAttributeMapping('regular_price', 'Price'); //base price
		$this->addAttributeMapping('description', 'Description',true,true);
		$this->addAttributeMapping('feature_imgurl', 'Image_link',true,true);
		
		//optional - offer identification
		$this->addAttributeMapping('SellerName', 'SellerName');
		$this->addAttributeMapping('mpn', 'MPN');
		$this->addAttributeMapping('isbn', 'ISBN');
		$this->addAttributeMapping('sku', 'SKU');
		$this->addAttributeMapping('gtin', 'GTIN');
		//optional - item identification
		$this->addAttributeMapping('availability', 'Availability');
		$this->addAttributeMapping('current_category', 'B_Category',true); //desired bing category
		$this->addAttributeMapping('condition', 'Condition');
		$this->addAttributeMapping('multipack', 'Multippack');
		$this->addAttributeMapping('product_type', 'Product_type');
		//optional - apparel products
		$this->addAttributeMapping('gender', 'Gender');
		$this->addAttributeMapping('age_group', 'Age_group'); //valid values: Newborn, Infant, Toddler, Kid, Adult
		$this->addAttributeMapping('color', 'Color');
		$this->addAttributeMapping('size', 'Size');
		//optional - product variants
		$this->addAttributeMapping('item_group_id', 'Item_group_id');
		$this->addAttributeMapping('material', 'Material');
		$this->addAttributeMapping('pattern', 'Pattern');
		//optional - bing attributes
		$this->addAttributeMapping('', 'Bingads_grouping');
		$this->addAttributeMapping('', 'Bingads_label');
		$this->addAttributeMapping('', 'Bingads_redirect');
		//optional - sales and promotions
		//if ($this->bingForcePriceDiscount)
		$this->addAttributeMapping('sale_price', 'Sale_price');
		$this->addAttributeMapping('', 'Sale_price_effective_date');		
	}

  function formatProduct($product) {
	
		//********************************************************************
		//Prepare the Product Attributes
		//********************************************************************

		//if ($product->attributes['isVariation'])
		//'Item Group ID' => $product->item_group_id;

		if (strlen($product->attributes['regular_price']) == 0)
			$product->attributes['regular_price'] = '0.00';

		$product->attributes['regular_price'] = sprintf($this->currency_format, $product->attributes['regular_price']);
		if ($product->attributes['has_sale_price'])
			$product->attributes['sale_price'] = sprintf($this->currency_format, $product->attributes['sale_price']);
		

		//Note: Only In stock && New products will publish on Bing
		if ($product->attributes['stock_status'] == 1)
			$product->attributes['availability'] = 'In Stock';
		else
			$product->attributes['availability'] = 'Out of Stock';

		//if ($this->bingForceGoogleCategory) {
		//For this to work, we need to enable a Google taxonomy dialog box.
		//}
		//Need one day: Bingads_grouping, Bingads_label, Bingads_redirect

		//********************************************************************
		//Validation checks & Error messages
		//********************************************************************
		/* 
		title, brand, (MPN), Sku, b_category = 255
		URL, ImageURL = 2000, UPC12 ISBN13
		Description 5000
		if (strlen($product->attributes['title']) > 255) {
			$product->attributes['title'] = substr($product->attributes['title'], 0, 254);
			$this->addErrorMessage(000, 'Title truncated for ' . $product->attributes['title'], true);
		}*/

		return parent::formatProduct($product);
	}

}

?>