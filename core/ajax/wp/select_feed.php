<?php

  /********************************************************************
  Version 2.1
    AJAX script fetches the feed the user needs when they change selection
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05
  2014-06 feedcore now loads wp-load.php and handles other init tasks
	2015-02 added actions
  ********************************************************************/

  require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
  require_once dirname(__FILE__) . '/../../data/feedcore.php';
  require_once dirname(__FILE__) . '/../../classes/dialogbasefeed.php';
	require_once dirname(__FILE__) . '/../../classes/providerlist.php';

do_action('load_cpf_modifiers');

global $pfcore;
$pfcore->trigger('cpf_init_feeds');

add_action( 'cpf_select_feed_main_hook', 'select_feed_main' );
do_action('cpf_select_feed_main_hook');

function select_feed_main() {
  
  $feedType = $_POST['feedtype'];

  if (strlen($feedType) == 0)
    return;

  $inc = dirname(__FILE__) . '/../../feeds/' . strtolower($feedType) . '/dialognew.php';
  $feedObjectName = $feedType . 'Dlg';

	if (file_exists($inc))
		include_once $inc;
  $f = new $feedObjectName();
  echo $f->mainDialog();
}

?>