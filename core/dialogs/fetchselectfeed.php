<?php

  /********************************************************************
  Version 2.0
    AJAX script fetches the feed the user needs when they change selection
	By: Keneto 2014-05-05

  ********************************************************************/

  $feedType = $_POST['feedtype'];
  
  if (strlen($feedType) == 0)
    return;
  
  $inc = 'Dlg' . $feedType . '.php';
  $feedObjectName = $feedType . 'Dlg';

  include_once $inc;
  $f = new $feedObjectName;
  echo $f->mainDialog();

?>