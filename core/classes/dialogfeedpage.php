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

		if ($pfcore->cmsName == 'WordPress') {
			$reg = new PLicense();
			if ($reg->valid)
				$lic = '<div style="position:absolute; left:300px; top:60px">
					 <a class="button-primary" type="submit" value="" id="submit" name="submit" href="http://shoppingcartproductfeed.com/" target="_blank">Thank You For Supporting The Project</a>
						</div>';
			else
				$lic = PLicenseKeyDialog::small_registration_dialog('');
		} else
			$lic = '';

		$providers = new PProviderList();

		$output = '
			<div class="postbox" style="width:98%;">
				<div class="inside-export-target">
					<div style="position:absolute;">
						<h4>Select Merchant Type</h4>
						<select id="selectFeedType" onchange="doSelectFeed();">
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