<?php

/********************************************************************
 * Version 1.0
 * Update user's saved credential information for a particular provider.
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Tyler 2014-12-27
 ********************************************************************/

//To do: This should one day just use the existing update_setting script -KH

require_once dirname(__FILE__) . '/../../../../../../wp-load.php';

if (isset($_POST['remember'])) {
    $remember = $_POST['remember'];
    $user_id = $_POST['userid'];
    $provider = $_POST['provider'];
    update_user_meta($user_id, "cpf_remember_$provider", $remember);
    if ($remember == 'true') {
        foreach ($_POST as $key => $val) {
            if (!in_array($key, array('remember', 'provider', 'userid')))
                update_user_meta($user_id, "cpf_$key" . "_$provider", $val);
        }
    } else {
        foreach ($_POST as $key => $val) {
            if (!in_array($key, array('remember', 'provider', 'userid')))
                update_user_meta($user_id, "cpf_$key" . "_$provider", '');
        }
    }
}
