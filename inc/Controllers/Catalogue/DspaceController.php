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

use BCcampus\OpenTextBooks\Views;
use BCcampus\OpenTextBooks\Models;
use BCcampus\Utility;

class DspaceController {

	/**
	 * @var array
	 */
	protected $defaultArgs = array(
		'collectionUuid' => '',
		'uuid'           => '',
		'search'         => '',
		'subject'        => '',
		'start'          => 0,
		'limit'          => '',
	);

	/**
	 * @var array
	 */
	private $args = array();

	/**
	 * @var array
	 */
	private $expected = [ 'books' ];

	/**
	 * DspaceController constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		// sanity check
		if ( ! is_array( $args ) ) {
			// TODO: add proper error handling
			new Views\Errors( [ 'msg' => 'Sorry, this does not pass the smell test' ] );
		}

		$args_get = array(
			// Strips characters that have a numerical value >127.
			'uuid'           => array(
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			),
			// Strips characters that have a numerical value >127.
			'collectionUuid' => array(
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			),
			// Strips characters that have a numerical value >127.
			'subject'        => array(
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			),
			// Remove all characters except digits, plus and minus sign.
			'start'          => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
			),
			// Remove all characters except digits, plus and minus sign.
			'limit'          => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
			),
			// Strips characters that have a numerical value >127.
			'search'         => array(
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			),
		);

		// filter get input, delete empty values
		$get = ( false !== filter_input_array( INPUT_GET, $args_get, false ) ) ? filter_input_array( INPUT_GET, $args_get, false ) : '';

		// let the filtered get variables override the default arguments
		if ( is_array( $get ) ) {
			// filtered get overrides default
			$this->args = array_merge( $this->defaultArgs, $get );
			// programmer arguments override everything
			$this->args = array_merge( $this->args, $args );

		} else {
			// programmers can override everything if it's hardcoded
			$this->args = array_merge( $this->defaultArgs, $args );
		}

		if ( in_array( $this->args['type_of'], $this->expected ) ) {
			$this->formatArgs( $this->args );
			$this->decider();
		} else {
			return new Views\Errors( [ 'msg' => 'Whoops! Looks like you need to pass an expected parameter. Love ya!' ] );
		}
	}

	/**
	 * Ensures the arguments are formatted correctly before passing them
	 * to a Model class
	 *
	 * @param $args
	 */
	private function formatArgs( $args ) {
		if ( ! is_array( $args ) ) {
			return;
		}

		$env = \BCcampus\OpenTextBooks\Config::getInstance()->get();
		// allow for collection to be overridden with a passed argument
		// otherwise default collection uuid should be set in .env.php
		if ( empty( $args['collectionUuid'] ) ) {
			$args['collectionUuid'] = $env['dspace']['uuid'];
		}
		// needs to be 'Arts+and+Culture'
		if ( ! empty( $this->args['subject'] ) ) {
			$this->args['subject'] = Utility\url_encode( $this->args['subject'] );
		}
		// multiple values need to be presented in an array
		if ( ! empty( $this->args['search'] ) ) {
			$this->args['search'] = explode( ' ', $this->args['search'] );
		}
		if ( ! empty( $this->args['start'] ) ) {
			$this->args['start'] = intval( $this->args['start'] );
		}
		if ( ! empty( $this->args['limit'] ) ) {
			$this->args['limit'] = intval( $this->args['limit'] );
		}
	}

	/**
	 * Controls which views get returned
	 *
	 */
	protected function decider() {
		$rest_api = new Models\DspaceApi();
		$data     = new Models\DspaceBooks( $rest_api, $this->args );

		if ( $this->args['type_of'] == 'books' ) {
			$view = new Views\DspaceBooks( $data );

			if ( ! empty( $this->args['uuid'] ) ) {
				$view->displayOneTextbook();
			} else {
				$view->displayBooks( $this->args['start'] );
			}
		}
	}

}
