<?php

  /********************************************************************
  Version 2.0
    For the main feed page
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05

  ********************************************************************/

class PFeedPageDialogs {

  public static function pageHeader ()
  {
    $gap = '
		  <div style="float:left; width: 50px;">
		    &nbsp;
		  </div>';

    $reg = new PLicense();
	if ($reg->valid) {
	  $notice = '
		  <div style="float:left; width: 200px;">
		    <a class="multi-line-button blue" type="submit" value="" id="submit" name="submit" href="http://shoppingcartproductfeed.com/" target="_blank">Thank you for supporting the project</a>
		  </div>';
	  $lic = '';
	} else {
	  $notice = '
		  <div style="float:left; width: 200px;">
		    <a class="multi-line-button blue" type="submit" value="" id="submit" name="submit" href="http://shoppingcartproductfeed.com/" target="_blank" style="height:50px">You are using the free version - Limited to 10 items</a>
		  </div>' . $gap . '
		  <div style="float:left; width: 200px;">
		    <a class="multi-line-button blue" type="submit" value="" id="submit" name="submit" href="https://www.purpleturtle.pro/cart.php?gid=8" target="_blank" style="height:50px">Purchase full or 5-day trial<br> Learn more</a>
		  </div>';
	  $lic = PLicenseKeyDialog::small_registration_dialog('');
	}

    $output = '
	  <h3>Export to Merchants</h3>
	  <div class="postbox" style="width: 98%;">
	    <div class="inside export-target">
		  <div style="float:left;">
			<h4>Select Merchant Type</h4>
			<select id="selectFeedType" onchange="doSelectFeed()">
			  <option></option>
			  <option value="Google">Google Merchant Feed</option>
			  <option value="Amazon">Amazon Product Ads Feed</option>
				<option value="AmazonSC">Amazon Seller Central</option>
			  <option value="eBay">eBay Feed</option>
				<option value="">------</option>
				<option value="Bing">Bing Feed</option>
				<option value="Nextag">Nextag Feed</option>
			  <option value="PriceGrabber">PriceGrabber</option>
				<option value="Rakuten">Rakuten Feed</option>
				<option value="ShareASale">ShareASale Merchant Data Feed</option>
				<option value="Shopzilla">Shopzilla Feed</option>
				<option value="AmmoSeek">AmmoSeek</option>
				<option value="">------</option>
				<option value="Productlistxml">Product List XML Export</option>
				<option value="Productlistcsv">Product List CSV Export</option>
			</select>
		  </div>
		  <div style="float:left; width: 100px;">
		    &nbsp;
		  </div>' . $notice . '
		  <div style="clear: left;">
		  </div>' . $lic . '
		</div>
	  </div>';
	return $output;
  }

  public static function pageBody()
  {
    $output = '

	  <div id="feedPageBody" class="postbox" style="width: 98%;">
	    <div class="inside export-target">
	      <h4>No feed type selected.</h4>
		  <hr />
		</div>
	  </div>
	  ';
	return $output;
  }

}

?>