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

		//basic item info (includes required and optional fields)
		$this->addAttributeMapping('sku', 'Seller Part #',true,true);
		$this->addAttributeMapping('', 'Manufacturer',true,true);
		$this->addAttributeMapping('', 'Manufacturer Part # / ISBN',true,true);
		$this->addAttributeMapping('', 'UPC',true,true);
		$this->addAttributeMapping('', 'Manufacturer Item URL');
		$this->addAttributeMapping('', 'Related Seller Part#');
		$this->addAttributeMapping('title', 'Website Short Title', true,true);
		$this->addAttributeMapping('', 'Website Long Title', true);
		$this->addAttributeMapping('description', 'Product Description', true,true);
		$this->addAttributeMapping('length', 'Item Length', true,true); //inches
		$this->addAttributeMapping('width', 'Item Width', true,true); //inches
		$this->addAttributeMapping('height', 'Item Height', true,true); //inches
		$this->addAttributeMapping('weight', 'Item Weight', true,true); //lbs
		$this->addAttributeMapping('condition', 'Item Condition');
		$this->addAttributeMapping('', 'Shipping Restriction');
		$this->addAttributeMapping('currency', 'Currency');
		$this->addAttributeMapping('', 'MSRP'); //minimum advertised price
		$this->addAttributeMapping('', 'MAP');
		$this->addAttributeMapping('', 'CheckoutMAP'); //true or false
		$this->addAttributeMapping('sale_price', 'Selling Price');
		$this->addAttributeMapping('', 'Shipping');
		$this->addAttributeMapping('stock_quantity', 'Inventory');
		$this->addAttributeMapping('', 'Activation Mark');
		$this->addAttributeMapping('', 'Action'); //"Create Item", "Update Item","Update Item Price and Inventory", "Update/Append Image", and "Replace Image"
		$this->addAttributeMapping('feature_imgurl', 'Item Images'); //separate by commas
		$this->addAttributeMapping('', 'Prop 65'); //Yes or No
		$this->addAttributeMapping('', 'Prop 65 - Motherboard'); //Yes or No
		$this->addAttributeMapping('', 'Country Of Origin');
		$this->addAttributeMapping('', 'Age 18+ Verification');
		$this->addAttributeMapping('', 'Choking Hazard 1');
		$this->addAttributeMapping('', 'Choking Hazard 2');
		$this->addAttributeMapping('', 'Choking Hazard 3');
		$this->addAttributeMapping('', 'Choking Hazard 4');
		$this->addAttributeMapping('', 'GroupBundle');

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

	function getFeedFooter() {
		//Override parent and do nothing
	}

    function formatProduct($product) {

		//cheat: Remap these
  		$productDescription = str_replace('"','""',$product->attributes['description']);		
		$product->attributes['description'] = trim($productDescription);	
		
		//Max 6 Additional Images
		// $product->attributes['additional_images'] = '';
		// $image_count = 0;
		// foreach($product->imgurls as $imgurl) {
		// 	$product->attributes['additional_images'] .= $imgurl . ',';
		// 	$image_count++;
		// 	if ($image_count >= 9)
		// 		break;
		// }
		//if (strlen($product->attributes['additional_images']) > 0)
		//	$product->attributes['additional_images'] = substr($product->attributes['additional_images'], 0, -1);

		if ( $this->newegg_combo_title )
		{
			$title_dash = " - ";
			$title_combo = "";
			//Modify Website Short Title to include Brand - Title - Flavour (if exists) - Size (if exists)		
			$title_combo = $product->attributes['Manufacturer'].$title_dash.$product->attributes['title'];
			if ( !empty($product->attributes['flavour']) ) {
				$title_combo = $title_combo.$title_dash.$product->attributes['flavour'];
			}
			if ( !empty($product->attributes['size']) ) {
				$title_combo = $title_combo.$title_dash.$product->attributes['size'];
			}
			$product->attributes['title'] = $title_combo;
		}

		// if ($product->attributes['stock_status'] == 1) {
		// 	if ( isset($product->attributes['stock_quantity']) )
		// 		$product->attributes['Inventory'] = $product->attributes['stock_quantity'];
		// 	else
		// 		$product->attributes['Inventory'] = $product->attributes['Inventory'];
		// }
		// else {
		// 	$product->attributes['Inventory'] = '0';
		// }
	
		if (strlen($product->attributes['Selling Price']) == 0)
			$product->attributes['Selling Price'] = '0.00';

		$product->attributes['Selling Price'] = $product->attributes['regular_price'];
		if ($product->attributes['has_sale_price'])
			$product->attributes['Selling Price'] = $product->attributes['sale_price'];

		return parent::formatProduct($product);

  }

  function truncate_string($string,$length,$append="&hellip;")
  {
	$string = trim($string);

	if(strlen($string) > $length) {
	$string = wordwrap($string, $length);
	$string = explode("\n", $string, 2);
	$string = $string[0] . $append;
	}
	return $string;
  }

}

?>