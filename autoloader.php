<?php
/**
 * PSR-4 compliant autoload.
 *
 * @modified from https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @param string $class The fully-qualified class name.
 *
 * @return void
 */
\spl_autoload_register( function ( $class ) {

	// project-specific namespace prefix
	$prefix = 'BCcampus\\OpenTextBooks';

	// base directory for the namespace prefix
	$base_dir = __DIR__ . '/inc';

	// does the class use the namespace prefix?
	$len = \strlen( $prefix );

	if ( \strncmp( $prefix, $class, $len ) !== 0 ) {
		// no, move to the next registered autoloader
		return;
	}

	// get the relative class name
	$relative_class = \substr( $class, $len );

	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . \str_replace( '\\', '/', $relative_class ) . '.php';

	// if the file exists, require it
	if ( \file_exists( $file ) ) {
		require $file;
	}
} );

/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
|
| Need to set a few constants. Trickery with OTB_URL is necessary to avoid
| having to set different paths for production and dev environments.
|
|
*/

if ( ! defined( 'OTB_DIR' ) ) {
	define( 'OTB_DIR', str_replace( '\\','/',dirname( __FILE__ ) ) . '/' ); // Must have trailing slash!
}

if ( ! defined( 'OTB_VERSION' ) ) {
	define( 'OTB_VERSION', '1.5.0' );
}

if ( ! defined( 'OTB_URL' ) ) {
	$path     = '';
	$domain   = $_SERVER['HTTP_HOST'];
	$doc_root = $_SERVER['DOCUMENT_ROOT'];
	$d        = explode( '/', $doc_root );
	$e        = explode( '/', OTB_DIR );
	$s        = array_diff( $e, $d );

	if ( is_array( $s ) ) {
		foreach ( $s as $dir ) {
			$path .= $dir . '/';
		}
	} else {
		$path = '';
	}
	$uri = '//' . $domain . '/' . $path;

	define( 'OTB_URL', $uri );
}

/*
|--------------------------------------------------------------------------
| Includes
|--------------------------------------------------------------------------
|
| Generic utility functions
|
|
*/
include( OTB_DIR . 'inc/otb-utility.php' );
