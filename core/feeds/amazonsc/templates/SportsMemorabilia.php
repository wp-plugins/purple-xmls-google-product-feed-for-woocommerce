<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'display_dimensions_unit_of_measure')->localized_name = 'Display Dimensions Unit Of Measure'; //MM,CM,M,IN,FT
	$this->addAttributeMapping('', 'item_display_weight_unit_of_measure')->localized_name = 'Item Display Weight Unit Of Measure'; //LB	
	$this->addAttributeMapping('', 'fulfillment_center_id')->localized_name = 'Fulfillment Center ID'; //AMAZON_NA
	$this->addAttributeMapping('', 'package_dimensions_unit_of_measure')->localized_name = 'Package Dimensions Unit Of Measure'; //IN
	$this->addAttributeMapping('', 'authenticated_by')->localized_name = 'Authentication Provided By'; //PSA
	$this->addAttributeMapping('', 'grade_rating')->localized_name = 'Condition Type'; //mint, excellent
	$this->addAttributeMapping('', 'graded_by')->localized_name = 'Grading Provided By'; //BEckett, PSA
	$this->addAttributeMapping('', 'item_thickness_unit_of_measure')->localized_name = 'Item Thickness Unit Of Measure'; //mm, in, ft

?>