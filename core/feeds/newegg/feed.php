<?php

	/********************************************************************
	Version 3.0
	A Newegg Feed
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-08
		2014-09 Moved to Mapping v3
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PNeweggFeed extends PCSVFeedEx {

	function __construct () {
		parent::__construct();
		$this->providerName = 'Newegg';
		$this->providerNameL = 'Newegg';
		$this->fileformat = 'csv';
		$this->fieldDelimiter = ",";
		$this->fields = array();

		//item identification fields
		$this->addAttributeMapping('sku', 'Seller Part #',true,true);
		$this->addAttributeMapping('brand', 'Manufacturer',true,true);
		$this->addAttributeMapping('', 'Manufacturer Part #/ISBN',true,true);
		$this->addAttributeMapping('', 'UPC',true,true);
		//required fields
		$this->addAttributeMapping('Action', 'Action'); //"Create Item", "Update Item","Update Item Price and Inventory", "Update/Append Image", and "Replace Image"
		$this->addAttributeMapping('stock_quantity', 'Inventory',true,true);
		$this->addAttributeMapping('item_images', 'Item Images',true); //separate by commas
		$this->addAttributeMapping('length', 'Item Length', true,true); //inches
		$this->addAttributeMapping('width', 'Item Width', true,true); //inches
		$this->addAttributeMapping('height', 'Item Height', true,true); //inches
		$this->addAttributeMapping('weight', 'Item Weight', true,true); //lbs
		$this->addAttributeMapping('description', 'Product Description', true,true);
		$this->addAttributeMapping('price', 'Selling Price',true,true);
		$this->addAttributeMapping('shipping', 'Shipping',true,true);
		$this->addAttributeMapping('title', 'Website Short Title', true,true);
		//optional
		$this->addAttributeMapping('', 'Activation Mark'); //true / false
		$this->addAttributeMapping('', 'Age 18+ Verification');
		$this->addAttributeMapping('', 'CheckoutMAP'); //true or false
		$this->addAttributeMapping('', 'Choking Hazard 1');
		$this->addAttributeMapping('', 'Choking Hazard 2');
		$this->addAttributeMapping('currency', 'Currency'); //default USD
		$this->addAttributeMapping('condition', 'Item Condition');
		$this->addAttributeMapping('', 'Item Package'); //retail (default) or OEM
		$this->addAttributeMapping('', 'Manufacturer Item URL');
		$this->addAttributeMapping('', 'MAP'); //minimum advertised price
		$this->addAttributeMapping('', 'MSRP'); 
		$this->addAttributeMapping('', 'Prop 65'); //Yes or No
		$this->addAttributeMapping('item_group_id', 'Related Seller Part #');
		$this->addAttributeMapping('', 'Shipping Restriction');
		$this->addAttributeMapping('', 'Website Long Title', true);		
			
		//$this->addAttributeMapping('', 'Prop 65 - Motherboard'); //Yes or No
		//$this->addAttributeMapping('', 'Country Of Origin');	
		//$this->addAttributeMapping('', 'GroupBundle');

		//MW fields specific to unlocked cell phones category
		/*
		$this->addAttributeMapping('MW3G', 'MW3G');
		$this->addAttributeMapping('MW4G', 'MW4G');
		$this->addAttributeMapping('MWAdditionalFunction', 'MWAdditionalFunction');
		$this->addAttributeMapping('MWAudio', 'MWAudio');
		$this->addAttributeMapping('MWAutoFocus', 'MWAutoFocus');
		$this->addAttributeMapping('MWBatteryCapacity', 'MWBatteryCapacity');
		$this->addAttributeMapping('MWBatteryType', 'MWBatteryType');
		$this->addAttributeMapping('MWBluetoothSupport', 'MWBluetoothSupport');
		$this->addAttributeMapping('MWBrand', 'MWBrand');
		$this->addAttributeMapping('MWCamera', 'MWCamera');
		$this->addAttributeMapping('MWCameraZoom', 'MWCameraZoom');
		$this->addAttributeMapping('MWCardCapacitySupport', 'MWCardCapacitySupport');
		$this->addAttributeMapping('MWCardSlot', 'MWCardSlot');
		$this->addAttributeMapping('MWColor', 'MWColor');
		$this->addAttributeMapping('MWCompatibleCarrierService', 'MWCompatibleCarrierService');
		$this->addAttributeMapping('MWCPU', 'MWCPU');
		$this->addAttributeMapping('MWCPUCore', 'MWCPUCore');
		$this->addAttributeMapping('MWCPUSpeed', 'MWCPUSpeed');
		$this->addAttributeMapping('MWDataTransfer', 'MWDataTransfer');
		$this->addAttributeMapping('MWDimensions', 'MWDimensions');
		$this->addAttributeMapping('MWDisclaimer', 'MWDisclaimer');
		$this->addAttributeMapping('MWDualSIM', 'MWDualSIM');
		$this->addAttributeMapping('MWEmail', 'MWEmail');
		$this->addAttributeMapping('MWExpansion', 'MWExpansion');
		$this->addAttributeMapping('MWExternalDisplayColor', 'MWExternalDisplayColor');
		$this->addAttributeMapping('MWExternalDisplayFormat', 'MWExternalDisplayFormat');
		$this->addAttributeMapping('MWExternalDisplayResolution', 'MWExternalDisplayResolution');
		$this->addAttributeMapping('MWExternalDisplaySize', 'MWExternalDisplaySize');
		$this->addAttributeMapping('MWFeatures', 'MWFeatures');
		$this->addAttributeMapping('MWFlash', 'MWFlash');
		$this->addAttributeMapping('MWGPSIntegrated', 'MWGPSIntegrated');
		$this->addAttributeMapping('MWHDVideoCapture', 'MWHDVideoCapture');
		$this->addAttributeMapping('MWHDMI', 'MWHDMI');
		$this->addAttributeMapping('MWInternalMemory', 'MWInternalMemory');		
		$this->addAttributeMapping('MWFormFactor', 'MWFormFactor');
		$this->addAttributeMapping('MWModel', 'MWModel');
		$this->addAttributeMapping('MWOperatingSystem', 'MWOperatingSystem');
		*/		

		$this->newegg_combo_title = false;		
		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule( 'description', 'description',array('max_length=500','strict') );

	}

	function getFeedHeader($file_name, $file_path) 
	{
		//Newegg header line 1
		$category = explode(";", $this->current_category);
		$output = implode(
			$this->fieldDelimiter, 
			array('Version=1.01', 'SubCategoryID='.trim($category[1]),'Overwrite=No','TemplateDate=2014-11-12','* Changing the wording to "Overwrite=Yes" will deactivate all of your items from this subcategory except for those listed on this datafeed. If this is not intended, keep "Overwrite=No". DO NOT make any changes to the version number or Subcategory ID.changes to the version number or SubcategoryID.')
		) .  "\r\n";

		//Newegg header line 2
		foreach($this->attributeMappings as $thisMapping)
		{
			if ($thisMapping->enabled && !$thisMapping->deleted)
			{
				$output .= $thisMapping->mapTo . $this->fieldDelimiter;
			}
		}
	    return substr($output, 0, -1) .  "\r\n";
	}

	function getFeedFooter($file_name, $file_path) {
		//Override parent and do nothing
	}

    function formatProduct($product) {

		//cheat: Remap these		
		//Max 6 Additional Images
		$product->attributes['item_images'] = $product->attributes['feature_imgurl'];
			if ( $this->allow_additional_images && (count($product->imgurls) > 0) ) {
				$image_count = 1;
				foreach($product->imgurls as $imgurl) {
					$product->attributes['additional_images'] .= ',' . $imgurl;
					$image_count++;
					if ($image_count >= 7)
						break;
				}
				$product->attributes['item_images'] .= $product->attributes['additional_images'];
			}
	
		//if (strlen($product->attributes['additional_images']) > 0)
		//	$product->attributes['additional_images'] = substr($product->attributes['additional_images'], 0, -1);

		$product->attributes['currency'] = $this->currency;
		if ( $this->newegg_combo_title )
		{
			$title_dash = " - ";
			$title_combo = "";
			//Modify Website Short Title to include Brand - Title - Flavour (if exists) - Size (if exists)		
			$title_combo = $product->attributes['brand'].$title_dash.$product->attributes['title'];
			if ( !empty($product->attributes['flavour']) ) {
				$title_combo = $title_combo.$title_dash.$product->attributes['flavour'];
			}
			if ( !empty($product->attributes['size']) ) {
				$title_combo = $title_combo.$title_dash.$product->attributes['size'];
			}
			$product->attributes['title'] = $title_combo;
		}

		$product->attributes['shipping'] = $this->newegg_shipping;

		if ( !isset($this->newegg_shipping) || strlen((string)$product->attributes['shipping']) == 0 ) {
			$this->addErrorMessage(17000, 'Shipping not configured. Need advanced command: $newegg_shipping = ....', true);
			//$this->addErrorMessage(9001, 'You can find your Merchant ID in the top left corner of the ShareASale web interface for advertisers/merchants (login required)', true);
			$this->productCount--;
		}

		return parent::formatProduct($product);
  }
 
}

?>