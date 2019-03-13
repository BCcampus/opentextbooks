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
use BCcampus\Utility;

if ( ! defined( 'OTB_DIR' ) ) {
	define( 'OTB_DIR', str_replace( '\\', '/', dirname( __DIR__ ) ) . '/' ); // Must have trailing slash!
}

if ( ! defined( 'OTB_VERSION' ) ) {
	define( 'OTB_VERSION', '2.0.0' );
}

if ( ! defined( 'OTB_URL' ) ) {
	// check for a WordPress environment
	define( 'OTB_URL', Utility\set_app_url( OTB_DIR ) );
}
