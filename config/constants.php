<?php
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
	define( 'OTB_DIR', str_replace( '\\', '/', dirname( dirname(__FILE__) ) ) . '/' ); // Must have trailing slash!
}

if ( ! defined( 'OTB_VERSION' ) ) {
	define( 'OTB_VERSION', '2.0.0' );
}

if ( ! defined( 'OTB_URL' ) ) {
// check for a WordPress environment
	if ( function_exists( 'get_site_url' ) ) {
		$wp_uri    = get_site_url( get_current_blog_id(), '/wp-content/' );
		$ex        = explode( '/', OTB_DIR );
		$key       = array_search( 'wp-content', $ex );
		$slice     = array_slice( $ex, $key + 1 );
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