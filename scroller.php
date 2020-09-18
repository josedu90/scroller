<?php
/**
 * Plugin Name: Scroller
 * Plugin URI: https://1.envato.market/scroller
 * Description: Scroller is a beautifully designed scroll bar for any element on a page or a whole WordPress page.
 * Author: Merkulove
 * Version: 1.1.5
 * Author URI: https://1.envato.market/cc-merkulove
 * Requires PHP: 5.6
 * Requires at least: 4.0
 * Tested up to: 5.4
 **/

/**
 * Scroller is a beautifully designed scroll bar for any element on a page or a whole WordPress page.
 * Exclusively on Envato Market: https://1.envato.market/scroller
 *
 * @encoding        UTF-8
 * @version         1.1.5
 * @copyright       Copyright (C) 2018 - 2020 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design), Alexander Khmelnitskiy (info@alexander.khmelnitskiy.ua)
 * @support         help@merkulov.design
 **/

namespace Merkulove;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/** Include plugin autoloader for additional classes. */
require __DIR__ . '/src/autoload.php';

use Merkulove\Scroller\AssignmentsTab;
use Merkulove\Scroller\EnvatoItem;
use Merkulove\Scroller\PluginActivation;
use Merkulove\Scroller\PluginUpdater;
use Merkulove\Scroller\MobileDetect;
use Merkulove\Scroller\PluginCustomCss;
use Merkulove\Scroller\StatusTab;
use Merkulove\Scroller\UninstallTab;
use Merkulove\Scroller\PluginHelper;
use Merkulove\Scroller\Helper;
use Merkulove\Scroller\UI;

/**
 * SINGLETON: Core class used to implement plugin.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since 1.0.0
 */
final class Scroller {

	/**
	 * Plugin version.
	 *
	 * @string version
	 * @since 1.0.0
	 **/
	public static $version = '';

    /**
     * Plugin settings.
     *
     * @var array()
     * @since 1.0.0
     */
    public $options = [];

    /**
     * Use minified libraries if SCRIPT_DEBUG is turned off.
     *
     * @since 1.0.0
     */
    public static $suffix;

    /**
     * Plugin base name.
     *
     * @var string
     * @since 1.0.0
     **/
    public static $basename = '';

    /**
     * URL (with trailing slash) to plugin folder.
     *
     * @var string
     * @since 1.0.0
     */
    public static $url = '';

	/**
	 * PATH to plugin folder.
	 *
	 * @var string
	 * @since 1.0.0
	 **/
	public static $path = '';

    /**
     * The one true Scroller.
     *
     * @var Scroller
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Sets up a new plugin instance.
     *
     * @since 1.0.0
     * @access private
     */
    private function __construct() {

        /** Initialize main variables. */
        $this->init();

        /** Initialize Plugin Helper. */
        PluginHelper::get_instance();

        /** Add plugin setting page. */
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );

        /** Making a body content wrapper */
        if ( $this->options['enable-body'] === 'on' ) {
	        add_action( 'wp_head', array( $this, 'wpse_wp' ), -1 );
        }

        /** Add the script to the user side of the site. */
        add_action( 'wp_enqueue_scripts', array( $this, 'set_fields_js' ) );

	    /** Initialize Plugin Custom Css. */
        PluginCustomCss::get_instance();

        /** Add webkit styles. */
	    add_action( 'wp_enqueue_scripts', array( $this, 'webkit_scroller' ) );
    }

	/**
	 * Return plugin version.
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 **/
    public function get_version() {
        return self::$version;
    }

	/**
	 * Hook into WordPress immediately before it starts output.
     *
	 * @since 1.0.3
	 **/
	function wpse_wp() {

		/** Start out buffering. */
		ob_start();

		/** Add action to get the buffer on shutdown. */
		add_action( 'shutdown', array( $this, 'wpse_shutdown' ), -1 );
	}

	/**
	 * We display the content wrapped in a scroll block.
     *
	 * @since 1.0.3
	 */
	function wpse_shutdown() {

		/** Get the output buffer. */
		$content = ob_get_clean();

		/** Use preg_replace to add a <div> wrapper inside the <body> tag. */
		$pattern = "/<body(.*?)>(.*?)<\/body>/is";
		$replacement = '<body$1>' . PHP_EOL . '<!-- Start Scroller Plugin  --><div id="scroller-body" class="scroller">$2</div><!-- End Scroller Plugin -->' . PHP_EOL . '</body>';

		/** Print the content to the screen. */
		print( preg_replace( $pattern, $replacement, $content ) );
	}

	/**
     * We pass the parameters to the scroll script.
     *
	 * @since 1.0.0
	 * @access public
     * @return void
	 */
	public function set_fields_js(){

	    /** We determine the device. */
		$mobDetect = new MobileDetect;
		if ( $mobDetect->isMobile() OR $mobDetect->isTablet() ) { return; }

		$scroller_settings = [
			'wheelSpeed'            => $this->options['wheel-speed'],
			'wheelPropagation'      => $this->options['wheel-propagation'],
			'minScrollbarLength'    => $this->options['min-scroll-bar-length'],
			'maxScrollbarLength'    => $this->options['max-scroll-bar-length'],
			'suppressScrollX'       => $this->options['suppress-scrollx'],
			'suppressScrollY'       => $this->options['suppress-scrolly'],
			'color'                 => $this->options['colors-scrollbar'],
			'bgColor'               => $this->options['background-scrollbar'],
			'borderRadius'          => $this->options['border-radius'],
			'scrollWidth'           => $this->options['scroll-bar-width'],
		    'sideSpace'             => $this->options['side-space'],
            'hoverAnimation'        => $this->options['hover-animation'],
            'windowScrollBar'       => $this->options['enable-body']
		];

		if ( $this->options['use-gradient-color'] === 'on' ) { $scroller_settings['gradientColor'] = $this->options['gradient-colors-scrollbar']; }

        if ( AssignmentsTab::get_instance()->display() ) {
            wp_localize_script( 'mdp-scroller', 'mdpScroller', $scroller_settings );
        }

    }


	/**
	 * Set styles scroller for mobile devices.
     *
	 * @since 1.0.0
	 * @access public
     * @return void
	 */
	public function webkit_scroller() {

		/** Enable styling on Touch Devices, WebKit browsers only. */
		if ( 'on' !== $this->options['enable-touch'] ) { return; }

		$mobDetect = new MobileDetect;
		if ( $mobDetect->isMobile() OR $mobDetect->isTablet()  ) {

			wp_enqueue_style( 'mdp-scroller-mob', Scroller::$url . 'css/scroller-mob' . Scroller::$suffix . '.css', array(), Scroller::$version );

			/** Get gradient CSS. */
			$webkit_css = $this->get_inline_css();
			wp_add_inline_style( 'mdp-scroller-mob', $webkit_css );

		}

    }

	/**
	 * Return inline CSS for scroller.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
    private function get_inline_css() {

    	/** Prepare variables. */
	    $border_radius = $this->options['border-radius'];
	    $bg_color = $this->options['background-scrollbar'];
	    $color_1 = $this->options['colors-scrollbar'];
	    $color_2 = $this->options['gradient-colors-scrollbar'];

	    $scroll_width = $this->options['scroll-bar-width'];

	    $webkit_css = "
			::-webkit-scrollbar, 
			.scroller::-webkit-scrollbar { 
				width: {$scroll_width}px; 
				height: {$scroll_width}px;
			}
			
			::-webkit-scrollbar-track, 
			.scroller::-webkit-scrollbar-track {  
				-webkit-border-radius: {$border_radius}px;
						border-radius: {$border_radius}px;
				background: {$bg_color};
			}
		";

	    if ( $this->options['use-gradient-color'] === 'on' ) {
	    	$webkit_css .= " 
				::-webkit-scrollbar-thumb, 
				.scroller::-webkit-scrollbar-thumb { 
					-webkit-border-radius: {$border_radius}px; 
							border-radius: {$border_radius}px;
	                background-image: -webkit-gradient(linear, left top, left bottom, from({$color_1}), to($color_2}));
	                background-image: -webkit-linear-gradient(top, {$color_1} 0%, {$color_2} 100%);
	                background-image: -o-linear-gradient(to bottom, {$color_1} 0%, {$color_2} 100%);
				    background-image: -moz-linear-gradient(to bottom, {$color_1} 0%, {$color_2} 100%);
				    background-image: linear-gradient(to bottom, {$color_1} 0%, {$color_2} 100%);
			    }
	        ";
	    } else {
		    $webkit_css .= "
                ::-webkit-scrollbar-thumb, 
                .scroller::-webkit-scrollbar-thumb { 
					-webkit-border-radius: {$border_radius}px; 
					border-radius: {$border_radius}px;
					background: {$color_1};
					-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5);
                }
            ";
	    }

	    return $webkit_css;
    }

    /**
     * Initialize main variables.
     *
     * @since 1.0.0
     * @access public
     **/
    public function init() {

	    /** Plugin version. */
	    if ( ! function_exists('get_plugin_data') ) {
		    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	    }

	    $plugin_data = get_plugin_data( __FILE__ );
	    self::$version = $plugin_data['Version'];

        /** Get plugin settings. */
        $this->get_options();

        /** Gets the plugin URL (with trailing slash). */
        self::$url = plugin_dir_url( __FILE__ );

	    /** Gets the plugin PATH. */
	    self::$path = plugin_dir_path( __FILE__ );

        /** Set plugin basename. */
        self::$basename = plugin_basename( __FILE__ );

        /** Use minified libraries if SCRIPT_DEBUG is turned off. */
        self::$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	    /** Plugin update mechanism enable only if plugin have Envato ID. */
	    $plugin_id = EnvatoItem::get_instance()->get_id();
	    if ( (int)$plugin_id > 0 ) {
		    PluginUpdater::get_instance();
	    }

    }

	/**
	 * Get plugin settings with default values.
	 *
	 * @return void
	 * @since 1.0.0
	 * @access public
	 */
    public function get_options() {
        /** Scroller tab settings. */
        $options = get_option( 'mdp_scroller_settings' );

	    /** Default settings. */
        $defaults = [
            'wheel-speed' => 1,
            'wheel-propagation' => 'off',
            'min-scroll-bar-length' => 35,
            'max-scroll-bar-length' => 100,
            'scroll-bar-width'  => 16,
            'hover-animation' => 'on',
	        'side-space' => 3,
            'suppress-scrollx' => 'off',
            'suppress-scrolly' => 'off',
            'colors-scrollbar' => '#9c07bd',
            'gradient-colors-scrollbar' => '#f1007d',
            'background-scrollbar' => '#ccc',
            'use-gradient-color' => 'off',
            'border-radius' => 3,
	        'enable-touch' => 'on',
	        'enable-body' => 'on'
        ];

        $results = wp_parse_args( $options, $defaults );

        $this->options = $results;
    }

    /**
     * Add admin menu for plugin settings.
     *
     * @since 1.0.0
     * @access public
     */
    public function add_admin_menu() {
        add_menu_page(
            esc_html__( 'Scroller Settings', 'scroller' ),
            esc_html__( 'Scroller', 'scroller' ),
            'manage_options',
            'mdp_scroller_settings',
            [ $this, 'options_page' ],
	        'data:image/svg+xml;base64,' . base64_encode( file_get_contents( self::$path . 'images/logo-menu.svg' ) ),
            '58.1989' // Always change digits after "." for different plugins.
        );
    }

    /**
     * Render "Settings Saved" nags.
     *
     * @since  1.0.0
     * @access public
     */
    public function render_nags() {

	    if ( ! isset( $_GET['settings-updated'] ) ) { return; }

        if ( ! isset( $_GET['settings-updated'] ) ) { return; }
        /** Render "Settings Saved" message. */
        UI::get_instance()->render_snackbar( esc_html__( 'Scroller settings saved!', 'scroller' ) );

	    if ( ! isset( $_GET['tab'] ) ) { return; }

	    if ( strcmp( $_GET['tab'], "activation" ) == 0 ) {

		    if ( PluginActivation::get_instance()->is_activated() ) {

			    /** Render "Activation success" message. */
			    UI::get_instance()->render_snackbar( esc_html__( 'Plugin activated successfully.', 'scroller' ), 'success', 5500 );

		    } else {

			    /** Render "Activation failed" message. */
			    UI::get_instance()->render_snackbar( esc_html__( 'Invalid purchase code.', 'scroller' ), 'error', 5500 );

		    }

	    }

    }

	/**
	 * Displays useful links for an activated and non-activated plugin.
	 *
	 * @since 1.0.0
	 */
    public function support_link() { ?>

        <hr class="mdc-list-divider">
        <h6 class="mdc-list-group__subheader"><?php echo esc_html__( 'Helpful links', 'scroller' ) ?></h6>

        <a class="mdc-list-item" href="https://docs.merkulov.design/tag/scroller/" target="_blank">
            <i class="material-icons mdc-list-item__graphic" aria-hidden="true"><?php echo esc_html__( 'collections_bookmark' ) ?></i>
            <span class="mdc-list-item__text"><?php echo esc_html__( 'Documentation', 'scroller' ) ?></span>
        </a>

	    <?php if ( PluginActivation::get_instance()->is_activated() ) : /** Activated. */ ?>
            <a class="mdc-list-item" href="https://1.envato.market/speaker-support" target="_blank">
                <i class="material-icons mdc-list-item__graphic" aria-hidden="true"><?php echo esc_html__( 'mail' ) ?></i>
                <span class="mdc-list-item__text"><?php echo esc_html__( 'Get help', 'speaker' ) ?></span>
            </a>
            <a class="mdc-list-item" href="https://1.envato.market/cc-downloads" target="_blank">
                <i class="material-icons mdc-list-item__graphic" aria-hidden="true"><?php echo esc_html__( 'thumb_up' ) ?></i>
                <span class="mdc-list-item__text"><?php echo esc_html__( 'Rate this plugin', 'speaker' ) ?></span>
            </a>
	    <?php endif; ?>

        <a class="mdc-list-item" href="https://1.envato.market/cc-merkulove" target="_blank">
            <i class="material-icons mdc-list-item__graphic" aria-hidden="true"><?php echo esc_html__( 'store' ) ?></i>
            <span class="mdc-list-item__text"><?php echo esc_html__( 'More plugins', 'scroller' ) ?></span>
        </a>
        <?php

    }

	/**
	 * Render Tabs Headers.
	 *
	 * @param string $current
	 *
	 * @since 1.0.0
	 * @access public
	 */
    public function render_tabs( $current = 'scroller' ) {

        /** Tabs array. */
        $tabs = [];
        $tabs['scroller'] = [
            'icon' => 'tune',
            'name' => esc_html__( 'General', 'scroller' )
        ];

        $tabs['assignments'] = [
            'icon' => 'flag',
            'name' => esc_html__( 'Assignments', 'scroller' )
        ];

        $tabs['css'] = [
            'icon' => 'code',
            'name' => esc_html__( 'Custom CSS', 'scroller' )
        ];

	    $tabs['activation'] = [
		    'icon' => 'vpn_key',
		    'name' => esc_html__( 'Activation', 'scroller' )
	    ];

        $tabs['status'] = [
            'icon' => 'info',
            'name' => esc_html__( 'Status', 'scroller' )
        ];

        $tabs['uninstall'] = [
            'icon' => 'delete_sweep',
            'name' => esc_html__( 'Uninstall', 'scroller' )
        ];

        /** Render Tabs. */
        ?>
        <aside class="mdc-drawer">
            <div class="mdc-drawer__content">
                <nav class="mdc-list">

                    <div class="mdc-drawer__header mdc-plugin-fixed">
                        <!--suppress HtmlUnknownAnchorTarget -->
                        <a class="mdc-list-item mdp-plugin-title" href="#wpwrap">
                            <i class="mdc-list-item__graphic" aria-hidden="true">
                                <img src="<?php echo esc_attr( self::$url . 'images/logo-color.svg' ); ?>" alt="<?php echo esc_html__( 'Scroller', 'scroller' ) ?>">
                            </i>
                            <span class="mdc-list-item__text">
                                    <?php echo esc_html__( 'Scroller', 'scroller' ) ?>
                                <sup><?php echo esc_html__( 'ver.', 'scroller' ) . esc_html( self::$version ); ?></sup>
                                </span>
                        </a>
                        <button type="submit" name="submit" id="submit"
                                class="mdc-button mdc-button--dense mdc-button--raised">
                            <span class="mdc-button__label"><?php echo esc_html__( 'Save changes', 'scroller' ) ?></span>
                        </button>
                    </div>

                    <hr class="mdc-plugin-menu">
                    <hr class="mdc-list-divider">
                    <h6 class="mdc-list-group__subheader"><?php echo esc_html__( 'Plugin settings', 'scroller' ) ?></h6>

                    <?php
                    /** Plugin settings tabs. */
                    foreach ( $tabs as $tab => $value ) {
                        $class = ( $tab == $current ) ? ' mdc-list-item--activated' : '';
                        echo "<a class='mdc-list-item " . $class . "' href='?page=mdp_scroller_settings&tab=" . $tab . "'><i class='material-icons mdc-list-item__graphic' aria-hidden='true'>" . $value['icon'] . "</i><span class='mdc-list-item__text'>" . $value['name'] . "</span></a>";
                    }

                    /** Helpful links. */
                    $this->support_link();

                     /** Activation Status. */
                    PluginActivation::get_instance()->display_status();
                    ?>

                </nav>
            </div>
        </aside>
        <?php
    }

    /**
     * Plugin Settings Page.
     *
     * @since 1.0.0
     * @access public
     */
    public function options_page() {
        if ( ! current_user_can('manage_options' ) ) { return; } ?>

        <!--suppress HtmlUnknownTarget -->
        <form action='options.php' method='post'>
            <div class="wrap">

                <?php
                $tab = 'scroller';
                if ( isset ( $_GET['tab'] ) ) {
                    $tab = $_GET['tab'];
                }

                /** Render "Plugin settings saved!" message. */
                $this->render_nags();

                /** Render Tabs Headers. */
                ?><section class="mdp-aside"><?php
                    $this->render_tabs( $tab );
                    ?></section><?php

                /** Render Tabs Body. */
                ?><section class="mdp-tab-content"><?php

                    /** General Tab. */
                    if ( $tab == 'scroller' ) {
                        echo '<h3>' . esc_html__( 'Scroller Settings', 'scroller' ) . '</h3>';
                        settings_fields( 'ScrollerOptionsGroup' );
                        do_settings_sections( 'ScrollerOptionsGroup' );

                    }
                    /** Design Tab. */
                    elseif ( $tab == 'design' ) {
                        echo '<h3>' . esc_html__( 'Design Settings', 'scroller' ) . '</h3>';
                        settings_fields( 'ScrollerOptionsGroup' );
                        do_settings_sections( 'ScrollerOptionsGroup' );

                    }
                    /** Assignments Tab. */
                    elseif ( $tab == 'assignments' ) {
                        echo '<h3>' . esc_html__( 'Assignments Settings', 'scroller' ) . '</h3>';
                        AssignmentsTab::get_instance()->render_form();
                        AssignmentsTab::get_instance()->render_assignments();
                    }

                    /** Custom Css Tab. */
                    elseif ( $tab == 'css' ) {
                        echo '<h3>' . esc_html__( 'Custom CSS', 'scroller' ) . '</h3>';
                        PluginCustomCss::get_instance()->render_form();
	                    PluginCustomCss::get_instance()->render_custom_css();
                    }

                    /** Activation Tab. */
                    elseif ( $tab == 'activation' ) {
	                    settings_fields( 'ScrollerActivationOptionsGroup' );
	                    do_settings_sections( 'ScrollerActivationOptionsGroup' );
	                    PluginActivation::get_instance()->render_pid();
                    }

                    /** System Requirements. */
                    elseif ( $tab == 'status' ) {
                        echo '<h3>' . esc_html__('System Requirements', 'scroller') . '</h3>';
                        StatusTab::get_instance()->render_form();
                    }

                    /** Uninstall Tab. */
                    elseif ( $tab == 'uninstall' ) {
                        echo '<h3>' . esc_html__( 'UninstallTab Settings', 'scroller' ) . '</h3>';
                        UninstallTab::get_instance()->render_form();
                    }

                    ?>
                </section>
            </div>
        </form>
        <?php
    }

    /**
     * Wheel Speed field.
     *
     * @since 1.0.0
     * @access public
     */
    public function field_wheel_speed(){

        /** Render slider. */
        UI::get_instance()->render_slider(
            $this->options['wheel-speed'],
            0,
            20,
            0.1,
            '',
            esc_html__( 'Wheel scrolling speed:', 'scroller') . ' <strong>' . esc_html( $this->options['wheel-speed'] ) . '</strong>',
            [
                'name' => 'mdp_scroller_settings[wheel-speed]',
                'class' => 'mdc-slider-width',
            ],
            false
        );

    }

    /**
     * Wheel propagation.
     *
     * @since 1.0.0
     * @access public
     */
    public function field_wheel_propagation(){

        /** Render switches. */
        UI::get_instance()->render_switches(
            $this->options['wheel-propagation'],
            esc_html__( 'Propagate from child to parent', 'scroller' ),
            '',
            [
                'name' => 'mdp_scroller_settings[wheel-propagation]',
                'id' => 'mdp-wheel-propagation'
            ]
        );

    }

	/**
	 * Animate Scroll Bar on hover.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function field_hover_animation(){

		/** Render switches. */
		UI::get_instance()->render_switches(
			$this->options['hover-animation'],
			esc_html__( 'Animate Scroll Bar on hover', 'scroller' ),
			'',
			[
				'name' => 'mdp_scroller_settings[hover-animation]',
				'id' => 'mdp-hover-animation'
			]
		);

	}


    /**
     * Use gradient color.
     *
     * @since 1.0.0
     * @access public
     */
    public function field_use_gradient_color(){

        /** Render switches. */
        UI::get_instance()->render_switches(
            $this->options['use-gradient-color'],
            esc_html__( 'Scrollbar gradient', 'scroller' ),
            '',
            [
                'name' => 'mdp_scroller_settings[use-gradient-color]',
                'id' => 'mdp-use-gradient-color',
            ]
        );
    }

    /**
     * Min Scroll Bar Length.
     *
     * @since 1.0.0
     * @access public
     */
    public function field_min_scroll_bar_length(){

        /** Render slider. */
        UI::get_instance()->render_slider(
            $this->options['min-scroll-bar-length'],
            0,
            500,
            1,
            '',
            esc_html__( 'Minimum Scrollbar length:', 'scroller') . ' <strong>' . esc_html( $this->options['min-scroll-bar-length'] ) . '</strong> ' . esc_html__( 'pixels', 'scroller' ),
            [
                'name' => 'mdp_scroller_settings[min-scroll-bar-length]',
                'class' => 'mdc-slider-width mdp-slider-min',
                'id' => 'mdp-min-length',

            ],
            false
        );

    }

    /**
     * Max Scroll Bar Length.
     *
     * @since 1.0.0
     * @access public
     */
    public function field_max_scroll_bar_length(){
        /** Render slider. */
        UI::get_instance()->render_slider(
            $this->options['max-scroll-bar-length'],
            0,
            500,
            1,
            '',
            esc_html__( 'Maximum Scrollbar length:', 'scroller') . ' <strong>' . esc_html( $this->options['max-scroll-bar-length'] ) . '</strong> ' . esc_html__( 'pixels', 'scroller' ),
            [
                'name' => 'mdp_scroller_settings[max-scroll-bar-length]',
                'class' => 'mdc-slider-width',
                'id' => 'mdp-max-length',
            ],
            false
        );
    }

	/**
	 * Scroll Bar width.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function field_scroll_bar_width(){
		/** Render slider. */
		UI::get_instance()->render_slider(
			$this->options['scroll-bar-width'],
			10,
			100,
			1,
			'',
			esc_html__( 'Scrollbar width:', 'scroller') . ' <strong>' . esc_html( $this->options['scroll-bar-width'] ) . '</strong> ' . esc_html__( 'pixels', 'scroller' ),
			[
				'name' => 'mdp_scroller_settings[scroll-bar-width]',
				'class' => 'mdc-slider-width',
				'id' => 'mdp-scroll-bar-width',
			],
			false
		);
	}

	/**
	 * Scroll Bar side space.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function field_side_space(){
		/** Render slider. */
		UI::get_instance()->render_slider(
			$this->options['side-space'],
			0,
			50,
			1,
			'',
			esc_html__( 'Scrollbar side space:', 'scroller') . ' <strong>' . esc_html( $this->options['side-space'] ) . '</strong> ' . esc_html__( 'pixels', 'scroller' ),
			[
				'name' => 'mdp_scroller_settings[side-space]',
				'class' => 'mdc-slider-width',
				'id' => 'mdp-side-space',
			],
			false
		);
	}


    /**
     * Suppress Scroll X.
     *
     * @since 1.0.0
     * @access public
     */
    public function field_suppress_scrollx(){
        /** Render switches. */
        UI::get_instance()->render_switches(
            $this->options[ 'suppress-scrollx' ],
            esc_html__( 'Disabling the horizontal scrollbar', 'scroller' ),
            '',
            [
                'name' => 'mdp_scroller_settings[suppress-scrollx]',
                'id' => 'suppress-scroll-x',
            ]
        );
    }

	/**
	 * Render border radius.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function field_border_radius(){
		/** Render slider. */
		UI::get_instance()->render_slider(
			$this->options['border-radius'],
			0,
			50,
			1,
			'',
			esc_html__( 'Scrollbar border radius:', 'scroller') . ' <strong>' . esc_html( $this->options['border-radius'] ) . '</strong> ' . esc_html__( 'pixels', 'scroller' ),
			[
				'name' => 'mdp_scroller_settings[border-radius]',
				'class' => 'mdc-slider-width',
				'id' => 'border-radius',
			],
			false
		);
	}

	/**
	 * Suppress Scroll X.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function field_suppress_scrolly(){
		/** Render switches. */
		UI::get_instance()->render_switches(
			$this->options[ 'suppress-scrolly' ],
			esc_html__( 'Disabling the vertical scrollbar', 'scroller' ),
			'',
			[
                'name' => 'mdp_scroller_settings[suppress-scrolly]',
                'id' => 'suppress-scroll-y',
            ]
		);
	}

	/**
	 * Enable Styles for Touch devices.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function field_enable_touch() {

		/** Render switches. */
		UI::get_instance()->render_switches(
			$this->options[ 'enable-touch' ],
			esc_html__( 'Enable styling on Touch Devices, WebKit browsers only.', 'scroller' ),
			'',
			[
				'name' => 'mdp_scroller_settings[enable-touch]',
				'id' => 'mdp-enable-touch',
			]
		);
	}

	/**
	 * Enable Styles for Page.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function field_enable_body() {

		/** Render switches. */
		UI::get_instance()->render_switches(
			$this->options[ 'enable-body' ],
			esc_html__( 'Window scrollbar styling', 'scroller' ),
			esc_html__( 'Enable styling for the window scrollbar', 'scroller' ),
			[
				'name' => 'mdp_scroller_settings[enable-body]',
				'id' => 'mdp-enable-body',
			]
		);
	}

    /**
     * Render Primary scrollbar color.
     *
     * @since 1.0.0
     * @access public
     */
    public function field_colors_scrollbar(){

        UI::get_instance()->render_colorpicker(
            $this->options[ 'colors-scrollbar' ],
            esc_html__( 'Scrollbar color', 'scroller' ),
	        esc_html__( 'Primary scrollbar color', 'scroller' ),
            [
                'name' => 'mdp_scroller_settings[colors-scrollbar]',
                'id' => 'mdp-colors-scrollbar',
                'readonly' => 'readonly'
            ]
        );

    }

	/**
	 * Render Secondary scrollbar color.
     *
	 * @since 1.0.0
	 * @access public
	 */
	public function field_gradient_colors_scrollbar(){

        UI::get_instance()->render_colorpicker(
            $this->options['gradient-colors-scrollbar'],
            esc_html__( 'Scrollbar Gradient color', 'scroller' ),
	        esc_html__( 'Second color for vertical gradient', 'scroller' ),
            [
                'name' => 'mdp_scroller_settings[gradient-colors-scrollbar]',
                'id' => 'mdp-gradient-colors-scrollbar',
                'readonly' => 'readonly'
            ]
        );

    }

	/**
	 * Render background color.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function field_background_scrollbar(){

		UI::get_instance()->render_colorpicker(
			$this->options[ 'background-scrollbar' ],
			esc_html__( 'Background color', 'scroller' ),
			esc_html__( 'Scrollbar background color', 'scroller' ),
			[
				'name' => 'mdp_scroller_settings[background-scrollbar]',
				'id' => 'mdp-background-scrollbar',
				'readonly' => 'readonly'
			]
		);

	}

    /**
     * Generate Settings Page.
     *
     * @since 1.0.0
     * @access public
     */
    public function settings_init() {
        /** Scroller Tab. */
        register_setting( 'ScrollerOptionsGroup', 'mdp_scroller_settings' );
        add_settings_section( 'mdp_scroller_pluginPage_section', '', null, 'ScrollerOptionsGroup' );

	    /** Scrollbar background color. */
	    add_settings_field( 'background-scrollbar', esc_html__( 'Background color:', 'scroller' ), [$this, 'field_background_scrollbar'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Scrollbar color. */
        add_settings_field( 'colors-scrollbar', esc_html__( 'Scrollbar color:', 'scroller' ), [$this, 'field_colors_scrollbar'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

        /** Scrollbar gradient color. */
        add_settings_field( 'gradient-colors-scrollbar', '', [$this, 'field_gradient_colors_scrollbar'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Use gradient color. */
	    add_settings_field( 'use-gradient-color', esc_html__( 'Scrollbar gradient:', 'scroller' ), [$this, 'field_use_gradient_color'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Scroller border radius. */
	    add_settings_field( 'border-radius', esc_html__( 'Border radius:', 'scroller' ), [$this, 'field_border_radius'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Min Scroll Bar Length. */
	    add_settings_field( 'min-scroll-bar-length', esc_html__( 'Min Scrollbar length:', 'scroller' ), [$this, 'field_min_scroll_bar_length'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Max Scroll Bar Length. */
	    add_settings_field( 'max-scroll-bar-length', esc_html__( 'Max Scrollbar length:', 'scroller' ), [$this, 'field_max_scroll_bar_length'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Scroll Bar Width. */
	    add_settings_field( 'scroll-bar-width', esc_html__( 'Scrollbar width:', 'scroller' ), [$this, 'field_scroll_bar_width'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Side space. */
	    add_settings_field( 'side-space', esc_html__( 'Side space:', 'scroller' ), [$this, 'field_side_space' ], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Animate Scroll Bar on hover. */
	    add_settings_field( 'hover-animation', esc_html__( 'Animate on hover:', 'scroller' ), [$this, 'field_hover_animation'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Wheel Speed. */
        add_settings_field( 'wheel-speed', esc_html__( 'Scroll speed:', 'scroller' ), [$this, 'field_wheel_speed'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

        /** Wheel Propagation. */
        add_settings_field( 'wheel-propagation', esc_html__( 'Wheel propagation:', 'scroller' ), [$this, 'field_wheel_propagation'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

        /** Suppress Scroll X */
        add_settings_field( 'suppress-scrollx', esc_html__( 'Suppress x-scrollbar:', 'scroller' ), [$this, 'field_suppress_scrollx'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Suppress Scroll Y */
	    add_settings_field( 'suppress-scrolly', esc_html__( 'Suppress y-scrollbar:', 'scroller' ), [$this, 'field_suppress_scrolly'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Disable Styles for Touch devices. */
	    add_settings_field( 'enable-touch', esc_html__( 'Enable for Touch Devices:', 'scroller' ), [$this, 'field_enable_touch'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

	    /** Disable Styles for Touch devices. */
	    add_settings_field( 'enable_body', esc_html__( 'Styles for Window:', 'scroller' ), [$this, 'field_enable_body'], 'ScrollerOptionsGroup', 'mdp_scroller_pluginPage_section' );

        /** Create Assignments Tab. */
        AssignmentsTab::get_instance()->add_settings();

	    /** Create Activation Tab. */
	    PluginActivation::get_instance()->add_settings();

        /** Create Custom Css Tab. */
        PluginCustomCss::get_instance()->add_settings();

        /** Create Status Tab */
	    StatusTab::get_instance()->add_settings();

        /** Create Uninstall Tab. */
        UninstallTab::get_instance()->add_settings();
    }

	/**
	 * Run when the plugin is activated.
	 *
	 * @static
	 * @since 2.0.0
	 **/
	public static function on_activation() {

		/** Security checks. */
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		/** Send install Action to our host. */
		Helper::get_instance()->send_action( 'install', 'scroller', self::$version );

	}

    /**
     * Main plugin instance.
     *
     * Insures that only one instance of Scroller exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Scroller ) ) {
            self::$instance = new Scroller;
        }
        return self::$instance;
    }

    /**
     * Throw error on object clone.
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be cloned.
     *
     * @since 1.0.0
     * @access public
     * @return void
     **/
    public function __clone() {
        /**
         * Cloning instances of the class is forbidden.
         */
        _doing_it_wrong(
            __FUNCTION__,
            esc_html__( 'The whole idea of the singleton design pattern is that there is a single 
                             object therefore, we don\'t want the object to be cloned.', 'scroller' ),
            self::$version
        );
    }

    /**
     * Disable unserializing of the class.
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be unserialized.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function __wakeup() {
        /**
         * Unserializing instances of the class is forbidden.
         */
        _doing_it_wrong(
            __FUNCTION__,
            esc_html__(
                'The whole idea of the singleton design pattern is that there is a single
                 object therefore, we don\'t want the object to be unserialized.', 'scroller' ),
            self::$version
        );
    }

}

/** Run when the plugin is activated. */
register_activation_hook( __FILE__, ['Merkulove\Scroller', 'on_activation'] );

/** @noinspection PhpUnusedLocalVariableInspection */
$Scroller = Scroller::get_instance();