<?php
if (!defined('ABSPATH')) {
	die('uh');
}

function CPplugin_info($plugin_name) {

	if (($retval = get_transient('cpc2_plugin_info_'.$plugin_name)) === false) {
		include_once (ABSPATH.'wp-admin/includes/plugin-install.php');

		$slugs = [];
		$all_plugins = get_plugins();
		foreach ($all_plugins as $path => $value) {
			$slugs[] = dirname($path);
		}

		$retval = '';
		$queryfor = ['slugs' => $slugs];
		if (is_wp_error($plugin_info = plugins_api('plugin_information', $queryfor))) {
			return $retval;
		}

		foreach ($all_plugins as $path => $value) {
			$slug = dirname($path);
			if (isset($plugin_info->{$slug}['requires']) && isset($plugin_info->{$slug}['version'])) {
				$info = [$plugin_info->{$slug}['requires'], $plugin_info->{$slug}['version']];
			} else {
				$info = '';
			}
			if ($slug === $plugin_name) {
				$retval = $info;
			}
			set_transient('cpc2_plugin_info_'.$slug, $info, 1 * HOUR_IN_SECONDS);
		}

	}

	return $retval;
}

?>