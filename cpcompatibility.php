<?php
/*
* Plugin Name: CPcompatibility
* Plugin URI: https://www.gieffeedizioni.it/classicpress
* Description: Tweaks for working with CP: wpcli compatibility, plugin checks. 
* Version: 0.0.9
* License: GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Author: Gieffe edizioni srl
* Author URI: https://www.gieffeedizioni.it/classicpress
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

?>
