<?php

  /********************************************************************
  Version 2.0
    Display the window asking for license key
	By: Keneto 2014-05-09

  ********************************************************************/

class PLicenseKeyDialog {

  function large_registration_dialog($current_licensekey) {
    $result = " <div id='poststuff'><div class='postbox' style='width: 98%;'><form name='feed_update_delay_form'  action='" . admin_url() . "admin.php?page=cart-product-feed-admin' id='cat-product-feeds-xml-form' method='post' target=''>
		<h3 class='hndle'>Unlock more features</h3>
		<div class='inside export-target'>
		 <table class='form-table' >
		  <tbody><tr>";
    $result .= "<th style='width:300px;'><label>Enter Valid License Key for Cart Product Feeds : </label></th>";
    $result .= '<td ><input name="license_key"  id="feed_update_delay" class="text_large" value="' . $current_licensekey . '"/></td>';
    $result .= "<td   ><input type='hidden' name='action' value='update_license' /><input class='navy_blue_button' style='float:right;' type='submit' value='Save Changes' id='submit' name='submit'></td>";
    $result .= "</tr><tr><td colspan='3'><br /><br /><br /><p><a class='multi-line-button green' href='https://www.purpleturtle.pro/cart.php?gid=8' target='_blank' style='width:22em'>
        <span class='title'>Buy a License Key</span><span class='subtitle'>Multi-Site License - $399.99USD</span><span class='subtitle'>Single Site License - $69.99USD</span>
      </a></p></td></tr></tbody></table></div></form></div></div></div>";
    $result .= "<br/><br/><br/><br/><br/><br/>";
    $result .= '<div class="clear"></div>';
	return $result;
  }

  function small_registration_dialog($current_licensekey) {
    $result = 
	  '
		  <form name="feed_update_delay_form"  action="' . admin_url() . 'admin.php?page=cart-product-feed-admin" id="cat-product-feeds-xml-form" method="post" target="">
		    <table class="form-table">
		    <tbody>
			<tr>
				<th style="width:400px;">
				  &nbsp;
				</th>
				<th style="width:300px;">
				  <label>Enter Valid License Key for Cart Product Feeds : </label>
				</th>
				<td>
				  <input name="license_key"  id="feed_update_delay" class="text_large" value="' . $current_licensekey . '"/>
				</td>
				<td>
				  <input type="hidden" name="action" value="update_license" />
				  <input class="navy_blue_button" type="submit" value="Save Key" id="submit" name="submit">
				</td>
			</tr>
			</tbody>
			</table>
		  </form>';
		  
	return $result;
  }

}
?>