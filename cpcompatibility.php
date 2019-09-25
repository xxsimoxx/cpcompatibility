<?php
/*
 * Plugin Name: CPcompatibility
 * Plugin URI: https://github.com/xxsimoxx/cpcompatibility
 * Description: Tweaks for working with CP: wpcli compatibility, plugin checks. 
 * Version: 0.0.10
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Author: Gieffe edizioni srl
 * Author URI: https://www.gieffeedizioni.it/classicpress
 * Text Domain: cpc
 * GitHub Plugin URI: xxsimoxx/cpcompatibility
 */

if (!defined('ABSPATH')) die('uh');

/**
 *
 * General functions to get plugin information frof wpapi.org
 *
 */
require "inc/plugin-info.php";

/**
 *
 * Functios to fix various plugins
 *
 */
require "inc/fix-plugins.php";

/**
 *
 * Here we fix wp-cli
 *
 */
require "inc/fix-wpcli.php";

/**
 *
 * Here we change the plugins page to mark incompatible plugins
 *
 */
require "inc/change-plugin-page.php";

/**
 *
 * Add a page in "tools" menu for displaying popular plugins and their 
 * compatibility with ClassicPress (WP 4.9.10)
 *
 */
require "inc/popular-menu.php";

// Load text domain
add_action( 'plugins_loaded', 'cpc_load_textdomain' );
function cpc_load_textdomain() {
	load_plugin_textdomain( 'cpc', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

// check for updates
function cpc_update_link(){
	if ( is_plugin_active("github-updater/github-updater.php") ){
		return false;
		// let's github-updater handle this for us!
	};
	$slug = dirname( plugin_basename( __FILE__ ) );
	$plugin_info = get_plugin_data(__FILE__);
	$plugin_installed_version = $plugin_info['Version'];
	$git_repo = "xxsimoxx/" . $slug;
	if ( false === ( $plugin_current_version = get_transient( $slug . 'lastversion' ) ) ) {
		$response = wp_remote_get( 'https://api.github.com/repos/' . $git_repo . '/releases/latest' , array( 'redirection' => 5 ) );
		if ( 200 === $response['response']['code'] ){
			$git_data = json_decode ( $response['body'], true );
			$plugin_current_version = ltrim ( $git_data['tag_name'], 'v');
		} else
		{
			$plugin_current_version = null;
		};
		if ( ! is_null( $plugin_current_version ) ) {
			set_transient( $slug . 'lastversion', $plugin_current_version, DAY_IN_SECONDS );
		};
	};
	if ( version_compare( $plugin_current_version, $plugin_installed_version , '>' ) ){
		/*Translators: %s is the new version available */
		$messagestring =  sprintf( __( "NEW v%s", "cpc" ), $plugin_current_version );
		return '<a target="_blank" href="https://www.github.com/' . $git_repo . '/releases/latest">' . $messagestring . '</a>';
	} else {
		return false;
	}
};

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'cpc_update' );
function cpc_update( $links ) {
	$update_link = cpc_update_link();
	if ( $update_link ){
		array_push( $links, $update_link );
	};
	return $links;
}
?>


