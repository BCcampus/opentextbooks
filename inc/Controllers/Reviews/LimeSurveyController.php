<?php

/**
 * Controller accepts input and converts it to commands for the model or view
 * LimeSurvey provides and API with a JSON-RPC web service which we initially
 * want to exploit to display book reviews.
 * http://manual.limesurvey.org/RemoteControl_2_API
 *
 * Permissions for the user 'limeSurveyAPIClient' to individual surveys in LimeSurvey
 * are set in the application survey.bccampus.ca
 *
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

namespace BCcampus\OpenTextBooks\Controllers\Reviews;

use BCcampus\OpenTextBooks\Controllers;
use BCcampus\OpenTextBooks\Models;
use BCcampus\OpenTextBooks\Views;
use BCcampus\OpenTextBooks\Config;
use org\jsonrpcphp;

ini_set( 'auto_detect_line_endings', 1 );

class LimeSurveyController {


	/**
	 * Needs at least this, or nothing works
	 *
	 * @var array
	 */
	protected $defaultArgs = array(
		'type_of' => '',
	);

	/**
	 * @var array
	 */
	private $expected = [ 'reviews', 'review_stats' ];

	/**
	 * @var array
	 */
	protected $args = array();

	/**
	 * Filter user input, limited to the variables we control
	 * Evaluate what model/view to invoke based on arguments passed to it
	 *
	 * @param $args
	 *
	 * @throws \Exception
	 */
	public function __construct( $args ) {

		// sanity check
		if ( ! is_array( $args ) ) {
			// TODO: add proper error handling
			new Views\Errors( [ 'msg' => 'Sorry, this does not pass the smell test' ] );
		}

		/**
		 * Control this by passing:
		 * ?reviews=books&uuid=c6d0e9bd-ba6b-4548-82d6-afbd0f166b65
		 * ?reviews=stats
		 */
		$args_get = array(
			'uuid'    => array(
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			),
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

		if ( in_array( $this->args['type_of'], $this->expected ) ) {
			$this->decider();
		} else {
			return '';
		}
	}


	/**
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function decider() {
		$env        = Config::getInstance()->get();
		$rpc_client = new jsonrpcphp\JsonRPCClient( $env['limesurvey']['url'] );
		$data       = new Models\OtbReviews( $rpc_client, $this->args );

		switch ( $this->args['type_of'] ) {

			case 'reviews':
				// check for uuid
				if ( isset( $this->args['uuid'] ) ) {
					$view = new Views\BookReviews( $data );
					$view->displayReviews();
				} else {
					new Views\Errors( [ 'msg' => 'Sorry, book reviews need a UUID parameter to be set' ] );
				}

				break;

			case 'review_stats':
				$view = new Views\StatsBookReviews( $data );
				$view->displayReports();

				break;

			default:
				//new Views\Errors(['msg' => 'Sorry, there is no view associated with that parameter']);
		}

		$c = new Models\Storage\CleanUp();
		//      $c->maybeRun( 'reviews', 'txt' );
		$c->maybeRun( 'reviews', 'csv' );
	}

}


