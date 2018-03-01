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
include( OTB_DIR . 'inc/otb-utility.php' );
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
$override = include( OTB_DIR . 'env.php' );
if ( file_exists( OTB_DIR . 'env.php' ) && ! empty( $override['environment'] ) ) {
	$domain = $override['environment'];
} else {
	$domain = $_SERVER['HTTP_HOST'];
}

// include the config file
if ( file_exists( OTB_DIR . 'config/environments/.env.' . $domain . '.php' ) ) {
	$env = include( OTB_DIR . 'config/environments/.env.' . $domain . '.php' );
	OpenTextBooks\Config::getInstance()->set( $env );
}


