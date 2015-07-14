<?php

	/********************************************************************
	Version 2.0
		Front Page Dialog for HOUZZ
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto, Calv 2015-23-02

	********************************************************************/

class HouzzDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Houzz';
		$this->service_name_long = 'Houzz Product CSV Export';
		$this->options = array(
        'LeadTimeMin',
        'LeadTimeMax',
        'Style',
        );	
	}

	function convert_option($option) {
		return strtolower(str_replace(" ", "_", $option));
	}

}