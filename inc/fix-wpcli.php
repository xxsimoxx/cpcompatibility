<?php
if (!defined('ABSPATH')) die('uh');

// Fix wp-cli behaviour on "core check-update"
if ( function_exists( 'classicpress_version' ) && defined( 'WP_CLI' ) && WP_CLI ) {
	// add a hook that runs before the command
	WP_CLI::add_hook( 'before_invoke:core check-update', 'cp_correct_check_update' );
}

function cp_correct_check_update( ) {
	// if we have ClassicPress
	if ( function_exists( 'classicpress_version' ) && WP_CLI ) {
		$gcu =  get_core_updates();
		if ( 'latest' == $gcu[0]->{'response'} ){ 
			WP_CLI::success( "ClassicPress is at the latest version." );
		} else {
			if ( $gcu ) {
				// Retrieve options in a very unusual way
				$cp_current_pid = getmypid();
				$cp_current_command = strtr ( trim(file_get_contents("/proc/$cp_current_pid/cmdline"), "\0"), "\0", " ");
				$cp_fields_match = array();
				if ( preg_match_all ( '/ --fields=([a-z,_]+)/' , $cp_current_command, $cp_fields_match ) > 0 ){
					$cp_arg_fields = $cp_fields_match[1][0] ;
				} else {
					$cp_arg_fields = "version,update_type,package_url";
				}
				if ( preg_match_all ( '/ --format=([a-z]+)/' , $cp_current_command, $cp_format_match ) > 0 ){
					$cp_format_fields = $cp_format_match[1][0];
				} else {
					$cp_format_fields = "table";
				}
				// $cp_version is only needed to evalutate "update_type"
				// Is the right way to find the path?
				include( get_home_path() . "/wp-includes/version.php" );
				$cp_table_output[0]["version"] = $gcu[0]->{'version'};
				$cp_table_output[0]["package_url"] = $gcu[0]->{'download'};
				// Don't break anything if $cp_version is null
				$cp_table_output[0]["update_type"] = ( isset ( $cp_version ) ? WP_CLI\Utils\get_named_sem_ver( $gcu[0]->{'version'}, $cp_version ) : "" );
				WP_CLI\Utils\format_items( $cp_format_fields, $cp_table_output, $cp_arg_fields );
			}
		};
		// then exit to prevent the core check-update command to 
		// continue his work
		exit;
	};
};

?>