<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2012-2017 Brad Payne <https://bradpayne.ca>
 * Date: 2017-05-29
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2012-2017, Brad Payne
 */

namespace BCcampus\OpenTextBooks\Models;

use BCcampus\OpenTextBooks\Polymorphism;


class DspaceApi implements Polymorphism\RestInterface {
	private $apiBaseUrl = '';
	private $collectionUuid = '';
	private $url = '';

	function retrieve( $args ) {
		$env                  = include( OTB_DIR . '.env.php' );
		$opts                 = array(
			'http' => array(
				'method' => 'GET',
				'header' => 'Accept: application/json',
			)
		);
		$context              = stream_context_create( $opts );
		$this->apiBaseUrl     = $env['dspace']['SITE_URL'];
		$this->collectionUuid = $env['dspace']['UUID'];

		// nothing can happen without a collection
		if ( empty( $args['collectionUuid'] ) ) {
			$args['collectionUuid'] = $this->collectionUuid;
		}

		// one item
		if ( ! empty( $args['uuid'] ) ) {
			$this->url = $this->apiBaseUrl . 'items/' . $args['uuid'] . '?expand=all';
			$result    = json_decode( file_get_contents( $this->url, false, $context ), true );
		} else {

		}

		return $result;
	}

	function create() {
		// TODO: Implement create() method.
	}

	function replace() {
		// TODO: Implement replace() method.
	}

	function remove() {
		// TODO: Implement remove() method.
	}
}