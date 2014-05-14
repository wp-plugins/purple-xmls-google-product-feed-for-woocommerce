<?php

  /********************************************************************
  Check the license key
  Version 2.0
	By: Keneto 2014-05-12

  ********************************************************************/

class PLicense {

  public $results;
  public $valid = false;

  function __construct() {
  
    //When loading license key, we must be careful to check for valid licenses from prior versions
    $licensekey = get_option('cp_licensekey');
	if (strlen($licensekey) == 0) {
	  //Look for old version of key
      $licensekey = get_option('purplexml_licensekey');
	  if (strlen($licensekey) > 0) {
	    set_option('cp_licensekey', $licensekey);
	  }
	}
    $localkey = get_option('cp_localkey');
	if (strlen($localkey) == 0) {
	  //Look for old version of key
	  $localkey = get_option('purplexml_localkey');
	  if (strlen($localkey) > 0) {
	    set_option('cp_localkey', $localkey);
	  }
	}
	$this->results = check_license($licensekey, $localkey);
	if ($this->results["status"] == "Active") {
	  $this->valid = true;
	}
  }
  
  function unregister() {
    //This will remove the license key (which is likely an undesirable course of action)
	update_option('cp_licensekey', '');
    update_option('cp_localkey', '');
  }

}

?>