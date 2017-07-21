<?php
/*
Plugin Name: Gravity Forms ACH Field Type
Plugin URI: https://wpgateways.com
Description: Adds and ACH field type to Gravity Forms just like a credit card type field
Version: 1.0.0
Author: WP Gateways
Author URI: https://wpgateways.com
*/


function gf_ach_load() {
	require_once( 'class-gf-field-ach.php' );
	require_once( 'scripts.php' );
	load_plugin_textdomain( 'gf-ach-field', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'gform_loaded', 'gf_ach_load' );