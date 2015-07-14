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

	public $banner = true; //In Createfeed page
	public $callSuffix = ''; //So getList() can map into getListJ() and getListW() depending on the cms
	public $form_method = 'GET';
	public $hide_outofstock = false;
	public $isJoomla = false;
	public $isWordPress = false;
	public $siteHost; //eg: www.mysite.com
	public $shopID = 0; //Used by RapidCart Source
  
  function __construct() {

		if (defined('_JEXEC')) {
			/********************************************************************
			Joomla init
			********************************************************************/
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart')) {
				$this->callSuffix = 'J';
				$this->cmsName = 'Joomla!';
				$this->cmsPluginName = 'Virtuemart';
				$this->currency = '$'; //!Should not be hard-coded
				$this->form_method = 'POST';
				$this->isJoomla = true;
				$this->siteHost = JURI::root(false);
				$this->siteHostAdmin = $this->siteHost;
				$this->timezone = JFactory::getConfig()->getValue('offset');
				$this->weight_unit = 'kg'; //!Should not be hard-coded
				$this->dimension_unit = 'm';
			} elseif (file_exists(JPATH_ADMINISTRATOR . '/components/com_rapidcart')) {
				$this->callSuffix = 'JS';
				$this->cmsName = 'Joomla!';
				$this->cmsPluginName = 'RapidCart';
				$this->currency = '$';
				$this->form_method = 'POST';
				$this->isJoomla = true;
				$this->siteHost = JURI::root(false);
				$this->siteHostAdmin = $this->siteHost;
				$this->timezone = JFactory::getConfig()->get('config.offset');
				$this->weight_unit = 'kg';
				$this->dimension_unit = 'm';
			} elseif (file_exists(JPATH_ADMINISTRATOR . '/components/com_hikashop'))  {
				$this->callSuffix = 'JH';
				$this->cmsName = 'Joomla!';
				$this->cmsPluginName = 'Hikashop';
				$this->currency = '$';
				$this->form_method = 'POST';
				$this->isJoomla = true;
				$this->siteHost = JURI::root(false);
				$this->siteHostAdmin = $this->siteHost;
				//$this->timezone = JFactory::getConfig()->getValue('offset'); //J2
				$this->timezone = JFactory::getConfig()->get('config.offset');
				$this->weight_unit = 'kg';
				$this->dimension_unit = 'm';
			}
		} else {
			/********************************************************************
			Wordpress init
			********************************************************************/
			//require_once dirname(__FILE__) . '/../../../../../wp-load.php'; Not safe to call this from inside a function!

			//check what plugin is available, assuming WooCommerce
			$pluginName = 'WooCommerce';
			$all_plugins = get_plugins();
			foreach($all_plugins as $index => $this_plugin)
				if ($this_plugin['Name'] == 'WP e-Commerce' || $this_plugin['Name'] == 'WP eCommerce') {
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
					$this->dimension_unit = esc_attr(get_option( 'woocommerce_dimension_unit' )); //cm
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

	public function listOfRapidCartShops() {
		$user = JFactory::getUser();
		$groups = $user->groups;
		if (isset($groups[8]) && $groups[8])
			$where = '';
		else
			$where = 'WHERE created_by = ' . $user->id;
		$db = JFactory::getDBO();
		$db->setQuery('
			SELECT id, name
			FROM #__rapidcart_shops
			' . $where);
		return $db->loadObjectList();
	}

	public function loadRequires($name) {

		//Allow external plugins to load a particular Feed object (Intended for WordPress)
		require_once dirname(__FILE__) . '/../classes/dialogbasefeed.php';
		require_once dirname(__FILE__) . '/../feeds/' . $name . '/feed.php';
		require_once dirname(__FILE__) . '/../feeds/' . $name . '/dialognew.php';

	}

	function localizedDate($format, $data) {
		$getListCall = 'localizedDate' . $this->callSuffix;
		return $this->$getListCall($format, $data);
	}

	function localizedDateJ($format, $data) {
		$this_date = new JDate($data, $this->timezone);
		return $this_date->format($format, false, false);
	}

	function localizedDateJS($format, $data) {
		$this_date = new JDate($data, $this->timezone);
		return $this_date->format($format, false, false);
	}

	function localizedDateW($format, $data) {
		return date_i18n($format, $data);
	}

	function localizedDateWE($format, $data) {
		return date_i18n($format, $data);
	}

	function settingDelete($settingName) {
		$getListCall = 'settingDelete' . $this->callSuffix;
		return $this->$getListCall($settingName);
	}

	function settingDeleteJ($settingName) {
		$db = JFactory::getDBO();
		$query = '
			DELETE
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName);
		$db->setQuery($query);
		$db->query();

		$result = $db->loadResult();
		return $result;
	}

	function settingDeleteJH($settingName) {
		return $this->settingDeleteJ($settingName);
	}

	function settingDeleteJS($settingName) {
		global $pfcore;
		$db = JFactory::getDBO();
		$db->setQuery('
			DELETE
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName) . ' AND (shop_id = ' . $pfcore->shopID . ')');
		$result = $db->loadResult();
		return $result;
	}
  
	function settingDeleteW($settingName) {
		return delete_option($settingName);
	}

	function settingDeleteWe($settingName) {
		return delete_option($settingName);
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
			WHERE name = ' . $db->quote($settingName);
		$db->setQuery($query);
		$db->query();

		$result = $db->loadResult();
		return $result;
	}

	function settingGetJH($settingName) {
		return $this->settingGetJ($settingName);
	}

	function settingGetJS($settingName) {
		global $pfcore;
		if ($pfcore->shopID == -1)
			$shopCondition = '';
		else
			$shopCondition = ' AND (shop_id = ' . $pfcore->shopID . ')';
		$db = JFactory::getDBO();
		$db->setQuery('
			SELECT value
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName) . $shopCondition);
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
			SELECT id
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote( $settingName);
		$db->setQuery($query);
		$result = $db->loadResult();
		if (($result == null) || ($result == 0))
			$isNew = true;
		else
			$isNew = false;

		$setting = new stdClass();
		$setting->name = $settingName;
		$setting->value = $value;
		if ($isNew) {
			$setting->kind = 0;
			//$setting->ordering int,
			$setting->created = $date->toSql();
			$setting->created_by = $user->get('id');
		} else
			$setting->id = $result;
		//$setting->catid
		$setting->modified = $date->toSql();
		$setting->modified_by = $user->get('id');

		if ($isNew)
			$db->insertObject('#__cartproductfeed_options', $setting, 'id');
		else
			$db->updateObject('#__cartproductfeed_options', $setting, 'id');

	}

	function settingSetJH($settingName, $value) {
		return $this->settingSetJ($settingName, $value);
	}

	function settingSetJS($settingName, $value) {

		//Initialize
		global $pfcore;
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		//Does this value already exist?
		$query = '
			SELECT id
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName) . ' AND (shop_id = ' . $pfcore->shopID . ')';
		$db->setQuery($query);
		$result = $db->loadResult();
		if (($result == null) || ($result == 0))
			$isNew = true;
		else
			$isNew = false;

		$setting = new stdClass();
		$setting->name = $settingName;
		$setting->value = $value;
		if ($isNew) {
			$setting->kind = 0;
			$setting->created = $date->toSql();
			$setting->created_by = $user->get('id');
		} else
			$setting->id = $result;
		$setting->modified = $date->toSql();
		$setting->modified_by = $user->get('id');
		$setting->shop_id = $pfcore->shopID;

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

	public function trigger($eventname) {
		$getListCall = 'trigger' . $this->callSuffix;
		$this->$getListCall($eventname);
	}

	private function triggerJ($eventname) {
	}

	private function triggerJS($eventname) {
	}

	private function triggerW($eventname) {
		do_action($eventname);
	}

	private function triggerWE($eventname) {
		do_action($eventname);
	}

	public function triggerFilter($eventname, $param1 = null, $param2 = null, $param3 = null) {
		$getListCall = 'triggerFilter' . $this->callSuffix;
		return $this->$getListCall($eventname, $param1, $param2, $param3);
	}

	private function triggerFilterJ($eventname, $param1, $param2, $param3) {
	}

	private function triggerFilterJS($eventname, $param1, $param2, $param3) {
	}

	private function triggerFilterW($eventname, $param1, $param2, $param3) {
		return apply_filters($eventname, $param1, $param2, $param3);
	}

	private function triggerFilterWE($eventname, $param1, $param2, $param3) {
		return apply_filters($eventname, $param1, $param2, $param3);
	}

}

global $pfcore;
$pfcore = new PFeedCore();

?>