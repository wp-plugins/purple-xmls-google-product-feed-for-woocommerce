<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Amazon
	By: Keneto 2014-05-05

  ********************************************************************/

include_once 'basefeeddialogs.php';

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
        'Computer memory size',
        'Department',
        'Digital Camera Resolution',
        'Display size',
        'Display technology',
        'Flash drive Size',
        'Flavor',
        'Gender',
        'Hard disk size',
        'Height',
        'Included RAM size',
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
        'Maximum age',
        'Memory Card Type',
        'Metal type',
        'Minimum age',
        'Model Number',
        'Operating system',
        'Optical zoom',
        'Ring size',
        'Scent',
        'Screen Resolution',
        'Size',
        'Size per pearl',
        'Theme HPC',
        'Total Diamond Weight',
        'Watch movement',
        'Width'
	);
  }

}