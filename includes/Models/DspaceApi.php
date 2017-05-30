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
use BCcampus\Utility;

class DspaceApi implements Polymorphism\RestInterface {
	private $apiBaseUrl = '';
	private $collectionUuid = '';
	private $url = '';
	private $handle = '';

	function retrieve( $args ) {
		$env = include( OTB_DIR . '.env.php' );
		/**
		 * would like a JSON response
		 */
		$opts                 = array(
			'http' => array(
				'method' => 'GET',
				'header' => 'Accept: application/json',
			)
		);
		$expand               = 'expand=metadata,bitstreams';
		$query_field          = 'query_field[]=';
		$query_op             = 'query_op[]=';
		$query_val            = 'query_val[]=';
		$start                = 0;
		$limit                = 0;
		$context              = stream_context_create( $opts );
		$this->apiBaseUrl     = $env['dspace']['SITE_URL'];
		$this->collectionUuid = $env['dspace']['UUID'];
		$this->handle         = $env['dspace']['HANDLE'];

		// allow for collection to be overridden with a passed argument
		// otherwise default collection uuid should be set in .env.php
		if ( empty( $args['collectionUuid'] ) ) {
			$args['collectionUuid'] = $this->collectionUuid;
		} else {
			$this->collectionUuid = $args['collectionUuid'];
		}


		// one item
		if ( ! empty( $args['uuid'] ) ) {
			$this->url = $this->apiBaseUrl . 'items/' . $args['uuid'] . '?' . $expand;
		} else {

			// return all items in the collection
			// rest/collections/:ID/items[?expand={metadata,bitstreams}]
			if ( empty( $args['keyword'] ) && empty( $args['subject'] ) && isset( $this->collectionUuid ) ) {
				$this->url = $this->apiBaseUrl . 'collections/' . $this->collectionUuid . '/items?' . $expand;
			}

			// filter by subject area, contain the search by the collection handle
			if ( isset( $args['subject'] ) && isset( $this->collectionUuid ) ) {
				$filtered_query   = $query_field . 'dc.subject&' . $query_op . 'contains&' . $query_val . $args['subject'];
				$collection_query = $query_field . 'dc.identifier.uri&' . $query_op . 'contains&' . $query_val . $this->handle;
				$this->url        = $this->apiBaseUrl . 'filtered-items?' . $filtered_query . '&' . $collection_query;
			}

			// filter by keyword

			// filter by contributor

			//

		}

		// fetch results
		$result = json_decode( file_get_contents( $this->url, false, $context ), true );

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