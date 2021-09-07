<?php
if (!defined('ABSPATH')) {
	die('uh');
}

class CPCompatibilityNotice {

	private $plugin_info_cache = false;

	public function __construct() {
		add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 100, 2);
	}

	private function get_plugin_info() {

		// Check if we already have that in memory
		if ($this->plugin_info_cache !== false) {
			return $this->plugin_info_cache;
		}

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
		$query = ['slugs'  => $slugs];
		if (is_wp_error($plugin_info = plugins_api('plugin_information', $query))) {
			return false;
		}

		// Strip down unnecessary data
		foreach ($plugin_info as $slug => $info) {
			foreach ($info as $property_name => $property_value) {
				if (in_array($property_name, ['version', 'requires'])) {
					continue;
				}
				unset($plugin_info->{$slug}[$property_name]);
			}
			if (!empty($plugin_info->{$slug})) {
				continue;
			}
			unset($plugin_info->{$slug});
		}

		// Save transient...
		set_transient('cpc_plugin_info', $plugin_info, apply_filters('cpc_plugin_info_life', 6 * HOUR_IN_SECONDS));
		// and keep in memory.
		$this->plugin_info_cache = $plugin_info;

		return $plugin_info;

	}

	public function plugin_row_meta($links, $file) {

		$slug = dirname($file);

		$plugin_info = $this->get_plugin_info();

		// Check if we have data we need for the plugin
		if (!isset($plugin_info->{$slug}['version'])) {
			return $links;
		}

		// Ok, it don't require WP 5+
		if (version_compare($plugin_info->{$slug}['requires'], '5', '<')) {
			return $links;
		}

		// Check if a plugin is bumped to 999 or alike.
		$plugindata = get_plugin_data(WP_PLUGIN_DIR.'/'.$file, false, false);
		if (version_compare($plugindata['Version'], $plugin_info->{$slug}['version'], '>')) {
			return $links;
		}

		// Add the notice
		// Translators: %1$s is the WordPress version required. %2$s Is the plugin version requiring WordPress X.
		array_push($links, '<span class="dashicons-before dashicons-warning">'.sprintf (__('Requires WordPress %1$s for version %2$s', 'cpc'), $plugin_info->{$slug}['requires'], $plugin_info->{$slug}['version']).'</span>');
		return $links;
	}

}

new CPCompatibilityNotice();
