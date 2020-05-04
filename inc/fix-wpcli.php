<?php
if (!defined('ABSPATH')) {
	die('uh');
}

// Fix wp-cli to have $cp_version globalized.
if (defined('WP_CLI') && WP_CLI) {
	WP_CLI::add_hook('before_wp_config_load', 'cp_globalize');
}

function cp_globalize() {
	if (isset($GLOBALS['cp_version'])) {
		// It's already fixed somewhere, do nothing.
		return;
	}
	global $cp_version;
	require ABSPATH.WPINC.'/version.php';
}



// Fix wp-cli behaviour on "core check-update".
if (function_exists('classicpress_version') && defined('WP_CLI') && WP_CLI) {
	// Add a hook that runs before the command.
	// This function must exit so the real "core check-update" is not run,
	WP_CLI::add_hook('before_invoke:core check-update', 'cp_correct_core_check_update');
}

function cp_correct_core_check_update() {

	// Check for updates. Bail on error.
	// When playing with versions, an empty array is returned if it's not on api.
	if (($core_updates = get_core_updates()) === false || $core_updates === []) {
		WP_CLI::error('Something went wrong checking for updates.');
		exit;
	}

	// We are on latest.
	if ($core_updates[0]->{'response'} === 'latest') {
		WP_CLI::success('ClassicPress is at the latest version.');
		exit;
	}

	// Standard options.
	$arg_fields = 'version,update_type,package_url';
	$format_fields = 'table';

	// Retrieve command line options and parse them.
	global $argv;
	$current_command = implode(' ', $argv);

	$fields_match = [];
	if (preg_match_all('/ --fields=([a-z,_]+)/', $current_command, $fields_match) > 0) {
		$arg_fields = $fields_match[1][0];
	}

	$field_match = [];
	if (preg_match_all('/ --field=([a-z_]+)/', $current_command, $field_match) > 0) {
		$arg_fields = $field_match[1][0];
	}

	$cp_format_match = [];
	if (preg_match_all('/ --format=([a-z]+)/', $current_command, $format_match) > 0) {
		$format_fields = $format_match[1][0];
	}

	$minor = preg_match('/ --minor */', $current_command);

	$major = preg_match('/ --major */', $current_command);

	// Put $cp_version into scope.
	global $cp_version;

	/*
	// Tests for multiple response.
	$core_updates[1]->{'version'} = '1.0.1';
	$core_updates[2]->{'version'} = '1.1.0';
	$core_updates[3]->{'version'} = '9.0.0';
	*/

	// Prepare output array.
	$table_output = [];

	// Loop in the update list.
	foreach ($core_updates as $index => $update) {

		// Get update type and skip if options tells to.
		$type = WP_CLI\Utils\get_named_sem_ver($update->{'version'}, $cp_version);
		if (($major === 1) && ($type !== 'major')) {
			continue;
		}
		if (($minor === 1) && ($type === 'patch')) {
			continue;
		}

		$table_output[] = [
			'version'     => $update->{'version'},
			'package_url' => $update->{'download'},
			'update_type' => $type,
		];

	}

	// Check if the filters left no updates.
	if (empty($table_output)) {
		WP_CLI::success('ClassicPress is at the latest version.');
		exit;
	}

	// Render output.
	WP_CLI\Utils\format_items($format_fields, $table_output, $arg_fields);

	// Exit to prevent the core check-update command to continue his work.
	exit;
}

?>