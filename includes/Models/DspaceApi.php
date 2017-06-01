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

	/**
	 * Based on DSpace RESTapi v6
	 * Documentation
	 * https://github.com/DSpace/DSpace/tree/master/dspace-rest
	 * https://wiki.duraspace.org/display/DSDOC6x/REST+Reports+-+Summary+of+API+Calls
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	function retrieve( $args ) {
		$env = include( OTB_DIR . '.env.php' );
		/**
		 * JSON response please
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
		$coll_sel             = 'collSel[]=';
		$start                = 'offset=';
		$filters              = 'is_discoverable';
		$limit                = 0;
		$context              = stream_context_create( $opts );
		$this->apiBaseUrl     = $env['dspace']['SITE_URL'];
		$this->collectionUuid = $env['dspace']['UUID'];

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
				$this->url = $this->apiBaseUrl . 'collections/' . $this->collectionUuid . '/items?' . $expand . '&' . $start . $args['start'];
			}

			// filter by subject area, contain the search by the collection handle
			if ( ! empty( $args['subject'] ) && ! empty( $this->collectionUuid ) ) {
				$filtered_query   = $query_field . 'dc.subject.*&' . $query_op . 'contains&' . $query_val . $args['subject'];
				$collection_query = $coll_sel . $this->collectionUuid;
				$filter_query     = 'filters=' . $filters;
				$this->url        = $this->apiBaseUrl . 'filtered-items?' . $filtered_query . '&' . $collection_query . '&' . $filter_query;
			}

			// filter by search term
			// rest/filtered-items?query_field[]=dc.subject.*&query_field[]=dc.creator&query_op[]=contains&query_op[]=matches&query_val[]=politic&query_val[]=.*Krogh.*
			// &collSel[]=&limit=100&offset=0&expand=parentCollection,metadata&filters=is_withdrawn,is_discoverable&show_fields[]=dc.subject&show_fields[]=dc.subject.other

			// filter by author

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