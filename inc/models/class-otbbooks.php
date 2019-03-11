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
 *
 * Main goal is to retrieve data from either storage or
 * an API request, set instance variables with that data
 *
 * uses Delegation design pattern and dependency injection
 * of an interface to lessen the pain when switching the api
 * at some point in the future.
 *
 */
namespace BCcampus\OpenTextBooks\Models;

use BCcampus\OpenTextBooks\Polymorphism;

class OtbBooks extends Polymorphism\DataAbstract {
	private $defaultArgs = [
		'subject'        => '',
		'uuid'           => '',
		'search'         => '',
		'start'          => '',
		'contributor'    => '',
		'keyword'        => '',
		'lists'          => '',
		'stats'          => '',
		'collectionUuid' => '',
		'filter'         => '',
	];
	protected $args      = [];
	protected $api;
	private $location = 'cache/catalogue';
	private $type     = 'txt';
	private $data;
	const ALL_RECORDS = '_ALL';

	/**
	 * OtbBooks constructor.
	 *
	 * @param Polymorphism\RestInterface $api
	 * @param array $args
	 */
	public function __construct( Polymorphism\RestInterface $api, $args ) {
		if ( is_array( $args ) ) {
			// let the args override the default args
			$this->args = array_merge( $this->defaultArgs, $args );
		}
		$this->api = $api;

		try {
			$this->retrieve();
		} catch ( \Exception $exc ) {
			error_log( $exc->getMessage(), 0 );
		}

	}

	/**
	 *
	 * @throws \Exception
	 */
	private function retrieve() {

		try {
			$this->setResponses();
		} catch ( \Exception $exp ) {
			error_log( $exp->getMessage(), 0 );
		}

	}

	/**
	 *
	 */
	private function setResponses() {
		$serialize = true;
		$file_name = $this->setFileName();
		$file_type = $this->type;

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		// check if there is a stored version of the results
		if ( $persistent_data ) {
			$this->data = $persistent_data->load();
		} else {
			// request an API response
			$this->data = $this->api->retrieve( $this->args );
			$this->saveToStorage( $this->location, $file_name, $file_type, $this->data, $serialize );
		}

	}

	/**
	 * @return mixed|string
	 */
	private function setFileName() {
		$name = '';
		// name file after the collection
		if ( empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) && empty( $this->args['search'] ) && empty( $this->args['subject_class_level2'] ) ) {
			$name = $this->args['collectionUuid'];
		} elseif ( ! empty( $this->args['uuid'] ) ) { // individual record
			$name = $this->args['uuid'];
		} elseif ( empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) && ! empty( $this->args['search'] ) ) { // name the file after the search term
			$name = $this->args['collectionUuid'] . $this->args['search'];
		} elseif ( ! empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) ) { // name the file after the subject area
			$name = $this->args['collectionUuid'] . $this->args['subject'] . $this->args['search'];
		} elseif ( ! empty( $this->args['subject'] ) && ! empty( $this->args['search'] ) ) { // name the file after the subject area and search term
			$name = $this->args['subject'] . $this->args['search'];
		} elseif ( ! empty( $this->args['subject_class_level2'] ) && ! empty( $this->args['subject_class_level1'] ) ) {
			$name = $this->args['subject_class_level2'] . $this->args['subject_class_level1'];
		} elseif ( ! empty( $this->args['subject_class_level2'] ) ) {
			$name = $this->args['subject_class_level2'];
		}

		return $name;
	}

	/**
	 * @return mixed
	 */
	public function getResponses() {
		return $this->data;

	}

	/**
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * slimmed down version of results
	 *
	 * @return array
	 */
	public function getPrunedResults() {
		$pruned = [];

		// if there are many
		if ( array_key_exists( 0, $this->data ) ) {
			foreach ( $this->data as $key => $item ) {
				$pruned[ $key ]['name']         = $item['name'];
				$pruned[ $key ]['uuid']         = $item['uuid'];
				$pruned[ $key ]['createdDate']  = $item['createdDate'];
				$pruned[ $key ]['modifiedDate'] = $item['modifiedDate'];
			}
			// if there is only one
		} else {
			$pruned['name']         = $this->data['name'];
			$pruned['uuid']         = $this->data['uuid'];
			$pruned['createdDate']  = $this->data['createdDate'];
			$pruned['modifiedDate'] = $this->data['modifiedDate'];
		}

		return $pruned;
	}

	/**
	 * Returns the array of books sorted DESC based on createdDate
	 *
	 * @return mixed
	 */
	public function sortByCreatedDate() {

		$created_date = $this->data;
		$ok           = usort(
			$created_date, function ( $a, $b ) {
				return strcmp( $b['createdDate'], $a['createdDate'] );
			}
		);

		if ( $ok ) {
			return $created_date;
		} else {
			return $this->data;
		}
	}

	/**
	 * @param $uuid
	 *
	 * @return mixed
	 */
	public function getBookSubjectAreas( $uuid ) {
		$subject_areas['subject_class_level1'] = '';
		$subject_areas['subject_class_level2'] = '';

		if ( 0 !== strcmp( $this->data['uuid'], $uuid ) ) {
			return $subject_areas;
		}

		$xml  = new \SimpleXMLElement( $this->data['metadata'] );
		$sub1 = $xml->xpath( 'item/subject_class_level1' );
		$sub2 = $xml->xpath( 'item/subject_class_level2' );

		$subject_areas['subject_class_level1'] = $sub1[0]->__toString();
		$subject_areas['subject_class_level2'] = $sub2[0]->__toString();

		return $subject_areas;
	}

	/**
	 * @param $subject_areas
	 *
	 * @return array
	 */
	public function sortByRelevance( $subject_areas, $random = false ) {
		$sorted_first  = [];
		$sorted_second = [];

		if ( ! is_array( $subject_areas ) ) {
			return [];
		}

		foreach ( $this->data as $book ) {
			$xml  = new \SimpleXMLElement( $book['metadata'] );
			$sub1 = $xml->xpath( 'item/subject_class_level1' );
			$sub2 = $xml->xpath( 'item/subject_class_level2' );

			if ( 0 === strcmp( $sub2[0]->__toString(), $subject_areas['subject_class_level2'] ) ) {
				array_push( $sorted_first, $book );
			}

			if ( 0 === strcmp( $sub1[0]->__toString(), $subject_areas['subject_class_level1'] ) ) {
				array_push( $sorted_second, $book );
			}
		}

		if ( true === $random ) {
			shuffle( $sorted_first );
		}

		return array_merge( $sorted_first, $sorted_second );
	}

	/**
	 * @return array
	 */
	public function getUuids() {
		$uuids = [];

		// if there are many
		if ( array_key_exists( 0, $this->data ) ) {
			foreach ( $this->data as $item ) {
				$uuids[] = $item['uuid'];
			}
		} else {
			$uuids[] = $this->data['uuid'];
		}

		return $uuids;
	}

	/**
	 * will return how many books are in each subject area
	 *
	 * @return array
	 */
	public function getSubjectAreas() {
		$subjects    = [];
		$num_sub2    = [];
		$unique_sub2 = [];

		// collect all sub1 and sub2 elements from data
		foreach ( $this->data as $book ) {
			$xml  = new \SimpleXMLElement( $book['metadata'] );
			$sub1 = $xml->xpath( 'item/subject_class_level1' );
			$sub2 = $xml->xpath( 'item/subject_class_level2' );

			foreach ( $sub2 as $obj ) {
				$tmp = $obj->__toString();
			}
			$subjects[ $sub1[0]->__toString() ][] = $tmp;

		}

		// arrange from most books per subject to least
		array_multisort( $subjects, SORT_DESC );

		// discover the number of subject level2 books
		foreach ( $subjects as $key => $subject ) {
			foreach ( $subject as $sub ) {
				$num_sub2[ $key ][ $sub ][] = 1;
			}
		}

		// final formatting
		foreach ( $num_sub2 as $key => $sub ) {
			foreach ( $sub as $k => $s ) {
				$unique_sub2[ $key ][ $k ] = count( $s );
			}
			// sort with most subjects at the top
			array_multisort( $unique_sub2[ $key ], SORT_DESC );
		}

		return $unique_sub2;

	}

}
