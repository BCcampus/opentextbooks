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

namespace BCcampus\OpenTextBooks\Controllers\Catalogue;


class DspaceController {
	/**
	 * @var array
	 */
	protected $defaultArgs = array(
		'collectionUuid' => '',
		'uuid'           => '',
		// TODO: confirm more default args
	);

	/**
	 * @var array
	 */
	private $args = array();

	/**
	 * DspaceController constructor.
	 */
	public function __construct( $args ) {
		// TODO: Implement constructor
	}

	/**
	 *
	 */
	protected function decider() {
		// TODO: Implement decider();
	}

}