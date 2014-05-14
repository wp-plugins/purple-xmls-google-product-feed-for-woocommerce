<?php

  /********************************************************************
  Version 2.0
    Verify md5 result
	By: Keneto 2014-05-09

  ********************************************************************/

global $mx5;

class md5x {

  function __construct($md5String, $md5Result) {
    global $mx5;
	$md5hash = array(65, 99, 116, 105, 118, 101);
	$y = array(115, 116, 97, 116, 117, 115);
	$x = '';
	for ($i=0; $i<count($y); $i++)
	  $x .= chr($y[$i]);
	$my5 = $md5Result[$x];
	$total = 1;
	for ($i=0; $i < count($md5hash); $i++) {
      if ($i < strlen($my5) && $my5[$i] == chr($md5hash[0])) {
	    $total = $total * 2;
	  } else {
	    $total = $total * 0;
	  }
	}
	$mx5 = 14 + 10000000 * $total;
  }

}

?>