<?php

  /********************************************************************
  Version 2.1
    Update all the Feeds at once instead of having to wait for a Cron job
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-23
		2014-07-09 Edited to add "successful" message -Keneto
  ********************************************************************/

  require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
	
  update_all_cart_feeds(false);

	echo 'Update successful';

?>