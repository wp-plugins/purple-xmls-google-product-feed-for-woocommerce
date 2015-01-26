<?php

  /********************************************************************
  Version 2.0
    Load extra, related product data.
		For WordPress, this involves a trip to the taxonomy table
		For everyone else, not yet implemented
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-14
  ********************************************************************/

class PProductSupplementalData {

	public $data = array();
	public $initialized = false;
	public $taxonomy = '';

	public function __construct($taxonomy) {
		$this->taxonomy = $taxonomy;
	}

	public function check($attributeName, $item) {

		if (!$this->initialized)
			$this->getData();

		foreach($this->data as $datum) {
			if ($datum->id == $item->id) {
				if (strlen($datum->name) > 0)
					$item->attributes[$attributeName] = $datum->name;
				break;
			}
		}

	}

	public function getData() {
		$this->initialized = true;
		global $pfcore;
		$proc = 'getData' . $pfcore->callSuffix;
		return $this->$proc();
	}

	public function getDataJ() {
	}

	public function getDataJS() {
	}

	public function getDataW() {
		global $wpdb;
		$sql = "
			SELECT id, post_title, post_name, $wpdb->term_taxonomy.term_taxonomy_id, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name
			FROM $wpdb->posts
			LEFT JOIN $wpdb->term_relationships on ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
			WHERE $wpdb->posts.post_type='product'
			AND $wpdb->term_taxonomy.taxonomy = '$this->taxonomy'
		";
		$this->data = $wpdb->get_results($sql);

	}

	public function getDataWe() {
	}

}