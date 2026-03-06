<?php
/**
 * Plugin bootstrap loader.
 *
 * @package DopeHeader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin bootstrap handler.
 */
final class Dope_Header_Plugin {
	const MINIMUM_ELEMENTOR_VERSION = '3.20.0';
	const MINIMUM_PHP_VERSION       = '7.4';

	/**
	 * Singleton instance holder.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Retrieves the main plugin instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Dope Header constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initializes plugin integrations and dependencies.
	 */
	public function init(): void {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_missing_elementor' ) );
			return;
		}

		if ( version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_elementor_version' ) );
			return;
		}

		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
			return;
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Registers frontend styles and scripts.
	 */
	public function register_assets(): void {
		wp_register_style(
			'dope-header-widget',
			DOPE_HEADER_URL . 'assets/css/dope-header.css',
			array(),
			DOPE_HEADER_VERSION
		);

		wp_register_script(
			'dope-header-widget',
			DOPE_HEADER_URL . 'assets/js/dope-header.js',
			array(),
			DOPE_HEADER_VERSION,
			true
		);
	}

	/**
	 * Registers the Elementor widget.
	 *
	 * @param \\Elementor\\Widgets_Manager $widgets_manager Widget manager instance.
	 */
	public function register_widgets( $widgets_manager ): void {
		require_once DOPE_HEADER_PATH . '/includes/widgets/class-dope-header-widget.php';
		$widgets_manager->register( new Dope_Header_Widget() );
	}

	/**
	 * Displays a notice when Elementor is not available.
	 */
	public function admin_notice_missing_elementor(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo esc_html__( 'Dope Header requires Elementor to be installed and activated.', 'dope-header' );
		echo '</p></div>';
	}

	/**
	 * Warns if Elementor is below the required version.
	 */
	public function admin_notice_minimum_elementor_version(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: required Elementor version. */
					__( 'Dope Header requires Elementor version %1$s or greater.', 'dope-header' ),
					self::MINIMUM_ELEMENTOR_VERSION
				)
			)
		);
	}

	/**
	 * Warns if PHP does not meet the minimum requirement.
	 */
	public function admin_notice_minimum_php_version(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: required PHP version. */
					__( 'Dope Header requires PHP version %1$s or greater.', 'dope-header' ),
					self::MINIMUM_PHP_VERSION
				)
			)
		);
	}
}
