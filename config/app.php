<?php

use BCcampus\OpenTextBooks;

/*
|--------------------------------------------------------------------------
| Includes
|--------------------------------------------------------------------------
|
| Generic utility functions
|
|
*/
include( 'constants.php' );
include( OTB_DIR . 'inc/utility/namespace.php' );
include( OTB_DIR . 'vendor/autoload.php' );

/*
|--------------------------------------------------------------------------
| Config
|--------------------------------------------------------------------------
|
| Load domain specific config file
|
|
*/

//find the domain
$override = include( 'env.php' );
if ( file_exists( 'env.php' ) && ! empty( $override['environment'] ) ) {
	$domain = $override['environment'];
} else {
	$domain = $_SERVER['HTTP_HOST'];
}

// include the config file
if ( file_exists( OTB_DIR . 'config/environments/.env.' . $domain . '.php' ) ) {
	$env = include( OTB_DIR . 'config/environments/.env.' . $domain . '.php' );
	OpenTextBooks\Config::getInstance()->set( $env );
} else {
	$ignored = [ '.', '..', '.htaccess', 'env.sample.php' ];
	$files   = [];

	foreach ( scandir( OTB_DIR . 'config/environments' ) as $file ) {
		if ( in_array( $file, $ignored, true ) ) {
			continue;
		}
		$files[] = $file;
	}

	$env = include( OTB_DIR . 'config/environments/' . $files[0] );
	OpenTextBooks\Config::getInstance()->set( $env );

}


