<?php

  /********************************************************************
  Version 2.0
    Simple: return a proper web content header
	By: Keneto 2014-05-07
  Note: One day, this needs to be moved to Joomla/VirtueMart compatibility

  ********************************************************************/

class PFeedTypeHeader {


  function get_header_forFeedType($feedType) {
	$result = 'Content-Type:text/xml'; 
	switch ($feedType) {
	case 'C':
	  $result = 'Content-Type:text/csv';
	  break;
	case 'T':
	  $result = 'Content-Type:text/csv';
	  break;
	}
    return $result;
  }

}
