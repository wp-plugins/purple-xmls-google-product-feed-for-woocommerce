<?php

	/********************************************************************
	Version 3.0
	A Pronto Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
		2014-09 Moved to Attribute mapping v3
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PProntoFeed extends PCSVFeedEx{

	function __construct () {

		parent::__construct();
		$this->providerName = 'Pronto';
		$this->providerNameL = 'pronto';
		$this->fileformat = 'txt';
		$this->fieldDelimiter = "\t";
		$this->fields = array();
		
//Required fields (6)
		$this->addAttributeMapping('title', 'Title',true,true); //384 char limit
		$this->addAttributeMapping('description', 'Description', true,true); //2048 char limit
		$this->addAttributeMapping('price', 'SalesPrice',true,true);		
		$this->addAttributeMapping('local_category', 'Category', true,true);
		$this->addAttributeMapping('condition', 'Condition',true,true); //new|used|refurb
		$this->addAttributeMapping('link', 'URL',true,true);
//Optional
		$this->addAttributeMapping('', 'ShortTitle',true); //user-friendly short title 128 chars
		$this->addAttributeMapping('color', 'Color');
		$this->addAttributeMapping('size', 'Size');
		$this->addAttributeMapping('', 'Attributes',true); //material=leather;length=long //2048 chars
		$this->addAttributeMapping('', 'Keywords',true);
		//$this->addAttributeMapping('', 'Image URL');
		$this->addAttributeMapping('brand', 'Brand');

		$this->addAttributeMapping('', 'Manufacturer');
		$this->addAttributeMapping('', 'ArtistAuthor',true);
		$this->addAttributeMapping('', 'RetailPrice');
		$this->addAttributeMapping('', 'SpecialOffer',true);
		$this->addAttributeMapping('', 'CouponText',true);
		$this->addAttributeMapping('', 'CouponCode',true);

		$this->addAttributeMapping('stock_status', 'InStock',true);
		$this->addAttributeMapping('quantity', 'InventoryCount',true);
		$this->addAttributeMapping('', 'Bundle'); //yes|no
		$this->addAttributeMapping('', 'ReleaseDate');
		$this->addAttributeMapping('pronto_category_id', 'ProntoCategoryID'); 
		$this->addAttributeMapping('', 'MobileURL');

		$this->addAttributeMapping('feature_imgurl', 'ImageURL',true);
		$this->addAttributeMapping('', 'ShippingCost');
		$this->addAttributeMapping('weight', 'ShippingWeight');
		$this->addAttributeMapping('', 'ZipCode');
		$this->addAttributeMapping('', 'EstimatedShipDate');
		$this->addAttributeMapping('', 'ProductBid');

		$this->addAttributeMapping('sku', 'ProductSKU',true);
		$this->addAttributeMapping('', 'ISBN');
		$this->addAttributeMapping('', 'UPC');
		$this->addAttributeMapping('', 'EAN');
		$this->addAttributeMapping('', 'MPN');
		$this->addAttributeMapping('', 'SaleRank');

		$this->addAttributeMapping('', 'ProductHighlights',true);
		$this->addAttributeMapping('AltImage0', 'AltImage0');
		$this->addAttributeMapping('AltImage1', 'AltImage1');
		$this->addAttributeMapping('AltImage2', 'AltImage2');
		$this->addAttributeMapping('AltImage3', 'AltImage3');
		$this->addAttributeMapping('AltImage4', 'AltImage4');
					
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule( 'description', 'description',array('max_length=2048','strict') );
		//$this->addRule( 'csv_standard', 'CSVStandard',array('title','384') ); 
		$this->addRule( 'substr','substr', array('title','0','385',true) ); //384 length
		//$this->addRule('csv_standard', 'CSVStandard',array('description')); 

	}

	function formatProduct($product) {

		//Prepare input:
		$category = explode(";", $this->current_category);
		if (isset($category[1]))
			$product->attributes['pronto_category_id'] = trim($category[1]);		

		$product->attributes['feature_imgurl'] = str_replace('https://','http://',$product->attributes['feature_imgurl']);

		if ( isset($product->attributes['quantity']) )
			if ( $product->attributes['quantity'] < 1 )
				$product->attributes['quantity'] = '';

		if ($product->attributes['stock_status'] == 1)
			$product->attributes['stock_status'] = 'In Stock';
		else
			$product->attributes['stock_status'] = 'Limited Quantities';

		if ( $this->allow_additional_images && (count($product->imgurls) > 0) ) {
				$image_count = 0;
				foreach($product->imgurls as $imgurl) {
					$product->attributes['AltImage'.$image_count] = $imgurl;
					$image_count++;
					if ($image_count >= 4)
						break;
				}
		}
		return parent::formatProduct($product);
	}

}

?>