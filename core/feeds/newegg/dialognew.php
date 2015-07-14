<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Newegg
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Calv 2014-11-11

  ********************************************************************/

class NeweggDlg extends PBaseFeedDialog {

  function __construct() {
    parent::__construct();
    $this->service_name = 'Newegg';
    $this->service_name_long = 'Newegg Products CSV Export';
	  $this->options = array();
  }

}