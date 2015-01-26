<?php

	/********************************************************************
	Version 2.1
		For the main feed page
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05
	********************************************************************/

class PFeedPageDialogs {

	public static function pageHeader() {

		global $pfcore;

		$gap = '
			<div style="float:left; width: 50px;">
				&nbsp;
			</div>';

    $reg = new PLicense();
		if ($reg->valid) {
			// $notice = '
			// <div style="float:left; width: 200px;">
			// 	<a class="multi-line-button blue" type="submit" value="" id="submit" name="submit" href="http://shoppingcartproductfeed.com/" target="_blank">Thank you for supporting the project</a>
			// </div>';
			$lic = '<div style="position:absolute; left:300px; top:60px">
		     <a class="button-primary" type="submit" value="" id="submit" name="submit" href="http://shoppingcartproductfeed.com/" target="_blank">Thank You For Supporting The Project</a>
		      </div>';
		} else {
			// $notice = '
			// <div style="float:left; width: 200px;">
			// 	<a class="multi-line-button blue" type="submit" value="" id="submit" name="submit" href="http://shoppingcartproductfeed.com/" target="_blank" style="height:50px">You are using the free version - Limited to 10 items</a>
			// </div>' . $gap . '
			// <div style="float:left; width: 200px;">
			// 	<a class="multi-line-button blue" type="submit" value="" id="submit" name="submit" href="https://www.purpleturtle.pro/cart.php?gid=8" target="_blank" style="height:50px">Purchase full or 5-day trial<br> Learn more</a>
			// </div>';
			$lic = PLicenseKeyDialog::small_registration_dialog('');
		}

		if (!$pfcore->banner)
			$notice = '';

		$providers = new PProviderList();

		$output = '
			<div class="postbox" style="width:98%;">
				<div class="inside-export-target">
					<div style="position:absolute;">
						<h4>Select Merchant Type</h4>
						<select id="selectFeedType" onchange="doSelectFeed()">
						<option></option>' . 
							$providers->asOptionList() . '
						</select>
					</div>				
					' . $lic . '
				</div>
			</div>
			<div class="clear"></div>';
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