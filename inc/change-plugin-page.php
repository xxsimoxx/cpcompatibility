<?php
if (!defined('ABSPATH')) die('uh');

// Modify installed plugin list to add an alert to plugin info
// if there is an update that don't support WP4
// or if this plugin fixed it
add_filter( 'plugin_row_meta', 'cp_plugin_row_meta', 10, 2 );
function cp_plugin_row_meta( $links, $file ) {
	global $fixed_plugins;
	$slug = dirname( $file );
	$version = CPplugin_info( $slug );
	if ( preg_match( '/wp 5/', $version ) ){
		array_push( $links, "<span class=' dashicons-before dashicons-warning'>$version</span>" ) ;
	}
	if ( in_array( $slug, $fixed_plugins ) ){
		array_push( $links, "<span class=' dashicons-before dashicons-hammer'> Fixed by cpcompatibility</span>" ) ;
	}
	return $links;
} 

?>