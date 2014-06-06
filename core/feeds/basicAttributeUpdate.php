<?php

  /********************************************************************
  Version 2.0
    Update Attributes
	By: Keneto 2014-05-08

  ********************************************************************/

//Join attribute and corresponding remote attributes together(attributes mapping)

class PattributeUpdate {

  public $attribute_tag = '';
  public $serviceName = '';

  function getFeedData() {

    $count = $_POST['count'];

    global $wpdb;
    $attr_table = $wpdb->prefix . 'options';

    for ($i = 0; $i < $count; $i++) {
        $wootext = "wooattr" . $i;

        $wooattr = $_POST[$wootext];
        $thisattr = $_POST[$this->attribute_tag . $i];

        $sql = "SELECT option_name, option_value FROM " . $attr_table . " WHERE option_name='" . $wooattr . "'";
        $result = $wpdb->get_results($sql);

        if (count($result) == 0) {
            $sql2 = "INSERT INTO " . $attr_table . "(option_name,option_value) VALUES('" . $wooattr . "', '" . $thisattr . "')";
            $wpdb->query($sql2);
        } else {
            $sql2 = "UPDATE " . $attr_table . " SET option_value='" . $thisattr . "' WHERE option_name='" . $wooattr . "'";
            $wpdb->query($sql2);
        }
    }
    $message = '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>' . $this->serviceName . ' attributes mapping changes saved!</strong></p></div>';
    echo $message;
  }

  function must_exit() {
    //Don't exit means advance to next page
    return false;
  }
}

?>