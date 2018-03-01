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

namespace BCcampus\OpenTextBooks;


class Config {
	/**
	 * @var Singleton
	 */
	private static $instance;

	private static $config = [];

	/**
	 * gets the instance via lazy initialization (created on first usage)
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * is not allowed to call from outside to prevent from creating multiple instances,
	 * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
	 */
	private function __construct() {
	}

	public function set( array $config ) {
		if ( is_array( $config ) ) {
			self::$config = $config;
		}
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function get() {
		if ( ! empty( self::$config ) ) {
			return self::$config;
		} else {
			throw new \Exception( 'Could not find a config file at \BCcampus\OpenTextbooks\Config::get' );
		}
	}

	/**
	 * prevent the instance from being cloned (which would create a second instance of it)
	 */
	private function __clone() {
	}

	/**
	 * prevent from being unserialized (which would create a second instance of it)
	 */
	private function __wakeup() {
	}
}