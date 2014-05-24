<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Product Categories
	By: Keneto 2014-05-05

  ********************************************************************/

require_once '../../../../../wp-load.php';
require_once dirname(__FILE__) . '/../classes/productcategories.php';

class ProductCategoriesDlg {

  function mainDialog()
  {
	return '
		<div id="poststuff">
	    <div class="postbox" style="width: 98%;">
		  <form name="products"  action="' . site_url() . '" id="cat-product-feeds-xml-form" method="get" target="_blank">
		  <h3 class="hndle">Products By Category</h3>
		  <div class="inside-export-target">
			  <div class="feed-type-col1">
			    <div class="feed-colheader">Feed Type</div>
				<select name="feedtype"><option selected="selected" value="X">XML Feed</option><option value="C">CSV Feed</option><option value="T">Tab Separated File</option></select>
			  </div>
			  <div class="feed-type-col2">
			    <div class="feed-colheader">Category</div>
				<select name="category" id="listproductcategory">' . PProductCategories::getList() . '</select>
			  </div>
			  <div class="feed-type-col3">
			    <div class="feed-colheader">Aggregation</div>
				<select name="aggregation"><option selected="selected" value="S">Standard (Singleton)</option><option value="C">Combinatoric(Using Attributes)</option></select>
			  </div>
			  <div class="feed-type-col4">&nbsp</div>
		      <input type="hidden" name="RequestCode" value="CategoryProducts" />
		      <input  style="float:right;" class="cupid-green" type="button" onclick="document.products.submit();" name="submit-productcategories-xml" value="Get Feed" id="cat-feeds-xml-products" />
			  <div class="feed-type-col4">&nbsp</div>
		  </div>
		  </form>
		</div>
		</div>
		';
  }











}



?>