<?php

  /********************************************************************
  Version 2.0
    Settings for feeds
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05
		Note: Joomla version does not use this part of the core... it defines its own component screens
  ********************************************************************/

class PFeedSettingsDialogs {

  static function formatIntervalOption($value, $descriptor, $current_delay) {
    $selected = '';
	if ($value == $current_delay) {
	  $selected = ' selected="selected"';
	}
	return '<option value="' . $value . '"' . $selected . '>' . $descriptor . '</option>';
  }

  static function fetchRefreshIntervalSelect() {
    $current_delay = get_option('cp_feed_delay');
    return '
					<select name="delay" class="select_medium" id="selectDelay">' . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(604800, '1 Week', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(86400, '24 Hours', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(43200, '12 Hours', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(21600, '6 Hours', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(3600, '1 Hour', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(900, '15 Minutes', $current_delay) . "\r\n" .
						PFeedSettingsDialogs::formatIntervalOption(300, '5 Minutes', $current_delay) . "\r\n" . '
					</select>';
  }

  public static function refreshTimeOutDialog() {
    global $wpdb;
    return '
      <div id="poststuff">
	    <div class="postbox">
		  <h3 class="hndle">Interval at which feed auto-refreshes</h3>
		  <div class="inside export-target">
		    <table class="form-table">
		      <tbody>
			    <tr>
				  <th style="width:90px;"><label>Interval:</label></th>
				  <td style="width:120px;"><div id="updateSettingMessage"></div>' . PFeedSettingsDialogs::fetchRefreshIntervalSelect() . '
				  </td>
				  <td>
					<input class="button-primary" style="margin-left:30px; float:left;" type="submit" value="Update Interval" id="submit" name="submit" onclick="doUpdateSetting(\'selectDelay\', \'cp_feed_delay\')">
				  </td>
				</tr>
			  </tbody>
			</table>
		  </div>
		  </form>
		</div>
	  </div>';
  }
}


?>