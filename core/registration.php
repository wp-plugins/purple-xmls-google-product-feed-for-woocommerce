<?php

  /********************************************************************
  Check the license key
  Version 2.1
    By: Keneto 2014-05-12
		2014-08: Added GTS plugin class PLicenseGTS
		license	GNU General Public License version 3 or later; see GPLv3.txt
  ********************************************************************/

class PLicense {

	public $error_message = '';
	public $results = array();
	public $valid = false;
	private $strErrorMsgMain = '- Register for the pro version to get full functionality: <a target=\'_blank\' href = \'http://shoppingcartproductfeed.com/\'>shoppingcartproductfeed.com</a> ';

	private $strLicenseKey = 'cp_licensekey';
	private $strLocalKey = 'cp_localkey';

	function __construct() {

		global $pfcore;

		//When loading license key, we must be careful to check for valid licenses from prior versions
		$licensekey = $pfcore->settingGet($this->strLicenseKey);
		if (strlen($licensekey) == 0) 
		{
			//Look for old version of key
			$licensekey = $pfcore->settingGet('purplexml_licensekey');
			if (strlen($licensekey) > 0)
				$pfcore->settingSet($this->strLicenseKey, $licensekey);   
		}

		$localkey = $pfcore->settingGet($this->strLocalKey);
		if (strlen($localkey) == 0) 
		{
			//Look for old version of key
			$localkey = $pfcore->settingGet('purplexml_localkey');
			if (strlen($localkey) > 0)
			$pfcore->settingSet($this->strLocalKey, $localkey);
		}

		$this->results['status'] = 'Invalid';
		$this->error_message = '';

		//If there are keys set, remember this fact
		$hasKeys = false;
		if ( strlen($localkey) > 0 || strlen($licensekey) > 0 )
			$hasKeys = true;
		$this->checkLicense( $licensekey, $localkey );
		$this->results['checkmd5x'] = new md5x( $licensekey . $localkey, $this->results );
		if ( $this->results['status'] == 'Active' )
			$this->valid = true;
		elseif ( $hasKeys ) 
			$this->error_message = 'License Key Invalid: ' . $this->error_message;
	}


	function checkLicense( $licensekey, $localkey = '' ) 
	{
		//initial values
	    $whmcsurl = 'https://www.purpleturtle.pro/';
	    $licensing_secret_key = '437682532'; # Unique value, should match what is set in the product configuration for MD5 Hash Verification
	    $check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
	    $checkdate = date('Ymd'); # Current date
	    $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
	    $localkeydays = 1; # How long the local key is valid for in between remote checks
	    $allowcheckfaildays = 0; # How many days to allow after local key expiry before blocking access if connection cannot be made
	    $localkeyvalid = false;
	    $originalcheckdate = '';
	    $localexpiry = 0;
	    $status = '';

		$Results = $this->validateLocalKey( $localkey, $localkeydays, $licensing_secret_key, $localexpiry );
		if ( $Results ) 
		{
			foreach( $Results as $k => $result )
				$this->results[$k] = $result;
			return;
		}

		$postfields['licensekey'] = $licensekey;
		$postfields['domain'] = $_SERVER['SERVER_NAME'];
		$postfields['ip'] = $usersip;
		$postfields['dir'] = dirname(__FILE__);

		if ( $check_token )
			$postfields['check_token'] = $check_token;

		if ( function_exists('curl_exec') ) 
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $whmcsurl . 'modules/servers/licensing/verify.php');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
			curl_setopt($ch, CURLOPT_TIMEOUT, 150);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//added for users who experience 'Remote Check Failed' license issue.
			//it is not enough to disable CURLOPT_SSL_VERIFYPEER
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);
			//echo curl_error($ch); //debug

			if (curl_errno($ch))
				$this->error_message = 'Curl error: ' . curl_error($ch);

			curl_close($ch);

		}
		else 
		{
			$fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);

			if ( $fp ) 
			{
				$querystring = '';
				foreach ($postfields as $k => $v)
					$querystring .= '$k=' . urlencode($v) . '&';

				$header = "POST " . $whmcsurl . "modules/servers/licensing/verify.php HTTP/1.0\r\n";
				$header.= "Host: " . $whmcsurl . "\r\n";
				$header.= "Content-type: application/x-www-form-urlencoded\r\n";
				$header.= "Content-length: " . @strlen($querystring) . "\r\n";
				$header.= "Connection: close\r\n\r\n";
				$header.= $querystring;

				$data = '';
				@stream_set_timeout($fp, 20);
				@fputs($fp, $header);
				$status = @socket_get_status($fp);

				while (!@feof($fp) && $status) {
					$data .= @fgets($fp, 1024);
					$status = @socket_get_status($fp);
				}

				@fclose($fp);
			}
		}

		if (!$data) 
		{
			$this->error_message = 'Remote Check Failed. ';
			return;
		}

		preg_match_all( '/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches );
		$inputdata = array();
		foreach ( $matches[1] as $k => $v ) 
		{
			$inputdata[$v] = $matches[2][$k];
			$this->results[$v] = $matches[2][$k];
		}

		if ( isset($inputdata['md5hash']) && $inputdata['md5hash'] ) 
		{
			if ( $inputdata['md5hash'] != md5($licensing_secret_key . $check_token) ) 
			{
				$this->error_message = 'MD5 Checksum Verification Failed. ';
				return;
			}
		}

		if ( $inputdata["status"] == "Active" ) 
		{

			$inputdata["checkdate"] = $checkdate;
			$data_encoded = serialize($inputdata);
			$data_encoded = base64_encode($data_encoded);
			$data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
			$data_encoded = strrev($data_encoded);
			$data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
			$data_encoded = wordwrap($data_encoded, 80, "\n", true);
			$inputdata["localkey"] = $data_encoded; 
		} 
		else
			$this->error_message .= $this->strErrorMsgMain;

		$this->inputdata["remotecheck"] = true;
		unset($postfields, $data, $matches, $whmcsurl, $licensing_secret_key, $checkdate, $usersip, $localkeydays, $allowcheckfaildays, $md5hash);
	}

	function unregister() 
	{
		//This will remove the license key (which is likely an undesirable course of action)
		$pfcore->settingSet($this->strLicenseKey, '');
		$pfcore->settingSet($this->strLocalKey, '');
	}

	function validateLocalKey( $localkey, $localkeydays, $licensing_secret_key, $localexpiry )
	{
		if ( !$localkey ) 
			return false;

		$localkey = str_replace("\n", '', $localkey); # Remove the line breaks
		$localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
		$md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash

		if ( $md5hash != md5($localdata . $licensing_secret_key) )
			return false;

		$localdata = strrev($localdata); # Reverse the string
		$md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
		$localdata = substr($localdata, 32); # Extract License Data
		$localdata = base64_decode($localdata);
		$localkeyresults = unserialize($localdata);
		$originalcheckdate = $localkeyresults['checkdate'];

		if ( $md5hash != md5($originalcheckdate . $licensing_secret_key) )
			return false;

		$locheck_licensecalexpiry = date('Ymd', mktime(0, 0, 0, date('m'), date('d') - $localkeydays, date('Y')));

		if ( $originalcheckdate < $localexpiry )
			return false;
			
		$validdomains = explode( ',', $localkeyresults['validdomain'] );
		if ( !in_array($_SERVER['SERVER_NAME'], $validdomains) ) 
		{
			$this->error_message = 'Valid domain incorrect. ';
			return false;
		}

		$validips = explode(',', $localkeyresults['validip']);
		if ( !in_array($usersip, $validips) ) 
		{
			$this->error_message = 'IP incorrect. ';
			return false;
		}

		if ( $localkeyresults['validdirectory'] != dirname(__FILE__) ) 
		{
			$this->error_message = 'Valid directory mismatch. ';
			return false;
		}
		return $localkeyresults;
	}
}

class PLicenseGTS extends PLicense {

	function __construct() {
		$strErrorMsgMain = '- Register for the pro version to get full functionality: <a target=\'_blank\' href = \'http://shoppingcartproductfeed.com/\'>shoppingcartproductfeed.com</a> ';
		$strLicenseKey = 'gts_licensekey';
		$strLocalKey = 'gts_localkey';
		parent::__construct();
	}
}

class PLicenseFV extends PLicense {

	function __construct() {
		$strErrorMsgMain = '- Register for the pro version to get full functionality: <a target=\'_blank\' href = \'http://shoppingcartproductfeed.com/\'>shoppingcartproductfeed.com</a> ';
		$strLicenseKey = 'fv_licensekey';
		$strLocalKey = 'fv_localkey';
		parent::__construct();
	}
}