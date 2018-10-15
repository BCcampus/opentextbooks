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

namespace BCcampus\OpenTextBooks\Controllers\Catalogue;

use BCcampus\OpenTextBooks\Config;
use BCcampus\OpenTextBooks\Models;
use BCcampus\OpenTextBooks\Views;
use org\jsonrpcphp;

class Otb {

	/**
	 * Needs at least this, or nothing works
	 * Some vars need to be defined to avoid warnings.
	 *
	 * @var array
	 */
	protected $defaultArgs = [
		'type_of'        => '',
		'collectionUuid' => '',
		'start'          => '',
		'view'           => '',
		'search'         => '',
		'subject'        => '',
	];

	/**
	 * @var array
	 */
	private $args = [];

	/**
	 * @var array
	 */
	private $expected = [
		'books',
		'book_stats',
		'subject_stats',
		'classify',
	];

	/**
	 * OtbController constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {

		// sanity check
		if ( ! is_array( $args ) ) {
			// TODO: add proper error handling
			new Views\Errors( [ 'msg' => 'Sorry, this does not pass the smell test' ] );
		}

		/**
		 * Control the view returned by passing:
		 *
		 * ?uuid=c6d0e9bd-ba6b-4548-82d6-afbd0f166b65
		 * ?subject=Biology
		 * ?subject=Biology&search=micro
		 * ?search=something
		 * ?search=something&keyword=true
		 * ?search=something&contributor=true
		 * ?lists=ancillary|adopted|reviews|accessible|titles
		 */
		$args_get = [
			// Strips characters that have a numerical value >127.
			'uuid'        => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			],
			// Strips characters that have a numerical value >127.
			'subject'     => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			],
			// looking for boolean value, string true/false
			'keyword'     => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			],
			// looking for boolean value, string true/false
			'contributor' => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			],
			// Strips characters that have a numerical value >127.
			'lists'       => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			],
			// Remove all characters except digits, plus and minus sign.
			'start'       => [
				'filter' => FILTER_SANITIZE_NUMBER_INT,
			],
			// Strips characters that have a numerical value >127.
			'search'      => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			],
			// Strips characters that have a numerical value >127.
			'type_of'     => [
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_FLAG_STRIP_HIGH,
			],
		];

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
			return new Views\Errors( [ 'msg' => 'Whoops! Looks like you need to pass an expected parameter. Love ya!' ] );
		}
	}

	/**
	 *
	 */
	protected function decider() {

		$rest_api = new Models\Api\Equella();
		$data     = new Models\OtbBooks( $rest_api, $this->args );

		if ( $this->args['type_of'] === 'books' ) {
			$view           = new Views\Books( $data );
			$expected_lists = [
				'adopted',
				'ancillary',
				'reviewed',
				'accessible',
				'titles',
			];

			// for lists of books matching certain criteria
			if ( ! empty( $this->args['lists'] ) && in_array( $this->args['lists'], $expected_lists ) ) {

				switch ( $this->args['lists'] ) {
					case 'titles':
						$env        = Config::getInstance()->get();
						$rpc_client = new jsonrpcphp\JsonRPCClient( $env['limesurvey']['url'] );
						$reviews    = new Models\OtbReviews( $rpc_client, $this->args );

						$view->displayContactFormTitles( $reviews->getNumReviewsPerBook() );
						break;

					default:
						$view->displayTitlesByType( $this->args['lists'] );
				}
			} // for one book
			elseif ( ! empty( $this->args['uuid'] ) ) {
				$view->displayOneTextbook();
			} else {
				$view->displayBooks( $this->args['start'] );
			}
		}

		if ( $this->args['type_of'] === 'book_stats' ) {
			$view = new Views\StatsBooks( $data );

			switch ( $this->args['view'] ) {

				case 'single':
					if ( ! empty( $this->args['uuid'] ) ) {
						$view->displayStatsUuid();
					} else {
						new Views\Errors( [ 'msg' => 'sorry, try passing a uuid parameter. We love you.' ] );
					}
					break;
				default:
					$view->displayStatsTitles();
			}
		}

		if ( $this->args['type_of'] === 'subject_stats' ) {
			$view = new Views\StatsBooks( $data );
			$view->displaySubjectStats();
		}

		if ( $this->args['type_of'] === 'classify' ) {
			$preprocess = new Models\Recommend\EquellaTrainingData( $data );
			/*
			|--------------------------------------------------------------------------
			| Data pre-processing
			|--------------------------------------------------------------------------
			|
			| @TODO - move to storage instead of processing everytime
			|
			|
			*/
			$preprocess->prepareData();

			$equella_training_targets = $preprocess->getTargets( $preprocess->getTraining() );
			$equella_training_samples = $preprocess->getDataArray( $preprocess->getTraining() );

			//	$equella_training_samples  = $training_data->getBigram( $training_samples );
			//	$equella_reporting_samples = $training_data->getBigram( $reporting_samples );

			/*
			|--------------------------------------------------------------------------
			| Train
			|--------------------------------------------------------------------------
			|
			| @TODO - shouldn't need to train every time.
			|
			|
			*/
			$predict    = new Models\Recommend\Predicting( $equella_training_samples, $equella_training_targets );
			$classifier = $predict->trainTheClassifier();
			$view       = new Views\Recommender();

			switch ( $this->args['view'] ) {
				case 'equella-report':
					$preprocess->separateReportDataFromTraining();
					$equella_reporting_targets = $preprocess->getTargets( $preprocess->getReporting() );
					$equella_reporting_samples = $preprocess->getDataArray( $preprocess->getReporting() );
					$predicted                 = $classifier->predict( $equella_reporting_samples );
					$report                    = $predict->runReport( $equella_reporting_targets, $predicted );
					$view->displayReport( $report );
					break;
				case 'equella-probability':
					$preprocess->separateReportDataFromTraining();
					$equella_reporting_samples = $preprocess->getDataArray( $preprocess->getReporting() );
					$predict->runProbability( $equella_reporting_samples );
					break;
				case 'remote-opentextbc':
					// pre-process the otb samples
					$otb_samples           = $preprocess->getPbJson( OTB_DIR . 'inc/models/recommend/data/otb.json' );
					$otb_reporting_targets = [
						'Business and Management',
						'Business and Management',
						'Liberal Arts and Humanities',
						'Social Sciences',
						'Sciences',
						'Social Sciences',
						'Business and Management',
						'Sciences',
						'Sciences',
						'Liberal Arts and Humanities',
					];
					$predicted             = $classifier->predict( $otb_samples );
					$report                = $predict->runReport( $otb_reporting_targets, $predicted );
					$view->displayReport( $report );
					break;
				case 'remote-pressbooks':
					// pre-process the otb samples
					$pb_samples           = $preprocess->getPbJson( OTB_DIR . 'inc/models/recommend/data/pb.json' );
					$pb_reporting_targets = [
						'Business and Management',
						'Recreation, Tourism, Hospitality and Service',
						'Support Resources',
						'Sciences',
						'Sciences',
						'Sciences',
						'Sciences',
						'Social Sciences',
						'Social Sciences',
						'Health Related',
					];
					$predicted            = $classifier->predict( $pb_samples );
					$report               = $predict->runReport( $pb_reporting_targets, $predicted );
					$view->displayReport( $report );
					break;
				default:
					echo 'hello';
			}


		}

		$c = new Models\Storage\CleanUp();
		$c->maybeRun( 'catalogue', 'txt' );

	}

}
