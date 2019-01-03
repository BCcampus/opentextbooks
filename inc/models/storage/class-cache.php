<?php

/**
 * SimplePie
 *
 * A PHP-Based RSS and Atom Feed Framework.
 * Takes the hard work out of managing a complete RSS/Atom solution.
 *
 * Copyright (c) 2004-2012, Ryan Parman, Geoffrey Sneddon, Ryan McCue, and contributors
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this list of
 *      conditions and the following disclaimer.
 *
 *    * Redistributions in binary form must reproduce the above copyright notice, this list
 *      of conditions and the following disclaimer in the documentation and/or other materials
 *      provided with the distribution.
 *
 *    * Neither the name of the SimplePie Team nor the names of its contributors may be used
 *      to endorse or promote products derived from this software without specific prior
 *      written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
 * AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @modified by Brad Payne, October 2012
 *
 */

/**
 * Modified from the SimplePie project, this caches data to the filesystem
 *
 * @package OPENTEXTBOOKS
 */

namespace BCcampus\OpenTextBooks\Models\Storage;

use BCcampus\OpenTextBooks\Polymorphism\StorageInterface;

class Cache implements StorageInterface {

	/**
	 * Location string
	 *
	 * @see SimplePie::$cache_location
	 * @var string
	 */
	private $location;

	/**
	 * Filename
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * File path
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Type of file to create
	 * @var string
	 */
	private $extension;
	//  private $cacheDuration = 86400; // 24 hours.
	private $cacheDuration = 28800; // 8 hours
	private $serialize;

	/**
	 * Create a new cache object, we only want one (singleton) because it's writing,
	 * reading and creating files.
	 *
	 * @param string $location Location string (from SimplePie::$cache_location)
	 * @param string $name Unique ID for the cache
	 * @param $type
	 * @param $serialize
	 */
	private function __construct( $location, $name, $type, $serialize ) {
		$this->location  = $location;
		$this->extension = $type;
		$this->filename  = md5( $name );
		$this->name      = "$this->location/$this->filename.$this->extension";
		$this->serialize = $serialize;
	}

	public static function create( $location, $name, $type, $serialize = true ) {

		$instance = new Cache( $location, $name, $type, $serialize );

		return $instance;
	}

	/**
	 * Save data to the cache
	 *
	 * @param array $data Data to store in the cache.
	 *
	 * @return bool Successfulness
	 */
	public function save( $data ) {
		// makes sense to throw in an .htaccess file at this point
		// to protect data
		\BCcampus\Utility\restrict_access();

		if ( file_exists( $this->name ) && is_writeable( $this->name ) || file_exists( $this->location ) && is_writeable( $this->location ) ) {
			if ( true === $this->serialize ) {
				$data = serialize( $data );
			}

			return (bool) file_put_contents( $this->name, $data );

		} else {
			$this->mkFile();
			if ( true === $this->serialize ) {
				$data = serialize( $data );
			}

			return (bool) file_put_contents( $this->name, $data );
		}

		return false;
	}

	/**
	 * Retrieve the data saved to the cache
	 *
	 * @return array Data for SimplePie::$data
	 */
	public function load() {
		if ( file_exists( $this->name ) && is_readable( $this->name ) ) {
			( true === $this->serialize ? $data = unserialize( file_get_contents( $this->name ) ) : $data = file_get_contents( $this->name ) );

			return $data;
		}

		return false;
	}

	/**
	 * Retrieve the last modified time for the cache
	 *
	 * @return int Timestamp
	 */
	public function mtime() {
		if ( file_exists( $this->name ) ) {
			return filemtime( $this->name );
		}

		return false;
	}

	/**
	 * Set the last modified time to the current time
	 *
	 * @return bool Success status
	 */
	public function touch() {
		if ( file_exists( $this->name ) ) {
			return touch( $this->name );
		}

		return false;
	}

	/**
	 * Remove the cache
	 *
	 * @return bool Success status
	 */
	public function remove() {
		if ( file_exists( $this->name ) ) {
			return unlink( $this->name );
		}

		return false;
	}

	/**
	 * a helper to create text files
	 */
	private function mkFile() {

		if ( ! file_exists( $this->name ) ) {

			fopen( "$this->filename.$this->extension", 'w+' );
		}
	}

	/**
	 * check to see if the cacheDuration has expired
	 * @return boolean true if it's expired, or doesn't exist
	 */
	public function expiredCache() {

		if ( ( time() - $this->mtime() ) > $this->cacheDuration ) {
			return true;
		}

		return false;
	}

	/**
	 * check to see if a file exists, or not
	 * @return boolean true if it does
	 */
	public function fileExists() {
		if ( file_exists( $this->name ) ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getFileSize() {
		if ( file_exists( $this->name ) ) {
			return filesize( $this->name );
		}

		return false;
	}

}
