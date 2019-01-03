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
 * A Model directly manages the data, logic and rules of the application.
 * Get and store data from the book Reviews from the OpenTextbook Project
 *
 * It avoids an API call by caching filtered responses
 */

namespace BCcampus\OpenTextBooks\Models;

use BCcampus\OpenTextBooks\Config;
use BCcampus\OpenTextBooks\Polymorphism;
use org\jsonrpcphp\JsonRPCClient;

class OtbReviews extends Polymorphism\DataAbstract {
	/**
	 * Comes in as arguments, saved as options
	 *
	 * @var array
	 */
	private $options = [ 'survey_id' => '338956' ];

	/**
	 * One of the limitations of the LimeSurvey API
	 * is that it serves content in CSV format
	 *
	 * @var
	 */
	private $responsesCSV;

	/**
	 * Contains results stripped of unwanted reviews and
	 * personal information
	 *
	 * @var array
	 */
	private $filteredResponsesArray = [];

	/**
	 * maps a bookid to its human readable name
	 *
	 * @var
	 */
	private $availableBooks;

	/**
	 * where the cache files live
	 *
	 * @var string
	 */
	private $location = 'cache/reviews';

	/**
	 * maps Institution Id to full Institution Name
	 *
	 * @var
	 */
	private $institutionIDs;

	/**
	 * LimeSurvey specific requirement for filtering what you want
	 * what you really, really want
	 *
	 * @var array
	 */
	private $questionPropOptions = [ 'answeroptions' ];

	/**
	 * Instance connection object, to interact with the API
	 *
	 * @var LimeSurveyApi
	 */
	private $limeSurveyApi;

	/**
	 * Once credentials are sent, a token is returned to maintain
	 * state-full-ness
	 *
	 * @var
	 */
	private $sessionKey;

	/**
	 * the id's of responses to be hidden from view
	 *
	 * @var array
	 */
	private $omit_responses = [ '108', '62', '105', '104', '195' ];

	/**
	 * CSV file needs to be created and then destroyed.
	 * This holds the value of the path to the file
	 * so that it can be destroyed.
	 *
	 * @var
	 */
	private $tmp_file;

	/**
	 * OtbReviews constructor.
	 *
	 * @param JsonRPCClient $rpc_client
	 * @param array $args
	 */
	public function __construct( JsonRPCClient $rpc_client, array $args ) {

		$this->limeSurveyApi = $rpc_client;

		// save options to an instance variable.
		// let the class variable override arguments given to it
		// since class won't work without survey_id being set to 338956
		$this->options = array_merge( $args, $this->options );

		try {
			$this->retrieve();
		} catch ( \Exception $exc ) {
			error_log( $exc->getMessage(), 0 );
		}

		if ( isset( $this->sessionKey ) ) {
			$this->limeSurveyApi->release_session_key( $this->sessionKey );
		}
	}


	/**
	 *
	 * @throws \Exception
	 */
	private function retrieve() {
		$this->setResponses();

		try {
			$this->setResponsesArray();
		} catch ( \Exception $exp ) {
			error_log( $exp->getMessage(), 0 );
		}

	}

	/**
	 * get exported responses, set them to an instance variable
	 */
	private function setResponses() {
		$serialize = true;
		$file_name = 'Responses338956';
		$file_type = 'txt';

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		// check if there is a stored version of the results
		if ( $persistent_data ) {
			$this->filteredResponsesArray = $persistent_data->load();

		} else {
			try {
				// request an API response
				$this->apiRequest();
			} catch ( \Exception $exp ) {
				error_log( $exp->getMessage(), 0 );

				// attempt to recover, return data even if it's old
				$persistent_data = $this->getFailSafeStorage( $this->location, $file_name, $file_type, $serialize );
				if ( $persistent_data ) {
					$this->filteredResponsesArray = $persistent_data->load();
				}
			}
		}

		$this->setInstitutionIDs();
		$this->setAvailableReviews();

	}

	/**
	 *
	 * @throws \Exception
	 */
	private function apiRequest() {
		$env              = Config::getInstance()->get();
		$this->sessionKey = $this->limeSurveyApi->get_session_key( $env['limesurvey']['user'], $env['limesurvey']['pswd'] );

		// check for a string, array is returned if uname/pswd not valid
		if ( is_string( $this->sessionKey ) ) {
			$this->responsesCSV = base64_decode( $this->limeSurveyApi->export_responses( $this->sessionKey, $this->options['survey_id'], 'csv', 'en', 'complete' ) );
			$this->saveResponsesTmp();
		} else {
			throw new \Exception( 'Invalid user name or password' );
		}
	}

	/**
	 * Sets the instance variable of responses from the survey
	 *
	 * @throws \Exception if there is no saved file to convert
	 */
	private function setResponsesArray() {
		$serialize = true;
		$file_name = 'Responses338956';
		$file_type = 'txt';

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$this->filteredResponsesArray = $persistent_data->load();
		} else {

			if ( ! isset( $this->tmp_file ) ) {
				throw new \Exception( "<p class='text-error'>There is no <strong>file</strong> to convert to an array.</p>" );
			} else {
				if ( ! isset( $this->sessionKey ) ) {
					$this->apiRequest();
				}
				$this->filteredResponsesArray = $this->csvToAssocArray( $this->tmp_file );
				// important to remove this particular file immediately because it may contain
				// personal information of reviewers
				unlink( $this->tmp_file );
				$this->saveToStorage( $this->location, $file_name, $file_type, $this->filteredResponsesArray, $serialize );

			}
		}
	}


	/**
	 * @param $file
	 *
	 * @return array|bool
	 */
	private function csvToAssocArray( $file ) {
		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return false;
		}

		$header = null;
		$data   = [];
		$handle = fopen( $file, 'r' );

		if ( ( $handle ) !== false ) {

			while ( ( $row = fgetcsv( $handle, 10000, ';' ) ) !== false ) {
				if ( ! $header ) {
					// @see https://stackoverflow.com/questions/29828508/fgetcsv-wrongly-adds-double-quotes-to-first-element-of-first-line
					$row[0] = str_replace( '"', '', $row[0] );
					$header = $row;
				} elseif ( ! empty( $row ) && ( count( $header ) === count( $row ) ) ) {
					$data[] = array_combine( $header, $row );
				}
			}

			fclose( $handle );
		}

		$data = $this->stripUnWanted( $data );

		return $data;
	}

	/**
	 * Take out personal information and unwanted responses
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function stripUnWanted( array $data ) {
		$num = count( $data );

		for ( $i = 0; $i < $num; $i ++ ) {
			// get rid of unwanted responses
			if ( isset( $data[ $i ]['id'] ) && in_array( $data[ $i ]['id'], $this->omit_responses, true ) ) {
				unset( $data[ $i ] );
			}
			// strip personal information
			unset( $data[ $i ]['info4'] );
			unset( $data[ $i ]['info3'] );
			unset( $data[ $i ]['ipaddr'] );
			unset( $data[ $i ]['token'] );
		}

		return $data;
	}

	/**
	 * creates an array that maps BC institutional IDs to their full name, as defined in one
	 * of the survey questions.
	 */
	private function setInstitutionIDs() {
		$serialize = true;
		$file_name = 'InstitutionID';
		$file_type = 'txt';
		//check if there is a stored version
		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$this->institutionIDs = $persistent_data->load();

		} else {
			if ( ! isset( $this->sessionKey ) ) {
				$this->apiRequest();
			}
			$qid    = 735;
			$result = $this->getQuestionProperties( $qid, $this->questionPropOptions );
			$data   = [];

			foreach ( $result['answeroptions'] as $key => $val ) {
				$data[ $key ] = $val['answer'];
			}
			$this->institutionIDs = $data;
			$this->saveToStorage( $this->location, $file_name, $file_type, $data, $serialize );
		}

	}

	/**
	 * creates an array that maps book uuid to their full name, as defined in one
	 * of the survey questions.
	 */
	private function setAvailableReviews() {
		$serialize = true;
		$file_name = 'AvailableBooks';
		$file_type = 'txt';

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$this->availableBooks = $persistent_data->load();

		} else {
			if ( ! isset( $this->sessionKey ) ) {
				$this->apiRequest();
			}
			$qid    = 736;
			$result = $this->getQuestionProperties( $qid, $this->questionPropOptions );
			$data   = [];

			foreach ( $result['answeroptions'] as $key => $val ) {
				$data[ $key ] = $val['answer'];
			}
			$this->availableBooks = $data;
			$this->saveToStorage( $this->location, $file_name, $file_type, $data, $serialize );
		}
	}

	/**
	 * Saves csv responses to a cache file - necessary to facilitate changing the csv to an associative array
	 * @see LimeSurveyAPI::csvToAssocArray( $file )
	 *
	 */
	private function saveResponsesTmp() {
		$serialize      = false;
		$file_type      = 'csv';
		$file_name      = microtime() . mt_rand();
		$this->tmp_file = OTB_DIR . $this->location . '/' . md5( $file_name ) . '.' . $file_type;

		$this->saveToStorage( $this->location, $file_name, $file_type, $this->responsesCSV, $serialize );

	}

	public function getNumReviewsPerBook() {
		$books = [];

		foreach ( $this->filteredResponsesArray as $response ) {
			$prev_val                    = ( isset( $books[ $response['info1'] ] ) ? intval( $books[ $response['info1'] ] ) : 0 );
			$books[ $response['info1'] ] = $prev_val + 1;
		}

		return $books;
	}

	/**
	 * @return array
	 */
	public function getInstitutionIDs() {
		return $this->institutionIDs;
	}

	/**
	 * @return array
	 */
	public function getAvailableReviews() {
		return $this->availableBooks;
	}

	/**
	 * gets different attributes of the questions from the survey
	 *
	 * @param int $qid
	 * @param array $props
	 *
	 * @return array
	 */
	private function getQuestionProperties( $qid, array $props ) {
		return $this->limeSurveyApi->get_question_properties( $this->sessionKey, $qid, $props );
	}

	/**
	 * @return mixed
	 */
	public function getUuid() {
		return substr( $this->options['uuid'], 0, 5 );
	}


	/**
	 * @return array
	 */
	public function getResponses() {
		return $this->filteredResponsesArray;
	}

	/**
	 * Useful when wanting to get more than one Author Name
	 * for one review
	 *
	 * @param array $response
	 *
	 * @return string of Institutions
	 */
	public function getNames( array $response ) {
		$names[] = $response['info2'];

		if ( ! empty( $response['info8']['SQ001'] ) ) {
			$names[] = $response['info8']['SQ001'];
		}
		if ( ! empty( $response['info8']['SQ003'] ) ) {
			$names[] = $response['info8']['SQ003'];
		}
		if ( ! empty( $response['info8']['SQ005'] ) ) {
			$names[] = $response['info8']['SQ005'];
		}

		$names = array_filter( array_unique( $names ) );
		$names = implode( ', ', $names );

		return $names;
	}

	/**
	 * Useful when wanting to get more than one Institution
	 * for one review
	 *
	 * @param array $response
	 *
	 * @return string of Institutions for one record
	 */
	public function getInstitutions( array $response ) {

		$institutions[] = $this->institutionIDs[ $response['info6'] ];

		if ( ! empty( $response['info8']['SQ002'] ) ) {
			$institutions[] = $response['info8']['SQ002'];
		}
		if ( ! empty( $response['info8']['SQ004'] ) ) {
			$institutions[] = $response['info8']['SQ004'];
		}
		if ( ! empty( $response['info8']['SQ006'] ) ) {
			$institutions[] = $response['info8']['SQ006'];
		}

		$institutions = array_filter( array_unique( $institutions ) );
		$institutions = implode( ', ', $institutions );

		return $institutions;
	}


}

