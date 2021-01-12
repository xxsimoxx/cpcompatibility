<?php
/**
 * Plugin Name: CPcompatibility
 * Plugin URI: https://software.gieffeedizioni.it
 * Description: Tweaks for working with CP: wpcli compatibility, plugin checks.
 * Version: 0.6.0
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Author: Gieffe edizioni srl
 * Author URI: https://www.gieffeedizioni.it/classicpress
 * Text Domain: cpc
 */

if (!defined('ABSPATH')) {
	die('uh');
}

/**
 *
 * Add auto updater
 * https://codepotent.com/classicpress/plugins/update-manager/
 *
 */
require_once 'inc/UpdateClient.class.php';

/**
 *
 * Here we fix wp-cli
 *
 */
require 'inc/fix-wpcli.php';

/**
 *
 * Here we change the plugins page to mark incompatible plugins
 *
 */
require 'inc/change-plugin-page.php';

/**
 *
 * Add a page in "tools" menu for displaying popular plugins and their
 * compatibility with ClassicPress (WP 4.9.10)
 *
 */
require 'inc/popular-menu.php';

// Load text domain
add_action('plugins_loaded', 'cpc_load_textdomain');
function cpc_load_textdomain() {
	load_plugin_textdomain('cpc', false, basename(dirname(__FILE__)).'/languages');
}

/*
 *
 * Add a statistics link in plugins page
 *
 */

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'cpcompatibility_pal');
function cpcompatibility_pal($links) {
	if (!function_exists('classicpress_version') || version_compare('1.1.0', classicpress_version(), '>')) {
		// ensure icon is displayed on CP < 1.1.0
		$link = '<a href="'.admin_url('tools.php?page=cpcompatibility').'" title="'.__('CP plugin compatibility', 'cpc').'"><i style="font: 16px dashicons; vertical-align: text-bottom;" class="dashicon dashicons-chart-bar"></i></a>';
	} else {
		$link = '<a href="'.admin_url('tools.php?page=cpcompatibility').'" title="'.__('CP plugin compatibility', 'cpc').'"><i class="dashicon dashicons-chart-bar"></i></a>';
	}
	array_unshift($links, $link);
	return $links;
}


?>