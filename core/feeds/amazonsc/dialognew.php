<?php

	/********************************************************************
	Version 2.0
		Front Page Dialog for Amazon Seller Central
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-08-08

	********************************************************************/

class AmazonSCDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'Amazonsc';
		$this->service_name_long = 'Amazon Seller Central';
	}

	function categoryList($initial_remote_category) {
		if ($this->blockCategoryList)
			return '';
		else
			return '
				  <label class="label" for="categoryDisplayText" >Template : </label>
				  <span><input type="text" name="categoryDisplayText" class="text_big" id="categoryDisplayText"  onkeyup="doFetchCategory_timed(\'' . $this->service_name . '\',  this.value);" value="' . $initial_remote_category . '" autocomplete="off" placeholder="Start typing template name" /></span>
				  <div id="categoryList" class="categoryList"></div>
				  <input type="hidden" id="remote_category" name="remote_category" value="' . $initial_remote_category . '">';
	}

    function uploadFeed() {
        global $pfcore;

				//2015-01-18: Only WP can support the db calls such as: get_current_user_id(), get_user_meta() etc
				//For now, we'll just break out of here if WP is not detected
				if ($pfcore->cmsName != 'WordPress')
					return '';

        $user_id = get_current_user_id();
        $remember = get_user_meta($user_id, "cpf_remember_$this->service_name", true);
        $seller_id = null;
        $marketplace_id = null;
        $access_id = null;
        $secret_id = null;
        if ($remember == null || empty($remember)) {
            add_user_meta($user_id, "cpf_remember_$this->service_name", 'false');
            $remember = 'false';
        } else if ($remember == 'true') {
            $seller_id = get_user_meta($user_id, "cpf_sellerid_$this->service_name", true);
            $marketplace_id = get_user_meta($user_id, "cpf_marketplaceid_$this->service_name", true);
            $access_id = get_user_meta($user_id, "cpf_accessid_$this->service_name", true);
            $secret_id = get_user_meta($user_id, "cpf_secretid_$this->service_name", true);
        }
        $output = '
                <div style="clear: both;">&nbsp;</div>
                <h2>Upload Feed</h2>
                <table style="float: right;">
                    <tr>
                        <td>Seller ID:</td>
                        <td><input type="text" class="remember-field" id="sellerid" name="sellerid" value="' . ($seller_id ? $seller_id : '') . '" size="20"/></td>
                    </tr>
                    <tr>
                        <td>Marketplace ID:</td>
                        <td><input type="text" class="remember-field" id="marketplaceid" name="marketplaceid" value="' . ($marketplace_id ? $marketplace_id : '') . '"  size="20"/></td>
                    </tr>
                    <tr>
                        <td>AWS Access Key ID:</td>
                        <td><input type="text" class="remember-field" id="accessid" name="accessid" value="' . ($access_id ? $access_id : '') . '"  size="20"/></td>
                    </tr>
                    <tr>
                        <td>Secret Key:</td>
                        <td><input type="text" class="remember-field" id="secretid" name="secretid" value="' . ($secret_id ? $secret_id : '') . '"  size="20"/></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="checkbox" name="remember" id="remember" ' . ($remember == 'true' ? 'checked' : '') . '/><label for="remember">Remember my credentials</label></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="1" style="text-align:left"><input type="checkbox" name="purgereplace" id="purgereplace"/><label for="purgereplace">Purge and Replace</label></td>
                    </tr>
                </table>
                <div style="clear: both;">&nbsp;</div>
                ';
        return $output;
    }

}