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
 * SINGLETON: Class used to implement StatusTab tab on plugin settings page.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class StatusTab{

    /**
     * The one true StatusTab.
     *
     * @var StatusTab
     * @since 1.0.0
     **/
    private static $instance;

    /**
     * Sets up a new StatusTab instance.
     *
     * @since 1.0.0
     * @access private
     **/
    private function __construct(){

    }

	/**
    * Generate Stauts Tab.
    *
    * @since 1.0.0
    * @access public
    **/
    public function add_settings() {

        /** Status Tab. */
        register_setting( 'ScrollerStatusOptionsGroup', 'mdp_scroller_status_settings' );
        add_settings_section( 'mdp_scroller_settings_page_status_section', '', null, 'ScrollerStatusOptionsGroup' );

    }

    /**
     * Render form with all settings fields.
     *
     * @access public
     **/
    public function render_form() {

	    settings_fields( 'ScrollerStatusOptionsGroup' );
        do_settings_sections( 'ScrollerStatusOptionsGroup' );

        /**
         * Render "System Requirements".
         */
        $this->render_system_requirements();

        /**
         * Render Privacy Notice.
         */
        $this->render_privacy_notice();

        /**
         * Render "Changelog".
         */
        $this->render_changelog();
    }

    /**
     * Render "System Requirements" field.
     *
     * @since 1.0.0
     * @access public
     **/
    public function render_system_requirements() {

        $reports = [
            'server' => ServerReporter::get_instance(),
            'wordpress' => WordPressReporter::get_instance(),
        ]
        ?>

        <div class="mdc-system-requirements">

        <?php foreach ( $reports as $key => $report ) : ?>
            <div class="mdp-scroller-<?php echo esc_attr( $key ); ?>">
                <table class="mdc-system-requirements-table">
                    <thead>
                    <tr>
                        <th colspan="2"><?php echo esc_html( $report->get_title() ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $report->get_report() as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row['label'] ); ?>:</td>
                            <td><span class="mdc-system-value"><?php echo wp_kses_post( $row['value'] ); ?></span></td>
                            <th class="mdc-text-left">
                                <?php if ( isset($row['warning']) AND $row['warning'] ) : ?>
                                    <i class="material-icons mdc-system-warn">warning</i>
                                    <?php echo ( isset( $row['recommendation'] ) ? esc_html( $row['recommendation'] ) : ''); ?>
                                <?php endif; ?>
                            </th>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        </div><?php

    }

    /**
     * Render "Changelog" field.
     *
     * @since 1.0.0
     * @access public
     **/
    public function render_changelog() {

	    /** Build change log url. */
	    $slug = strstr( Scroller::$basename, "/", true );
	    $changelog_url = 'https://' . $slug . '.merkulov.design/wp-content/plugins/' . $slug . '/changelog.md';

	    /** Suppress warning, if changelog file not exist. */
	    $context = stream_context_create( ['http' => ['ignore_errors' => true] ] );
	    $changelog = file_get_contents( $changelog_url, false, $context );

	    /** Get response code. */
	    $code = substr( $http_response_header[0], 9, 3 );

	    /** Changelog not found. */
	    if ( $code != '200' ) { return; }

	    /** Changelog not found. */
	    if ( ! $changelog ) { return; }

        $changelogGreen = '+ <svg height="8" width="8"><circle cx="4" cy="4" r="4" fill="rgb(158,242,112)" /></svg> ';
        $changelogOrange = '- <svg height="8" width="8"><circle cx="4" cy="4" r="4" fill="rgb(248,212,114)" /></svg> ';
        $changelog = str_replace("+ ", $changelogGreen, $changelog );
        $changelog = str_replace("- ", $changelogOrange, $changelog );
        ?>

        <div class="mdc-changelog"><?php echo Parsedown::instance()->text( $changelog ); ?></div>

        <?php
    }

    /**
     * Render Privacy Notice.
     *
     * @since 1.0.0
     * @access public
     **/
    public function render_privacy_notice() {
        ?>
        <div class="mdc-text-field-helper-line">
            <div class="mdc-text-field-helper-text mdc-text-field-helper-text--persistent"><?php esc_html_e( 'Some data will be sent to our server to verify purchase and to ensure that a plugin is compatible with your install. We will never collect any confidential data. All data is stored anonymously.', 'scroller' );?></div>
        </div>
        <?php
    }

    /**
     * Main StatusTab Instance.
     *
     * Insures that only one instance of StatusTab exists in memory at any one time.
     *
     * @static
     * @return StatusTab
     * @since 1.0.0
     **/
    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof StatusTab ) ) {
            self::$instance = new StatusTab;
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