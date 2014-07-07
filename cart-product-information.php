<?php
//Checks cart-product-feed version
function CPF_check_version()
{
	//taken from /include/update.php line 270
	$plugin_info = get_site_transient( 'update_plugins' );
	
	//we want to always display 'up to date', therefore we don't need the below check
	if ( !isset( $plugin_info->response[ CPF_PLUGIN_BASENAME ] ) )
		return ' | You are up to date';
	
	$CPF_WP_version = $plugin_info->response[ CPF_PLUGIN_BASENAME ]->new_version; //wordpress repository version
	//version_compare:
	//returns -1 if the first version is lower than the second, 
	//0 if they are equal, 
	//1 if the second is lower.
	$doUpdate = version_compare( $CPF_WP_version, FEED_PLUGIN_VERSION );
	//if current version is older than wordpress repo version
	if ( $doUpdate == 1 ) return ' | <a href=\'plugins.php\'>Out of date - please update</a>';
	//else, up to date
	return ' | You are up to date';
}

function CPF_print_info()
{
	echo 
	'<div class=\'version-style\'>
	 <a target="_blank" href="http://www.shoppingcartproductfeed.com">Product Site</a> | 
	 <a target="_blank" href="http://www.shoppingcartproductfeed.com/tos">How To\'s</a><br>
	 Version: ' . FEED_PLUGIN_VERSION . CPF_check_version() .'<br>
	 </div>';
}
