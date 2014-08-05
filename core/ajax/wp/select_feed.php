<?php

  /********************************************************************
  Version 2.0
    AJAX script fetches the feed the user needs when they change selection
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05
  2014-06-08 feedcore now loads wp-load.php and handles other init tasks
  ********************************************************************/

  require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
  require_once dirname(__FILE__) . '/../../data/feedcore.php';
  require_once dirname(__FILE__) . '/../../classes/dialogbasefeed.php';
  
  $feedType = $_POST['feedtype'];

  if (strlen($feedType) == 0)
    return;

  $inc = dirname(__FILE__) . '/../../feeds/' . strtolower($feedType) . '/dialognew.php';
  $feedObjectName = $feedType . 'Dlg';

  include_once $inc;
  $f = new $feedObjectName();
  echo $f->mainDialog();

?>