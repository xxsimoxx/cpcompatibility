<?php

if (!defined('ABSPATH')) {
	die('uh');
}

// This code adds a menu in the admin interface to list
// plugins that have an update that don't support WP4

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

// Class for display statistics page.
class CPC_List_Table extends \WP_List_Table {

	// Output columns definition.
	function get_columns() {
		return [
			'compatible' => esc_html__('Compatible', 'cpc'),
			'name'       => esc_html__('Name', 'cpc'),
			'version'    => 'version',
			'link'       => 'link',
			'minimum'    => esc_html__('Minimum required WP version', 'cpc'),
			'downloaded' => esc_html__('Downloaded', 'cpc'),
		];
	}

	// Output hidden columns.
	function get_hidden_columns() {
		return [
			'version',
			'link',
		];
	}

	// Output sortable columns.
	function get_sortable_columns() {
		return [
			'compatible'  => ['compatible', false],
			'name'        => ['name',       false],
			'downloaded'  => ['downloaded', false],
			'minimum'     => ['minimum',    false],
		];
	}

	// Callable to be used with usort.
	function reorder($a, $b) {
		// If no orderby or wrong orderby, default to plugin or theme name.
		$orderby = (!empty($_GET['orderby']) && in_array($_GET['orderby'], ['name', 'downloaded', 'minimum', 'compatible'], true)) ? $_GET['orderby'] : 'downloaded';
		// If no order or wrong order, default to asc.
		$order = (!empty($_GET['order']) && $_GET['order'] !== 'desc') ? 'asc' : 'desc';

		// Properly order numeric values or reorder text case-insensitive.
		if ($orderby === 'downloaded') {
			$result = $a[$orderby] - $b[$orderby];
		} elseif ($orderby === 'minimum') {
			$result = version_compare($a[$orderby], $b[$orderby]);
		} else {
			$result = strcasecmp($a[$orderby], $b[$orderby]);
		}
		if ($result === 0) {
			$result = $a['downloaded'] - $b['downloaded'];
		}
		return ( $order === 'asc' ) ? $result : -$result;
	}

	// Define columns output.
	function column_default($item, $column_name) {
		return $item[$column_name];
	}

	function column_name($item) {
		$class = '';
		if (preg_match('/^5/', $item['minimum'])) {
			$class = 'cpc-bad-news';
		}

		if ($item['link'] !== '') {
			$name = '<span class="row-title '.$class.'"><a href="'.$item['link'].'" target="_blank">'.$item['name'].'</a> v. '.$item['version'].'</span>';
		} else {
			$name = '<span class="row-title '.$class.'">'.$item['name'].' v. '.$item['version'].'</span>';
		}
		return sprintf('%1$s', $name);
	}

	function column_downloaded($item) {
		$count = number_format($item['downloaded'], 0, '', _x(',', 'thousands separator', 'cpc'));
		return sprintf('%1$s', $count);
	}

	function column_compatible($item) {
		$class = '<i class="dashicons cpc-'.$item['compatible'].'-compatible"></i>';
		return sprintf('%1$s', $class);
	}

	function load_data() {
		if (($saved = get_transient('cpc_popular'))) {
			return $saved;
		}
		include_once(ABSPATH.'wp-admin/includes/plugin-install.php');
		$iterations = apply_filters('cpc_popular_plugin_API_iterations', 2);
		$list = [];
		for ($i = 1; $i <= $iterations; $i++) {
			$call_api = plugins_api('query_plugins', [
					'browse'   => 'popular',
					'page'     => $i,
					'per_page' => apply_filters('cpc_popular_plugin_API_per_page', 250),
					'fields'   => [
						'downloaded'        => true,
						'rating'            => false,
						'description'       => false,
						'short_description' => false,
						'donate_link'       => false,
						'tags'              => false,
						'sections'          => false,
						'homepage'          => true,
						'added'             => false,
						'last_updated'      => false,
						'compatibility'     => false,
						'tested'            => false,
						'requires'          => true,
						'downloadlink'      => true,
					],
				]
			);
			$list = array_merge($list, $call_api->{'plugins'});
		}

		$data = [];
		foreach ($list as $plugin) {
			$data[] = [
				'compatible' => (preg_match('/^5/', $plugin->requires) === 1) ? 'not' : 'is',
				'name'       => $plugin->name,
				'version'    => $plugin->version,
				'link'       => $plugin->homepage,
				'downloaded' => $plugin->downloaded,
				'minimum'    => $plugin->requires,
			];
		}

		set_transient('cpc_popular', $data, 3600);
		return $data;

	}


	// Prepare our columns and insert data.
	function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [$columns, $hidden, $sortable];

		$alldata = $this->load_data();
		usort($alldata, [&$this, 'reorder']);

		if (isset($_GET['s']) && $_GET['s'] !== '') {
			foreach ($alldata as $key => &$value) {
				if (strpos(strtoupper($value['name']), strtoupper($_GET['s'])) === false) {  //phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
					unset($alldata[$key]);
				}
			}
		}

		$per_page = cpc_popular_per_page();
		$total_items = count($alldata);
		$current_page = $this->get_pagenum();

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page ,
		]);

		$this->items = array_slice($alldata, (($current_page - 1) * $per_page), $per_page);
	}

}

function cpc_popular_per_page() {
	$user_meta = get_user_meta(get_current_user_id(), 'cpc_popular_per_page');
	if (isset($user_meta[0])) {
		return $user_meta[0];
	}
	return apply_filters('cpc_popular_plugin_per_page', 10);
}

add_action('admin_menu', 'cpc_popular_submenu');
function cpc_popular_submenu() {
	$cpc_page_name = __('CP plugin compatibility', 'cpc');
	$cpc_page = add_submenu_page('tools.php', $cpc_page_name, $cpc_page_name, 'manage_options', 'cpcompatibility', 'cpc_popular_plugin_page');
}

function cpc_popular_plugin_page() {
	echo '<div class="wrap">';
	echo '<h1>'.__('CPcompatibility', 'cpc').'</h1>';
	echo '<h2>'.__('Most popular plugins from WordPress.org', 'cpc').'</h2>';
	$CPCListTable = new CPC_List_Table();
	$CPCListTable->prepare_items();
	echo '<form method="get">';
	echo '<input type="hidden" name="page" value="cpcompatibility" />';
	$CPCListTable->search_box(__('Search'), 'name');
	echo '</form>';
	$CPCListTable->display();
	echo '</div>';
}

add_action('load-tools_page_cpcompatibility', 'cpc_screen_options');
function cpc_screen_options() {
	$options = [
		'label'		=> __('Number of items per page:'),
		'default'	=> apply_filters('cpc_popular_plugin_per_page', 10),
		'option'	=> 'cpc_popular_per_page',
	];
	add_screen_option('per_page', $options);
}

add_filter('set-screen-option', 'cpc_set_screen_option', 10, 3);
function cpc_set_screen_option($status, $option, $value) {
	if ($option === 'cpc_popular_per_page') {
		return $value;
	}
}


add_action('admin_enqueue_scripts', 'cpc_wp_admin_style');
function cpc_wp_admin_style($hook) {
	if ($hook !== 'tools_page_cpcompatibility') {
		return;
	}
	wp_enqueue_style('cpcompatibility_css', plugins_url('../css/cpcompatibility.css', __FILE__));
}




