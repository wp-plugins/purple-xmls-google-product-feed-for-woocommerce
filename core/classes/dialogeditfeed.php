<?php

  /********************************************************************
	Version 2.0
		Edit a feed's basic information
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
  ********************************************************************/

class PEditFeedDialog {

	public static function pageBody($feed_id) {

		require_once dirname(__FILE__) . '/../data/savedfeed.php';
		require_once 'dialogbasefeed.php';

		if ($feed_id == 0)
			return;

		$feed = new PSavedFeed($feed_id);

		//Figure out the dialog for the provider
		$dialog_file = dirname(__FILE__) . '/../feeds/' . strtolower($feed->provider) . '/dialognew.php';
		if (file_exists($dialog_file))
			require_once $dialog_file;

		//Instantiate the dialog
		$provider = $feed->provider . 'Dlg';
		$provider_dialog = new $provider();

		echo $provider_dialog->mainDialog($feed);
		
	}

}