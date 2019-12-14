<?php
if (!defined('ABSPATH')) die('uh');

/**
 * CPplugin_info( $plugin_name ) : array( $requires, $version )
 * 
 */
function CPplugin_info($plugin_name){
    if (($retval = get_transient('cpc_plugin_info_' . $plugin_name)) === false) {
    	include_once (ABSPATH . "wp-admin/includes/plugin-install.php");
    	$queryfor = ['slug' => $plugin_name];
    	$plugin_info = plugins_api('plugin_information', $queryfor);
    	if (!is_wp_error($plugin_info = plugins_api('plugin_information', $queryfor))) {
			$retval = [$plugin_info->requires, $plugin_info->version];
			set_transient('cpc_plugin_info_'.$plugin_name, $retval, 24 * HOUR_IN_SECONDS);
		} else {
			set_transient('cpc_plugin_info_'.$plugin_name, "", 24 * HOUR_IN_SECONDS);
			return "";
		}
	}
	return $retval;  
}

?>