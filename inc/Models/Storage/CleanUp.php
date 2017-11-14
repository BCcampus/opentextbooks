<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2012-2017 Brad Payne <https://bradpayne.ca>
 * Date: 2017-11-12
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2012-2017, Brad Payne
 */

namespace BCcampus\OpenTextBooks\Models\Storage;


class CleanUp {

	/**
	 * maximum age (days) of a cache file
	 *
	 * @var int
	 */
	var $max_age = 3600 * 24 * 7;

	/**
	 * path to the cache directory
	 *
	 * @var string
	 */
	var $path = OTB_DIR . 'cache/';

	/**
	 * probability
	 *
	 * @var int
	 */
	var $probability = 10;

	/**
	 * random number
	 *
	 * @var int
	 */
	var $random;

	/**
	 * CleanUp constructor.
	 */
	public function __construct() {
		$this->random = rand( 0, 100 );

	}

	/**
	 * Clean up occurs on a probability basis
	 *
	 * @param string $dir name of the directory
	 * @param string $ext name of the suffix 'txt'
	 */
	public function maybeRun( $dir, $ext ) {
		if ( $this->random < $this->probability ) {
			$this->clean( $dir, $ext );
		}
	}

	/**
	 * Remove files with a specific suffix from a directory
	 * based on a predetermined max_age
	 *
	 * @param string $dir
	 * @param string $ext
	 */
	protected function clean( $dir, $ext ) {

		if ( $handle = opendir( $this->path . $dir ) ) {

			while ( false !== ( $file = readdir( $handle ) ) ) {
				// check the file extension
				$suffix = explode( '.', $file );
				$match  = array_pop( $suffix );
				if ( 0 === strcmp( $ext, $match ) ) {

					$filelastmodified = filemtime( $this->path . $dir . '/' . $file );

					if ( ( time() - $filelastmodified ) > $this->max_age ) {
						unlink( $this->path . $dir . '/' . $file );
					}

				}
			}

			closedir( $handle );
		}
	}
}