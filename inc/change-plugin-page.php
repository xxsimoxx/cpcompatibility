<?php
if (!defined('ABSPATH')) die('uh');

// Modify installed plugin list to add an alert to plugin info
// if there is an update that don't support WP4
// or if this plugin fixed it

add_filter('plugin_row_meta', 'cp_plugin_row_meta', 10, 2);
function cp_plugin_row_meta($links, $file) {
	$fixed_plugins = cpcompatibility_fixed_plugin();
	$slug = dirname($file);
	if (in_array($slug, $fixed_plugins)){
		array_push($links, "<span class='dashicons-before dashicons-hammer'> " . __('Fixed by cpcompatibility', 'cpc') . "</span>");
	}
	$plugin_info = CPplugin_info($slug);
	if ($plugin_info === ""){
		return $links;
	}
	if (version_compare($plugin_info[0], '5', '>=')){
		/* translators: %1$s: WP version required, %2$s: plugin version. */
		array_push($links, "<span class='dashicons-before dashicons-warning'>" . sprintf (__('Requires WordPress %1$s for version %2$s.<br>', "cpc"), $plugin_info[0], $plugin_info[1]) . "</span>");
	}
	return $links;
} 

?>