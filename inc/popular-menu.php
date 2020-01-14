<?php

if (!defined('ABSPATH')) die('uh');

// This code adds a menu in the admin interface to list 
// plugins that have an update that don't support WP4


add_action('admin_menu', 'CPplugincheck_create_submenu');
function CPplugincheck_create_submenu() {
	$cpc_page_name = __('CP plugin compatibility', 'cpc');
	$cpc_page = add_submenu_page('tools.php', $cpc_page_name, $cpc_page_name, 'manage_options', 'cpcompatibility', 'CPplugincheck_page');
}

add_action('admin_enqueue_scripts', 'cpc_wp_admin_style');
function cpc_wp_admin_style($hook){
	if ($hook === 'tools_page_cpcompatibility'){
		wp_enqueue_style('cpcompatibility_css', plugins_url('../css/cpcompatibility.css', __FILE__));
	}
}

function CPplugincheck_page() {
	$listlimit = 200;
	$browse = "popular";
	//	'popular','featured','news' are the alternatives

	echo '<div class="wrap">';
	echo '<H1>' . __('Plugins not ClassicPress-friendly', 'cpc') . '</H1>';
	echo '<H3>' . __('List of <i>your</i> plugins incompatible with WordPress 4.x', 'cpc') . '</H3>';
	$all_plugins = get_plugins();
	$pcount = 0;
	foreach($all_plugins as $row => $innerArray){
		$path = explode("/", $row);
		$slug = $path[0];
		$plugin_info = CPplugin_info($slug);
		if (($plugin_info !== "") && version_compare($plugin_info[0], '5', '>=')){
		/* translators: %1$s: plugin name, %2$s: WP version required, %3$s: plugin version. */
		$plugin_requires = sprintf (__('<b>%1$s</b> requires wp %2$s for version %3$s.<br>', "cpc"), $innerArray['Name'], $plugin_info[0], $plugin_info[1]);
			echo $plugin_requires;
			$pcount++;
		}  
	}
	if ( ! $pcount ){
		echo esc_html__("(none)", "cpc");
	}

	echo '<H3>';
	/* translators: %1$s: plugin number, %2$s: plugin type()popular, featured... */
	printf ( esc_html__( 'List of %1$s top %2$s plugins and required WordPress version' , 'cpc' ), $listlimit, $browse ); 
	echo '</H3>';

	include_once ( ABSPATH . "wp-admin/includes/plugin-install.php" );
	$call_api = plugins_api( 'query_plugins',
		array(
			'browse'   => $browse,
			'page'     => '1',
			'per_page' => $listlimit,
			'fields'   => array(
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
				'downloadlink'      => true
				)
			)
	); 
	
	$ordered_list = $call_api->{'plugins'};

	usort($ordered_list, function ($a, $b) {
		return $b->{'downloaded'} - $a->{'downloaded'};
	});
	
    if (is_wp_error($call_api)) {
        echo '<pre>' . print_r( $call_api->get_error_message(), true ) . '</pre>';
    } else {
    	$totalplugin=0;
    	$totalincplugin=0;
    	$totaldownload=0;
    	$totalincdownload=0;
    	$outputtable="";
    	$table_slug = __('Slug', 'cpc');
    	$table_downloaded = __('Downloaded', 'cpc');
    	$table_requires = __('Minimum<br>WordPress version', 'cpc');
		foreach ($ordered_list as $element) {
			$totalplugin++;
			$totaldownload+=$element->{'downloaded'};
			$extraclass = "";
			if (preg_match('/^5/', $element->{'requires'})){
				$extraclass = 'class="cpc-evidence"';
				$totalincplugin++;
    			$totalincdownload+=$element->{'downloaded'};
			}
			/* translators: this is the thousands separator */
			$outputtable .= "<tr $extraclass><td><a href='" . $element->{'homepage'} . "'>" . $element->{'slug'} . "</a><td class='cpc-number'>" . number_format( $element->{'downloaded'}, 0, ",", __( '&nbsp;', 'cpc' ) ) . "<td>" . $element->{'requires'} . "</tr>\n";
		}	 
  		$totaldownload = number_format($totaldownload, 0, ",", __('&nbsp;', 'cpc'));
    	$totalincdownload = number_format($totalincdownload, 0, ",", __('&nbsp;', 'cpc'));
 		/* Translators Total plugins: 1 number of plugins 2 downloaded total times*/
 		printf(esc_html__('%1$s popular plugins have ben downloaded %2$s times.', 'cpc') . "<br>" , $totalplugin, $totaldownload);
 		/* Translators Incompatible plugins: 1 number of plugins 2 downloaded total times*/
 		printf(esc_html__('%1$s of those are incompatible with ClassicPress and have ben downloaded %2$s times.', 'cpc') . "</span>", $totalincplugin, $totalincdownload);
 		echo "<table class='cpc'><tr><th>$table_slug<th>$table_downloaded<th>$table_requires</tr>\n";
 		echo "$outputtable</table><span>";
 		echo "</div>\n"; 
	}
}