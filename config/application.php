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

$domain = include( OTB_DIR . 'env.php' );

if ( file_exists( OTB_DIR . 'config/environments/.env.' . $domain['environment'] . '.php' ) ) {
	$env = include( OTB_DIR . 'config/environments/.env.' . $domain['environment'] . '.php' );
	OpenTextBooks\Config::getInstance()->set($env);
}

