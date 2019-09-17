<?php
if (!defined('ABSPATH')) die('uh');

/**
 * CPplugin_info( $plugin_name ) : array( $requires, $version )
 * 
 */
function CPplugin_info( $plugin_name ){
    $plugin_url = "http://wpapi.org/api/plugin/$plugin_name.json";
    if ( false === ( $response = get_transient( 's_pinfo_'.$plugin_name ) ) ) {
		$response = wp_remote_retrieve_body( wp_remote_get( $plugin_url ) );
		set_transient( 's_pinfo_'.$plugin_name, $response, 12 * HOUR_IN_SECONDS );
	};
	$response = json_decode( $response, true );
	$requires = $response['requires'];
	$version = $response['version'];
	return array( $requires, $version );  
}

?>