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
		$this->fieldDelimiter = "\t";
		$this->descriptionStrict = true;
		$this->stripHTML = true;	
		//Create some attributes (Mapping 3.0)
		$this->addAttributeMapping('id', 'MPID');
		$this->addAttributeMapping('title', 'Title');
		$this->addAttributeMapping('link', 'ProductURL');
		$this->addAttributeMapping('regular_price', 'Price');
		if ($this->bingForcePriceDiscount)
			$this->addAttributeMapping('sale_price', 'PriceWithDiscount');
		$this->addAttributeMapping('description', 'Description');
		$this->addAttributeMapping('feature_imgurl', 'ImageURL');
		//Note: Bing Specs require SKU != MPN. MPN currently not used
		$this->addAttributeMapping('sku', 'SKU');
		$this->addAttributeMapping('condition', 'Condition');
		$this->addAttributeMapping('availability', 'Availability');
		$this->addAttributeMapping('product_type', 'ProductType');
		$this->addAttributeMapping('current_category', 'B_Category');
		
	}

  function formatProduct($product) {
	
		//********************************************************************
		//Prepare the Product Attributes
		//********************************************************************

		//Cheat: These three fields aren't ready to be attributes yet, so adding manually:
		$product->attributes['description'] = $product->description;
		$product->attributes['current_category'] = $this->current_category;
		$product->attributes['feature_imgurl'] = $product->feature_imgurl;

		//if ($product->isVariable)
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