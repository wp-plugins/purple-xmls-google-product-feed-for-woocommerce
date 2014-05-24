<?php

  /********************************************************************
  Version 2.0
    Centralize all those bazillion reference to wp-upload so we can change it
	more easily
	By: Keneto 2014-05-23

  ********************************************************************/

class PFeedFolder {

  function uploadFolder () {
    $upload_dir = wp_upload_dir();
	return $upload_dir['basedir'] . '/cart_product_feeds/';
  }
  
  function uploadRoot () {
    $upload_dir = wp_upload_dir();
	return $upload_dir['basedir'];
  }

}

?>