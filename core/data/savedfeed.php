<?php

  /********************************************************************
	Version 2.0
		A feed (as realized in the database)
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
  ********************************************************************/

class PSavedFeed {

	function __construct($id) {
		global $pfcore;
		$feedLoader = 'feedLoader' . $pfcore->callSuffix;
		$this->$feedLoader($id);
	}

	private function feedLoaderJ($id) {
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__cartproductfeed_feeds WHERE id=$id");
		$feed_details = $db->loadObject();

		if (!isset($feed_details) || !isset($feed_details->type) || strlen($feed_details->type) == 0) {
			$feed_details = new stdClass();
			$feed_details->provider = '';
			$feed_details->category_id = 0;
			$feed_details->remote_category = 0;
			$feed_details->filename = '';
			$feed_details->url = '';
			$feed_details->own_overrides = false;
			$feed_details->feed_overrides = '';
			$feed_details->title = '';
			$feed_details->category = 0;
			$feed_details->type = '';
		}

		$this->id = $id;
		$this->provider = $feed_details->type;
		//$this->local_category = $feed_details->local_category;
		$this->category_id = $feed_details->category;
		$this->remote_category = $feed_details->remote_category;
		$this->filename = $feed_details->filename;
		$this->url = $feed_details->url;
		$this->own_overrides = $feed_details->own_overrides;
		$this->feed_overrides = $feed_details->feed_overrides;

		//Load the categories
		$this->local_category = '';
		$my_categories = explode(",", $this->category_id);
		$db->setQuery('
			SELECT a.virtuemart_category_id as id, b.category_name as title
			FROM #__virtuemart_categories a
			LEFT JOIN #__virtuemart_categories_en_gb b ON a.virtuemart_category_id = b.virtuemart_category_id
			GROUP BY a.virtuemart_category_id');
		$j_categories = $db->loadObjectList();
		foreach($j_categories as $this_category)
			if (in_array($this_category->id, $my_categories))
				$this->local_category .= $this_category->title . ', ';
		//Strip trailing comma
		$this->local_category = substr($this->local_category, 0, -2);
	}

	private function feedLoaderJS($id) {
		//Don't technically need shop_id here, but this does prevent a malicious user from supplying a feed_id he doesn't own
		global $pfcore;

		$shopID = (int) $pfcore->shopID;
		if ((strlen($shopID) > 0) && ($shopID > 0))
			$shopIDClause = " AND (shop_id = $shopID)";
		else
			$shopIDClause = '';

		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__cartproductfeed_feeds WHERE (id=$id) $shopIDClause");
		$feed_details = $db->loadObject();

		if (!isset($feed_details) || !isset($feed_details->type) || strlen($feed_details->type) == 0) {
			$this->provider = '';
			$this->category_id = 0;
			$this->remote_category = 0;
			$this->filename = '';
			$this->url = '';
			$this->own_overrides = false;
			$this->feed_overrides = '';
			$this->title = '';
			return;
		}

		$this->id = $id;
		$this->provider = $feed_details->type;
		//$this->local_category = $feed_details->local_category;
		$this->category_id = $feed_details->category;
		$this->remote_category = $feed_details->remote_category;
		$this->filename = $feed_details->filename;
		$this->url = $feed_details->url;
		$this->own_overrides = $feed_details->own_overrides;
		$this->feed_overrides = $feed_details->feed_overrides;
		$this->title = $feed_details->title;

		//Load the categories
		$this->local_category = '';
		$my_categories = explode(',', $this->category_id);
		$db->setQuery('
			SELECT id, title
			FROM #__rapidcart_categories');
		$j_categories = $db->loadObjectList();
		foreach($j_categories as $this_category)
			if (in_array($this_category->id, $my_categories))
				$this->local_category .= $this_category->title . ', ';
		//Strip trailing comma
		$this->local_category = substr($this->local_category, 0, -2);
	}

	private function feedLoaderW($id) {
		global $wpdb;
		$feed_table = $wpdb->prefix . 'cp_feeds';

		//Go load the feed in question
		$sql = "SELECT f.*,description as local_category FROM $feed_table as f LEFT JOIN $wpdb->term_taxonomy t on ( f.category=term_id and t.taxonomy='product_cat'  ) WHERE f.id=$id";
		$feed = $wpdb->get_results($sql);
		$feed_details = $feed[0];

		$this->id = $id;
		$this->provider = $feed_details->type;
		//$this->local_category = $feed_details->local_category;
		$this->category_id = $feed_details->category;
		$this->remote_category = $feed_details->remote_category;
		$this->filename = $feed_details->filename;
		$this->url = $feed_details->url;
		$this->own_overrides = $feed_details->own_overrides;
		$this->feed_overrides = $feed_details->feed_overrides;

		//Load the categories
		$this->local_category = '';
		$my_categories = explode(",", $this->category_id);
		$sql = "
			SELECT tdesc.term_id, description, tname.name
			FROM $wpdb->term_taxonomy tdesc
			LEFT JOIN $wpdb->terms tname ON (tdesc.term_id = tname.term_id)
			WHERE tdesc.taxonomy='product_cat'";
		$wp_categories = $wpdb->get_results($sql);
		foreach($wp_categories as $this_category)
			if (in_array($this_category->term_id, $my_categories))
				$this->local_category .= $this_category->name . ', ';

		//Strip trailing comma
		$this->local_category = substr($this->local_category, 0, -2);
		
	}

	private function feedLoaderWe($id) {
		$this->feedLoaderW($id);
	}

	public function fullFilename() {
		//return "file.ext" since that takes a bit of computation
		$ext = '.xml';
		if ( strpos( strtolower($this->url), '.csv' ) > 0 )
		  $ext = '.csv';
		return $this->filename . $ext;
	}

	public function save_ownoverrides($value) {
		global $pfcore;
		$feedOverrideSaver = 'feedOverrideSaver' . $pfcore->callSuffix;
		$this->$feedOverrideSaver($value);
	}

	private function feedOverrideSaverJ($value) {
		$db = JFactory::getDBO();
		$data = new stdClass();
		$data->id = $this->id;
		$data->own_overrides = true;
		$data->feed_overrides = $value;
		$db->updateObject('#__cartproductfeed_feeds', $data, 'id');
	}

	private function feedOverrideSaverJS($value) {
		$this->feedOverrideSaverJ($value);
	}

	private function feedOverrideSaverW($value) {
		//WordPress doesn't need due to AJAX differences
	}

	private function feedOverrideSaverWe($value) {
		//WordPress doesn't need due to AJAX differences
	}

}

?>