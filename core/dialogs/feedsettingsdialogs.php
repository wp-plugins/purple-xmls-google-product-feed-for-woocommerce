<?php

  /********************************************************************
  Version 2.0
    Settings for feeds
	By: Keneto 2014-05-05

  ********************************************************************/

class PFeedSettingsDialogs {

  function formatIntervalOption($value, $descriptor, $current_delay) {
    $selected = '';
	if ($value == $current_delay) {
	  $selected = ' selected="selected"';
	}
	return '<option value="' . $value . '"' . $selected . '>' . $descriptor . '</option>';
  }

  function fetchRefreshIntervalSelect() {
    $current_delay = get_option('cp_feed_delay');
    return '
					<select name="delay" class="select_medium" id="selectDelay">' . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(604800, '1 Week', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(86400, '24 Hours', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(43200, '12 Hours', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(21600, '6 Hours', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(3600, '1 Hour', $current_delay) . "\r\n" .
					  PFeedSettingsDialogs::formatIntervalOption(900, '15 Minutes', $current_delay) . "\r\n" . '
					</select>';
  }

  public function refreshTimeOutDialog() {
    global $wpdb;
    return '
      <div id="poststuff">
	    <div class="postbox" style="width: 98%;">
		  <h3 class="hndle">Interval at which feed auto-refreshes</h3>
		  <div class="inside export-target">
		    <table class="form-table">
		      <tbody>
			    <tr>
				  <th style="width:150px;"><label>Interval:</label></th>
				  <td><div id="updateSettingMessage"></div>' . PFeedSettingsDialogs::fetchRefreshIntervalSelect() . '
				  </td>
				  <td>
					<input class="navy_blue_button" style="float:right;" type="submit" value="Update" id="submit" name="submit" onclick="doUpdateSetting(\'selectDelay\', \'cp_feed_delay\')">
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