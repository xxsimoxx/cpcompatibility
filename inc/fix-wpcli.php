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

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	class cp_plugin_install_latest_git_release {
	
		/**
		* Download the latest release of a plugin from GitHub.
		*
		* ## OPTIONS
		*
		* <user>
		* : The name of the person to greet.
		*
		* <repo>
		* : The name of the person to greet.
		*
		* [--activate]
		* : If set, the plugin will be activated immediately after install.
		*
		* [--force]
		* : If set, the command will overwrite any installed version of the plugin, without prompting
    	* for confirmation.
		*
		* ## EXAMPLES
		*
		*     wp plugin latestgit xxsimoxx cpvars
		*
		*/

		public function latestgit( $args, $assoc_args ) {
			if ( "" == $args[0] || "" == $args[1] ){
				WP_CLI::error( "You must supply a valid argument in the form <user/repo>." );
			};
			$git_repo = $args[0] . '/' . $args[1];
			$plugin_name = $args[1] . ".zip";
			$response = wp_remote_get( 'https://api.github.com/repos/' . $git_repo . '/releases/latest' , array( 'redirection' => 5 ) );
			if ( 200 === $response['response']['code'] ){
				$git_data = json_decode ( $response['body'], true );
				$opts = array( 'http'=>array( 'header' => "User-Agent:WPcli/1.0\r\n") ); 
				$context = stream_context_create( $opts );
				file_put_contents( $plugin_name, file_get_contents( $git_data['zipball_url'], false, $context ) );

				// BEGIN garbage
				/*
				* I'm trying to rename the first level folder of the zip from
				* something like user-repo-123456/ to repo/ to get the right 
				* folder name for the plugin, but in this process many assumptions
				* are likeky wrong. This also relies on the presence of ZipArchive.
				*/
				$pluginzip = new ZipArchive;
				$res = $pluginzip->open( $plugin_name, ZipArchive::CREATE );
				$renamethis =  rtrim( $pluginzip->getNameIndex(0), "/" );
				$renameto = preg_replace('/-/', '_', $args[1]);
				
				$i=0;
				while($item_name = $pluginzip->getNameIndex($i)){
					$pluginzip->renameIndex( $i, preg_replace("/^$renamethis/", $renameto, $item_name ) );
					$i++;
				}
				$pluginzip->close();
				// END garbage
				$activateoption = WP_CLI\Utils\get_flag_value($assoc_args, 'activate' );
				$activate = ( is_null( $activateoption ) ) ? "" : "--activate";
				$forceoption = WP_CLI\Utils\get_flag_value($assoc_args, 'force' );
				$force = ( is_null( $forceoption ) ) ? "" : "--force";
				$options = array(
					'return'     => false,   // Return 'STDOUT'; use 'all' for full object.
					'launch'     => false,  // Reuse the current process.
					'exit_error' => true,   // Halt script execution on error.
				);
				$plugins = WP_CLI::runcommand( "plugin install \"$plugin_name\" $activate $force", $options );
				
				unlink( $plugin_name );
			} else {
				WP_CLI::error( $response['response']['code'] . ": " . $response['response']['message'] );
			}
		}
		
	}
	
	WP_CLI::add_command( 'plugin', 'cp_plugin_install_latest_git_release' );
}

?>