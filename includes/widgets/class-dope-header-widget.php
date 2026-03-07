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
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

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
				'label' => esc_html__( 'Topbar', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_topbar',
			array(
				'label'        => esc_html__( 'Enable Topbar', 'dope-header' ),
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
			'topbar_items',
			array(
				'label'       => esc_html__( 'Announcements', 'dope-header' ),
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
			'topbar_show_arrows',
			array(
				'label'        => esc_html__( 'Show Arrows', 'dope-header' ),
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
			'show_topbar_socials',
			array(
				'label'        => esc_html__( 'Show Topbar Social Icons', 'dope-header' ),
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
				'label'       => esc_html__( 'Social Items', 'dope-header' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $social_repeater->get_controls(),
				'title_field' => '{{{ social_label }}}',
				'default'       => array(
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
				'condition'     => array(
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
				'label' => esc_html__( 'Header', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'logo_image',
			array(
				'label'   => esc_html__( 'Logo Image', 'dope-header' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => Utils::get_placeholder_image_src() ),
			)
		);

		$this->add_control(
			'logo_alt',
			array(
				'label'       => esc_html__( 'Logo Alt Text', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => get_bloginfo( 'name' ),
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
			'menu_id',
			array(
				'label'   => esc_html__( 'Navigation Menu', 'dope-header' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_menu_options(),
				'default' => '',
			)
		);

		$this->add_control(
			'menu_fallback_label',
			array(
				'label'       => esc_html__( 'Editor Fallback Label', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Select a WordPress menu in the widget settings.', 'dope-header' ),
			)
		);

		$this->add_control(
			'header_absolute_position',
			array(
				'label'        => esc_html__( 'Absolute Position Header', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Places the header above the next section, useful for hero overlays.', 'dope-header' ),
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
				'label' => esc_html__( 'Actions', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->register_action_controls(
			'search',
			esc_html__( 'Search', 'dope-header' ),
			array(
				'value'   => 'fas fa-magnifying-glass',
				'library' => 'fa-solid',
			)
		);

		$this->register_action_controls(
			'account',
			esc_html__( 'Account', 'dope-header' ),
			array(
				'value'   => 'far fa-user',
				'library' => 'fa-regular',
			)
		);

		$this->register_action_controls(
			'cart',
			esc_html__( 'Cart', 'dope-header' ),
			array(
				'value'   => 'fas fa-cart-shopping',
				'library' => 'fa-solid',
			)
		);

		$this->add_control(
			'language_label',
			array(
				'label'       => esc_html__( 'Language Label', 'dope-header' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'ENG',
			)
		);

		$this->add_control(
			'language_link',
			array(
				'label'         => esc_html__( 'Language Link', 'dope-header' ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'default'       => array(
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
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
				'condition' => array( 'show_language_chevron' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers controls for a single header action.
	 *
	 * @param string $key          Action setting key.
	 * @param string $label        Action label.
	 * @param array  $default_icon Default icon control value.
	 * @return void
	 */
	private function register_action_controls( string $key, string $label, array $default_icon ): void {
		$this->add_control(
			'show_' . $key . '_action',
			array(
				/* translators: %s: header action label. */
				'label'        => sprintf( esc_html__( 'Show %s', 'dope-header' ), $label ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			$key . '_action_link',
			array(
				/* translators: %s: header action label. */
				'label'         => sprintf( esc_html__( '%s Link', 'dope-header' ), $label ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'default'       => array(
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
				),
				'condition'     => array( 'show_' . $key . '_action' => 'yes' ),
			)
		);

		$this->add_control(
			$key . '_action_icon',
			array(
				/* translators: %s: header action label. */
				'label'     => sprintf( esc_html__( '%s Icon', 'dope-header' ), $label ),
				'type'      => Controls_Manager::ICONS,
				'default'   => $default_icon,
				'condition' => array( 'show_' . $key . '_action' => 'yes' ),
			)
		);
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
				'label' => esc_html__( 'Mobile', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_mobile_drawer',
			array(
				'label'        => esc_html__( 'Enable Hamburger Drawer', 'dope-header' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'mobile_breakpoint',
			array(
				'label'     => esc_html__( 'Mobile Breakpoint (px)', 'dope-header' ),
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
				'label' => esc_html__( 'Topbar', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
				'label' => esc_html__( 'Navigation Row', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
					'{{WRAPPER}} .dh-main' => 'border-bottom-color: {{VALUE}};',
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
				'label' => esc_html__( 'Menu Links', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'menu_typography',
				'selector' => '{{WRAPPER}} .dh-menu > li > a',
			)
		);

		$this->add_control(
			'menu_color',
			array(
				'label'     => esc_html__( 'Text Color', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .dh-menu > li > a' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .dh-menu > li > a:hover, {{WRAPPER}} .dh-menu > li > a:focus-visible' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .dh-menu > li.current-menu-item > a::after, {{WRAPPER}} .dh-menu > li.current-menu-ancestor > a::after' => 'background-color: {{VALUE}};',
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
				'label' => esc_html__( 'Actions and Language', 'dope-header' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
			'mobile_drawer_background',
			array(
				'label'     => esc_html__( 'Drawer Background', 'dope-header' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0d0d0d',
				'selectors' => array(
					'{{WRAPPER}} .dh-mobile-drawer__panel' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .dh-menu--mobile a, {{WRAPPER}} .dh-mobile-drawer__close' => 'color: {{VALUE}};',
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

		$topbar_items        = $this->get_topbar_items( $settings );
		$topbar_social_items = $this->get_topbar_social_items( $settings );
		$show_topbar         = $this->is_enabled( $settings, 'enable_topbar', true ) && ! empty( $topbar_items );
		$show_topbar_arrows  = $this->is_enabled( $settings, 'topbar_show_arrows', true ) && count( $topbar_items ) > 1;
		$topbar_autoplay     = $this->is_enabled( $settings, 'topbar_autoplay', true ) && count( $topbar_items ) > 1;
		$show_topbar_socials = $this->is_enabled( $settings, 'show_topbar_socials', true ) && ! empty( $topbar_social_items );

		$mobile_enabled      = $this->is_enabled( $settings, 'enable_mobile_drawer', true );
		$mobile_breakpoint   = $this->sanitize_int( $settings['mobile_breakpoint'] ?? 1024, 1024, 640 );
		$mobile_close_on_nav = $this->is_enabled( $settings, 'mobile_close_on_link_click', true );

		$menu_id       = isset( $settings['menu_id'] ) ? absint( $settings['menu_id'] ) : 0;
		$desktop_menu  = $this->get_menu_markup( $menu_id, 'dh-menu dh-menu--desktop' );
		$mobile_menu   = $this->get_menu_markup( $menu_id, 'dh-menu dh-menu--mobile' );
		$is_editor     = $this->is_editor_mode();
		$fallback_text = isset( $settings['menu_fallback_label'] ) ? sanitize_text_field( $settings['menu_fallback_label'] ) : '';

		$logo_src = Utils::get_placeholder_image_src();
		if ( isset( $settings['logo_image']['url'] ) && '' !== $settings['logo_image']['url'] ) {
			$logo_src = esc_url( $settings['logo_image']['url'] );
		}

		$logo_alt = isset( $settings['logo_alt'] ) ? sanitize_text_field( $settings['logo_alt'] ) : '';
		if ( '' === $logo_alt ) {
			$logo_alt = get_bloginfo( 'name' );
		}

		$logo_url        = $this->get_url_value( $settings['logo_link'] ?? array(), home_url( '/' ) );
		$logo_attributes = $this->get_link_attributes( $settings['logo_link'] ?? array() );

		$uid       = wp_unique_id( 'dh-widget-' );
		$drawer_id = $uid . '-drawer';

		$topbar_config = array(
			'items'        => $topbar_items,
			'autoplay'     => $topbar_autoplay,
			'delay'        => $this->sanitize_int( $settings['topbar_autoplay_delay'] ?? 3500, 3500, 1000 ),
			'pauseOnHover' => $this->is_enabled( $settings, 'topbar_pause_on_hover', true ),
			'arrows'       => $show_topbar_arrows,
		);

		$mobile_config = array(
			'enabled'          => $mobile_enabled,
			'breakpoint'       => $mobile_breakpoint,
			'closeOnLinkClick' => $mobile_close_on_nav,
		);

		$widget_classes = 'dh-widget' . ( $show_topbar ? '' : ' dh-widget--no-topbar' );

		if ( $this->is_enabled( $settings, 'header_absolute_position', false ) ) {
			$widget_classes .= ' dh-widget--absolute';
		}

		echo '<header class="' . esc_attr( $widget_classes ) . '" id="' . esc_attr( $uid ) . '" style="--dh-mobile-breakpoint:' . esc_attr( (string) $mobile_breakpoint ) . 'px;" data-dh-mobile-config="' . esc_attr( wp_json_encode( $mobile_config ) ) . '">';

		if ( $show_topbar ) {
			echo '<div class="dh-topbar" data-dh-config="' . esc_attr( wp_json_encode( $topbar_config ) ) . '">';
			echo '<div class="dh-shell dh-topbar__inner">';
			echo '<div class="dh-topbar__center">';

			if ( $show_topbar_arrows ) {
				echo '<button type="button" class="dh-topbar__arrow dh-topbar__arrow--prev" data-dh-prev aria-label="' . esc_attr__( 'Previous announcement', 'dope-header' ) . '">';
				echo wp_kses( $this->render_inline_chevron( true ), $this->get_allowed_svg_html() );
				echo '</button>';
			}

			echo '<div class="dh-topbar__viewport" aria-live="polite"><div class="dh-topbar__track">';
			foreach ( $topbar_items as $index => $item ) {
				$is_active = 0 === $index;
				echo '<span class="dh-topbar__item' . ( $is_active ? ' is-active' : '' ) . '" data-dh-index="' . esc_attr( (string) $index ) . '"' . ( $is_active ? '' : ' hidden' ) . '>' . esc_html( $item ) . '</span>';
			}
			echo '</div></div>';

			if ( $show_topbar_arrows ) {
				echo '<button type="button" class="dh-topbar__arrow dh-topbar__arrow--next" data-dh-next aria-label="' . esc_attr__( 'Next announcement', 'dope-header' ) . '">';
				echo wp_kses( $this->render_inline_chevron( false ), $this->get_allowed_svg_html() );
				echo '</button>';
			}

			echo '</div>';

			if ( $show_topbar_socials ) {
				echo '<div class="dh-topbar__socials">';
				foreach ( $topbar_social_items as $social_item ) {
					$this->render_topbar_social_icon( $social_item );
				}
				echo '</div>';
			}

			echo '</div></div>';
		}

		echo '<div class="dh-main"><div class="dh-shell dh-main__inner">';

		echo '<nav class="dh-nav" aria-label="' . esc_attr__( 'Primary navigation', 'dope-header' ) . '">';
		if ( '' !== trim( $desktop_menu ) ) {
			echo $desktop_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( $is_editor ) {
			echo '<div class="dh-menu-fallback">' . esc_html( $fallback_text ) . '</div>';
		}
		echo '</nav>';

		echo '<div class="dh-actions-wrap"><div class="dh-actions">';
		$this->render_actions( $settings );
		echo '</div>';

		if ( $mobile_enabled ) {
			echo '<button type="button" class="dh-mobile-toggle" aria-expanded="false" aria-controls="' . esc_attr( $drawer_id ) . '" aria-label="' . esc_attr__( 'Open menu', 'dope-header' ) . '"><span class="dh-mobile-toggle__line"></span><span class="dh-mobile-toggle__line"></span><span class="dh-mobile-toggle__line"></span></button>';
		}

		echo '</div></div></div>';

		echo '<div class="dh-brand-layer"><div class="dh-shell">';
		printf(
			'<div class="dh-brand-float"><a class="dh-brand-float__link" href="%1$s"%2$s%3$s><img class="dh-brand-float__logo" src="%4$s" alt="%5$s" loading="lazy" /></a></div>',
			esc_url( $logo_url ),
			isset( $logo_attributes['target'] ) ? ' target="' . esc_attr( $logo_attributes['target'] ) . '"' : '',
			isset( $logo_attributes['rel'] ) ? ' rel="' . esc_attr( $logo_attributes['rel'] ) . '"' : '',
			esc_url( $logo_src ),
			esc_attr( $logo_alt )
		);
		echo '</div></div>';

		if ( $mobile_enabled ) {
			echo '<div class="dh-mobile-drawer" id="' . esc_attr( $drawer_id ) . '" hidden>';
			echo '<button type="button" class="dh-mobile-drawer__overlay" data-dh-drawer-close aria-label="' . esc_attr__( 'Close menu', 'dope-header' ) . '"></button>';
			echo '<div class="dh-mobile-drawer__panel" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Mobile menu', 'dope-header' ) . '">';
			echo '<div class="dh-mobile-drawer__header"><span class="dh-mobile-drawer__title">' . esc_html__( 'Menu', 'dope-header' ) . '</span><button type="button" class="dh-mobile-drawer__close" data-dh-drawer-close aria-label="' . esc_attr__( 'Close menu', 'dope-header' ) . '">&times;</button></div>';
			echo '<nav class="dh-mobile-drawer__nav" aria-label="' . esc_attr__( 'Mobile navigation', 'dope-header' ) . '">';
			if ( '' !== trim( $mobile_menu ) ) {
				echo $mobile_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} elseif ( $is_editor ) {
				echo '<div class="dh-menu-fallback">' . esc_html( $fallback_text ) . '</div>';
			}
			echo '</nav>';
			echo '<div class="dh-actions dh-actions--mobile">';
			$this->render_actions( $settings );
			echo '</div></div></div>';
		}

		echo '</header>';
	}

	/**
	 * Renders the action links.
	 *
	 * @param array $settings Widget settings.
	 * @return void
	 */
	private function render_actions( array $settings ): void {
		$this->render_single_action( $settings, 'search', esc_html__( 'Search', 'dope-header' ) );
		$this->render_single_action( $settings, 'account', esc_html__( 'Account', 'dope-header' ) );
		$this->render_single_action( $settings, 'cart', esc_html__( 'Cart', 'dope-header' ) );

		$language_label      = isset( $settings['language_label'] ) ? sanitize_text_field( $settings['language_label'] ) : 'ENG';
		$language_url        = $this->get_url_value( $settings['language_link'] ?? array(), '#' );
		$language_attributes = $this->get_link_attributes( $settings['language_link'] ?? array() );

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
	 * @param array  $settings Widget settings.
	 * @param string $key      Action key.
	 * @param string $label    Action label.
	 * @return void
	 */
	private function render_single_action( array $settings, string $key, string $label ): void {
		if ( ! $this->is_enabled( $settings, 'show_' . $key . '_action', true ) ) {
			return;
		}

		$url              = $this->get_url_value( $settings[ $key . '_action_link' ] ?? array(), '#' );
		$link_attributes  = $this->get_link_attributes( $settings[ $key . '_action_link' ] ?? array() );
		$action_icon_html = $this->get_action_icon_html( $settings, $key );

		printf(
			'<a class="dh-action dh-action--%1$s" href="%2$s"%3$s%4$s>',
			esc_attr( $key ),
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
	 * @param array  $settings Widget settings.
	 * @param string $key      Action key.
	 * @return string
	 */
	private function get_action_icon_html( array $settings, string $key ): string {
		$icon_html = $this->get_icon_html( $settings[ $key . '_action_icon' ] ?? array() );

		if ( '' !== $icon_html && false !== strpos( strtolower( $icon_html ), '<svg' ) ) {
			return $icon_html;
		}

		return $this->get_builtin_icon_svg( $key );
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
	 * Gets inline chevron SVG markup.
	 *
	 * @param bool $left Whether to render the left-facing chevron.
	 * @return string
	 */
	private function render_inline_chevron( bool $left ): string {
		if ( $left ) {
			return '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M12.2 4.8a1 1 0 0 1 0 1.4L8.4 10l3.8 3.8a1 1 0 1 1-1.4 1.4l-4.5-4.5a1 1 0 0 1 0-1.4l4.5-4.5a1 1 0 0 1 1.4 0z" fill="currentColor"/></svg>';
		}

		return '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M7.8 15.2a1 1 0 0 1 0-1.4l3.8-3.8-3.8-3.8a1 1 0 1 1 1.4-1.4l4.5 4.5a1 1 0 0 1 0 1.4l-4.5 4.5a1 1 0 0 1-1.4 0z" fill="currentColor"/></svg>';
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
}
