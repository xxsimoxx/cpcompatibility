<?php
/*
* Plugin Name: CPcompatibility
* Plugin URI: https://www.gieffeedizioni.it/
* Description: Tweaks for working with CP: wpcli compatibility, plugin checks. 
* Version: 0.01
* License: GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Author: Gieffe edizioni srl
* Author URI: https://www.gieffeedizioni.it
*/
if (!defined('ABSPATH')) die('uh');

// Fixing plugins
$fixed_plugins = array();
array_push( $fixed_plugins, 'seo-by-rank-math' );

// Fix rankmath
if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
	if ( ! wp_script_is( 'lodash', 'registered' ) ) {
		wp_register_script( 'lodash', plugins_url( 'js/lodash.min.js', __FILE__ ));
		$fixed_plugins[] = 'seo-by-rank-math';
	}
};

// Fix wp-cli behaviour on "core check-update"
if ( function_exists( 'classicpress_version' ) && defined( 'WP_CLI' ) && WP_CLI ) {
	// add a hook that runs before the command
	WP_CLI::add_hook( 'before_invoke:core check-update', 'cp_correct_check_update' );
}

function cp_correct_check_update() {
	// if we have ClassicPress
	if ( function_exists( 'classicpress_version' ) ) {
		$gcu =  get_core_updates();
		if ( 'latest' == $gcu[0]->{'response'} ){
			WP_CLI::success( "ClassicPress is up-to-date." );
		} else {
			WP_CLI::warning( "ClassicPress needs you: response=" . $gcu[0]->{'response'} . "." );
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

add_action('admin_menu', 'CPplugincheck_create_menu');
function CPplugincheck_create_menu() {
	add_menu_page('CP plugin compatibility', 'CP plugin compatibility', 'administrator', __FILE__, 'CPplugincheck_page' ,'dashicons-thumbs-up' );
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
?>

<H3>List of <?php echo $listlimit; ?> Popular plugins and required versions</H3>
<?php
	include ( ABSPATH . "wp-admin/includes/plugin-install.php" );
	$call_api = plugins_api( 'query_plugins',
		array(
			'browse' => 'popular',
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
