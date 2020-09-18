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

/**
 * Register Plugin Custom Autoloader.
 */
spl_autoload_register( function ( $class ) {

	$namespace = 'Merkulove\\';

	/** Bail if the class is not in our namespace. */
	if ( 0 !== strpos( $class, $namespace ) ) {
		return;
	}

	/** Build the filename. */
	$file = realpath( __DIR__ );
	$file = $file . DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

	/** If the file exists for the class name, load it. */
	if ( file_exists( $file ) ) {
		include( $file );
	}

} );