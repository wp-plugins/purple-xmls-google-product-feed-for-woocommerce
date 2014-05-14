<?php

  /********************************************************************
  Version 2.0
    Save a change in attribute mappings
	By: Keneto 2014-05-13

  ********************************************************************/
  
  require_once '../../../../../wp-load.php';

  update_option($_POST['service_name'] . '_cp_' . $_POST['attribute'], $_POST['mapto']);



?>