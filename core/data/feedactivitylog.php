<?php

  /********************************************************************
  Make a database entry about the feed that just occurred
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-06-16

  ********************************************************************/

class PFeedActivityLog {

	function __construct ($feedIdentifier = '') {
		//When instantiated (as opposed to static calls) it means we need to log the phases
		//therefore, save the feedIdentifier
		$this->feedIdentifier = $feedIdentifier;
	}

	function __destruct() {
		global $pfcore;
		if (!empty($pfcore) && (strlen($pfcore->callSuffix) > 0)) {
			$deleteLogData = 'deleteLogData' . $pfcore->callSuffix;
			$this->$deleteLogData();
		}
	}

	/********************************************************************
	Add a record to the activity log for "Manage Feeds"
	********************************************************************/

	private static function addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount) {
		global $pfcore;
		$addNewFeedData = 'addNewFeedData' . $pfcore->callSuffix;
		PFeedActivityLog::$addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount);
	}

	private static function addNewFeedDataJ($category, $remote_category, $file_name, $file_path, $providerName, $productCount) {
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$db = JFactory::getDBO();

		$sql = 'SELECT COUNT(*) FROM #__cartproductfeed_feeds';
		$db->setQuery($sql);
		$db->query();
		$ordering = $db->loadResult() + 1;

		$newData = new stdClass();
		$newData->title = $file_name;
		$newData->category = $category;
		$newData->remote_category = $remote_category;
		$newData->filename = $file_name;
		$newData->url = $file_path;
		$newData->type = $providerName;
		$newData->product_count = $productCount;
		$newData->ordering = $ordering;
		$newData->created = $date->toSql();
		$newData->created_by = $user->get('id');
		//$newData->catid int,
		$newData->modified = $date->toSql();
		$newData->modified_by = $user->get('id');
		//$productCount
		$db->insertObject('#__cartproductfeed_feeds', $newData, 'id');
	}

	private static function addNewFeedDataJS($category, $remote_category, $file_name, $file_path, $providerName, $productCount) {

		global $pfcore;
		$shopID = $pfcore->shopID;

		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$db = JFactory::getDBO();

		$sql = 'SELECT COUNT(*) FROM #__cartproductfeed_feeds';
		$db->setQuery($sql);
		$db->query();
		$ordering = $db->loadResult() + 1;

		$indexOfHyphen = strpos($file_name, '-');
		if ($indexOfHyphen !== false)
			$title = substr($file_name, $indexOfHyphen + 1);
		else
			$title = $file_name;

		$newData = new stdClass();
		$newData->title = $title;
		$newData->category = $category;
		$newData->remote_category = $remote_category;
		$newData->filename = $file_name;
		$newData->url = $file_path;
		$newData->type = $providerName;
		$newData->product_count = $productCount;
		$newData->ordering = $ordering;
		$newData->created = $date->toSql();
		$newData->created_by = $user->get('id');
		//$newData->catid int,
		$newData->modified = $date->toSql();
		$newData->modified_by = $user->get('id');
		$newData->shop_id = $shopID;
		$newData->state = 1;
		//$productCount
		$db->insertObject('#__cartproductfeed_feeds', $newData, 'id');
	}

	private static function addNewFeedDataW($category, $remote_category, $file_name, $file_path, $providerName, $productCount) {
		global $wpdb;
		$feed_table = $wpdb->prefix . 'cp_feeds';
		$sql = "INSERT INTO $feed_table(`category`, `remote_category`, `filename`, `url`, `type`, `product_count`) VALUES ('$category','$remote_category','$file_name','$file_path','$providerName', '$productCount')";
		$wpdb->query($sql);
	}

	private static function addNewFeedDataWe($category, $remote_category, $file_name, $file_path, $providerName, $productCount) {
		PFeedActivityLog::addNewFeedDataW($category, $remote_category, $file_name, $file_path, $providerName, $productCount);
	}

	/********************************************************************
	Search the DB for a feed matching filename / providerName
	********************************************************************/

	public static function feedDataToID($file_name, $providerName) {
		global $pfcore;
		$feedDataToID = 'feedDataToID' . $pfcore->callSuffix;
		return PFeedActivityLog::$feedDataToID($file_name, $providerName);
	}

	private static function feedDataToIDJ($file_name, $providerName) {

		$db = JFactory::getDBO();
		$db->setQuery('
			SELECT id
			FROM #__cartproductfeed_feeds
			WHERE (filename=' . $db->quote($file_name) . ') AND (type=' . $db->quote($providerName) . ')');
		$result = $db->loadObject();
		if (!$result)
			return -1;

		return $result->id;

	}

	private static function feedDataToIDJS($file_name, $providerName) {

		global $pfcore;
		$shopID = $pfcore->shopID;

		$db = JFactory::getDBO();
		$db->setQuery('
			SELECT id
			FROM #__cartproductfeed_feeds
			WHERE (filename=' . $db->quote($file_name) . ') AND (type=' . $db->quote($providerName) . ') AND (shop_id = ' . (int) $shopID . ')');
		$result = $db->loadObject();
		if (!$result)
			return -1;

		return $result->id;

	}
  
	private static function feedDataToIDW($file_name, $providerName) {
		global $wpdb;
		$feed_table = $wpdb->prefix . 'cp_feeds';
		$sql = "SELECT * from $feed_table WHERE `filename`='$file_name' AND `type`='$providerName'";
		$list_of_feeds = $wpdb->get_results($sql, ARRAY_A);
		if ($list_of_feeds) {
			return $list_of_feeds[0]['id'];
		} else {
			return -1;
		}
	}

	private static function feedDataToIDWe($file_name, $providerName) {
		return PFeedActivityLog::feedDataToIDW($file_name, $providerName);
	}

	/********************************************************************
	Called from outside... this class has to make sure the feed shows under "Manage Feeds"
	********************************************************************/

	public static function updateFeedList($category, $remote_category, $file_name, $file_path, $providerName, $productCount) {
		$id = PFeedActivityLog::feedDataToID($file_name, $providerName);
		if ($id == -1)
			PFeedActivityLog::addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount);
		else
			PFeedActivityLog::updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount);
	}

	/********************************************************************
	Update a record in the activity log
	********************************************************************/

	private static function updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount) {
		global $pfcore;
		$updateFeedData = 'updateFeedData' . $pfcore->callSuffix;
		PFeedActivityLog::$updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount);
	}

	private static function updateFeedDataJ($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount) {

		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$db = JFactory::getDBO();

		$newData = new stdClass();
		$newData->id = $id;
		$newData->category = $category;
		$newData->remote_category = $remote_category;
		$newData->filename = $file_name;
		$newData->url = $file_path;
		$newData->type = $providerName;
		$newData->product_count = $productCount;
		$newData->modified = $date->toSql();
		$newData->modified_by = $user->get('id');

		//$productCount
		$db->updateObject('#__cartproductfeed_feeds', $newData, 'id');
	}

	private static function updateFeedDataJS($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount) {

		global $pfcore;
		$shopID = $pfcore->shopID;

		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$db = JFactory::getDBO();

		$newData = new stdClass();
		$newData->id = $id;
		$newData->category = $category;
		$newData->remote_category = $remote_category;
		$newData->filename = $file_name;
		$newData->url = $file_path;
		$newData->type = $providerName;
		$newData->product_count = $productCount;
		$newData->modified = $date->toSql();
		$newData->modified_by = $user->get('id');
		$newData->shop_id = $shopID;
		$newData->state = 1;
		//$productCount

		$db->updateObject('#__cartproductfeed_feeds', $newData, 'id');

	}

	private static function updateFeedDataW($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount) {
		global $wpdb;
		$feed_table = $wpdb->prefix . 'cp_feeds';
		$sql = "
			UPDATE $feed_table 
			SET 
				`category`='$category',
				`remote_category`='$remote_category',
				`filename`='$file_name',
				`url`='$file_path',
				`type`='$providerName',
				`product_count`='$productCount'
			WHERE `id`=$id";
		$wpdb->query($sql);
	}

	private static function updateFeedDataWe($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount) {
		PFeedActivityLog::updateFeedDataW($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount);
	}

	/********************************************************************
	Save a Feed Phase
	********************************************************************/

	function logPhase($activity) {
		global $pfcore;
		$pfcore->settingSet('cp_feedActivity_' . $this->feedIdentifier, $activity);
	}

	/********************************************************************
	Remove Log info
	********************************************************************/

	function deleteLogDataJ() {

	}

	function deleteLogDataJS() {

	}

	function deleteLogDataW() {
		delete_option('cp_feedActivity_' . $this->feedIdentifier);
	}

	function deleteLogDataWe() {
		delete_option('cp_feedActivity_' . $this->feedIdentifier);
	}

}

?>