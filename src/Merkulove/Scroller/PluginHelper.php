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
 * SINGLETON: Class used to implement base plugin features.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 */
final class PluginHelper {

    /**
     * The one true Helper.
     *
     * @var PluginHelper
     * @since 1.0.0
     **/
    private static $instance;

    /**
     * Sets up a new Helper instance.
     *
     * @since 1.0.0
     * @access private
     **/
    private function __construct() {
        /** Load translation. */
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        /** Add plugin links. */
        add_filter( 'plugin_action_links_' . Scroller::$basename, [ $this, 'add_links' ] );

        /** Add plugin meta. */
        add_filter( 'plugin_row_meta', [ $this, 'add_row_meta' ], 10, 2 );

        /** Add admin styles. */
        add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_styles'] );

        /** Add admin javascript. */
        add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_scripts' ] );

	    /** Add the script to the user side of the site. */
	    add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ]);
	    add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

    }

    /**
     * Loads plugin translated strings.
     *
     * @since 1.0.0
     * @access public
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'scroller', false, dirname( Scroller::$basename ) . '/languages/' );
    }

	/**
	 * Add "merkulov.design" and  "Envato Profile" links on plugin page.
	 *
	 * @param array $links Current links: Deactivate | Edit
	 *
	 * @return array
	 * @since 1.0.0
	 * @access public
	 *
	 */
    public function add_links($links) {
        array_unshift( $links, '<a title="' . esc_html__( 'Settings', 'scroller' ) . '" href="' . admin_url( 'admin.php?page=mdp_scroller_settings' ) . '">' . esc_html__( 'Settings', 'scroller' ) . '</a>' );
        array_push( $links, '<a title="' . esc_html__( 'Documentation', 'scroller' ) . '" href="https://docs.merkulov.design/tag/scroller/" target="_blank">' . esc_html__( 'Documentation', 'scroller' ) . '</a>' );
        array_push( $links, '<a href="https://1.envato.market/cc-merkulove" target="_blank" class="cc-merkulove"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB2aWV3Qm94PSIwIDAgMTE3Ljk5IDY3LjUxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8ZGVmcz4KPHN0eWxlPi5jbHMtMSwuY2xzLTJ7ZmlsbDojMDA5ZWQ1O30uY2xzLTIsLmNscy0ze2ZpbGwtcnVsZTpldmVub2RkO30uY2xzLTN7ZmlsbDojMDA5ZWUyO308L3N0eWxlPgo8L2RlZnM+CjxjaXJjbGUgY2xhc3M9ImNscy0xIiBjeD0iMTUiIGN5PSI1Mi41MSIgcj0iMTUiLz4KPHBhdGggY2xhc3M9ImNscy0yIiBkPSJNMzAsMmgwQTE1LDE1LDAsMCwxLDUwLjQ4LDcuNUw3Miw0NC43NGExNSwxNSwwLDEsMS0yNiwxNUwyNC41LDIyLjVBMTUsMTUsMCwwLDEsMzAsMloiLz4KPHBhdGggY2xhc3M9ImNscy0zIiBkPSJNNzQsMmgwQTE1LDE1LDAsMCwxLDk0LjQ4LDcuNUwxMTYsNDQuNzRhMTUsMTUsMCwxLDEtMjYsMTVMNjguNSwyMi41QTE1LDE1LDAsMCwxLDc0LDJaIi8+Cjwvc3ZnPgo=" alt="' . esc_html__( 'Plugins', 'scroller' ) . '">' . esc_html__( 'Plugins', 'scroller' ) . '</a>' );
        return $links;
    }

	/**
	 * Add "Rate us" link on plugin page.
	 *
	 * @param array $links Current links: Deactivate | Edit
	 * @param $file
	 *
	 * @return array
	 * @since 1.0.0
	 * @access public
	 */
    public function add_row_meta( $links, $file ) {
        if ( Scroller::$basename !== $file ) {
            return $links;
        }
        $links[] = esc_html__( 'Rate this plugin:', 'scroller' )
            . "<span class='mdp-rating-stars'>"
            . "     <a href='https://1.envato.market/cc-downloads' target='_blank'>"
            . "         <span class='dashicons dashicons-star-filled'></span>"
            . "     </a>"
            . "     <a href='https://1.envato.market/cc-downloads' target='_blank'>"
            . "         <span class='dashicons dashicons-star-filled'></span>"
            . "     </a>"
            . "     <a href='https://1.envato.market/cc-downloads' target='_blank'>"
            . "         <span class='dashicons dashicons-star-filled'></span>"
            . "     </a>"
            . "     <a href='https://1.envato.market/cc-downloads' target='_blank'>"
            . "         <span class='dashicons dashicons-star-filled'></span>"
            . "     </a>"
            . "     <a href='https://1.envato.market/cc-downloads' target='_blank'>"
            . "         <span class='dashicons dashicons-star-filled'></span>"
            . "     </a>"
            . "<span>";
        return $links;
    }

    /**
     * Main Helper Instance.
     *
     * Insures that only one instance of Helper exists in memory at any one time.
     *
     * @static
     * @return PluginHelper
     * @since 1.0.0
     **/
    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof PluginHelper ) ) {
            self::$instance = new PluginHelper();
        }
        return self::$instance;
    }

    /**
     * Add CSS for admin area.
     *
     * @since   1.0.0
     * @return void
     */
    public function load_admin_styles() {
        $screen = get_current_screen();
        /** Add styles only on setting page. */
        if ( $screen->base == 'plugins' ) {
            wp_enqueue_style( 'mdp-scroller-plugin-page', Scroller::$url . 'css/plugin-page' . Scroller::$suffix . '.css', array(), Scroller::$version );
        }
        if ( $screen->base == "toplevel_page_mdp_scroller_settings" ) {
            wp_enqueue_style( 'merkulov-ui', Scroller::$url . 'css/merkulov-ui' . Scroller::$suffix .'.css', array(), Scroller::$version );
            wp_enqueue_style( 'mdp-scroller-admin', Scroller::$url . 'css/admin' . Scroller::$suffix . '.css', [], Scroller::$version );
        } elseif ( 'plugin-install' == $screen->base ) {
	        /** Styles only for our plugin. */
	        if ( isset( $_GET['plugin'] )  AND $_GET['plugin'] == 'scroller' ) {
		        wp_enqueue_style( 'mdp-scroller-plugin-install', Scroller::$url . 'css/plugin-install' . Scroller::$suffix . '.css', [], Scroller::$version );
	        }
        }
    }

    /**
     * Add JS for admin area.
     *
     * @since  1.0.0
     * @return void
     **/
    public function load_admin_scripts() {
        $screen = get_current_screen();
        /** Add styles only on setting page */
        if ( $screen->base == 'plugins' ) {
            wp_enqueue_script( 'mdp-scroller-plugin-page', Scroller::$url . 'js/plugin-page' . Scroller::$suffix . '.js', array(), Scroller::$version, true );
        }
        if ( $screen->base == "toplevel_page_mdp_scroller_settings" ) {
            wp_enqueue_script( 'merkulov-ui', Scroller::$url . 'js/merkulov-ui' . Scroller::$suffix . '.js', array(), Scroller::$version, true);
	        wp_enqueue_script( 'mdp-scroller-admin', Scroller::$url . 'js/admin' . Scroller::$suffix . '.js', array(), Scroller::$version, true);

	        /** Remove "Thank you for creating with WordPress" and WP version from plugin settings page. */
	        add_filter( 'admin_footer_text', '__return_empty_string', 11 );
	        add_filter( 'update_footer', '__return_empty_string', 11 );
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since  1.0.0
     * @return void
     */
    public function enqueue_scripts() {

	    $mobDetect = new MobileDetect;

	    if ( !( $mobDetect->isMobile() OR $mobDetect->isTablet() ) ) {
		    if ( AssignmentsTab::get_instance()->display() ) {
		    	wp_enqueue_script( 'mdp-scroller', Scroller::$url . 'js/scroller' . Scroller::$suffix . '.js', array(), Scroller::$version, true );
		    }
	    }

    }

    /**
     * Register CSS for the public-facing side of the site.
     *
     * @since  1.0.0
     * @return void
     */
    public function enqueue_styles() {

	    $mobDetect = new MobileDetect;

	    if ( !( $mobDetect->isMobile() OR $mobDetect->isTablet() ) ) {
		    if ( AssignmentsTab::get_instance()->display() ) {
			    wp_enqueue_style( 'mdp-scroller', Scroller::$url . 'css/scroller' . Scroller::$suffix . '.css', array(), Scroller::$version );
		    }
	    }

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
        _doing_it_wrong(
    __FUNCTION__,
            esc_html__( 'The whole idea of the singleton design pattern is that there is a single 
            object therefore, we don\'t want the object to be cloned.', 'scroller' ),
    Scroller::$version
        );
    }

    /**
     * Disable unserializing of the class.
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be unserialized.
     *
     * @return void
     **@since 1.0.0
     * @access public
     */
    public function __wakeup() {
        /** Unserializing instances of the class is forbidden. */
        _doing_it_wrong(
    __FUNCTION__,
            esc_html__( 'The whole idea of the singleton design pattern is that there is a single 
            object therefore, we don\'t want the object to be unserialized.', 'scroller' ),
     Scroller::$version
        );

    }
}