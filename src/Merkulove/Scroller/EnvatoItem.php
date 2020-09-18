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

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class contain information about the envato item.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class EnvatoItem {

	/**
	 * The one true EnvatoItem.
	 *
	 * @var EnvatoItem
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Return CodeCanyon Item ID.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 **/
	public function get_url() {

		return 'https://1.envato.market/scroller';

	}

	/**
	 * Return CodeCanyon Item ID.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 **/
	public function get_id() {

		/** Do we have stored Envato ID? */
		$item_id = get_option( 'mdp_scroller_envato_id' );

		/** If we have stored Envato ID, return it. */
		if ( $item_id ) {
			return $item_id;
		}

		/** Else get id from our server. */
		$item_id = $this->get_remote_plugin_id();

		/** Store local option if this is real item ID. */
		if ( (int)$item_id > 0 ) {
			update_option( 'mdp_scroller_envato_id', $item_id );
		}

		return (string)$item_id;
	}

	/**
	 * Return CodeCanyon Plugin ID from out server.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function get_remote_plugin_id() {

		/** Get Plugin name. */
		if ( ! function_exists('get_plugin_data') ) {
			/** @noinspection PhpIncludeInspection */
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/scroller/scroller.php' );
		$plugin_name = $plugin_data['Name'];

		/** Build URL. */
		$url = 'https://merkulove.host/wp-content/plugins/mdp-purchase-validator/src/Merkulove/PurchaseValidator/GetMyId.php';
		$url .= '?plugin_name=' . urlencode( $plugin_name );

		/** Suppress warning, if file not exist. */
		$context = stream_context_create( ['http' => ['ignore_errors' => true] ] );
		$plugin_id = file_get_contents( $url, false, $context );

		/** We don't have plugin ID. */
		if ( false === $plugin_id ) { return '0'; }

		$plugin_id = json_decode( $plugin_id );

		/** Wrong JSON. */
		if ( null === $plugin_id ) { return '0'; }

		return $plugin_id;
	}

	/**
	 * Main EnvatoItem Instance.
	 *
	 * Insures that only one instance of EnvatoItem exists in memory at any one time.
	 *
	 * @static
	 * @return EnvatoItem
	 * @since 1.0.0
	 **/
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EnvatoItem ) ) {
			self::$instance = new EnvatoItem;
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
	 * @access protected
	 **/
	public function __clone() {
		/** Cloning instances of the class is forbidden. */
		_doing_it_wrong( __FUNCTION__, esc_html__( 'The whole idea of the singleton design pattern is that there is a single object therefore, we don\'t want the object to be cloned.', 'scroller' ), '1.1.5' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be unserialized.
	 *
	 * @return void
	 * @since 1.0.0
	 * @access protected
	 **/
	public function __wakeup() {
		/** Unserializing instances of the class is forbidden. */
		_doing_it_wrong( __FUNCTION__, esc_html__( 'The whole idea of the singleton design pattern is that there is a single object therefore, we don\'t want the object to be unserialized.', 'scroller' ), '1.1.5' );
	}

} // End Class EnvatoItem.