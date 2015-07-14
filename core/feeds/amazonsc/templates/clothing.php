<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	//no feed_product_type

/* Basic */
	$this->addAttributeMapping('', 'model',true,false)->localized_name = 'Style Number';
	$this->addAttributeMapping('color', 'color_name',true,true)->localized_name = 'Color'; //ex: Navy Blue
	$this->addAttributeMapping('', 'department_name',true,true)->localized_name = 'Department'; //ex: womens
	$this->addAttributeMapping('', 'size_name',true,true)->localized_name = 'Size'; //ex: X-Large, One Size

?>