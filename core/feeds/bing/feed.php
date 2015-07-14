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
		$this->fileformat = 'txt';
		$this->fields = array();
		$this->fieldDelimiter = "\t";
		$this->stripHTML = true;
		//Create some attributes (Mapping 3.0)

		//required
		$this->addAttributeMapping('id', 'MPID',false,true);
		$this->addAttributeMapping('title', 'Title', true,true);
		$this->addAttributeMapping('brand', 'Brand',false,true);
		$this->addAttributeMapping('link', 'Link',false,true);
		$this->addAttributeMapping('regular_price', 'Price',false,true); //base price
		$this->addAttributeMapping('description', 'Description',true,true);
		$this->addAttributeMapping('feature_imgurl', 'Image_link',false,true);
		$this->addAttributeMapping('', 'SellerName',false,false); //Only required for aggregators - not accepted from direct merchants
		//optional - offer identification		
		$this->addAttributeMapping('', 'MPN');
		$this->addAttributeMapping('', 'UPC');
		$this->addAttributeMapping('', 'ISBN');
		$this->addAttributeMapping('sku', 'SKU');
		$this->addAttributeMapping('', 'GTIN');
		//optional - item identification
		$this->addAttributeMapping('availability', 'Availability');
		$this->addAttributeMapping('current_category', 'B_Category'); //desired bing category
		$this->addAttributeMapping('condition', 'Condition');
		$this->addAttributeMapping('', 'Expiration_date');
		$this->addAttributeMapping('', 'Multipack');
		$this->addAttributeMapping('', 'Product_type');
		//optional - apparel products
		$this->addAttributeMapping('', 'Gender');
		$this->addAttributeMapping('', 'Age_group'); //valid values: Newborn, Infant, Toddler, Kid, Adult
		$this->addAttributeMapping('color', 'Color');
		$this->addAttributeMapping('size', 'Size');
		//optional - product variants
		$this->addAttributeMapping('item_group_id', 'Item_group_id');
		$this->addAttributeMapping('', 'Material');
		$this->addAttributeMapping('', 'Pattern');
		//optional - bing attributes
		$this->addAttributeMapping('', 'Bingads_grouping');
		$this->addAttributeMapping('', 'Bingads_label');
		$this->addAttributeMapping('', 'Bingads_redirect');
		//optional - sales and promotions
		//if ($this->bingForcePriceDiscount)
		$this->addAttributeMapping('sale_price', 'Sale_price');
		$this->addAttributeMapping('', 'Sale_price_effective_date');
		
		$this->addRule( 'description', 'description', array('max_length=5000','strict') );
		// $this->addRule( 'csv_standard', 'CSVStandard',array('title','150') );
		// $this->addRule( 'csv_standard', 'CSVStandard',array('description') ); 
		$this->addRule( 'substr','substr', array('title','0','150',true) ); //150 length		
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