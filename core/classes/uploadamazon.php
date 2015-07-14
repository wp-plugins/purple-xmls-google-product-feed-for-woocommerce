<?php

	/********************************************************************
	Version 1.0
		Upload to Amazon procedures
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By:
		Not for production code (yet)
		Example of how to implement the Amazon Upload code in CMS-Agnostic fashion
	********************************************************************/

class PAmazonUpload {

	public static function uploadFeed() {

        global $pfcore;

        $remember = $pfcore->settingGet("cpf_remember_$this->service_name");
        $seller_id = null;
        $marketplace_id = null;
        $access_id = null;
        $secret_id = null;
        if ($remember == null || empty($remember)) {
            $pfcore->settingSet("cpf_remember_$this->service_name", 0);
            $remember = 'false';
        } else if ($remember == 1) {
            $seller_id = $pfcore->settingGet("cpf_sellerid_$this->service_name");
            $marketplace_id = $pfcore->settingGet("cpf_marketplaceid_$this->service_name");
            $access_id = $pfcore->settingGet("cpf_accessid_$this->service_name");
            $secret_id = $pfcore->settingGet("cpf_secretid_$this->service_name");
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