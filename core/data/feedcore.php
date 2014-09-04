<?php

  /********************************************************************
  Version 2.1
    PFeedCore points to site url
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-06
		2014-08 Added getVersion because in the future, GTS Trusted Stores 
			will need to know what services are available.

  ********************************************************************/

class PFeedCore {

	public $callSuffix = ''; //So getList() can map into getListJ() and getListW() depending on the cms
	public $form_method = 'GET';
	public $isJoomla = false;
	public $isWordPress = false;
	public $siteHost; //eg: www.mysite.com
  
  function __construct() {

		if (defined('_JEXEC')) {
			/********************************************************************
			Joomla init
			********************************************************************/
			$this->callSuffix = 'J';
			$this->cmsName = 'Joomla!';
			$this->cmsPluginName = 'Virtuemart';
			$this->currency = '$'; //!Should not be hard-coded
			$this->form_method = 'POST';
			$this->isJoomla = true;
			$this->siteHost = JURI::root(false);
			$this->siteHostAdmin = $this->siteHost;
			$this->weight_unit = 'kg'; //!Should not be hard-coded
		} else {
			/********************************************************************
			Wordpress init
			********************************************************************/
			//require_once dirname(__FILE__) . '/../../../../../wp-load.php'; Not safe to call this from inside a function!

			//check what plugin is available, assuming WooCommerce
			$pluginName = 'WooCommerce';
			$all_plugins = get_plugins();
			foreach($all_plugins as $index => $this_plugin)
				if ($this_plugin['Name'] == 'WP e-Commerce') {
					$pluginName = 'WP e-Commerce';
					break;
				}

			switch ($pluginName) {
				case 'WooCommerce':
					global  $woocommerce;
					$this->callSuffix = 'W';
					$this->cmsName = 'WordPress';
					$this->cmsPluginName = 'Woocommerce';
					if (function_exists('get_woocommerce_currency'))
						$this->currency = get_woocommerce_currency();
					else
						$this->currency = '$'; //!Should not be hard-coded
					$this->isWordPress = true;
					$this->siteHost = site_url();
					$this->siteHostAdmin = admin_url();
					$this->weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
					$this->manage_stock = strtolower(get_option('woocommerce_manage_stock')) == 'yes';
					$this->hide_outofstock = strtolower(get_option('woocommerce_hide_out_of_stock_items')) == 'yes';
					break;
				case 'WP e-Commerce':
					$this->callSuffix = 'We';
					$this->cmsName = 'WordPress';
					$this->cmsPluginName = 'WP e-Commerce';
					$this->currency = '$'; //!Should not be hard-coded
					$this->isWordPress = true;
					$this->siteHost = site_url();
					$this->siteHostAdmin = admin_url();
					$this->weight_unit = 'kg'; //!Should not be hard-coded
					break;
			}
		}
	}
  
	function settingGet($settingName) {
		$getListCall = 'settingGet' . $this->callSuffix;
		return $this->$getListCall($settingName);
	}

	function settingGetJ($settingName) {
		$db = JFactory::getDBO();
		$query = '
			SELECT value
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote( $db->escape($settingName), false);
		$db->setQuery($query);
		$db->query();

		$result = $db->loadResult();
		return $result;
	}

	function getVersion() {
		return 2;
	}
  
	function settingGetW($settingName) {
		return get_option($settingName);
	}

	function settingGetWe($settingName) {
		return get_option($settingName);
	}

	function settingSet($settingName, $value) {
		$getListCall = 'settingSet' . $this->callSuffix;
		$this->$getListCall($settingName, $value);
	}
  
	function settingSetJ($settingName, $value) {

		//Initialize
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		//Does this value already exist?
		$query = '
			SELECT id, name
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote( $db->escape($settingName), false);
		$result = $db->loadObject();
		if ($result == null)
			$isNew = true;
		else
			$isNew = false;

		$setting = new stdClass();
		$setting->name = $settingName;
		$setting->value = $value;
		if ($isNew) {
			$setting->id = $result->id;
			$setting->kind = 0;
			//$setting->ordering int,
			$setting->created = $date->toSql();
			$setting->created_by = $user->get('id');
		}
		//$setting->catid
		$setting->modified = $date->toSql();
		$setting->modified_by = $user->get('id');

		if ($isNew)
			$db->insertObject('#__cartproductfeed_options', $setting, 'id');
		else
			$db->updateObject('#__cartproductfeed_options', $setting, 'id');

	}
  
	function settingSetW($settingName, $value) {
		update_option($settingName, $value);
	}

	function settingSetWe($settingName, $value) {
		update_option($settingName, $value);
	}

}

global $pfcore;
$pfcore = new PFeedCore();

?>