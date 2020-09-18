<?php
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

namespace Merkulove\Scroller;

use Merkulove\Scroller as Scroller;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/**
 * SINGLETON: Class used to implement PluginCustomCss tab on plugin settings page.
 *
 * @since 1.0.0
 * @author Nemirovakiy Vitaliy (nemirovskiyvitaliy@gmail.com)
 **/
final class PluginCustomCss {

    /**
     * The one true PluginCustomCss.
     *
     * @var PluginCustomCss
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Sets up a new PluginCustomCss instance.
     *
     * @since 1.0.0
     * @access private
     */
    private function __construct() {
	    add_action( 'wp_enqueue_scripts', [$this, 'load_styles'] );
    }

    /**
     * Render form with all settings fields.
     *
     * @access public
     * @since 1.0.0
     */
    public function render_form() {
        settings_fields( 'ScrollerCustomCssOptionsGroup' );
        do_settings_sections( 'ScrollerCustomCssOptionsGroup' );
    }

    /**
     * Generate Activation Tab.
     *
     * @since 1.0.0
     * @access public
     */
    public function add_settings() {
        /** Custom Css Tab. */
        register_setting( 'ScrollerCustomCssOptionsGroup', 'mdp_scroller_custom_css_settings' );
        add_settings_section( 'mdp_scroller_settings_page_custom_css_section', '', null, 'ScrollerCustomCssOptionsGroup' );
    }


	/**
	 * Add custom CSS style.
	 *
	 * @since 1.0.0
	 */
	public function render_custom_css(){
        /** Get uninstall settings. */
        $CustomCssSettings = get_option( 'mdp_scroller_custom_css_settings' );

        /** Set Default value 'plugin' . */
        $CustomCssSettings = [
            'customcss' => isset( $CustomCssSettings[ 'customcss' ] ) ? $CustomCssSettings[ 'customcss' ] : '',
        ];

        UI::get_instance()->render_textarea(
            $CustomCssSettings['customcss'],
            esc_html__('Add custom CSS here. You probably have to use an expression !important for some rules.', 'scroller' ),
            [
                'id' => 'mdp_custom_css_fld',
                'class' => 'mdp_custom_css_fld',
                'name' => 'mdp_scroller_custom_css_settings[customcss]'
            ]
        );
    }

	/**
	 * Add CSS for the public-facing side of the site.
	 *
	 * @since   1.0.0
	 * @return void
	 **/
	public function load_styles() {

		/** Checks if plugin should work on this page. */
		if( ! AssignmentsTab::get_instance()->display() ) { return; }

		/** Get custom CSS settings. */
		$CustomCssSettings = get_option( 'mdp_scroller_custom_css_settings' ) ? get_option( 'mdp_scroller_custom_css_settings' ) : [ 'customcss' => '' ];
		wp_add_inline_style( 'mdp-scroller', $CustomCssSettings['customcss'] );

	}

    /**
     * Main PluginCustomCssTab Instance.
     *
     * Insures that only one instance of UninstallTab exists in memory at any one time.
     *
     * @static
     * @return PluginCustomCss
     * @since 1.0.0
     **/
    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof PluginCustomCss ) ) {
            self::$instance = new PluginCustomCss;
        }
        return self::$instance;
    }

    /**
     * Throw error on object clone.
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be cloned.
     *
     * @return void
     * @since 1.0.0
     * @access public
     **/
    public function __clone() {
        /** Cloning instances of the class is forbidden. */
        _doing_it_wrong( __FUNCTION__, esc_html__( 'The whole idea of the singleton design pattern is that there is a single object therefore, we don\'t want the object to be cloned.', 'scroller' ), Scroller::$version );
    }

    /**
     * Disable unserializing of the class.
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be unserialized.
     *
     * @return void
     * @since 1.0.0
     * @access public
     **/
    public function __wakeup() {
        /** Unserializing instances of the class is forbidden. */
        _doing_it_wrong( __FUNCTION__, esc_html__( 'The whole idea of the singleton design pattern is that there is a single object therefore, we don\'t want the object to be unserialized.', 'scroller' ), Scroller::$version );
    }
}