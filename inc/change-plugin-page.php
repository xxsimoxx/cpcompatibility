<?php
if (!defined('ABSPATH')) {
	die('uh');
}

function cp_plugin_info() {

	// Check if we already have a transient for that
	$plugin_info = get_transient('cpc_plugin_info');
	if ($plugin_info !== false) {
		return $plugin_info;
	}

	// Get an array of all installed plugins.
	$all_plugins = get_plugins();
	foreach ($all_plugins as $path => $value) {
		$slugs[] = dirname($path);
	}

	// Run a query to get informations
	include_once (ABSPATH.'wp-admin/includes/plugin-install.php');
	$queryfor = ['slugs' => $slugs];
	if (is_wp_error($plugin_info = plugins_api('plugin_information', $queryfor))) {
		return false;
	}

	foreach ($plugin_info as $slug => $info) {
		foreach ($info as $property_name => $property_value) {
			if (!in_array($property_name, ['version', 'requires'])) {
				continue;
			}
			unset($plugin_info->{$slug}[$property_name]);
		}
	}

	set_transient('cpc_plugin_info', $plugin_info, 6 * HOUR_IN_SECONDS);
	return $plugin_info;

}

add_filter('plugin_row_meta', 'cp_plugin_row_meta', 10, 2);
function cp_plugin_row_meta($links, $file) {
	$slug = dirname($file);
	$plugin_info = cp_plugin_info();
	if ($plugin_info === false) {
		return $links;
	}
	if (isset($plugin_info->{$slug}['version']) && version_compare($plugin_info->{$slug}['requires'], '5', '>=')) {
		/* translators: %1$s: WP version required, %2$s: plugin version. */
		array_push($links, '<span class="dashicons-before dashicons-warning">'.sprintf (__('Requires WordPress %1$s for version %2$s.<br>', 'cpc'), $plugin_info->{$slug}['requires'], $plugin_info->{$slug}['version']).'</span>');
	}
	return $links;
}

?>