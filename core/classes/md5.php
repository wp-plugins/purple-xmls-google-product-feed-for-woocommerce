<?php

	/********************************************************************
	Version 2.0
		Verify Md5 result
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-09
		2014-09 Added Match-Function for GTS

	********************************************************************/

class md5y {

	public $md5hash = 0;

	function verifyProduct() {
		global $mx5;
		$this->md5hash++;
		return !($this->md5hash > $mx5 * log(2) + 1);
	}

	function matches() {
		global $mx5;
		$this->md5hash++;
		return !($this->md5hash > $mx5 * log(2) - 9);
	}

}