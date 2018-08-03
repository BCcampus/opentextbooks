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
		$args['subject'] = \BCcampus\Utility\url_encode( $args['subject'] );
		$anyQuery        = $args['search'];
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
		} // MANY ITEMS
		else {

			//the limit for the API is 50 items, so we need 50 or less. 0 is 'limitless' so we need to set
			//it to the max and loop until we reach all available results, 50 at a time.
			$limit = ( $limit == 0 || $limit > 50 ? $limit = 50 : $limit = $limit );

			$firstSubjectPath  = '';
			$secondSubjectPath = '';
			$is                = \BCcampus\Utility\raw_url_encode( self::OPR_IS );
			$or                = \BCcampus\Utility\raw_url_encode( self::OPR_OR );
			$optionalParam     = '&info=' . \BCcampus\Utility\array_to_csv( $info ) . '';

			// if there's a specified user query, deal with it, change the order
			// to relevance as opposed to 'modified' (default)
			if ( $anyQuery != '' ) {
				$order    = 'relevance';
				$anyQuery = \BCcampus\Utility\raw_url_encode( $anyQuery );
				$anyQuery = 'q=' . $anyQuery . '&';
			}

			// start building the URL
			$searchWhere = 'search?' . $anyQuery . '&collections=' . $args['collectionUuid'] . '&start=' . $start . '&length=' . $limit . '&order=' . $order . '&where=';   //limit 50 is the max results allowed by the API
			//switch the API url, depending on whether you are searching for a keyword or a subject.
			if ( empty( $args['subject'] ) ) {
				$this->url = $this->apiBaseUrl . $searchWhere . $optionalParam;
			} // SCENARIOS, require three distinct request urls depending...
			// 1
			elseif ( $args['keyword'] == true ) {
				$firstSubjectPath = \BCcampus\Utility\url_encode( $this->keywordPath );
				//oh, the API is case sensitive so this broadens our results, which we want
				$secondWhere = strtolower( $args['subject'] );
				$firstWhere  = ucwords( $args['subject'] );
				$this->url   = $this->apiBaseUrl . $searchWhere . $firstSubjectPath . $is . "'" . $firstWhere . "'" . $or . $firstSubjectPath . $is . "'" . $secondWhere . "'" . $optionalParam;  //add the base url, put it all together
			} // 2
			elseif ( $args['contributor'] == true ) {
				$firstSubjectPath = \BCcampus\Utility\url_encode( $this->contributorPath );
				$this->url        = $this->apiBaseUrl . $searchWhere . $firstSubjectPath . $is . "'" . $args['subject'] . "'" . $optionalParam;
			} // 3
			else {
				$firstSubjectPath  = \BCcampus\Utility\url_encode( $this->subjectPath1 );
				$secondSubjectPath = \BCcampus\Utility\url_encode( $this->subjectPath2 );
				$this->url         = $this->apiBaseUrl . $searchWhere . $firstSubjectPath . $is . "'" . $args['subject'] . "'" . $or . $secondSubjectPath . $is . "'" . $args['subject'] . "'" . $optionalParam;  //add the base url, put it all together
			}

			//get the array back from the API call
			$result = json_decode( file_get_contents( $this->url ), true );

			//if the # of results we get back is less than the max we asked for
			if ( $result['length'] != 50 ) {
				return $result['results'];
			} else {

				// is the available amount greater than the what was returned? Get more!
				$availableResults = $result['available'];
				$start            = $result['start'];
				$limit            = $result['length'];

				if ( $availableResults > $limit ) {
					$loop = intval( $availableResults / $limit );

					for ( $i = 0; $i < $loop; $i ++ ) {
						$start       = $start + 50;
						$searchWhere = 'search?' . $anyQuery . '&collections=' . $args['collectionUuid'] . '&start=' . $start . '&length=' . $limit . '&order=' . $order . '&where=';   //length 50 is the max results allowed by the API
						//Three different scenarios here, depending..
						//1
						if ( ! empty( $args['subject'] ) && $args['contributor'] == true ) {
							$this->url = $this->apiBaseUrl . $searchWhere . $firstSubjectPath . $is . "'" . $args['subject'] . "'" . $optionalParam;
						} //2
						elseif ( ! empty( $args['subject'] ) ) {
							$this->url = $this->apiBaseUrl . $searchWhere . $firstSubjectPath . $is . "'" . $args['subject'] . "'" . $or . $secondSubjectPath . $is . "'" . $args['subject'] . "'" . $optionalParam;  //add the base url, put it all together
						} //3
						else {
							$this->url = $this->apiBaseUrl . $searchWhere . $optionalParam;
						}
						$nextResult = json_decode( file_get_contents( $this->url ), true );

						// push each new result onto the existing array
						$partOfNextResult = $nextResult['results'];
						foreach ( $partOfNextResult as $val ) {
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
