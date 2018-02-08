<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2012-2016 Brad Payne <https://bradpayne.ca>
 * Date: 2016-05-31
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2012-2016, Brad Payne
 *
 * Change the name of this file to `.env.mydomain.php`
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Adoption stats
	|--------------------------------------------------------------------------
	|
	| Database connection
	|
	|
	*/
	'webform'    => [
		'db_host' => '',
		'db_port' => '',
		'db_name' => '',
		'db_user' => '',
		'db_pswd' => '',
	],

	/*
	|--------------------------------------------------------------------------
	| Book Reviews
	|--------------------------------------------------------------------------
	|
	| Endpoint to LimeSurvey instance
	|
	|
	*/
	'limesurvey' => [
		'url'  => '',
		'user' => '',
		'pswd' => '',
	],

	/*
	|--------------------------------------------------------------------------
	| Matomo
	|--------------------------------------------------------------------------
	|
	| Matomo (formerly Piwik) Analytics endpoint
	|
	|
	*/
	'matomo'     => [
		'url'   => '',
		'token' => '',
	],

	/*
	|--------------------------------------------------------------------------
	| Equella
	|--------------------------------------------------------------------------
	|
	| Endpoints for Equella Instance
	|
	|
	*/
	'equella'    => [
		'url'  => '',
		'uuid' => ''
	],

	/*
	|--------------------------------------------------------------------------
	| DSpace
	|--------------------------------------------------------------------------
	|
	| Endpoints for Dspace instance
	|
	|
	*/
	'dspace'     => [
		'url'  => '',
		'uuid' => '',
	],

	/*
	|--------------------------------------------------------------------------
	| Short Code Service
	|--------------------------------------------------------------------------
	|
	|
	|
	|
	*/
	'yourls'     => [
		'url'  => '',
		'uuid' => '',
	],

	/*
	|--------------------------------------------------------------------------
	| Domain
	|--------------------------------------------------------------------------
	|
	| Application specific
	|
	|
	*/
	'domain'     => [
		'scheme'          => '',
		'host'            => '',
		'app_path'        => '',
		'adoption_path'   => '',
		'adaptation_path' => '',
	],
];
