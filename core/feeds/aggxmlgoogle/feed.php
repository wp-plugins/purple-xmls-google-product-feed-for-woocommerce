<?php

	/********************************************************************
	Version 3
		A Product List TXT Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-11
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAggXmlGoogleFeed extends PAggregateFeed {

	public $shopID = 0;

	function __construct ($saved_feed = null) {

		parent::__construct();

		$this->providerName = 'AggXmlGoogle';
		$this->providerNameL = 'aggxmlgoogle';
		$this->fileformat = 'xml';
		$this->providerType = 1;

		global $pfcore;
		$loadInitialSettings = 'loadInitialSettings' . $pfcore->callSuffix;
		$this->$loadInitialSettings($saved_feed);

	}

	function getFeedData($category, $remote_category, $file_name, $saved_feed = null) {

		$this->logActivity('Initializing...');

		global $message;
		global $pfcore;
		$providers = new PProviderList();

		//if ($saved_feed != null) {
			//Fill $this->feed_list from saved_feed? No
		//}
		//$loadFeeds = 'loadFeeds' . $pfcore->callSuffix;
		//$this->$loadFeeds();

		//$this->initializeFeed($category, $remote_category);

		$this->logActivity('Loading paths...');
		if (!$this->checkFolders())
			return;

		$file_url = PFeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
		$file_path = PFeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
			
		//Special (WordPress): where admin is https and site is http, path to wp-uploads works out incorrectly as https
		//  we check the content_url() for https... if not present, patch the file_path
		if (($pfcore->cmsName == 'WordPress') && (strpos($file_path, 'https://') !== false) && (strpos(content_url(), 'https') === false))
			$file_path = str_replace('https://', 'http://', $file_path);
		
		//Create the Feed
		$this->logActivity('Creating feed data');
		$this->filename = $file_url;
		$this->productCount = 0;

		$content = '<?xml version="1.0" encoding="UTF-8" ?>
			<messages>
				<message>Place-Holder File</message>
				<message>This file will be replaced next refresh</message>
				<message>To manually fill this data, go to manage feeds and click "Update Now"</message>
			</messages>
		';
		file_put_contents($this->filename, $content);

		$this->logActivity('Updating Feed List');
		PFeedActivityLog::updateFeedList($category, $remote_category, $file_name, $file_path, $this->providerName, $this->productCount);

		//Save the feedlist
		$id = PFeedActivityLog::feedDataToID($file_name, $this->providerName);
		$pfcore->settingSet('cpf_aggrfeedlist_' . $id, implode(',', $this->feed_list));

		if ($this->productCount == 0) {
			//$this->message .= '<br>No products returned';
			//return;
		}

		$this->success = true;
  }

  function finalizeAggregateFeed() {
/*		
		$content = '
  </products>';
*/
   $content = '
   </channel>
</rss>';
		file_put_contents($this->filename, $content, FILE_APPEND);
		global $pfcore;
		if ($this->shopID > 0)
			$pfcore->shopID = $this->shopID;

		PFeedActivityLog::updateFeedList('n/a', 'n/a', $this->file_name_short, $this->file_url, $this->providerName, $this->productCount);
  }

	function aggregateProductSave($id, $product, $product_text) {
		//fwrite($this->fileHandle, $product_text);
		if (isset($this->feeds[$id])) {
			file_put_contents($this->filename, $product_text, FILE_APPEND);
			$this->productCount++;
		}
	}

  function initializeAggregateFeed($id, $file_name) {

		parent::initializeAggregateFeed($id, $file_name);

/* 
		$content = '<?xml version="1.0" encoding="UTF-8" ?>
  <products>';
 */

  //Google:
  $content = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">
  <channel>
    <title>' . $file_name . '</title>
    <link><![CDATA[' . $this->file_url . ']]></link>
    <description>' . $file_name . '</description>';

		file_put_contents($this->filename, $content);

  }

	function loadInitialSettingsJ($saved_feed) {
	}

	function loadInitialSettingsJS($saved_feed) {

		global $pfcore;

		//Check if the feed_list is available from input
		$input = JFactory::getApplication()->input;
		$this->feed_list = $input->get('feed_ids', array(), 'array');
		$db = JFactory::getDBO();

		//Convert feed_list into feeds (array of boolean)
		$this->feeds = array();

		//If we received a feedlist, assign the shop as the first shop in the feedlist
		if (count($this->feed_list) > 0) {
			$db->setQuery('SELECT shop_id FROM #__cartproductfeed_feeds WHERE id = ' . (int) $this->feed_list[0]);
			$pfcore->shopID = $db->loadResult();
			$this->shopID = $pfcore->shopID;
		}

		//if (count($this->feeds) == 0) {
		if ($saved_feed != null) {
			//By-pass settingGet since shopID not known
			//$data = $pfcore->settingGet('cpf_aggrfeedlist_' . $saved_feed->id);
			$db->setQuery('SELECT value, shop_id FROM #__cartproductfeed_options WHERE name = ' . $db->quote('cpf_aggrfeedlist_' . $saved_feed->id ) );
			$result = $db->loadObject();
			$data = explode(',', $result->value);
			foreach($data as $datum)
				$this->feeds[$datum] = true;
			//Save shopID for later
			$this->shopID = $result->shop_id;
		}

	}

	function loadInitialSettingsW($saved_feed) {
	}

	function loadInitialSettingsWe($saved_feed) {
	}

	/*public function loadFeedsJ() {
		$this->feed_list = array();
	}

	public function loadFeedsJS() {
		$this->feed_list = array();
	}

	public function loadFeedsW() {
		global $wpdb;
		$feed_table = $wpdb->prefix . 'cp_feeds';
		$feed_list = implode(',', $this->feed_list);
		$sql = "
			SELECT id,type,filename,product_count 
			FROM $feed_table
			WHERE id in ($feed_list)";
		$this->feeds = $wpdb->get_results($sql);
	}

	public function loadFeedsWe() {
		$this->loadFeedsW();
	}*/


}