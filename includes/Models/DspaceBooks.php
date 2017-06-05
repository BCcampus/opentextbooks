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

	}

	/**
	 * @return mixed
	 */
	public function getResponses() {
		$data = ( isset( $this->data['items'] ) ) ? $this->data['items'] : $this->data;

		return $data;
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