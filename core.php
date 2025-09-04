<?php
/*
Plugin Name: Gravity Forms ACH Field Type
Plugin URI: https://wpgateways.com/products/gravity-forms-ach-echeck-payments/
Description: Adds and ACH field type to Gravity Forms just like a credit card type field
Version: 1.0.4
Author: WP Gateways
Author URI: https://wpgateways.com
Text Domain: gf-ach-field
Domain Path: /languages
Requires Plugins: gravityforms

	Copyright: © WP Gateways.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

define( 'GF_ACH_VERSION', '1.0.4' );
define( 'GF_ACH_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'GF_ACH_PLUGIN_URL', plugins_url( plugin_basename( GF_ACH_PLUGIN_DIR ) ) );
define( 'GF_ACH_PLUGIN_PATH', plugin_basename( __FILE__ ) );

function gf_ach_load() {
	require_once( 'class-gf-field-ach.php' );
	require_once( 'scripts.php' );
	load_plugin_textdomain( 'gf-ach-field', false, GF_ACH_PLUGIN_DIR . '/languages/' );
}
add_action( 'gform_loaded', 'gf_ach_load' );