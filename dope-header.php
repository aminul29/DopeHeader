<?php
/**
 * Plugin Name: Dope Header for Elementor
 * Description: Elementor widget for a configurable header with topbar text carousel, navigation, and actions.
 * Version: 1.1.0
 * Author: Aminul Islam
 * Text Domain: dope-header
 * Requires Plugins: elementor
 *
 * @package DopeHeader
 *
 * Elementor tested up to: 3.29.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DOPE_HEADER_VERSION', '1.1.0' );
define( 'DOPE_HEADER_FILE', __FILE__ );
define( 'DOPE_HEADER_PATH', __DIR__ );
define( 'DOPE_HEADER_URL', plugin_dir_url( __FILE__ ) );

require_once DOPE_HEADER_PATH . '/includes/class-dope-header-plugin.php';

Dope_Header_Plugin::instance();
