<?php
/**
 * Dope Header Elementor widget.
 *
 * @package DopeHeader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( class_exists( 'Walker_Nav_Menu' ) && ! class_exists( 'Dope_Header_Mobile_Menu_Walker' ) ) {
	/**
	 * Mobile menu walker with submenu toggles for the drawer.
	 */
	class Dope_Header_Mobile_Menu_Walker extends Walker_Nav_Menu {
		/**
		 * Prefix used to keep submenu ids unique per widget instance.
		 *
		 * @var string
		 */
		private $submenu_prefix = '';

		/**
		 * Tracks submenu ids by depth while walking.
		 *
		 * @var array<int, string>
		 */
		private $submenu_ids = array();

		/**
		 * Constructor.
		 *
		 * @param string $submenu_prefix Prefix for submenu ids.
		 */
		public function __construct( string $submenu_prefix ) {
			$this->submenu_prefix = sanitize_html_class( $submenu_prefix );
		}

		/**
		 * Starts a submenu level.
		 *
		 * @param string   $output Used to append additional content.
		 * @param int      $depth  Menu depth.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 * @return void
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ): void {
			$indent     = str_repeat( "\t", $depth );
			$submenu_id = isset( $this->submenu_ids[ $depth ] ) ? $this->submenu_ids[ $depth ] : '';
			$id_attr    = '' !== $submenu_id ? ' id="' . esc_attr( $submenu_id ) . '"' : '';

			$output .= "\n$indent<ul$id_attr class=\"sub-menu\" data-dh-submenu hidden>\n";
		}

		/**
		 * Ends a submenu level.
		 *
		 * @param string   $output Used to append additional content.
		 * @param int      $depth  Menu depth.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 * @return void
		 */
		public function end_lvl( &$output, $depth = 0, $args = null ): void {
			$indent = str_repeat( "\t", $depth );
			unset( $this->submenu_ids[ $depth ] );
			$output .= "$indent</ul>\n";
		}

		/**
		 * Starts the element output.
		 *
		 * @param string   $output            Used to append additional content.
		 * @param WP_Post  $item              Menu item data object.
		 * @param int      $depth             Depth of menu item.
		 * @param stdClass $args              An object of wp_nav_menu() arguments.
		 * @param int      $current_object_id Current item id.
		 * @return void
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $current_object_id = 0 ): void {
			$indent            = $depth ? str_repeat( "\t", $depth ) : '';
			$classes           = empty( $item->classes ) ? array() : (array) $item->classes;
			$has_children      = in_array( 'menu-item-has-children', $classes, true );
			$submenu_id        = $has_children ? $this->submenu_prefix . '-submenu-' . absint( $item->ID ) : '';
			$sanitized_classes = array_filter(
				array_map(
					'sanitize_html_class',
					$classes
				)
			);

			if ( $has_children ) {
				$sanitized_classes[]    = 'has-dropdown';
				$this->submenu_ids[ $depth ] = $submenu_id;
			}

			$output .= $indent . '<li class="' . esc_attr( implode( ' ', array_unique( $sanitized_classes ) ) ) . '">';

			$link_attributes = array(
				'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
				'target' => ! empty( $item->target ) ? $item->target : '',
				'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
				'href'   => ! empty( $item->url ) ? $item->url : '',
			);

			$link_output = '';
			foreach ( $link_attributes as $attribute => $value ) {
				if ( '' === $value ) {
					continue;
				}

				$escaped_value = 'href' === $attribute ? esc_url( $value ) : esc_attr( $value );
				$link_output  .= ' ' . $attribute . '="' . $escaped_value . '"';
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			if ( $has_children ) {
				$output .= '<div class="dh-mobile-menu__row">';
			}

			$output .= '<a' . $link_output . '>' . esc_html( $title ) . '</a>';

			if ( $has_children ) {
				$toggle_label = sprintf(
					/* translators: %s: menu item label. */
					__( 'Toggle submenu for %s', 'dope-header' ),
					wp_strip_all_tags( $title )
				);

				$output .= '<button type="button" class="dh-mobile-submenu-toggle" data-dh-submenu-toggle aria-expanded="false" aria-controls="' . esc_attr( $submenu_id ) . '" aria-label="' . esc_attr( $toggle_label ) . '"><span class="dh-mobile-submenu-toggle__icon" aria-hidden="true"></span></button>';
				$output .= '</div>';
			}
		}

		/**
		 * Ends the element output.
		 *
		 * @param string   $output Used to append additional content.
		 * @param WP_Post  $item   Menu item data object.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 * @return void
		 */
		public function end_el( &$output, $item, $depth = 0, $args = null ): void {
			$output .= "</li>\n";
		}
	}
}

/**
 * Elementor widget implementation for the Dope Header plugin.
 */
class Dope_Header_Widget extends Widget_Base {
	/**
	 * Gets the widget slug.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'dope_header';
	}

	/**
	 * Gets the widget label.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Dope Header', 'dope-header' );
	}

	/**
	 * Gets the widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-header';
	}

	/**
	 * Gets the Elementor widget categories.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( 'general' );
	}

	/**
	 * Gets widget search keywords.
	 *
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( 'header', 'topbar', 'menu', 'navigation', 'announcement' );
	}

	/**
	 * Gets the widget stylesheet dependencies.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array(
			'dope-header-widget',
			'elementor-icons-fa-solid',
			'elementor-icons-fa-regular',
			'elementor-icons-fa-brands',
		);
	}

	/**
	 * Gets the widget script dependencies.
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'dope-header-widget' );
	}

	/**
	 * Registers all widget controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_topbar_controls();
		$this->register_header_controls();
		$this->register_actions_controls();
		$this->register_mobile_controls();
		$this->register_style_controls();
	}

	/**
	 * Registers topbar content controls.
	 *
	 * @return void
	 */
	private function register_topbar_controls(): void {
		$this->start_controls_section(
			'section_topbar',
			array(
				'label'     => esc_html__( 'Announcement Bar', 'dope-header' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'topbar_visibility_heading',
			array(
				'label' => esc_html__( 'Visibility', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'enable_topbar',
			array(
				'label'        => esc_html__( 'Show Announcement Bar', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'announcement_text',
			array(
				'label'       => esc_html__( 'Text', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Free Shipping Nationwide - Orders Above 3000 Taka', 'dope-header' ),
			)
		);

		$this->add_control(
			'topbar_messages_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'topbar_messages_heading',
			array(
				'label' => esc_html__( 'Announcement Items', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'topbar_items',
			array(
				'label'       => esc_html__( 'Messages', 'dope-header' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ announcement_text }}}',
				'default'     => array(
					array( 'announcement_text' => esc_html__( 'Free Shipping Nationwide - Orders Above 3000 Taka', 'dope-header' ) ),
					array( 'announcement_text' => esc_html__( 'New Arrivals Every Week - Check Collection', 'dope-header' ) ),
					array( 'announcement_text' => esc_html__( 'Cash on Delivery Available Across Bangladesh', 'dope-header' ) ),
				),
				'condition'   => array( 'enable_topbar' => 'yes' ),
			)
		);

		$this->add_control(
			'topbar_slider_divider',
			array(
				'type'      => Controls_Manager::DIVIDER,
				'condition' => array( 'enable_topbar' => 'yes' ),
			)
		);

		$this->add_control(
			'topbar_slider_heading',
			array(
				'label'     => esc_html__( 'Carousel Settings', 'dope-header' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array( 'enable_topbar' => 'yes' ),
			)
		);

		$this->add_control(
			'topbar_show_arrows',
			array(
				'label'        => esc_html__( 'Show Navigation Arrows', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'enable_topbar' => 'yes' ),
			)
		);

		$this->add_control(
			'topbar_autoplay',
			array(
				'label'        => esc_html__( 'Autoplay', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'enable_topbar' => 'yes' ),
			)
		);

		$this->add_control(
			'topbar_autoplay_delay',
			array(
				'label'     => esc_html__( 'Autoplay Delay (ms)', 'dope-header' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 3500,
				'min'       => 1000,
				'step'      => 100,
				'condition' => array(
					'enable_topbar'   => 'yes',
					'topbar_autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'topbar_pause_on_hover',
			array(
				'label'        => esc_html__( 'Pause On Hover', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'enable_topbar'   => 'yes',
					'topbar_autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'topbar_socials_divider',
			array(
				'type'      => Controls_Manager::DIVIDER,
				'condition' => array( 'enable_topbar' => 'yes' ),
			)
		);

		$this->add_control(
			'topbar_socials_heading',
			array(
				'label'     => esc_html__( 'Social Links', 'dope-header' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array( 'enable_topbar' => 'yes' ),
			)
		);

		$this->add_control(
			'show_topbar_socials',
			array(
				'label'        => esc_html__( 'Show Social Links', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'enable_topbar' => 'yes',
				),
			)
		);

		$social_repeater = new Repeater();
		$social_repeater->add_control(
			'social_label',
			array(
				'label'       => esc_html__( 'Label', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Facebook', 'dope-header' ),
			)
		);

		$social_repeater->add_control(
			'social_icon',
			array(
				'label'   => esc_html__( 'Icon', 'dope-header' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fab fa-facebook-f',
					'library' => 'fa-brands',
				),
			)
		);

		$social_repeater->add_control(
			'social_link',
			array(
				'label'         => esc_html__( 'Link', 'dope-header' ),
				'type'          => Controls_Manager::URL,
				'show_external' => true,
				'label_block'   => true,
				'default'       => array(
					'url'         => '#',
					'is_external' => true,
					'nofollow'    => false,
				),
			)
		);

		$this->add_control(
			'topbar_social_items',
			array(
				'label'       => esc_html__( 'Social Link Items', 'dope-header' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $social_repeater->get_controls(),
				'title_field' => '{{{ social_label }}}',
				'default'     => array(
					array(
						'social_label' => esc_html__( 'Facebook', 'dope-header' ),
						'social_icon'  => array(
							'value'   => 'fab fa-facebook-f',
							'library' => 'fa-brands',
						),
						'social_link'  => array(
							'url'         => '#',
							'is_external' => true,
							'nofollow'    => false,
						),
					),
					array(
						'social_label' => esc_html__( 'Instagram', 'dope-header' ),
						'social_icon'  => array(
							'value'   => 'fab fa-instagram',
							'library' => 'fa-brands',
						),
						'social_link'  => array(
							'url'         => '#',
							'is_external' => true,
							'nofollow'    => false,
						),
					),
					array(
						'social_label' => esc_html__( 'WhatsApp', 'dope-header' ),
						'social_icon'  => array(
							'value'   => 'fab fa-whatsapp',
							'library' => 'fa-brands',
						),
						'social_link'  => array(
							'url'         => '#',
							'is_external' => true,
							'nofollow'    => false,
						),
					),
				),
				'condition'   => array(
					'show_topbar_socials' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers header content controls.
	 *
	 * @return void
	 */
	private function register_header_controls(): void {
		$this->start_controls_section(
			'section_header',
			array(
				'label' => esc_html__( 'Branding and Navigation', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'header_layout_heading',
			array(
				'label' => esc_html__( 'Header Layout', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'header_layout',
			array(
				'label'   => esc_html__( 'Layout', 'dope-header' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'default' => esc_html__( 'Default', 'dope-header' ),
					'minimal' => esc_html__( 'Minimal', 'dope-header' ),
				),
			)
		);

		$this->add_control(
			'header_brand_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'header_brand_heading',
			array(
				'label' => esc_html__( 'Branding', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'logo_type',
			array(
				'label'   => esc_html__( 'Logo Type', 'dope-header' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => array(
					'image' => esc_html__( 'Image', 'dope-header' ),
					'text'  => esc_html__( 'Text', 'dope-header' ),
				),
			)
		);

		$this->add_control(
			'logo_image',
			array(
				'label'   => esc_html__( 'Logo Image', 'dope-header' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => Utils::get_placeholder_image_src() ),
				'condition' => array(
					'logo_type' => 'image',
				),
			)
		);

		$this->add_control(
			'logo_text',
			array(
				'label'       => esc_html__( 'Logo Text', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => get_bloginfo( 'name' ),
				'condition'   => array(
					'logo_type' => 'text',
				),
			)
		);

		$this->add_control(
			'logo_alt',
			array(
				'label'       => esc_html__( 'Logo Alt Text', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => get_bloginfo( 'name' ),
				'condition'   => array(
					'logo_type' => 'image',
				),
			)
		);

		$this->add_control(
			'logo_link',
			array(
				'label'         => esc_html__( 'Logo Link', 'dope-header' ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'default'       => array(
					'url'         => home_url( '/' ),
					'is_external' => false,
					'nofollow'    => false,
				),
			)
		);

		$this->add_control(
			'header_navigation_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'header_navigation_heading',
			array(
				'label' => esc_html__( 'Navigation', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'menu_id',
			array(
				'label'   => esc_html__( 'Primary Menu', 'dope-header' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_menu_options(),
				'default' => '',
			)
		);

		$this->add_control(
			'menu_fallback_label',
			array(
				'label'       => esc_html__( 'Editor Placeholder Text', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Select a WordPress menu in the widget settings.', 'dope-header' ),
			)
		);

		$this->add_control(
			'header_layout_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'header_layout_heading',
			array(
				'label' => esc_html__( 'Layout Behavior', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'header_absolute_position',
			array(
				'label'        => esc_html__( 'Overlay Header on Hero', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Places the header above the next section, useful for hero overlays.', 'dope-header' ),
				'condition'    => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'minimal_sticky_divider',
			array(
				'type'      => Controls_Manager::DIVIDER,
				'condition' => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->add_control(
			'minimal_sticky_heading',
			array(
				'label'     => esc_html__( 'Sticky Behavior', 'dope-header' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->add_control(
			'enable_sticky_header',
			array(
				'label'        => esc_html__( 'Stick Header to Top', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->add_control(
			'enable_header_shrink',
			array(
				'label'        => esc_html__( 'Shrink Header on Scroll', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'header_layout'       => 'minimal',
					'enable_sticky_header' => 'yes',
				),
			)
		);

		$this->add_control(
			'sticky_scroll_threshold',
			array(
				'label'     => esc_html__( 'Shrink Threshold (px)', 'dope-header' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 24,
				'min'       => 0,
				'step'      => 1,
				'condition' => array(
					'header_layout'        => 'minimal',
					'enable_sticky_header' => 'yes',
					'enable_header_shrink' => 'yes',
				),
			)
		);

		$this->add_control(
			'minimal_header_height',
			array(
				'label'     => esc_html__( 'Header Height (px)', 'dope-header' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 92,
				'min'       => 48,
				'step'      => 1,
				'condition' => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->add_control(
			'minimal_header_height_scrolled',
			array(
				'label'     => esc_html__( 'Scrolled Header Height (px)', 'dope-header' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 72,
				'min'       => 40,
				'step'      => 1,
				'condition' => array(
					'header_layout'        => 'minimal',
					'enable_sticky_header' => 'yes',
					'enable_header_shrink' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers action controls.
	 *
	 * @return void
	 */
	private function register_actions_controls(): void {
		$this->start_controls_section(
			'section_actions',
			array(
				'label' => esc_html__( 'Header Actions', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'actions_items_heading',
			array(
				'label'     => esc_html__( 'Utility Icons', 'dope-header' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'header_layout' => 'default',
				),
			)
		);

		$actions_repeater = new Repeater();
		$actions_repeater->add_control(
			'action_label',
			array(
				'label'       => esc_html__( 'Label', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Search', 'dope-header' ),
			)
		);

		$actions_repeater->add_control(
			'action_link',
			array(
				'label'         => esc_html__( 'Link', 'dope-header' ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'label_block'   => true,
				'default'       => array(
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
				),
			)
		);

		$actions_repeater->add_control(
			'action_icon',
			array(
				'label'   => esc_html__( 'Icon', 'dope-header' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-magnifying-glass',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'action_items',
			array(
				'label'       => esc_html__( 'Header Icon Items', 'dope-header' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $actions_repeater->get_controls(),
				'title_field' => '{{{ action_label }}}',
				'default'     => array(
					array(
						'action_label' => esc_html__( 'Search', 'dope-header' ),
						'action_link'  => array(
							'url'         => '#',
							'is_external' => false,
							'nofollow'    => false,
						),
						'action_icon'  => array(
							'value'   => 'fas fa-magnifying-glass',
							'library' => 'fa-solid',
						),
					),
					array(
						'action_label' => esc_html__( 'Account', 'dope-header' ),
						'action_link'  => array(
							'url'         => '#',
							'is_external' => false,
							'nofollow'    => false,
						),
						'action_icon'  => array(
							'value'   => 'far fa-user',
							'library' => 'fa-regular',
						),
					),
					array(
						'action_label' => esc_html__( 'Cart', 'dope-header' ),
						'action_link'  => array(
							'url'         => '#',
							'is_external' => false,
							'nofollow'    => false,
						),
						'action_icon'  => array(
							'value'   => 'fas fa-cart-shopping',
							'library' => 'fa-solid',
						),
					),
				),
				'condition'   => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'actions_language_divider_content',
			array(
				'type'      => Controls_Manager::DIVIDER,
				'condition' => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'actions_language_heading_content',
			array(
				'label'     => esc_html__( 'Language Selector', 'dope-header' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'show_language_menu',
			array(
				'label'        => esc_html__( 'Show Language Selector', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'language_menu_id',
			array(
				'label'       => esc_html__( 'Language Selector Menu', 'dope-header' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_menu_options(),
				'default'     => '',
				'description' => esc_html__( 'The first top-level item from the selected menu will be used as the language label and link.', 'dope-header' ),
				'condition'   => array(
					'header_layout'     => 'default',
					'show_language_menu' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_language_chevron',
			array(
				'label'        => esc_html__( 'Show Language Chevron', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'header_layout'     => 'default',
					'show_language_menu' => 'yes',
				),
			)
		);

		$this->add_control(
			'language_chevron_icon',
			array(
				'label'     => esc_html__( 'Chevron Icon', 'dope-header' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				),
				'condition' => array(
					'header_layout'        => 'default',
					'show_language_menu'    => 'yes',
					'show_language_chevron' => 'yes',
				),
			)
		);

		$this->add_control(
			'minimal_cta_divider',
			array(
				'type'      => Controls_Manager::DIVIDER,
				'condition' => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->add_control(
			'minimal_cta_heading',
			array(
				'label'     => esc_html__( 'Call to Action Button', 'dope-header' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->add_control(
			'cta_button_text',
			array(
				'label'       => esc_html__( 'Button Text', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Contact', 'dope-header' ),
				'condition'   => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->add_control(
			'cta_button_link',
			array(
				'label'         => esc_html__( 'Button Link', 'dope-header' ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'default'       => array(
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
				),
				'condition'     => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers mobile controls.
	 *
	 * @return void
	 */
	private function register_mobile_controls(): void {
		$this->start_controls_section(
			'section_mobile',
			array(
				'label' => esc_html__( 'Mobile Menu', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'mobile_behavior_heading',
			array(
				'label' => esc_html__( 'Drawer Behavior', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'enable_mobile_drawer',
			array(
				'label'        => esc_html__( 'Show Mobile Drawer Toggle', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'mobile_breakpoint',
			array(
				'label'     => esc_html__( 'Drawer Breakpoint (px)', 'dope-header' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 1024,
				'min'       => 640,
				'max'       => 1440,
				'step'      => 1,
				'condition' => array( 'enable_mobile_drawer' => 'yes' ),
			)
		);

		$this->add_control(
			'mobile_close_on_link_click',
			array(
				'label'        => esc_html__( 'Close Drawer On Link Click', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'enable_mobile_drawer' => 'yes' ),
			)
		);

		$this->add_control(
			'enable_mobile_submenus',
			array(
				'label'        => esc_html__( 'Enable Dropdown Submenus', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'enable_mobile_drawer' => 'yes' ),
			)
		);

		$this->add_control(
			'mobile_menu_mode',
			array(
				'label'       => esc_html__( 'Mobile Menu Presentation', 'dope-header' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''         => esc_html__( 'Use Layout Default', 'dope-header' ),
					'drawer'   => esc_html__( 'Sidebar Drawer', 'dope-header' ),
					'dropdown' => esc_html__( 'Dropdown Panel', 'dope-header' ),
				),
				'description' => esc_html__( 'Default layout uses sidebar drawer. Minimal layout uses dropdown panel by default.', 'dope-header' ),
				'condition'   => array( 'enable_mobile_drawer' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers all style controls.
	 *
	 * @return void
	 */
	private function register_style_controls(): void {
		$this->register_style_topbar_controls();
		$this->register_style_nav_controls();
		$this->register_style_menu_controls();
		$this->register_style_actions_controls();
		$this->register_style_minimal_controls();
		$this->register_style_mobile_controls();
	}

	/**
	 * Registers topbar style controls.
	 *
	 * @return void
	 */
	private function register_style_topbar_controls(): void {
		$this->start_controls_section(
			'section_style_topbar',
			array(
				'label'     => esc_html__( 'Topbar', 'dope-header' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'topbar_content_heading',
			array(
				'label' => esc_html__( 'Announcement Content', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'topbar_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f1f1ef',
				'selectors' => array(
					'{{WRAPPER}} .dh-topbar' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'topbar_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#191919',
				'selectors' => array(
					'{{WRAPPER}} .dh-topbar__item' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'topbar_typography',
				'selector' => '{{WRAPPER}} .dh-topbar__item',
			)
		);

		$this->add_control(
			'topbar_content_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'topbar_arrows_heading',
			array(
				'label' => esc_html__( 'Carousel Arrows', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'topbar_arrow_color',
			array(
				'label'     => esc_html__( 'Arrow Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#888888',
				'selectors' => array(
					'{{WRAPPER}} .dh-topbar__arrow' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'topbar_arrow_border',
			array(
				'label'        => esc_html__( 'Show Arrow Border', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'selectors'    => array(
					'{{WRAPPER}} .dh-topbar__arrow' => 'border: 1px solid currentColor; border-radius: 3px;',
				),
			)
		);

		$this->add_control(
			'topbar_arrow_hover_color',
			array(
				'label'     => esc_html__( 'Arrow Hover Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#b10f0f',
				'selectors' => array(
					'{{WRAPPER}} .dh-topbar__arrow:hover, {{WRAPPER}} .dh-topbar__arrow:focus-visible' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'topbar_social_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'topbar_social_heading',
			array(
				'label' => esc_html__( 'Social Icons', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'topbar_social_background',
			array(
				'label'     => esc_html__( 'Social Circle Background', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#a10000',
				'selectors' => array(
					'{{WRAPPER}} .dh-topbar__social' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'topbar_social_color',
			array(
				'label'     => esc_html__( 'Social Icon Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-topbar__social' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'topbar_social_background_size',
			array(
				'label'      => esc_html__( 'Social Background Size', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 18,
						'max' => 64,
					),
				),
				'default'    => array(
					'size' => 24,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-topbar__social' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'topbar_layout_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'topbar_layout_heading',
			array(
				'label' => esc_html__( 'Bar Layout', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'topbar_height',
			array(
				'label'      => esc_html__( 'Topbar Height', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 32,
						'max' => 120,
					),
				),
				'default'    => array(
					'size' => 52,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-widget' => '--dh-topbar-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dh-topbar' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers navigation row style controls.
	 *
	 * @return void
	 */
	private function register_style_nav_controls(): void {
		$this->start_controls_section(
			'section_style_nav',
			array(
				'label'     => esc_html__( 'Navigation Row', 'dope-header' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'nav_row_heading',
			array(
				'label' => esc_html__( 'Row Styling', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'nav_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#050505',
				'selectors' => array(
					'{{WRAPPER}} .dh-main' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'nav_border_color',
			array(
				'label'     => esc_html__( 'Bottom Border Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-main__inner' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'nav_height',
			array(
				'label'      => esc_html__( 'Min Height', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 48,
						'max' => 160,
					),
				),
				'default'    => array(
					'size' => 58,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-widget'      => '--dh-main-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dh-main__inner' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'nav_logo_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'nav_logo_heading',
			array(
				'label' => esc_html__( 'Logo Block', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'logo_width',
			array(
				'label'      => esc_html__( 'Logo Width', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 60,
						'max' => 360,
					),
					'%'  => array(
						'min' => 8,
						'max' => 45,
					),
				),
				'default'    => array(
					'size' => 138,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-widget'      => '--dh-logo-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dh-brand-float' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'logo_text_style_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'logo_text_style_heading',
			array(
				'label' => esc_html__( 'Text Logo', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'logo_text_typography',
				'selector' => '{{WRAPPER}} .dh-widget--layout-default .dh-brand-float__text, {{WRAPPER}} .dh-widget--layout-default .dh-default-mobile-brand__text',
			)
		);

		$this->add_control(
			'logo_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-default .dh-brand-float__text, {{WRAPPER}} .dh-widget--layout-default .dh-default-mobile-brand__text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'logo_text_hover_color',
			array(
				'label'     => esc_html__( 'Hover Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-default .dh-brand-float__link:hover .dh-brand-float__text, {{WRAPPER}} .dh-widget--layout-default .dh-brand-float__link:focus-visible .dh-brand-float__text, {{WRAPPER}} .dh-widget--layout-default .dh-default-mobile-brand__link:hover .dh-default-mobile-brand__text, {{WRAPPER}} .dh-widget--layout-default .dh-default-mobile-brand__link:focus-visible .dh-default-mobile-brand__text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers menu link style controls.
	 *
	 * @return void
	 */
	private function register_style_menu_controls(): void {
		$this->start_controls_section(
			'section_style_menu',
			array(
				'label'     => esc_html__( 'Menu Links', 'dope-header' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'menu_typography_heading',
			array(
				'label' => esc_html__( 'Typography', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'menu_typography',
				'selector' => '{{WRAPPER}} .dh-widget--layout-default .dh-menu > li > a',
			)
		);

		$this->add_control(
			'menu_colors_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'menu_colors_heading',
			array(
				'label' => esc_html__( 'Colors and States', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'menu_color',
			array(
				'label'     => esc_html__( 'Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-default .dh-menu > li > a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'menu_hover_color',
			array(
				'label'     => esc_html__( 'Hover Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e6e6e6',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-default .dh-menu > li > a:hover, {{WRAPPER}} .dh-widget--layout-default .dh-menu > li > a:focus-visible' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'menu_active_underline',
			array(
				'label'     => esc_html__( 'Active Underline Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-default .dh-menu > li.current-menu-item > a::after, {{WRAPPER}} .dh-widget--layout-default .dh-menu > li.current-menu-ancestor > a::after' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers action button style controls.
	 *
	 * @return void
	 */
	private function register_style_actions_controls(): void {
		$this->start_controls_section(
			'section_style_actions',
			array(
				'label'     => esc_html__( 'Actions and Language', 'dope-header' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'header_layout' => 'default',
				),
			)
		);

		$this->add_control(
			'actions_icons_heading',
			array(
				'label' => esc_html__( 'Icons and Text', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'actions_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-action, {{WRAPPER}} .dh-language' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .dh-action i, {{WRAPPER}} .dh-language i' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .dh-action i:before, {{WRAPPER}} .dh-language i:before' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .dh-action svg, {{WRAPPER}} .dh-language svg' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important; stroke: {{VALUE}} !important;',
					'{{WRAPPER}} .dh-action svg *, {{WRAPPER}} .dh-language svg *' => 'fill: {{VALUE}} !important; stroke: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'actions_color',
			array(
				'label'     => esc_html__( 'Language Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-language__label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'actions_hover_color',
			array(
				'label'     => esc_html__( 'Actions Hover Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#d9d9d9',
				'selectors' => array(
					'{{WRAPPER}} .dh-action:hover, {{WRAPPER}} .dh-action:focus-visible, {{WRAPPER}} .dh-language:hover, {{WRAPPER}} .dh-language:focus-visible' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .dh-action:hover svg, {{WRAPPER}} .dh-action:focus-visible svg, {{WRAPPER}} .dh-language:hover svg, {{WRAPPER}} .dh-language:focus-visible svg' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important; stroke: {{VALUE}} !important;',
					'{{WRAPPER}} .dh-action:hover svg *, {{WRAPPER}} .dh-action:focus-visible svg *, {{WRAPPER}} .dh-language:hover svg *, {{WRAPPER}} .dh-language:focus-visible svg *' => 'fill: {{VALUE}} !important; stroke: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'actions_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 40,
					),
				),
				'default'    => array(
					'size' => 18,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-action i, {{WRAPPER}} .dh-action svg, {{WRAPPER}} .dh-language i, {{WRAPPER}} .dh-language svg' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'actions_surface_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'actions_surface_heading',
			array(
				'label' => esc_html__( 'Action Button Surface', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'actions_icon_background',
			array(
				'label'     => esc_html__( 'Icon Background', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dh-action' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'actions_icon_background_hover',
			array(
				'label'     => esc_html__( 'Icon Hover Background', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dh-action:hover, {{WRAPPER}} .dh-action:focus-visible' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'actions_icon_border_color',
			array(
				'label'     => esc_html__( 'Icon Border Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dh-action' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'actions_icon_border_radius',
			array(
				'label'      => esc_html__( 'Icon Border Radius', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
					'%'  => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-action' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'actions_icon_padding',
			array(
				'label'      => esc_html__( 'Icon Padding', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 24,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-action' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'actions_language_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'actions_language_heading',
			array(
				'label' => esc_html__( 'Language Link Border', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'language_border',
				'selector' => '{{WRAPPER}} .dh-language',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers minimal layout style controls.
	 *
	 * @return void
	 */
	private function register_style_minimal_controls(): void {
		$this->start_controls_section(
			'section_style_minimal',
			array(
				'label'     => esc_html__( 'Minimal Layout', 'dope-header' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'header_layout' => 'minimal',
				),
			)
		);

		$this->add_control(
			'minimal_row_heading',
			array(
				'label' => esc_html__( 'Header Row', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'minimal_row_background',
			array(
				'label'     => esc_html__( 'Background Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f5f5f5',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-main' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimal_row_border_color',
			array(
				'label'     => esc_html__( 'Bottom Border Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#d8d8d8',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-main__inner' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'minimal_row_shadow',
				'selector' => '{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-main',
			)
		);

		$this->add_responsive_control(
			'minimal_row_side_padding',
			array(
				'label'      => esc_html__( 'Side Padding', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 120,
					),
				),
				'default'    => array(
					'size' => 22,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-main__inner' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'minimal_logo_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'minimal_logo_heading',
			array(
				'label' => esc_html__( 'Logo', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'minimal_logo_width',
			array(
				'label'      => esc_html__( 'Logo Width', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 80,
						'max' => 420,
					),
					'%'  => array(
						'min' => 8,
						'max' => 40,
					),
				),
				'default'    => array(
					'size' => 250,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-widget--layout-minimal' => '--dh-minimal-logo-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-brand' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'minimal_logo_text_typography',
				'selector' => '{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-brand__text',
			)
		);

		$this->add_control(
			'minimal_logo_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1c3d72',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-brand__text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimal_logo_text_hover_color',
			array(
				'label'     => esc_html__( 'Hover Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1746a2',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-brand__link:hover .dh-minimal-brand__text, {{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-brand__link:focus-visible .dh-minimal-brand__text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimal_menu_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'minimal_menu_heading',
			array(
				'label' => esc_html__( 'Menu', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'minimal_menu_typography',
				'selector' => '{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-menu > li > a',
			)
		);

		$this->add_control(
			'minimal_menu_color',
			array(
				'label'     => esc_html__( 'Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#445065',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-menu > li > a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimal_menu_hover_color',
			array(
				'label'     => esc_html__( 'Hover Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1f57c3',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-menu > li > a:hover, {{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-menu > li > a:focus-visible' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimal_menu_active_color',
			array(
				'label'     => esc_html__( 'Active Underline Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1f57c3',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-menu > li.current-menu-item > a::after, {{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-menu > li.current-menu-ancestor > a::after' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'minimal_menu_gap',
			array(
				'label'      => esc_html__( 'Menu Gap', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 64,
					),
				),
				'default'    => array(
					'size' => 24,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-menu' => 'gap: 10px {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'minimal_button_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'minimal_button_heading',
			array(
				'label' => esc_html__( 'Button', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'minimal_button_typography',
				'selector' => '{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta',
			)
		);

		$this->add_control(
			'minimal_button_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimal_button_background',
			array(
				'label'     => esc_html__( 'Background Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1f57c3',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimal_button_hover_text_color',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta:hover, {{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta:focus-visible' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'minimal_button_hover_background',
			array(
				'label'     => esc_html__( 'Hover Background', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1746a2',
				'selectors' => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta:hover, {{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta:focus-visible' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'minimal_button_border',
				'selector' => '{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta',
			)
		);

		$this->add_responsive_control(
			'minimal_button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'dope-header' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 48,
					),
				),
				'default'    => array(
					'size' => 28,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'minimal_button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'dope-header' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .dh-widget--layout-minimal .dh-minimal-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers mobile drawer style controls.
	 *
	 * @return void
	 */
	private function register_style_mobile_controls(): void {
		$this->start_controls_section(
			'section_style_mobile',
			array(
				'label' => esc_html__( 'Mobile Drawer', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'mobile_toggle_heading',
			array(
				'label' => esc_html__( 'Hamburger Toggle', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'mobile_toggle_color',
			array(
				'label'     => esc_html__( 'Hamburger Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-toggle' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'mobile_toggle_background_color',
			array(
				'label'     => esc_html__( 'Hamburger Background', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-toggle' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'mobile_toggle_hover_color',
			array(
				'label'     => esc_html__( 'Hamburger Hover Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#d9d9d9',
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-toggle:hover, {{WRAPPER}} .dh-mobile-toggle:focus-visible' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'mobile_drawer_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'mobile_drawer_heading',
			array(
				'label' => esc_html__( 'Panels and Overlay', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'mobile_overlay_color',
			array(
				'label'     => esc_html__( 'Overlay Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0, 0, 0, 0.55)',
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-drawer__overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'mobile_drawer_background',
			array(
				'label'     => esc_html__( 'Drawer Background', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0d0d0d',
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-drawer--drawer .dh-mobile-drawer__panel' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'mobile_drawer_text_color',
			array(
				'label'     => esc_html__( 'Drawer Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-drawer--drawer .dh-menu--mobile a, {{WRAPPER}} .dh-mobile-drawer--drawer .dh-mobile-drawer__close, {{WRAPPER}} .dh-mobile-drawer--drawer .dh-mobile-submenu-toggle' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'mobile_dropdown_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'mobile_dropdown_heading',
			array(
				'label' => esc_html__( 'Dropdown Panel', 'dope-header' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'mobile_dropdown_background',
			array(
				'label'     => esc_html__( 'Dropdown Background', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-drawer--dropdown .dh-mobile-drawer__panel' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'mobile_dropdown_text_color',
			array(
				'label'     => esc_html__( 'Dropdown Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#233754',
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-drawer--dropdown .dh-menu--mobile a, {{WRAPPER}} .dh-mobile-drawer--dropdown .dh-mobile-submenu-toggle' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the widget markup.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$layout              = $this->get_header_layout( $settings );
		$mobile_enabled      = $this->is_enabled( $settings, 'enable_mobile_drawer', true );
		$mobile_breakpoint   = $this->sanitize_int( $settings['mobile_breakpoint'] ?? 1024, 1024, 640 );
		$mobile_close_on_nav = $this->is_enabled( $settings, 'mobile_close_on_link_click', true );
		$mobile_submenus     = $this->is_enabled( $settings, 'enable_mobile_submenus', true );
		$mobile_menu_mode    = $this->get_mobile_menu_mode( $settings, $layout );
		$menu_id             = isset( $settings['menu_id'] ) ? absint( $settings['menu_id'] ) : 0;
		$desktop_menu_class  = 'minimal' === $layout ? 'dh-minimal-menu' : 'dh-menu dh-menu--desktop';
		$mobile_config = array(
			'enabled'          => $mobile_enabled,
			'breakpoint'       => $mobile_breakpoint,
			'closeOnLinkClick' => $mobile_close_on_nav,
			'submenusEnabled'  => $mobile_submenus,
			'mode'             => $mobile_menu_mode,
		);

		$render_data = array(
			'mobile_enabled'      => $mobile_enabled,
			'mobile_breakpoint'   => $mobile_breakpoint,
			'mobile_menu_mode'    => $mobile_menu_mode,
			'desktop_menu'        => $this->get_menu_markup( $menu_id, $desktop_menu_class ),
			'mobile_menu'         => $this->get_menu_markup( $menu_id, 'dh-menu dh-menu--mobile' ),
			'is_editor'           => $this->is_editor_mode(),
			'fallback_text'       => isset( $settings['menu_fallback_label'] ) ? sanitize_text_field( $settings['menu_fallback_label'] ) : '',
			'logo_type'           => $this->get_logo_type( $settings ),
			'logo_text'           => $this->get_logo_text( $settings ),
			'logo_src'            => $this->get_logo_src( $settings ),
			'logo_alt'            => $this->get_logo_alt( $settings ),
			'logo_url'            => $this->get_url_value( $settings['logo_link'] ?? array(), home_url( '/' ) ),
			'logo_attributes'     => $this->get_link_attributes( $settings['logo_link'] ?? array() ),
			'uid'                 => wp_unique_id( 'dh-widget-' ),
		);

		$render_data['drawer_id'] = $render_data['uid'] . '-drawer';
		$render_data['mobile_menu'] = $this->get_mobile_menu_markup( $menu_id, 'dh-menu dh-menu--mobile', $mobile_submenus, $render_data['uid'] );
		$render_data['mobile_config_json'] = wp_json_encode( $mobile_config );

		if ( 'minimal' === $layout ) {
			$render_data['sticky_enabled']               = $this->is_enabled( $settings, 'enable_sticky_header', true );
			$render_data['shrink_enabled']               = $this->is_enabled( $settings, 'enable_header_shrink', true );
			$render_data['sticky_scroll_threshold']      = $this->sanitize_int( $settings['sticky_scroll_threshold'] ?? 24, 24, 0 );
			$render_data['minimal_header_height']        = $this->sanitize_int( $settings['minimal_header_height'] ?? 92, 92, 48 );
			$render_data['minimal_header_height_scrolled'] = $this->sanitize_int( $settings['minimal_header_height_scrolled'] ?? 72, 72, 40 );

			if ( $render_data['minimal_header_height_scrolled'] > $render_data['minimal_header_height'] ) {
				$render_data['minimal_header_height_scrolled'] = $render_data['minimal_header_height'];
			}

			$this->render_minimal_layout( $settings, $render_data );
			return;
		}

		$topbar_items        = $this->get_topbar_items( $settings );
		$topbar_social_items = $this->get_topbar_social_items( $settings );
		$show_topbar         = $this->is_enabled( $settings, 'enable_topbar', true ) && ! empty( $topbar_items );
		$show_topbar_arrows  = $this->is_enabled( $settings, 'topbar_show_arrows', true ) && count( $topbar_items ) > 1;
		$topbar_autoplay     = $this->is_enabled( $settings, 'topbar_autoplay', true ) && count( $topbar_items ) > 1;

		$render_data['topbar_items']         = $topbar_items;
		$render_data['topbar_social_items']  = $topbar_social_items;
		$render_data['show_topbar']          = $show_topbar;
		$render_data['show_topbar_arrows']   = $show_topbar_arrows;
		$render_data['show_topbar_socials']  = $this->is_enabled( $settings, 'show_topbar_socials', true ) && ! empty( $topbar_social_items );
		$render_data['topbar_config_json']   = wp_json_encode(
			array(
				'items'        => $topbar_items,
				'autoplay'     => $topbar_autoplay,
				'delay'        => $this->sanitize_int( $settings['topbar_autoplay_delay'] ?? 3500, 3500, 1000 ),
				'pauseOnHover' => $this->is_enabled( $settings, 'topbar_pause_on_hover', true ),
				'arrows'       => $show_topbar_arrows,
			)
		);

		$this->render_default_layout( $settings, $render_data );
	}

	/**
	 * Renders the default widget layout.
	 *
	 * @param array $settings    Widget settings.
	 * @param array $render_data Prepared render data.
	 * @return void
	 */
	private function render_default_layout( array $settings, array $render_data ): void {
		$widget_classes = 'dh-widget dh-widget--layout-default dh-widget--mobile-menu-' . sanitize_html_class( $render_data['mobile_menu_mode'] ) . ( $render_data['show_topbar'] ? '' : ' dh-widget--no-topbar' );

		if ( $this->is_enabled( $settings, 'header_absolute_position', false ) ) {
			$widget_classes .= ' dh-widget--absolute';
		}

		echo '<header class="' . esc_attr( $widget_classes ) . '" id="' . esc_attr( $render_data['uid'] ) . '" style="--dh-mobile-breakpoint:' . esc_attr( (string) $render_data['mobile_breakpoint'] ) . 'px;" data-dh-mobile-config="' . esc_attr( $render_data['mobile_config_json'] ) . '">';

		if ( $render_data['show_topbar'] ) {
			echo '<div class="dh-topbar" data-dh-config="' . esc_attr( $render_data['topbar_config_json'] ) . '">';
			echo '<div class="dh-shell dh-topbar__inner">';
			echo '<div class="dh-topbar__center">';

			if ( $render_data['show_topbar_arrows'] ) {
				echo '<button type="button" class="dh-topbar__arrow dh-topbar__arrow--prev" data-dh-prev aria-label="' . esc_attr__( 'Previous announcement', 'dope-header' ) . '">';
				echo wp_kses( $this->render_inline_chevron( true ), $this->get_allowed_svg_html() );
				echo '</button>';
			}

			echo '<div class="dh-topbar__viewport" aria-live="polite"><div class="dh-topbar__track">';
			foreach ( $render_data['topbar_items'] as $index => $item ) {
				$is_active = 0 === $index;
				echo '<span class="dh-topbar__item' . ( $is_active ? ' is-active' : '' ) . '" data-dh-index="' . esc_attr( (string) $index ) . '"' . ( $is_active ? '' : ' hidden' ) . '>' . esc_html( $item ) . '</span>';
			}
			echo '</div></div>';

			if ( $render_data['show_topbar_arrows'] ) {
				echo '<button type="button" class="dh-topbar__arrow dh-topbar__arrow--next" data-dh-next aria-label="' . esc_attr__( 'Next announcement', 'dope-header' ) . '">';
				echo wp_kses( $this->render_inline_chevron( false ), $this->get_allowed_svg_html() );
				echo '</button>';
			}

			echo '</div>';

			if ( $render_data['show_topbar_socials'] ) {
				echo '<div class="dh-topbar__socials">';
				foreach ( $render_data['topbar_social_items'] as $social_item ) {
					$this->render_topbar_social_icon( $social_item );
				}
				echo '</div>';
			}

			echo '</div></div>';
		}

		echo '<div class="dh-main"><div class="dh-shell dh-main__inner">';
		echo $this->get_logo_markup( $render_data, 'default-mobile' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '<nav class="dh-nav" aria-label="' . esc_attr__( 'Primary navigation', 'dope-header' ) . '">';
		if ( '' !== trim( $render_data['desktop_menu'] ) ) {
			echo $render_data['desktop_menu']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( $render_data['is_editor'] ) {
			echo '<div class="dh-menu-fallback">' . esc_html( $render_data['fallback_text'] ) . '</div>';
		}
		echo '</nav>';

		echo '<div class="dh-actions-wrap"><div class="dh-actions">';
		$this->render_actions( $settings );
		echo '</div>';

		if ( $render_data['mobile_enabled'] ) {
			echo '<button type="button" class="dh-mobile-toggle" aria-expanded="false" aria-controls="' . esc_attr( $render_data['drawer_id'] ) . '" aria-label="' . esc_attr__( 'Open menu', 'dope-header' ) . '"><span class="dh-mobile-toggle__line"></span><span class="dh-mobile-toggle__line"></span><span class="dh-mobile-toggle__line"></span></button>';
		}

		echo '</div></div></div>';

		echo '<div class="dh-brand-layer"><div class="dh-shell">';
		echo $this->get_logo_markup( $render_data, 'default' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></div>';

		$this->render_mobile_menu_container( $settings, $render_data, false );

		echo '</header>';
	}

	/**
	 * Renders the minimal widget layout.
	 *
	 * @param array $settings    Widget settings.
	 * @param array $render_data Prepared render data.
	 * @return void
	 */
	private function render_minimal_layout( array $settings, array $render_data ): void {
		$widget_classes = 'dh-widget dh-widget--layout-minimal dh-widget--mobile-menu-' . sanitize_html_class( $render_data['mobile_menu_mode'] );
		if ( $render_data['sticky_enabled'] ) {
			$widget_classes .= ' dh-widget--sticky';
		}

		$style_parts = array(
			'--dh-mobile-breakpoint:' . $render_data['mobile_breakpoint'] . 'px',
			'--dh-minimal-height:' . $render_data['minimal_header_height'] . 'px',
			'--dh-minimal-height-scrolled:' . $render_data['minimal_header_height_scrolled'] . 'px',
		);

		$sticky_config = array(
			'enabled'   => $render_data['sticky_enabled'],
			'shrink'    => $render_data['shrink_enabled'],
			'threshold' => $render_data['sticky_scroll_threshold'],
		);

		echo '<header class="' . esc_attr( $widget_classes ) . '" id="' . esc_attr( $render_data['uid'] ) . '" style="' . esc_attr( implode( ';', $style_parts ) ) . '" data-dh-mobile-config="' . esc_attr( $render_data['mobile_config_json'] ) . '" data-dh-sticky-config="' . esc_attr( wp_json_encode( $sticky_config ) ) . '">';
		echo '<div class="dh-minimal-main"><div class="dh-shell dh-minimal-main__inner">';

		echo $this->get_logo_markup( $render_data, 'minimal' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '<nav class="dh-minimal-nav" aria-label="' . esc_attr__( 'Primary navigation', 'dope-header' ) . '">';
		if ( '' !== trim( $render_data['desktop_menu'] ) ) {
			echo $render_data['desktop_menu']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( $render_data['is_editor'] ) {
			echo '<div class="dh-menu-fallback">' . esc_html( $render_data['fallback_text'] ) . '</div>';
		}
		echo '</nav>';

		echo '<div class="dh-minimal-actions">';
		$this->render_minimal_cta( $settings, 'dh-minimal-cta' );

		if ( $render_data['mobile_enabled'] ) {
			echo '<button type="button" class="dh-mobile-toggle" aria-expanded="false" aria-controls="' . esc_attr( $render_data['drawer_id'] ) . '" aria-label="' . esc_attr__( 'Open menu', 'dope-header' ) . '"><span class="dh-mobile-toggle__line"></span><span class="dh-mobile-toggle__line"></span><span class="dh-mobile-toggle__line"></span></button>';
		}

		echo '</div></div></div>';

		$this->render_mobile_menu_container( $settings, $render_data, true );

		echo '</header>';
	}

	/**
	 * Renders the mobile menu container for the selected presentation mode.
	 *
	 * @param array $settings        Widget settings.
	 * @param array $render_data     Prepared render data.
	 * @param bool  $include_minimal Whether to render the minimal CTA in mobile.
	 * @return void
	 */
	private function render_mobile_menu_container( array $settings, array $render_data, bool $include_minimal ): void {
		if ( ! $render_data['mobile_enabled'] ) {
			return;
		}

		$mode = $render_data['mobile_menu_mode'];

		echo '<div class="dh-mobile-drawer dh-mobile-drawer--' . esc_attr( $mode ) . '" id="' . esc_attr( $render_data['drawer_id'] ) . '" hidden>';
		echo '<button type="button" class="dh-mobile-drawer__overlay" data-dh-drawer-close aria-label="' . esc_attr__( 'Close menu', 'dope-header' ) . '"></button>';
		echo '<div class="dh-mobile-drawer__panel" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Mobile menu', 'dope-header' ) . '">';

		if ( 'drawer' === $mode ) {
			echo '<div class="dh-mobile-drawer__header"><span class="dh-mobile-drawer__title">' . esc_html__( 'Menu', 'dope-header' ) . '</span><button type="button" class="dh-mobile-drawer__close" data-dh-drawer-close aria-label="' . esc_attr__( 'Close menu', 'dope-header' ) . '">&times;</button></div>';
		}

		echo '<nav class="dh-mobile-drawer__nav" aria-label="' . esc_attr__( 'Mobile navigation', 'dope-header' ) . '">';
		if ( '' !== trim( $render_data['mobile_menu'] ) ) {
			echo $render_data['mobile_menu']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( $render_data['is_editor'] ) {
			echo '<div class="dh-menu-fallback">' . esc_html( $render_data['fallback_text'] ) . '</div>';
		}
		echo '</nav>';

		if ( $include_minimal ) {
			echo '<div class="dh-minimal-drawer-cta">';
			$this->render_minimal_cta( $settings, 'dh-minimal-cta dh-minimal-cta--mobile' );
			echo '</div>';
		} elseif ( 'drawer' === $mode ) {
			echo '<div class="dh-actions dh-actions--mobile">';
			$this->render_actions( $settings );
			echo '</div>';
		}

		echo '</div></div>';
	}

	/**
	 * Renders the minimal layout CTA button.
	 *
	 * @param array  $settings   Widget settings.
	 * @param string $class_name CSS class list.
	 * @return void
	 */
	private function render_minimal_cta( array $settings, string $class_name ): void {
		$label           = isset( $settings['cta_button_text'] ) ? sanitize_text_field( $settings['cta_button_text'] ) : '';
		$label           = '' !== $label ? $label : esc_html__( 'Contact', 'dope-header' );
		$cta_link        = $settings['cta_button_link'] ?? array();
		$url             = $this->get_url_value( $cta_link, '#' );
		$link_attributes = $this->get_link_attributes( $cta_link );
		$aria_label      = '' !== $label ? $label : esc_html__( 'Call to action', 'dope-header' );

		printf(
			'<a class="%1$s" href="%2$s"%3$s%4$s aria-label="%5$s"><span class="dh-minimal-cta__label">%6$s</span></a>',
			esc_attr( $class_name ),
			esc_url( $url ),
			isset( $link_attributes['target'] ) ? ' target="' . esc_attr( $link_attributes['target'] ) . '"' : '',
			isset( $link_attributes['rel'] ) ? ' rel="' . esc_attr( $link_attributes['rel'] ) . '"' : '',
			esc_attr( $aria_label ),
			esc_html( $label )
		);
	}

	/**
	 * Renders the action links.
	 *
	 * @param array $settings Widget settings.
	 * @return void
	 */
	private function render_actions( array $settings ): void {
		$action_items = $this->get_action_items( $settings );

		foreach ( $action_items as $action_item ) {
			$this->render_single_action( $action_item );
		}

		if ( ! $this->is_enabled( $settings, 'show_language_menu', true ) ) {
			return;
		}

		$language_menu_id   = isset( $settings['language_menu_id'] ) ? absint( $settings['language_menu_id'] ) : 0;
		$language_menu_item = $this->get_language_menu_item( $language_menu_id );

		if ( ! $language_menu_item ) {
			if ( $this->is_editor_mode() ) {
				echo '<span class="dh-language dh-language--placeholder">' . esc_html__( 'Select a language menu', 'dope-header' ) . '</span>';
			}
			return;
		}

		$language_label      = sanitize_text_field( $language_menu_item->title );
		$language_url        = ! empty( $language_menu_item->url ) ? $language_menu_item->url : '#';
		$language_attributes = $this->get_menu_item_link_attributes( $language_menu_item );

		printf(
			'<a class="dh-language" href="%1$s"%2$s%3$s><span class="dh-language__label">%4$s</span>',
			esc_url( $language_url ),
			isset( $language_attributes['target'] ) ? ' target="' . esc_attr( $language_attributes['target'] ) . '"' : '',
			isset( $language_attributes['rel'] ) ? ' rel="' . esc_attr( $language_attributes['rel'] ) . '"' : '',
			esc_html( $language_label )
		);

		if ( $this->is_enabled( $settings, 'show_language_chevron', true ) ) {
			$icon_html = $this->get_icon_html( $settings['language_chevron_icon'] ?? array() );
			if ( '' === $icon_html || false === strpos( strtolower( $icon_html ), '<svg' ) ) {
				$icon_html = $this->render_inline_chevron( false );
			}
			echo '<span class="dh-language__chevron" aria-hidden="true">';
			echo wp_kses( $icon_html, $this->get_allowed_svg_html() );
			echo '</span>';
		}

		echo '</a>';
	}

	/**
	 * Renders a single header action link.
	 *
	 * @param array $item Action repeater row.
	 * @return void
	 */
	private function render_single_action( array $item ): void {
		$label            = isset( $item['action_label'] ) ? sanitize_text_field( $item['action_label'] ) : esc_html__( 'Action', 'dope-header' );
		$url              = $this->get_url_value( $item['action_link'] ?? array(), '#' );
		$link_attributes  = $this->get_link_attributes( $item['action_link'] ?? array() );
		$action_icon_html = $this->get_action_icon_html( $item['action_icon'] ?? array() );

		if ( '' === $action_icon_html ) {
			return;
		}

		printf(
			'<a class="dh-action" href="%1$s"%2$s%3$s>',
			esc_url( $url ),
			isset( $link_attributes['target'] ) ? ' target="' . esc_attr( $link_attributes['target'] ) . '"' : '',
			isset( $link_attributes['rel'] ) ? ' rel="' . esc_attr( $link_attributes['rel'] ) . '"' : ''
		);
		echo wp_kses( $action_icon_html, $this->get_allowed_svg_html() );
		echo '<span class="screen-reader-text">' . esc_html( $label ) . '</span></a>';
	}

	/**
	 * Renders a topbar social icon link.
	 *
	 * @param array $item Social repeater row.
	 * @return void
	 */
	private function render_topbar_social_icon( array $item ): void {
		$url             = $this->get_url_value( $item['social_link'] ?? array(), '#' );
		$link_attributes = $this->get_link_attributes( $item['social_link'] ?? array() );
		$label           = isset( $item['social_label'] ) ? sanitize_text_field( $item['social_label'] ) : esc_html__( 'Social link', 'dope-header' );
		$icon_html       = $this->get_fontawesome_icon_html( $item['social_icon'] ?? array() );
		$item_id         = isset( $item['_id'] ) ? sanitize_html_class( (string) $item['_id'] ) : '';
		$network_slug    = sanitize_html_class( sanitize_title( $label ) );

		if ( '' === $label ) {
			$label = esc_html__( 'Social link', 'dope-header' );
		}

		if ( '' === $icon_html ) {
			return;
		}

		$classes = array(
			'dh-topbar__social',
			'elementor-icon',
			'elementor-social-icon',
		);

		if ( '' !== $network_slug ) {
			$classes[] = 'elementor-social-icon-' . $network_slug;
		}

		if ( '' !== $item_id ) {
			$classes[] = 'elementor-repeater-item-' . $item_id;
		}

		printf(
			'<a class="%1$s" href="%2$s"%3$s%4$s>',
			esc_attr( implode( ' ', $classes ) ),
			esc_url( $url ),
			isset( $link_attributes['target'] ) ? ' target="' . esc_attr( $link_attributes['target'] ) . '"' : '',
			isset( $link_attributes['rel'] ) ? ' rel="' . esc_attr( $link_attributes['rel'] ) . '"' : ''
		);
		echo '<span class="elementor-screen-only">' . esc_html( $label ) . '</span>';
		echo wp_kses( $icon_html, $this->get_allowed_svg_html() );
		echo '</a>';
	}

	/**
	 * Gets Font Awesome <i> markup from an Elementor icon control value.
	 *
	 * @param array $icon Icon control value.
	 * @return string
	 */
	private function get_fontawesome_icon_html( array $icon ): string {
		if ( empty( $icon['value'] ) || ! is_string( $icon['value'] ) ) {
			return '';
		}

		$classes = array_filter(
			array_map(
				'sanitize_html_class',
				preg_split( '/\s+/', trim( $icon['value'] ) )
			)
		);

		if ( empty( $classes ) ) {
			return '';
		}

		return sprintf(
			'<i aria-hidden="true" class="%s"></i>',
			esc_attr( implode( ' ', $classes ) )
		);
	}

	/**
	 * Gets the markup for a configured action icon.
	 *
	 * @param array $icon Icon control value.
	 * @return string
	 */
	private function get_action_icon_html( array $icon ): string {
		$icon_html = $this->get_fontawesome_icon_html( $icon );

		if ( '' !== $icon_html ) {
			return $icon_html;
		}

		return '';
	}

	/**
	 * Gets built-in SVG markup for a supported icon.
	 *
	 * @param string $icon_key Icon key.
	 * @return string
	 */
	private function get_builtin_icon_svg( string $icon_key ): string {
		switch ( $icon_key ) {
			case 'search':
				return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M16 16l5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			case 'account':
				return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 21c1.8-3.3 4.5-5 8-5s6.2 1.7 8 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			case 'cart':
				return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 4h2l1.6 9.5a2 2 0 0 0 2 1.6H18a2 2 0 0 0 1.9-1.4l1.3-5.7H7.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9.2" cy="19.2" r="1.5" fill="currentColor"/><circle cx="17.2" cy="19.2" r="1.5" fill="currentColor"/></svg>';
			case 'facebook':
				return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M13.3 21v-7h2.3l.4-2.8h-2.7V9.4c0-.8.2-1.4 1.4-1.4h1.5V5.6c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 4v1.7H7.8V14h2.3v7h3.2z" fill="currentColor"/></svg>';
			case 'instagram':
				return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="4.5" y="4.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.3" cy="6.8" r="1.1" fill="currentColor"/></svg>';
			case 'whatsapp':
				return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 4a8 8 0 0 0-6.9 12.1L4 20l4-1A8 8 0 1 0 12 4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9.3 9.2c.2-.5.4-.5.7-.5h.6c.2 0 .4 0 .5.4l.6 1.5c.1.3 0 .4-.1.6l-.5.6c.6 1.2 1.5 2.1 2.7 2.7l.6-.5c.2-.1.3-.2.6-.1l1.5.6c.4.1.4.3.4.5v.6c0 .3 0 .5-.5.7-.4.2-1.3.4-2.2.2-1.1-.2-2.4-.9-3.6-2.1-1.2-1.2-1.9-2.5-2.1-3.6-.2-.9 0-1.8.2-2.2z" fill="currentColor"/></svg>';
			default:
				return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>';
		}
	}

	/**
	 * Gets Elementor icon markup for an icon control.
	 *
	 * @param array $icon Icon control value.
	 * @return string
	 */
	private function get_icon_html( array $icon ): string {
		if ( empty( $icon['value'] ) ) {
			return '';
		}

		ob_start();
		Icons_Manager::render_icon(
			$icon,
			array(
				'aria-hidden' => 'true',
			)
		);

		return (string) ob_get_clean();
	}

	/**
	 * Gets rendered menu markup.
	 *
	 * @param int    $menu_id    WordPress menu term ID.
	 * @param string $class_name Menu class attribute.
	 * @return string
	 */
	private function get_menu_markup( int $menu_id, string $class_name ): string {
		if ( $menu_id <= 0 ) {
			return '';
		}

		$menu_markup = wp_nav_menu(
			array(
				'menu'        => $menu_id,
				'container'   => false,
				'menu_class'  => $class_name,
				'fallback_cb' => '__return_empty_string',
				'echo'        => false,
				'depth'       => 2,
			)
		);

		return is_string( $menu_markup ) ? $menu_markup : '';
	}

	/**
	 * Gets rendered mobile menu markup with optional submenu toggles.
	 *
	 * @param int    $menu_id          WordPress menu term ID.
	 * @param string $class_name       Menu class attribute.
	 * @param bool   $enable_submenus  Whether submenu toggles should be rendered.
	 * @param string $instance_prefix  Unique widget instance prefix.
	 * @return string
	 */
	private function get_mobile_menu_markup( int $menu_id, string $class_name, bool $enable_submenus, string $instance_prefix ): string {
		if ( ! $enable_submenus || ! class_exists( 'Dope_Header_Mobile_Menu_Walker' ) ) {
			return $this->get_menu_markup( $menu_id, $class_name );
		}

		$menu_markup = wp_nav_menu(
			array(
				'menu'        => $menu_id,
				'container'   => false,
				'menu_class'  => $class_name,
				'fallback_cb' => '__return_empty_string',
				'echo'        => false,
				'depth'       => 2,
				'walker'      => new Dope_Header_Mobile_Menu_Walker( $instance_prefix ),
			)
		);

		return is_string( $menu_markup ) ? $menu_markup : '';
	}

	/**
	 * Gets the first top-level item from a selected language menu.
	 *
	 * @param int $menu_id WordPress menu term ID.
	 * @return object|null
	 */
	private function get_language_menu_item( int $menu_id ) {
		if ( $menu_id <= 0 ) {
			return null;
		}

		$items = wp_get_nav_menu_items(
			$menu_id,
			array(
				'update_post_term_cache' => false,
			)
		);

		if ( ! is_array( $items ) ) {
			return null;
		}

		foreach ( $items as $item ) {
			if ( isset( $item->menu_item_parent ) && 0 === (int) $item->menu_item_parent ) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Gets available WordPress navigation menus for the control.
	 *
	 * @return array<string, string>
	 */
	private function get_menu_options(): array {
		$options = array(
			'' => esc_html__( 'Select Menu', 'dope-header' ),
		);

		$menus = wp_get_nav_menus();
		if ( is_array( $menus ) ) {
			foreach ( $menus as $menu ) {
				if ( isset( $menu->term_id, $menu->name ) ) {
					$options[ (string) absint( $menu->term_id ) ] = $menu->name;
				}
			}
		}

		return $options;
	}

	/**
	 * Gets the selected widget layout.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_header_layout( array $settings ): string {
		$layout = isset( $settings['header_layout'] ) ? sanitize_key( $settings['header_layout'] ) : 'default';

		return in_array( $layout, array( 'default', 'minimal' ), true ) ? $layout : 'default';
	}

	/**
	 * Gets the selected mobile menu presentation mode.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $layout   Resolved header layout.
	 * @return string
	 */
	private function get_mobile_menu_mode( array $settings, string $layout ): string {
		$mode = isset( $settings['mobile_menu_mode'] ) ? sanitize_key( $settings['mobile_menu_mode'] ) : '';

		if ( in_array( $mode, array( 'drawer', 'dropdown' ), true ) ) {
			return $mode;
		}

		return 'minimal' === $layout ? 'dropdown' : 'drawer';
	}

	/**
	 * Gets the configured logo type.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_logo_type( array $settings ): string {
		$logo_type = isset( $settings['logo_type'] ) ? sanitize_key( (string) $settings['logo_type'] ) : 'image';

		return in_array( $logo_type, array( 'image', 'text' ), true ) ? $logo_type : 'image';
	}

	/**
	 * Gets the configured text logo value.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_logo_text( array $settings ): string {
		$logo_text = isset( $settings['logo_text'] ) ? sanitize_text_field( $settings['logo_text'] ) : '';

		return '' !== $logo_text ? $logo_text : get_bloginfo( 'name' );
	}

	/**
	 * Gets the configured logo source URL.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_logo_src( array $settings ): string {
		if ( isset( $settings['logo_image']['url'] ) && '' !== $settings['logo_image']['url'] ) {
			return esc_url( $settings['logo_image']['url'] );
		}

		return Utils::get_placeholder_image_src();
	}

	/**
	 * Gets the configured logo alt text.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_logo_alt( array $settings ): string {
		$logo_alt = isset( $settings['logo_alt'] ) ? sanitize_text_field( $settings['logo_alt'] ) : '';

		return '' !== $logo_alt ? $logo_alt : get_bloginfo( 'name' );
	}

	/**
	 * Builds the rendered logo markup for a header layout.
	 *
	 * @param array  $render_data Prepared render data.
	 * @param string $layout      Target layout.
	 * @return string
	 */
	private function get_logo_markup( array $render_data, string $layout ): string {
		if ( 'minimal' === $layout ) {
			$wrap_class = 'dh-minimal-brand';
			$link_class = 'dh-minimal-brand__link';
			$text_class = 'dh-minimal-brand__text';
			$logo_class = 'dh-minimal-brand__logo';
		} elseif ( 'default-mobile' === $layout ) {
			$wrap_class = 'dh-default-mobile-brand';
			$link_class = 'dh-default-mobile-brand__link';
			$text_class = 'dh-default-mobile-brand__text';
			$logo_class = 'dh-default-mobile-brand__logo';
		} else {
			$wrap_class = 'dh-brand-float';
			$link_class = 'dh-brand-float__link';
			$text_class = 'dh-brand-float__text';
			$logo_class = 'dh-brand-float__logo';
		}
		$link_attrs = '';

		if ( isset( $render_data['logo_attributes']['target'] ) ) {
			$link_attrs .= ' target="' . esc_attr( $render_data['logo_attributes']['target'] ) . '"';
		}

		if ( isset( $render_data['logo_attributes']['rel'] ) ) {
			$link_attrs .= ' rel="' . esc_attr( $render_data['logo_attributes']['rel'] ) . '"';
		}

		if ( 'text' === $render_data['logo_type'] ) {
			return sprintf(
				'<div class="%1$s"><a class="%2$s" href="%3$s"%4$s><span class="%5$s">%6$s</span></a></div>',
				esc_attr( $wrap_class ),
				esc_attr( $link_class ),
				esc_url( $render_data['logo_url'] ),
				$link_attrs,
				esc_attr( $text_class ),
				esc_html( $render_data['logo_text'] )
			);
		}

		return sprintf(
			'<div class="%1$s"><a class="%2$s" href="%3$s"%4$s><img class="%5$s" src="%6$s" alt="%7$s" loading="lazy" /></a></div>',
			esc_attr( $wrap_class ),
			esc_attr( $link_class ),
			esc_url( $render_data['logo_url'] ),
			$link_attrs,
			esc_attr( $logo_class ),
			esc_url( $render_data['logo_src'] ),
			esc_attr( $render_data['logo_alt'] )
		);
	}

	/**
	 * Gets sanitized topbar announcement items.
	 *
	 * @param array $settings Widget settings.
	 * @return array<int, string>
	 */
	private function get_topbar_items( array $settings ): array {
		$items = array();
		$raw   = isset( $settings['topbar_items'] ) && is_array( $settings['topbar_items'] ) ? $settings['topbar_items'] : array();

		foreach ( $raw as $row ) {
			$text = isset( $row['announcement_text'] ) ? sanitize_text_field( $row['announcement_text'] ) : '';
			if ( '' !== $text ) {
				$items[] = $text;
			}
		}

		return $items;
	}

	/**
	 * Gets sanitized topbar social repeater items.
	 *
	 * @param array $settings Widget settings.
	 * @return array<int, array<string, mixed>>
	 */
	private function get_topbar_social_items( array $settings ): array {
		$items = array();
		$raw   = isset( $settings['topbar_social_items'] ) && is_array( $settings['topbar_social_items'] ) ? $settings['topbar_social_items'] : array();

		foreach ( $raw as $row ) {
			$icon = isset( $row['social_icon'] ) && is_array( $row['social_icon'] ) ? $row['social_icon'] : array();

			if ( empty( $icon['value'] ) ) {
				continue;
			}

			$items[] = array(
				'_id'          => isset( $row['_id'] ) ? sanitize_key( $row['_id'] ) : '',
				'social_label' => isset( $row['social_label'] ) ? sanitize_text_field( $row['social_label'] ) : '',
				'social_icon'  => $icon,
				'social_link'  => isset( $row['social_link'] ) && is_array( $row['social_link'] ) ? $row['social_link'] : array(),
			);
		}

		return $items;
	}

	/**
	 * Gets sanitized action repeater items.
	 *
	 * @param array $settings Widget settings.
	 * @return array<int, array<string, mixed>>
	 */
	private function get_action_items( array $settings ): array {
		$items = array();
		$raw   = isset( $settings['action_items'] ) && is_array( $settings['action_items'] ) ? $settings['action_items'] : array();

		foreach ( $raw as $row ) {
			$icon = isset( $row['action_icon'] ) && is_array( $row['action_icon'] ) ? $row['action_icon'] : array();

			if ( empty( $icon['value'] ) ) {
				continue;
			}

			$items[] = array(
				'action_label' => isset( $row['action_label'] ) ? sanitize_text_field( $row['action_label'] ) : '',
				'action_link'  => isset( $row['action_link'] ) && is_array( $row['action_link'] ) ? $row['action_link'] : array(),
				'action_icon'  => $icon,
			);
		}

		return $items;
	}

	/**
	 * Gets inline chevron SVG markup.
	 *
	 * @param bool $left Whether to render the left-facing chevron.
	 * @return string
	 */
	private function render_inline_chevron( bool $left ): string {
		if ( $left ) {
			return '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M15 10H5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M9 6l-4 4 4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		}

		return '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M5 10h10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M11 6l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * Gets the SVG tags and attributes allowed for widget icon output.
	 *
	 * @return array<string, array<string, bool>>
	 */
	private function get_allowed_svg_html(): array {
		return array(
			'i'      => array(
				'class'       => true,
				'aria-hidden' => true,
			),
			'span'   => array(
				'class'       => true,
				'aria-hidden' => true,
			),
			'svg'    => array(
				'aria-hidden' => true,
				'focusable'   => true,
				'viewBox'     => true,
				'width'       => true,
				'height'      => true,
				'class'       => true,
				'xmlns'       => true,
				'fill'        => true,
				'stroke'      => true,
				'role'        => true,
			),
			'g'      => array(
				'fill'   => true,
				'stroke' => true,
			),
			'path'   => array(
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'transform'       => true,
			),
			'circle' => array(
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'rect'   => array(
				'x'            => true,
				'y'            => true,
				'width'        => true,
				'height'       => true,
				'rx'           => true,
				'ry'           => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
		);
	}

	/**
	 * Checks whether the widget is rendering inside Elementor editor mode.
	 *
	 * @return bool
	 */
	private function is_editor_mode(): bool {
		if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
			return false;
		}

		$plugin = \Elementor\Plugin::$instance;

		if ( ! isset( $plugin->editor ) || ! method_exists( $plugin->editor, 'is_edit_mode' ) ) {
			return false;
		}

		return (bool) $plugin->editor->is_edit_mode();
	}

	/**
	 * Resolves a switcher setting to a boolean.
	 *
	 * @param array  $settings      Widget settings.
	 * @param string $key           Setting key.
	 * @param bool   $default_value Fallback value when unset.
	 * @return bool
	 */
	private function is_enabled( array $settings, string $key, bool $default_value = false ): bool {
		if ( ! array_key_exists( $key, $settings ) ) {
			return $default_value;
		}

		return 'yes' === $settings[ $key ];
	}

	/**
	 * Sanitizes an integer setting.
	 *
	 * @param mixed $value         Raw value.
	 * @param int   $default_value Default value.
	 * @param int   $min           Minimum accepted value.
	 * @return int
	 */
	private function sanitize_int( $value, int $default_value, int $min = 0 ): int {
		if ( ! is_numeric( $value ) ) {
			return $default_value;
		}

		$value = (int) $value;

		if ( $value < $min ) {
			return $default_value;
		}

		return $value;
	}

	/**
	 * Gets a sanitized URL from a control value.
	 *
	 * @param mixed  $url_control   Raw URL control value.
	 * @param string $default_value Default URL.
	 * @return string
	 */
	private function get_url_value( $url_control, string $default_value = '#' ): string {
		if ( is_array( $url_control ) && isset( $url_control['url'] ) && '' !== $url_control['url'] ) {
			return esc_url( $url_control['url'] );
		}

		if ( is_string( $url_control ) && '' !== $url_control ) {
			return esc_url( $url_control );
		}

		return esc_url( $default_value );
	}

	/**
	 * Builds link attributes from an Elementor URL control.
	 *
	 * @param mixed $url_control URL control value.
	 * @return array<string, string>
	 */
	private function get_link_attributes( $url_control ): array {
		$attributes = array();
		$rel_parts  = array();

		if ( is_array( $url_control ) ) {
			if ( ! empty( $url_control['is_external'] ) ) {
				$attributes['target'] = '_blank';
				$rel_parts[]          = 'noopener';
				$rel_parts[]          = 'noreferrer';
			}

			if ( ! empty( $url_control['nofollow'] ) ) {
				$rel_parts[] = 'nofollow';
			}
		}

		if ( ! empty( $rel_parts ) ) {
			$attributes['rel'] = implode( ' ', array_unique( $rel_parts ) );
		}

		return $attributes;
	}

	/**
	 * Builds link attributes from a WordPress menu item object.
	 *
	 * @param object $menu_item WordPress menu item object.
	 * @return array<string, string>
	 */
	private function get_menu_item_link_attributes( $menu_item ): array {
		$attributes = array();
		$rel_parts  = array();

		if ( ! empty( $menu_item->target ) ) {
			$attributes['target'] = sanitize_text_field( $menu_item->target );
		}

		if ( ! empty( $menu_item->xfn ) ) {
			$rel_parts[] = sanitize_text_field( $menu_item->xfn );
		}

		if ( isset( $attributes['target'] ) && '_blank' === $attributes['target'] ) {
			$rel_parts[] = 'noopener';
			$rel_parts[] = 'noreferrer';
		}

		if ( ! empty( $rel_parts ) ) {
			$attributes['rel'] = implode( ' ', array_unique( $rel_parts ) );
		}

		return $attributes;
	}
}
