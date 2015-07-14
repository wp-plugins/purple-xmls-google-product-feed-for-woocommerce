<?php

  /********************************************************************
  Version 2.0
    AJAX script fetches the template the user changes it
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2015-01
  ********************************************************************/

  require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
  require_once dirname(__FILE__) . '/../../data/feedcore.php';
  require_once dirname(__FILE__) . '/../../classes/dialogbasefeed.php';
	require_once dirname(__FILE__) . '/../../classes/providerlist.php';
  
  $feedType = $_POST['provider']; //ex: 'kelkoo', 'amazonsc';
  $category = $_POST['template'];
	//$template = $_POST['template'];

  if (strlen($feedType) == 0)
    return;

  $inc = dirname(__FILE__) . '/../../feeds/' . strtolower($feedType) . '/dialognew.php';
  $feedObjectName = $feedType . 'Dlg';

  include_once $inc;
  $f = new $feedObjectName();
	$f->initializeProvider();
  $f->provider->initializeFeed($category, $category);
	//$f->provider->loadTemplate($template, $template);
  echo $f->attributeMappings();
	
?>