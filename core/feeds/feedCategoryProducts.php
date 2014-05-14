<?php

  /********************************************************************
  Version 2.0
    A Feed for Products by Category
	This gets a little complex. See design-document -> ProductCategoryExport
	By: Keneto 2014-05-08

  ********************************************************************/

require_once __DIR__ . '/../productCategory/productExporters.php';
require_once __DIR__ . '/../data/productCombiner.php';
require_once __DIR__ . '/../productCategory/productFeedTypeHeader.php';
require_once __DIR__ . '../../classes/md5.php';
require_once __DIR__ . '../registration.php';

class PCategoryProductsFeed {

 
  function getFeedData() {

	$category_id = $_REQUEST['category'];
	$feedType = $_REQUEST['feedtype'];
	$aggregation = $_REQUEST['aggregation'];
	new PLicense();
	
	//Tell the server what type of content we plan to return
	header( PFeedTypeHeader::get_header_forFeedType($feedType) );

	//Generate the ProductList
	$product_list = ProductsByCategory::ProductList($category_id);

	if ($aggregation == 'C') {
	  //In the combinatoric form, each product will appear multiple times with different attributes
	  $product_list = ProductsByCategory::ProductCombinations($product_list, $category_id);
	}

	//Output
    switch ($feedType) {
      case 'C':
		$categoryExport = new CategoryExportCSV();
	    break;
      case 'T':
	    $categoryExport = new CategoryExportTabbedTextFile();
	    break;
	  default:
	    $categoryExport = new CategoryExportXML();
    }
	$categoryExport->feed_category = new md5y();
	$categoryExport->DoExport($product_list, $aggregation);
  }

  function must_exit() {
    //Yes: exit so the page will remain in place
    return true;
  }

}

?>