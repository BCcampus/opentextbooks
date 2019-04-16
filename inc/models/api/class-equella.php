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
 */

namespace BCcampus\OpenTextBooks\Models\Api;

use BCcampus\OpenTextBooks\Config;
use BCcampus\OpenTextBooks\Polymorphism;
use BCcampus\Utility;

class Equella implements Polymorphism\RestInterface {
	private $apiBaseUrl      = '';
	private $collectionUuid  = '';
	private $subjectPath1    = '/xml/item/subject_class_level1';
	private $subjectPath2    = '/xml/item/subject_class_level2';
	private $contributorPath = '/xml/contributordetails/institution';
	private $keywordPath     = '/xml/item/keywords';
	private $url             = '';

	const OPR_IS      = ' is ';
	const OPR_OR      = ' OR ';
	const ALL_RECORDS = '_ALL';

	/**
	 * @param $args
	 *
	 * @return mixed
	 */
	function retrieve( $args ) {
		$env                  = Config::getInstance()->get();
		$this->apiBaseUrl     = $env['equella']['url'];
		$this->collectionUuid = $env['equella']['uuid'];

		// must be url encoded
		$args['subject'] = Utility\url_encode( $args['subject'] );
		$any_query       = $args['search'];
		$order           = 'modified';
		$start           = 0;
		$info            = [ 'basic', 'metadata', 'detail', 'attachment', 'drm' ];
		$limit           = 0;
		// provide a default collection, yet allow for override
		if ( empty( $args['collectionUuid'] ) ) {
			$args['collectionUuid'] = $this->collectionUuid;
		}

		// ONE ITEM
		if ( ! empty( $args['uuid'] ) ) {
			$this->url = $this->apiBaseUrl . 'item/' . $args['uuid'] . '/latestlive';
			$result    = json_decode( file_get_contents( $this->url ), true );

			return $result;
		} else { // MANY ITEMS

			//the limit for the API is 50 items, so we need 50 or less. 0 is 'limitless' so we need to set
			//it to the max and loop until we reach all available results, 50 at a time.
			$limit = ( $limit === 0 || $limit > 50 ) ? $limit = 50 : $limit;

			$first_subject_path  = Utility\url_encode( $this->subjectPath1 );
			$second_subject_path = Utility\url_encode( $this->subjectPath2 );
			$sec_subj            = [];
			$combined            = '';
			$is                  = Utility\raw_url_encode( self::OPR_IS );
			$or                  = Utility\raw_url_encode( self::OPR_OR );
			$optional_param      = '&info=' . Utility\array_to_csv( $info ) . '';

			// if there's a specified user query, deal with it, change the order
			// to relevance as opposed to 'modified' (default)
			if ( $any_query !== '' ) {
				$order     = 'relevance';
				$any_query = Utility\raw_url_encode( $any_query );
				$any_query = 'q=' . $any_query;
			}

			// start building the URL
			$search_where = 'search?' . $any_query . '&collections=' . $args['collectionUuid'] . '&start=' . $start . '&length=' . $limit . '&order=' . $order . '&where=';   //limit 50 is the max results allowed by the API
			//switch the API url, depending on whether you are searching for a keyword or a subject.
			if ( empty( $args['subject'] ) && empty( $args['subject_class_level2'] ) ) {
				$this->url = $this->apiBaseUrl . $search_where . $optional_param;
			} elseif ( $args['keyword'] === true ) { // SCENARIOS, require three distinct request urls depending...
				$first_subject_path = Utility\url_encode( $this->keywordPath );
				//oh, the API is case sensitive so this broadens our results, which we want
				$second_where = strtolower( $args['subject'] );
				$first_where  = ucwords( $args['subject'] );
				$this->url    = $this->apiBaseUrl . $search_where . $first_subject_path . $is . "'" . $first_where . "'" . $or . $first_subject_path . $is . "'" . $second_where . "'" . $optional_param;  //add the base url, put it all together
			} elseif ( $args['contributor'] === true ) {
				$first_subject_path = Utility\url_encode( $this->contributorPath );
				$this->url          = $this->apiBaseUrl . $search_where . $first_subject_path . $is . "'" . $args['subject'] . "'" . $optional_param;
			} elseif ( ! empty( $args['subject_class_level2'] ) && ! empty( $args['subject_class_level1'] ) ) {
				$this->url = sprintf( '%1$s%2$s%3$s%4$s\'%5$s\'%6$s%7$s%4$s\'%8$s\'%9$s', $this->apiBaseUrl, $search_where, $first_subject_path, $is, Utility\raw_url_encode( $args['subject_class_level1'] ), $or, $second_subject_path, Utility\raw_url_encode( $args['subject_class_level2'] ), $optional_param );
			} elseif ( isset( $args['subject_class_level2'] ) && ! empty( $args['subject_class_level2'] ) ) { // to handle multiple secondary subjects
				$sec_subj   = explode( ',', $args['subject_class_level2'] );
				$c_sec_subj = count( $sec_subj );
				$i          = 1;

				foreach ( $sec_subj as $s ) {
					$sec_subj_opr = ( $i === $c_sec_subj ) ? '' : $or;
					$combined    .= $second_subject_path . $is . "'" . Utility\raw_url_encode( $s ) . "'" . $sec_subj_opr;
					$i ++;
				}
				$this->url = sprintf( '%1$s%2$s%3$s%4$s', $this->apiBaseUrl, $search_where, $combined, $optional_param );
			} else {
				$this->url = $this->apiBaseUrl . $search_where . $first_subject_path . $is . "'" . $args['subject'] . "'" . $or . $second_subject_path . $is . "'" . $args['subject'] . "'" . $optional_param;  //add the base url, put it all together
			}

			//get the array back from the API call
			$result = json_decode( file_get_contents( $this->url ), true );

			//if the # of results we get back is less than the max we asked for
			if ( $result['length'] !== 50 ) {
				return $result['results'];
			} else {

				// is the available amount greater than the what was returned? Get more!
				$available_results = $result['available'];
				$start             = $result['start'];
				$limit             = $result['length'];

				if ( $available_results > $limit ) {
					$loop = intval( $available_results / $limit );

					for ( $i = 0; $i < $loop; $i ++ ) {
						$start        = $start + 50;
						$search_where = 'search?' . $any_query . '&collections=' . $args['collectionUuid'] . '&start=' . $start . '&length=' . $limit . '&order=' . $order . '&where=';   //length 50 is the max results allowed by the API
						//Three different scenarios here, depending..
						//1
						if ( ! empty( $args['subject'] ) && $args['contributor'] === true ) {
							$this->url = $this->apiBaseUrl . $search_where . $first_subject_path . $is . "'" . $args['subject'] . "'" . $optional_param;
						} elseif ( ! empty( $args['subject'] ) ) {
							$this->url = $this->apiBaseUrl . $search_where . $first_subject_path . $is . "'" . $args['subject'] . "'" . $or . $second_subject_path . $is . "'" . $args['subject'] . "'" . $optional_param;  //add the base url, put it all together
						} else {
							$this->url = $this->apiBaseUrl . $search_where . $optional_param;
						}
						$next_result = json_decode( file_get_contents( $this->url ), true );

						// push each new result onto the existing array
						$part_of_next_result = $next_result['results'];
						foreach ( $part_of_next_result as $val ) {
							array_push( $result['results'], $val );
						}
					}
				} /* end of if */
			} /* end of else */

			return $result['results'];
		} /* end of else */
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
