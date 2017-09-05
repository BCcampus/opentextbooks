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
namespace BCcampus\OpenTextBooks\Controllers\Webform;

use BCcampus\OpenTextBooks\Models;
use BCcampus\OpenTextBooks\Views;

class AdoptionController {
	/**
	 * Needs at least this, or nothing works
	 *
	 * @var array
	 */
	private $defaultArgs = array(
		'type_of' => 'webform_stats',
	);

	/**
	 * @var array
	 */
	private $args = array();

	/**
	 * RedirectController constructor.
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
			'type_of' => array(
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

		$this->decider();

	}

	/**
	 *
	 */
	private function decider() {

		$data = new Models\WebForm();
		$view = new Views\Webform( $data );

		switch ( $this->args['type_of'] ) {
			case 'webform_stats':
				$view->displayOtbStats();
				//$view->displayFacultyNames();
				break;
			case 'webform_summary':
				$view->displaySummaryStats();
				break;
			case 'rest_stats':
				$view->restSummaryStats();
				break;
			default:
				new Views\Errors( [ 'msg' => 'Sorry, not a valid argument' ] );
		}

	}


}
