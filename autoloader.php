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

	if ( false !== ( $last_ns_pos = strripos( $relative_class, '\\' ) ) ) {
		$namespace = substr( $relative_class, 0, $last_ns_pos );
		$class     = substr( $relative_class, $last_ns_pos + 1 );
		$file      = str_replace( '\\', DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
	}
	$file .= 'class-' . str_replace( '_', '-', $class ) . '.php';

	$path = $base_dir . strtolower( $file );

	// if the file exists, require it
	if ( \file_exists( $path ) ) {
		require $path;
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
	define( 'OTB_VERSION', '2.0.0' );
}

if ( ! defined( 'OTB_URL' ) ) {
	// check for a WordPress environment
	if ( function_exists( 'get_site_url' ) ) {
		$wp_uri = get_site_url( get_current_blog_id(), '/wp-content/' );
		$ex     = explode( '/', OTB_DIR );
		$key    = array_search( 'wp-content', $ex );
		$slice  = array_slice( $ex, $key + 1 );
		$file_path = '';

		foreach ( $slice as $path ) {
			if ( empty( $path ) ) {
				continue;
			}
			$file_path .= '/' . $path;
		}

		$uri = $wp_uri . $file_path . '/';

	} else {
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
	}

	define( 'OTB_URL', $uri );
}

/*
|--------------------------------------------------------------------------
| Application
|--------------------------------------------------------------------------
|
| constants, includes, config
|
|
*/
include_once( __DIR__ . '/config/application.php' );
