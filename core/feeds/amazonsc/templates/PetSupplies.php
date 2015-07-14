<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('condition', 'condition_type',true,true)->localized_name = 'Item Condition';
	
	//Use this to specify the target audience for your product. Example: Amphibians. Refer to BTG
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping('', 'target_audience_keywords' . $i, true)->localized_name = 'Target Audience' . $i;
?>