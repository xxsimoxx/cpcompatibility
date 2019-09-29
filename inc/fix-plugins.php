<?php
if (!defined('ABSPATH')) die('uh');

$fixed_plugins = array();

// Fix rankmath
add_action( 'admin_init', 'cpcompatibility_fix_rankmath' );
function cpcompatibility_fix_rankmath(){
	global $fixed_plugins;
	if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
		// lodash missing. Fixed in 1.0.31
		$rankmathinfo = get_plugin_data( ABSPATH . 'wp-content/plugins/seo-by-rank-math/rank-math.php');
		$rankmathversion = $rankmathinfo['Version'];
		if ( version_compare( $rankmathversion, '1.0.31', '<' ) ){
			if ( ! wp_script_is( 'lodash', 'registered' ) ) {
				array_push( $fixed_plugins, 'seo-by-rank-math' );
				wp_register_script( 'lodash', plugins_url( 'js/lodash.min.js', __FILE__ ));
			}
		}

	}
}

// Fix Caldera Forms
add_action( 'admin_init', 'cpcompatibility_fix_calderaforms' );
function cpcompatibility_fix_calderaforms(){
	global $fixed_plugins;
	if ( is_plugin_active( 'caldera-forms/caldera-core.php' ) ) {
		// lodash missing. Fixed in 1.0.31
		$calderainfo = get_plugin_data( ABSPATH . 'wp-content/plugins/caldera-forms/caldera-core.php');
		$calderaversion = $calderainfo['Version'];
		if ( version_compare( $calderaversion, '1.8.4', '>' ) ){
			if ( ! wp_style_is( 'wp-components', 'registered' ) ) {
				array_push( $fixed_plugins, 'caldera-forms' );
				wp_register_style( 'wp-components', plugins_url( 'js/fake.min.js', __FILE__ ) );
			}
		}

	}
}

/**
*
* function cpcompatibility_fixed_plugin ( ) : array ( )
* return an array of slugs of fixed plugins
*
*/

function cpcompatibility_fixed_plugin(){
	global $fixed_plugins;
	return $fixed_plugins;
}

?>