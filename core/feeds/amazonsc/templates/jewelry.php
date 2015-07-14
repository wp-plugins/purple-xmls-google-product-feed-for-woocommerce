<?php
	//********************************************************************
	//Amazon Seller Template
	//2015-01
	//********************************************************************

	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	
	//$this->addAttributeMapping('title', 'item_name', true)->localized_name = 'Title';
	//Unique to UK template:
	// $this->addAttributeMapping('', 'part_number',true,false)->localized_name = 'Manufacturer Part Number'; //This is the manufacturers part number of the product
	// $this->addAttributeMapping('', 'target_audience_base',true,false)->localized_name = 'Target Audience'; //Specify the product's target audience. ex: women
	// $this->addAttributeMapping('recommended_browse_nodes', 'recommended_browse_nodes',true,false)->localized_name = 'Recommended Browse Nodes';
	//End UK template:
	$this->addAttributeMapping('', 'model',true,true)->localized_name = 'Model Number'; //This is the manufacturers model number of the product
	$this->addAttributeMapping('dimension_unit', 'display_dimensions_unit_of_measure',true,true)->localized_name = 'Display Dimensions Unit Of Measure'; //MM,CM,M,IN,FT
	//$this->addAttributeMapping('', 'parent_child')->localized_name = 'Parentage'; //parent or child
	$this->addAttributeMapping('', 'department_name')->localized_name = 'Gender'; //ex: womens
	$this->addAttributeMapping('', 'metal_type', true)->localized_name = 'Metal Type'; //ex: white-gold
	$this->addAttributeMapping('', 'metal_stamp')->localized_name = 'Metal Stamp'; //18k
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', "gem_type$i", true)->localized_name = 'Gem Type' . $i; //gems present: carnelian

?>