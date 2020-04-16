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
		$columns = [
			'name'       => esc_html__('Name', 'cpc'),
			'version'    => 'version',
			'link'       => 'link',
			'minimum'    => esc_html__('Minimum required WP version', 'cpc'),
			'downloaded' => esc_html__('Downloaded', 'cpc'),
		];
		return $columns;
	}

	// Output hidden columns.
	function get_hidden_columns() {
		$hidden_columns = [
			'version',
			'link',
		];
		return $hidden_columns;
	}

	// Output sortable columns.
	function get_sortable_columns() {
		$sortable_columns = [
			'name'        => ['name',       false],
			'downloaded'  => ['downloaded', false],
			'minimum'     => ['minimum',    false],
		];
		return $sortable_columns;
	}

	// Callable to be used with usort.
	function reorder($a, $b) {
		// If no orderby or wrong orderby, default to plugin or theme name.
		$orderby = (!empty($_GET['orderby']) && in_array($_GET['orderby'], ['name', 'downloaded', 'minimum'], true)) ? $_GET['orderby'] : 'downloaded';
		// If no order or wrong order, default to asc.
		$order = (!empty($_GET['order']) && $_GET['order'] !== 'desc') ? 'asc' : 'desc';

		// Properly order numeric values or reorder text case-insensitive.
		if ($orderby === 'downloaded') {
			$result = $a[$orderby] - $b[$orderby];
		} else {
			$result = strcasecmp($a[$orderby], $b[$orderby]);
		}

		return ( $order === 'asc' ) ? $result : -$result;
	}

	// Just output the column.
	function column_default($item, $column_name) {
		return $item[$column_name];
	}

	// For "Name" column add row actions and reformat it.
	function column_name($item) {
		$class = '';
		if (preg_match('/^5/', $item['minimum'])) {
			$class = 'cpc-bad-news';
		}
		$name = '<span class="row-title '.$class.'"><a href="'.$item['link'].'" target="_blank">'.$item['name'].'</a> v. '.$item['version'].'</span>';
		return sprintf('%1$s', $name);
	}


	function column_downloaded($item) {
		$count = number_format($item['downloaded'], 0, '', _x(',', 'thousands separator', 'cpc'));
		return sprintf('%1$s', $count);
	}

	function load_data() {
		if (($saved = get_transient('cpc_popular'))) {
			return $saved;
		}
		include_once(ABSPATH.'wp-admin/includes/plugin-install.php');
		$call_api = plugins_api('query_plugins', [
				'browse'   => 'popular',
				'page'     => 1,
				'per_page' => 1000,
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

		$list = $call_api->{'plugins'};

		$data = [];
		foreach ($list as $plugin) {
			$data[] = [
				'name'       => $plugin->name,
				'version'    => $plugin->version,
				'link'       => $plugin->homepage,
				'downloaded' => $plugin->downloaded,
				'minimum'    => $plugin->requires,
			];
		}

		set_transient('cpc_popular', $data, 900);
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

		$per_page = 20;
		$total_items = count($alldata);
		$current_page = $this->get_pagenum();

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page ,
		]);

		$this->items = array_slice($alldata, (($current_page - 1) * $per_page), $per_page);
	}

}


add_action('admin_menu', 'CPplugincheck_create_submenu');
function CPplugincheck_create_submenu() {
	$cpc_page_name = __('CP plugin compatibility', 'cpc');
	$cpc_page = add_submenu_page('tools.php', $cpc_page_name, $cpc_page_name, 'manage_options', 'cpcompatibility', 'CPplugincheck_page');
}

add_action('admin_enqueue_scripts', 'cpc_wp_admin_style');
function cpc_wp_admin_style($hook) {
	if ($hook === 'tools_page_cpcompatibility') {
		wp_enqueue_style('cpcompatibility_css', plugins_url('../css/cpcompatibility.css', __FILE__));
	}
}

function CPplugincheck_page() {
	echo '<div class="wrap">';
	echo '<H1>'.__('Plugins not ClassicPress-friendly', 'cpc').'</H1>';

	$CPCListTable = new CPC_List_Table();
	$CPCListTable->prepare_items();
	$CPCListTable->display();
	
	echo '</div>';
}




