<?php

  /********************************************************************
  Version 2.0
    Update Attributes
	By: Keneto 2014-05-08

  ********************************************************************/

//Join attribute and corresponding remote attributes together(attributes mapping)

require_once 'basicAttributeUpdate.php';
 
class PattributeUpdateAmazonFeed extends PattributeUpdate {

  function __construct() {
    $this->attribute_tag = 'amazonattr';
	$this->serviceName = 'Amazon';
  }

  //Amazon's is a little unique
  function getFeedData() {

    $count = $_POST['count'];
    global $wpdb;
    $attr_table = $wpdb->prefix . 'options';

    for ($i = 0; $i < $count; $i++) {
        $wootext = "wooattr" . $i;
        $amazontext = "amazonattr" . $i;

        $wooattr = $_POST[$wootext];
        $wooattr = "amazon_pa_" . $wooattr;
        $amazonattr = $_POST[$amazontext];
        $sql = "SELECT option_name, option_value FROM " . $attr_table . " WHERE option_name='" . $wooattr . "'";
        $result = $wpdb->get_results($sql);

        if (count($result) == 0) {
            $sql2 = "INSERT INTO " . $attr_table . "(option_name,option_value) VALUES('" . $wooattr . "', '" . $amazonattr . "')";
            $wpdb->query($sql2);
        } else {
            $sql2 = "UPDATE " . $attr_table . " SET option_value='" . $amazonattr . "' WHERE option_name='" . $wooattr . "'";
            $wpdb->query($sql2);
        }
    }
    $message = '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Amazon attributes mapping changes saved!</strong></p></div>';
    echo $message;
  }

}

?>