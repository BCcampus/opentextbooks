<?php
/**
 * You can override which config file gets loaded (ideal for development)
 * otherwise the default is to get the value from $_SERVER['HTTP_HOST']
 * leve this blank for default behaviour (ie environment => '')
 *
 * set the value of 'environment' equal to the domain of the application
 * create a corresponding .env.mydomain.php file in `config/environments`
 *
 * example:
 *
 * set      'environment' => 'mydomain'
 * create   config/environment/.env.mydomain.php
 *
 */
return [
	'environment' => ''
];
