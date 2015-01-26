<?php

	/********************************************************************
	Version 3
		A Product List TXT Feed
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-11
	********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PAggXmlFeed extends PBasicFeed {

	function __construct () {
		parent::__construct();
		$this->providerName = 'AggXml';
		$this->providerNameL = 'aggxml';
		$this->fileformat = 'xml';
		$this->providerType = 1;
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

		/*foreach ($this->feeds as $index => $thisFeed) {
			$sourceFile = PFeedFolder::uploadFolder() . $thisFeed->type . '/' . $thisFeed->filename . '.' . $providers->getFileFormatByType($thisFeed->type);
			$sourceContent = file_get_contents($sourceFile);
			if ($index > 0) {
				$firstLine = strpos($sourceContent, "\n");
				$sourceContent = substr($sourceContent, $firstLine + 1);
				file_put_contents($this->filename, $sourceContent, FILE_APPEND);
			} else
				file_put_contents($this->filename, $sourceContent);
			$this->productCount += $thisFeed->product_count;
		}*/

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
		$content = '
  </products>';
		file_put_contents($this->filename, $content, FILE_APPEND);
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
		$this->filename = PFeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
		$this->file_url = PFeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
		$this->productCount = 0;
		$this->file_name_short = $file_name;

		$content = '<?xml version="1.0" encoding="UTF-8" ?>
  <products>';
		file_put_contents($this->filename, $content);

		global $pfcore;
		$data = $pfcore->settingGet('cpf_aggrfeedlist_' . $id);
		$data = explode(',', $data);
		$this->feeds = array();
		foreach($data as $datum)
			$this->feeds[$datum] = true;
  }

	/*public function loadFeedsJ() {
		$this->feeds = array();
	}

	public function loadFeedsJS() {
		$this->feeds = array();
	}

	public function loadFeedsW() {
		global $wpdb;
		$feed_table = $wpdb->prefix . 'cp_feeds';
		$feeds = implode(',', $this->feed_list);
		$sql = "
			SELECT id,type,filename,product_count 
			FROM $feed_table
			WHERE id in ($feeds)";
		$this->feeds = $wpdb->get_results($sql);
	}

	public function loadFeedsWe() {
		$this->loadFeedsW();
	}*/


}