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

namespace BCcampus\OpenTextBooks\Controllers\Analytics;

use BCcampus\OpenTextBooks\Models;
use BCcampus\OpenTextBooks\Views;
use VisualAppeal\Piwik;

class PiwikController {

	/**
	 * Needs at least this, or nothing works
	 *
	 * @var array
	 */
	private $defaultArgs = array(
		'type_of' => 'site',
		'range'   => 4, // number of months
		'site_id' => 12,
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

			// Strips characters that have a numerical value > 127.
			'type_of' => array(
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			),
			'site_id' => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
			),
			'range'   => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
			),
			'uuid'    => array(
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

		// check for minimum requirements, otherwise nothing works
		foreach ( $this->defaultArgs as $required => $val ) {
			if ( ! array_key_exists( $required, $this->args ) ) {
				// try and populate it with defaults
				$this->args[ $required ] = $val;
			}
		}

		// configure date range
		$time_string               = "-{$this->args['range']} months";
		$t                         = strtotime( $time_string );
		$this->args['range_start'] = date( 'Y-m-d', $t );
		$this->args['range_end']   = date( 'Y-m-d', time() );

		$this->decider();

	}

	private function decider() {
		$env      = include( OTB_DIR . '.env.php' );
		$rest_api = new Piwik( $env['piwik']['SITE_URL'], $env['piwik']['SITE_TOKEN'], 12, Piwik::FORMAT_JSON );
		$rest_api->setPeriod( Piwik::PERIOD_RANGE );
		$rest_api->setRange( $this->args['range_start'], $this->args['range_end'] );
		$data = new Models\Matomo( $rest_api, $this->args );
		$view = new Views\Analytics( $data );

		switch ( $this->args['site_id'] ) {
			// open downloads
			case 12:
				if ( ! empty( $this->args['uuid'] ) ) {
					$books_rest_api = new Models\EquellaApi();
					$books_data     = new Models\OtbBooks( $books_rest_api, [
						'type_of' => 'books',
						'uuid'    => $this->args['uuid'],
					] );
					$d              = $books_data->getResponses();
					$view->displayOpenSingleBook( $this->args['range_start'], $d );
				} else {
					// need to grab the number of books in the collection
					$books_rest_api = new Models\EquellaApi();
					$books_data     = new Models\OtbBooks( $books_rest_api, [ 'type_of' => 'books' ] );
					$num_of_books   = count( $books_data->getResponses() );
					$view->displayOpenSummary( $num_of_books );
				}

				break;
			// opentext summary of all sites
			case 8:
				// need to grab the number of books in the collection
				$books_rest_api = new Models\EquellaApi();
				$books_data     = new Models\OtbBooks( $books_rest_api, [ 'type_of' => 'books' ] );
				$num_of_books   = count( $books_data->getResponses() );

				// likely adoptions by visit
				if ( 0 === strcmp( $this->args['type_of'], 'adoptions-v' ) ) {
					// need to grab the number of books in the collection
					$view->displayAdoptionsByVisits( $num_of_books );

				} elseif ( 0 === strcmp( $this->args['type_of'], 'adoptions-d' ) ) {
					$view->displayAdoptionsByDownloads( $num_of_books );

				} else {
					// need to grab the number of books in the collection
					$view->displayOpenTextSummary( $num_of_books );
				}
				break;
			// single site stats
			default:
				$view->displaySingleSite( $this->args['range_start'] );

		}

		$c = new Models\Storage\CleanUp();
		$c->maybeRun( 'analytics', 'txt' );

	}
}
