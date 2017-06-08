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

class DspaceBooks extends Polymorphism\DataAbstract {
	/**
	 * @var
	 */
	private $data;

	protected $args = array();

	protected $size;
	private $location = 'cache/catalogue';
	private $type = 'txt';
	protected $api;

	/**
	 * DspaceBooks constructor.
	 *
	 * @param Polymorphism\RestInterface $api
	 * @param $args
	 */
	public function __construct( Polymorphism\RestInterface $api, $args ) {
		// TODO: Implement more robust constructor
		$this->data = $api->retrieve( $args );
		$this->args = $args;
		$this->size = count( $this->getResponses() );
		$this->api  = $api;

		try {
			$this->setResponses();
		} catch ( \Exception $exp ) {
			error_log( $exp->getMessage() );
		}


	}

	/**
	 * @return mixed
	 */
	public function getResponses() {
		$data = ( isset( $this->data['items'] ) ) ? $this->data['items'] : $this->data;

		return $data;
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
		if ( empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) && empty( $this->args['search'] ) ) {
			$name = $this->args['collectionUuid'];
		} // individual record
		elseif ( ! empty( $this->args['uuid'] ) ) {
			$name = $this->args['collectionUuid'] . $this->args['uuid'];
		} // search term
		elseif ( empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) && ! empty( $this->args['search'] ) ) {
			$name = $this->args['collectionUuid'] . $this->args['search'];
		} // subject area
		elseif ( ! empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) && empty( $this->args['search'] ) ) {
			$name = $this->args['collectionUuid'] . $this->args['subject'];
		} // subject area and search term
		elseif ( ! empty( $this->args['subject'] ) && ! empty( $this->args['search'] ) ) {
			$name = $this->args['collectionUuid'] . $this->args['subject'] . $this->args['search'];
		}

		return $name;
	}

	/**
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * @return int
	 */
	public function getSize() {
		return $this->size;
	}
}