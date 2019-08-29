<?php
/*
* Plugin Name: CPcompatibility
* Plugin URI: https://www.gieffeedizioni.it/classicpress
* Description: Tweaks for working with CP: wpcli compatibility, plugin checks. 
* Version: 0.0.7
* License: GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Author: Gieffe edizioni srl
* Author URI: https://www.gieffeedizioni.it/classicpress
*/
if (!defined('ABSPATH')) die('uh');

ini_set('display_startup_errors', true);
error_reporting(E_ALL);
ini_set('display_errors', true);

// Fixing plugins
$fixed_plugins = array();

// Fix rankmath
add_action( 'admin_init', 'cpcompatibility_fix_rankmath' );
function cpcompatibility_fix_rankmath(){
	global $fixed_plugins;
	if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
		// lodash missing. Fixed in 1.0.31
		$rankmathinfo = get_plugin_data( ABSPATH . 'wp-content/plugins/seo-by-rank-math/rank-math.php');
		$rankmathversion = $rankmathinfo['Version'];
		if ( version_compare( $rankmathversion, '1.0.31', '<' ) ){
			if ( ! wp_script_is( 'lodash', 'registered' ) ) {
				array_push( $fixed_plugins, 'seo-by-rank-math' );
				wp_register_script( 'lodash', plugins_url( 'js/lodash.min.js', __FILE__ ));
			}
		}

	}
}

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

// function to get info from wpapi
function CPplugin_info( $plugin_name ){
    $plugin_url = "http://wpapi.org/api/plugin/$plugin_name.json";
    if ( false === ( $response = get_transient( 's_pinfo_'.$plugin_name ) ) ) {
		$response = wp_remote_retrieve_body( wp_remote_get( $plugin_url ) );
		set_transient( 's_pinfo_'.$plugin_name, $response, 12 * HOUR_IN_SECONDS );
	};
	$response = json_decode( $response, true );
	$requires = $response['requires'];
	$version = $response['version'];
	return "Requires wp $requires for version $version.";   
}


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

// This code adds a menu in the admin interface to list 
// plugins that have an update that don't support WP4
add_action('admin_menu', 'CPplugincheck_create_submenu');
function CPplugincheck_create_submenu() {
	add_submenu_page('tools.php', 'CP plugin compatibility', 'CP plugin compatibility', 'manage_options', 'cpcompatibility', 'CPplugincheck_page' );
}

function CPplugincheck_page() {
?>
<div class="wrap">
<H1>Plugins not ClassicPress-friendly</H1>
<H3>List of your plugins that - at their latest version - dropped support for WP 4</H3>
<?php
	$all_plugins = get_plugins();
	$pcount = 0;
	foreach( $all_plugins as $row => $innerArray ){
		$path = explode( "/", $row );
		$slug = $path[0];
		$message =  $innerArray{'Name'} . " " . CPplugin_info($slug) . "<br/>";
		if ( preg_match( '/equires wp 5/', $message ) ){
			echo $message;
			$pcount++;
		}  
	}
	if ( ! $pcount ){
		echo "(none)";
	}
	$listlimit=200;
	$browse="popular";
	//	'popular','featured','news' are the alternatives

?>

<H3>List of <?php echo $listlimit; ?> top <?php echo $browse; ?> plugins and required versions</H3>
<?php
	include ( ABSPATH . "wp-admin/includes/plugin-install.php" );
	$call_api = plugins_api( 'query_plugins',
		array(
			'browse' => $browse,
			'page' => '1',
			'per_page' => $listlimit,
			'fields' => array(
				'downloaded' => true,
				'rating' => false,
				'description' => false,
				'short_description' => false,
				'donate_link' => false,
				'tags' => false,
				'sections' => false,
				'homepage' => false,
				'added' => false,
				'last_updated' => false,
				'compatibility' => false,
				'tested' => false,
				'requires' => true,
				'downloadlink' => true,
			)
		)
	); 
    if ( is_wp_error( $call_api ) ) {
        echo '<pre>' . print_r( $call_api->get_error_message(), true ) . '</pre>';
    } else {
 		echo "<table><tr style='background-color:#0073AA; color:white;'><th>Slug<th>Downloaded<th>Requires</tr>\n";
 		$count=0;
		foreach ( $call_api->{'plugins'} as $element ) {
			if ( $count % 2 == 0 ){
				$color='style="background-color:white;"';
				} else {
				$color='';
			};
			$count++;
			if ( preg_match( '/^5/', $element->{'requires'} ) ){
				$color='style="background-color:orange;"';
			}
			echo "<tr $color><td>" . $element->{'slug'} . "<td>" . $element->{'downloaded'} . "<td>" . $element->{'requires'} . "</tr>\n";
		}	 
 		echo "</table></div>\n";
	}
}


?>
