<?php

if (!defined('ABSPATH')) die('uh');

// This code adds a menu in the admin interface to list 
// plugins that have an update that don't support WP4
add_action('admin_menu', 'CPplugincheck_create_submenu');
function CPplugincheck_create_submenu() {
	add_submenu_page('tools.php', 'CP plugin compatibility', 'CP plugin compatibility', 'manage_options', 'cpcompatibility', 'CPplugincheck_page' );
}

function CPplugincheck_page() {
	$listlimit=200;
	$browse="popular";
	//	'popular','featured','news' are the alternatives
?>
<div class="wrap">
<H1>Plugins not ClassicPress-friendly</H1>
<H3>List of your plugins that - at their latest version - dropped support for WP 4</H3>
<?php
	$all_plugins = get_plugins();
	$pcount = 0;
	foreach( $all_plugins as $row => $innerArray ){
		$path = explode( "/", $row );
		$slug = $path[0];
		$message =  $innerArray{'Name'} . " " . CPplugin_info($slug) . "<br/>";
		if ( preg_match( '/equires wp 5/', $message ) ){
			echo $message;
			$pcount++;
		}  
	}
	if ( ! $pcount ){
		echo "(none)";
	}
?>

<H3>List of <?php echo $listlimit; ?> top <?php echo $browse; ?> plugins and required versions</H3>
<?php
	include ( ABSPATH . "wp-admin/includes/plugin-install.php" );
	$call_api = plugins_api( 'query_plugins',
		array(
			'browse' => $browse,
			'page' => '1',
			'per_page' => $listlimit,
			'fields' => array(
				'downloaded' => true,
				'rating' => false,
				'description' => false,
				'short_description' => false,
				'donate_link' => false,
				'tags' => false,
				'sections' => false,
				'homepage' => false,
				'added' => false,
				'last_updated' => false,
				'compatibility' => false,
				'tested' => false,
				'requires' => true,
				'downloadlink' => true,
			)
		)
	); 
    if ( is_wp_error( $call_api ) ) {
        echo '<pre>' . print_r( $call_api->get_error_message(), true ) . '</pre>';
    } else {
 		echo "<table><tr style='background-color:#0073AA; color:white;'><th>Slug<th>Downloaded<th>Requires</tr>\n";
 		$count=0;
		foreach ( $call_api->{'plugins'} as $element ) {
			if ( $count % 2 == 0 ){
				$color='style="background-color:white;"';
				} else {
				$color='';
			};
			$count++;
			if ( preg_match( '/^5/', $element->{'requires'} ) ){
				$color='style="background-color:orange;"';
			}
			echo "<tr $color><td>" . $element->{'slug'} . "<td>" . $element->{'downloaded'} . "<td>" . $element->{'requires'} . "</tr>\n";
		}	 
 		echo "</table></div>\n";
	}
}

?>