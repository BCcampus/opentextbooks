<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2012-2018 Brad Payne <https://bradpayne.ca>
 * Date: 2018-02-07
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2012-2018, Brad Payne
 */

use Illuminate\Container\Container;

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

$container = new Container();

$container->bind( 'Config', function ( $container ) {
//	return new \BCcampus\Config\Config( $container->make( 'Config' ) );
//	return $container->make( 'Config' ) ;
	$domain = include( OTB_DIR . 'env.php' );
	$env = include( OTB_DIR . 'config/environments/.env.' . $domain['environment'] . '.php' );


}, true );


//$domain = include( OTB_DIR . 'env.php' );
//
//if ( file_exists( OTB_DIR . 'config/environments/.env.' . $domain['environment'] . '.php' ) ) {
//	$env = include( OTB_DIR . 'config/environments/.env.' . $domain['environment'] . '.php' );
//
//	$container = new Container();
//	$container['config'] = $env;
//}





//$config['environment'] = function ($c) {
//	$domain = include( OTB_DIR . 'env.php' );
//	$env    = include( OTB_DIR . 'config/environments/' . '.env' . $domain['environment'] . '.php' );
//
//	return $env;
//};

//$config->singleton( 'environment', function () {
//	$domain = include( OTB_DIR . 'env.php' );
//	$env = include( OTB_DIR . 'config/environment/' . '.env' . $domain['environment'] . '.php' );
//
//	return $env;
//} );

//Container::getInstance()
//         ->bindIf('config', function () {
//	         $domain = include( OTB_DIR . 'env.php' );
//	         return new Config([
//		         'env' => require (OTB_DIR . 'config/environment/' . '.env' . $domain['environment'] . '.php'),
//	         ]);
//         }, true);

//if ( file_exists( OTB_DIR . 'config/environment/' . '.env' . $domain['environment'] . '.php' ) ) {
//	require_once( OTB_DIR . 'config/environment/' . '.env' . $domain['environment'] . '.php' );
//}