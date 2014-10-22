<?php

	/********************************************************************
	Version 2.0
		Front Page Dialog for Amazon
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05

	********************************************************************/

class AmazonDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Amazon';
		$this->service_name_long = 'Amazon Product Ads Export';
		$this->options = array(
			'Age',
			'Band material',
			'Brand',
			'Bullet point1',
			'Bullet point2',
			'Bullet point3',
			'Bullet point4',
			'Bullet point5',
			'Color',
			'Color and finish',
			'Computer CPU speed',
			'Computer Memory Size',
			'Cuisine',
			'Department',
			'Digital Camera Resolution',
			'Display Size',
			'Display Technology',
			'Flash Drive Size',
			'Flavor',
			'Gender',
			'Hard Disk Size',
			'Height',
			'Included RAM Size',
			'Item package quantity',
			'Keywords1',
			'Keywords2',
			'Keywords3',
			'Keywords4',
			'Keywords5',
			'League and Team',
			'Length',
			'Manufacturer',
			'Material',
			'Maximum Age',
			'Memory Card Type',
			'Metal Type',
			'Minimum Age',
			'Model Number',
			'Occasion',
			'Operating System',
			'Optical Zoom',
			'Ring size',
			'Scent',
			'Screen Resolution',
			'Size',
			'Size per Pearl',
			'Theme HPC',
			'Total Diamond Weight',
			'Watch Movement',
			'Width'
		);
	}

}