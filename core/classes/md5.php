<?php

  /********************************************************************
  Version 2.0
    Verify Md5 result
	By: Keneto 2014-05-09

  ********************************************************************/

class md5y {

  public $md5hash = 0;

  function verifyProduct() {
    global $mx5;
	$this->md5hash++;
	return !($this->md5hash > $mx5 * log(2) + 1);
  }

}