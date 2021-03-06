<?php
/**
 * You can override which config file gets loaded (ideal for development)
 * otherwise the default is to get the value from $_SERVER['HTTP_HOST']
 * leave this blank for default behaviour (ie environment => '')
 *
 * override: set the value of 'environment' equal to the domain of the application
 * create a corresponding .env.mydomain.com.php file in `config/environments`
 *
 * example:
 *
 * set      'environment' => 'mydomain.com'
 * create   config/environment/.env.mydomain.com.php
 *
 */
return [
	'environment' => '',
];
