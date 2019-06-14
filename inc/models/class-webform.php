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

namespace BCcampus\OpenTextBooks\Models;

use BCcampus\OpenTextBooks\Config;
use BCcampus\OpenTextBooks\Polymorphism;
use BCcampus\OpenTextBooks\Views;

class WebForm extends Polymorphism\DataAbstract {

	/**
	 * @var $db - PDO object, connection to the database
	 */
	protected $db;

	private $type      = 'txt';
	private $location  = 'cache/webform';
	private $responses = [];
	private $faculty   = [];

	/**
	 *
	 * @var array - list of email domains,
	 * for known private bc institutions.
	 *
	 */
	private $other_bc_domains = [
		'acsenda.com',
		'adler.edu',
		'alexandercollege.ca',
		'artinstitutes.edu',
		'cdicollege.ca',
		'columbiacollege.ca',
		'columbiacollege.bc.ca',
		'corpuschristi.ca',
		'etoncollege.ca',
		'fdu.edu',
		'fraseric.ca',
		'necvancouver.org',
		'pcu-whs.ca',
		'questu.ca',
	];

	private $baseline_date = 1463601425.1563; // may 18, 2016

	/**
	 * as of may 18, 2016
	 * @var array
	 */
	private $baseline_institutions  = [
		'Acsenda School of Management'                           => 0,
		'Adler University'                                       => 0,
		'Alexander College'                                      => 0,
		'Art Institute of Vancouver'                             => 0,
		'Columbia College'                                       => 0,
		'Coquitlam College'                                      => 0,
		'Corpus Christi College'                                 => 0,
		'BC Institute of Technology'                             => 14,
		'Camosun College'                                        => 37,
		'Capilano University'                                    => 9,
		'CDI College'                                            => 4,
		'College of New Caledonia'                               => 1,
		'College of the Rockies'                                 => 23,
		'Douglas College'                                        => 35,
		'Emily Carr University of Art and Design'                => 0,
		'Eton College'                                           => 1,
		'Fairleigh Dickinson University'                         => 0,
		'Fraser International College'                           => 0,
		'Justice Institute of BC'                                => 40,
		'Kwantlen Polytechnic University'                        => 115,
		'Langara College'                                        => 56,
		'Native Education College'                               => 3,
		'Nicola Valley Institute of Technology'                  => 0,
		'North Island College'                                   => 1,
		'Northern Lights College'                                => 0,
		'Coast Mountain College'                                 => 33,
		'Okanagan College'                                       => 0,
		'Pacific Coast University for Workplace Health Sciences' => 0,
		'Quest University'                                       => 0,
		'Royal Roads University'                                 => 6,
		'Selkirk College'                                        => 5,
		'Simon Fraser University'                                => 1,
		'Thompson Rivers University'                             => 24,
		'Trinity Western University'                             => 1,
		'University of British Columbia'                         => 24,
		'University Canada West'                                 => 0,
		'University of Northern British Columbia'                => 2,
		'University of the Fraser Valley'                        => 28,
		'University of Victoria'                                 => 7,
		'Vancouver Community College'                            => 10,
		'Vancouver Island University'                            => 16,
		'Other'                                                  => 6,
	];
	public $baseline_num_adoptions  = 501; // as of nov 3 2017
	public $baseline_num_students   = 14603; // as of nov 3 2017
	public $baseline_savings_100    = 1459800; // as of nov 3 2017
	public $baseline_savings_actual = 1851366; // as of nov 3 2017
	public $baseline_num_inst       = 32; // as of nov 3 2017
	public $baseline_num_faculty    = 149; // as of nov 3 2017

	/**
	 * WebForm constructor.
	 */
	public function __construct() {

		try {
			$this->setResponses();
			$this->setFacultyInfo();

		} catch ( \Exception $e ) {
			new Views\Errors( [ 'all errors as exceptions' => $e->getMessage() ] );
		}

	}

	/**
	 *
	 * @return \PDO
	 */
	private function connection() {
		$db  = '';
		$env = Config::getInstance()->get();
		$dsn = "mysql:host={$env['webform']['db_host']};dbname={$env['webform']['db_name']};port={$env['webform']['db_port']}";
		$usr = $env['webform']['db_user'];
		$pwd = $env['webform']['db_pswd'];

		try {
			$db = new \PDO( $dsn, $usr, $pwd );
		} catch ( \PDOException $e ) {
			new Views\Errors( [ 'Connection failed' => $e->getMessage() ] );
		}

		return $db;
	}

	protected function setResponses() {
		$serialize = true;
		$file_name = 'allresponses';
		$file_type = $this->type;

		$results         = [];
		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$this->responses = $persistent_data->load();

		} else {

			$db = $this->connection();
			if ( ! \is_object( $db ) ) {
				throw new \Exception( '\BCcampus\OpenTextBooks\Models\WebForm\SetResponses could not establish a connection to the database' );
			}

			$stmt = $db->prepare( "SELECT * FROM `wp_9_cf7dbplugin_submits` WHERE `form_name` = 'Adoption of an Open Textbook' AND `submit_time` > ?" );
			$stmt->bindParam( 1, $this->baseline_date, \PDO::PARAM_INT );
			$stmt->execute();
			$res = $stmt->fetch( \PDO::FETCH_ASSOC );

			do {
				if ( ! isset( $results[ $res['submit_time'] ]['submitted'] ) ) {
					$results[ $res['submit_time'] ]['submitted'] = date( 'Y-m-d H:i:s', $res['submit_time'] );
				}

				switch ( $res['field_name'] ) {
					case 'Institutions':
						$results[ $res['submit_time'] ]['institution_name'] = $res['field_value'];
						break;
					case 'open-textbook':
						$results[ $res['submit_time'] ]['textbook_name'] = $res['field_value'];
						break;
					case 'course-name':
						$results[ $res['submit_time'] ]['course_name'] = $res['field_value'];
						break;
					case 'number-of-course':
						$results[ $res['submit_time'] ]['num_courses'] = $res['field_value'];
						break;
					case 'number-of-students':
						$results[ $res['submit_time'] ]['num_students'] = $res['field_value'];
						break;
					case 'semester1':
						$results[ $res['submit_time'] ]['past_semesters'] = $res['field_value'];
						break;
					case 'semester2':
						$results[ $res['submit_time'] ]['future_semesters'] = $res['field_value'];
						break;
					case 'cost-of-textbook':
						$results[ $res['submit_time'] ]['cost_textbook'] = $res['field_value'];
						break;
					case 'your-email':
						$results[ $res['submit_time'] ]['email'] = $res['field_value'];
						break;
					case 'first-name':
						$results[ $res['submit_time'] ]['first_name'] = $res['field_value'];
						break;
					case 'last-name':
						$results[ $res['submit_time'] ]['last_name'] = $res['field_value'];
						break;
					case 'current-term':
						$results[ $res['submit_time'] ]['current_semester'] = $res['field_value'];
						break;
					case 'publish-name':
						$results[ $res['submit_time'] ]['publish_name'] = $res['field_value'];
						break;
				}
			} while ( $res = $stmt->fetch( \PDO::FETCH_ASSOC ) );

			$this->responses = $results;
			$this->filterNonBcAdoptions();
			$this->setCalculatedFields();
			$this->saveToStorage( $this->location, $file_name, $file_type, $this->responses, $serialize );
		}
	}

	/**
	 * need a way to only count adoptions in B.C.
	 */
	private function filterNonBcAdoptions() {

		foreach ( $this->responses as $key => $response ) {
			if ( 0 === strcmp( $response['institution_name'], 'Other' ) ) {
				//get rid of username
				$part = strstr( $response['email'], '@' );
				// return everything but the @
				$domain = substr( $part, 1 );
				if ( ! in_array( $domain, $this->other_bc_domains, true ) ) {
					// @TODO - deal with non bc responses
					// set the variable
					// $this->non_bc_responses[ $key ] = $this->responses[ $key ];
					// delete record, if they don't have a private bc institute domain
					unset( $this->responses[ $key ] );
				}
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function getResponses() {
		return $this->responses;
	}

	/**
	 * @return array
	 */
	public function getStudentSavings() {
		$today = time();
		$total = [
			'100'    => '0',
			'actual' => '0',
		];

		foreach ( $this->responses as $response ) {
			// past
			if ( $response['past_repeat_savings'] >= 1 ) {
				$total['100']    = $total['100'] + $response['past_savings_100'];
				$total['actual'] = $total['actual'] + $response['past_savings_actual'];
			}

			// current
			if ( isset( $response['current_semester'] ) && 0 === strcmp( $response['current_semester'], 'Yes' ) ) {
				$total['100']    = $total['100'] + $response['class_savings_100'];
				$total['actual'] = $total['actual'] + $response['class_savings_actual'];
			};

			// future
			if ( isset( $response['projected_savings_100'] ) ) {
				foreach ( $response['projected_savings_100'] as $key => $val ) {
					// only get those in the past
					if ( $today > $key ) {
						$total['100'] = $total['100'] + $val;
					}
				}
			}
			if ( isset( $response['projected_savings_actual'] ) ) {
				foreach ( $response['projected_savings_actual'] as $key => $val ) {
					// only get those in the past
					if ( $today > $key ) {
						$total['100'] = $total['actual'] + $val;
					}
				}
			}
		}
		// add baseline
		$total['100']    = $total['100'] + $this->baseline_savings_100;
		$total['actual'] = $total['actual'] + $this->baseline_savings_actual;

		return $total;

	}

	/**
	 * Actual Savings = actual cost of book x number of students x number of courses
	 * Savings 100 = $100 x number of students x number of courses
	 *
	 */
	private function setCalculatedFields() {
		foreach ( $this->responses as $key => $response ) {
			$this->responses[ $key ]['class_savings_actual'] = intval( $response['num_courses'] ) * ( intval( $response['cost_textbook'] ) * intval( $response['num_students'] ) );
			$this->responses[ $key ]['class_savings_100']    = intval( $response['num_courses'] ) * ( 100 * intval( $response['num_students'] ) );
			if ( ! empty( $response['future_semesters'] ) ) {
				$future_repeat                                       = $this->getNumSemesters( $response['future_semesters'] );
				$this->responses[ $key ]['future_repeat_savings']    = $future_repeat;
				$this->responses[ $key ]['future_savings_actual']    = $future_repeat * $this->responses[ $key ]['class_savings_actual'];
				$this->responses[ $key ]['future_savings_100']       = $future_repeat * $this->responses[ $key ]['class_savings_100'];
				$this->responses[ $key ]['projected_savings_actual'] = $this->setFutureSavings( $future_repeat, $this->responses[ $key ]['future_savings_actual'], $this->responses[ $key ]['submitted'] );
				$this->responses[ $key ]['projected_savings_100']    = $this->setFutureSavings( $future_repeat, $this->responses[ $key ]['future_savings_100'], $this->responses[ $key ]['submitted'] );
				$this->responses[ $key ]['projected_adoptions']      = $this->setProjections( $future_repeat, $this->responses[ $key ]['num_courses'], $this->responses[ $key ]['submitted'] );
				$this->responses[ $key ]['projected_students']       = $this->setProjections( $future_repeat, ( $this->responses[ $key ]['num_students'] * $this->responses[ $key ]['num_courses'] ), $this->responses[ $key ]['submitted'] );

			} else {
				$this->responses[ $key ]['future_repeat_savings'] = 0;
			}
			if ( ! empty( $response['past_semesters'] ) ) {
				$past_repeat                                    = $this->getNumSemesters( $response['past_semesters'] );
				$this->responses[ $key ]['past_repeat_savings'] = $past_repeat;
				$this->responses[ $key ]['past_savings_actual'] = $past_repeat * $this->responses[ $key ]['class_savings_actual'];
				$this->responses[ $key ]['past_savings_100']    = $past_repeat * $this->responses[ $key ]['class_savings_100'];
				$this->responses[ $key ]['past_adoptions']      = $past_repeat * $this->responses[ $key ]['num_courses'];
				$this->responses[ $key ]['past_students']       = $past_repeat * ( $this->responses[ $key ]['num_students'] * $this->responses[ $key ]['num_courses'] );
			} else {
				$this->responses[ $key ]['past_repeat_savings'] = 0;
			}
			// if a request has been made to not publish their name
			if ( 0 === strcmp( $this->responses[ $key ]['publish_name'], 'No' ) ) {
				unset( $this->responses[ $key ]['first_name'] );
				unset( $this->responses[ $key ]['last_name'] );
			}
		}
	}

	/**
	 * @param $repeat
	 * @param $savings
	 * @param $submit_date
	 *
	 * @return array
	 */
	private function setFutureSavings( $repeat, $savings, $submit_date ) {
		$result       = [];
		$savings_unit = intval( $savings ) / intval( $repeat );
		$interval     = 4;

		for ( $i = 0; $i < $repeat; $i ++ ) {
			$t            = strtotime( $submit_date . " +{$interval} months" );
			$result[ $t ] = $savings_unit;
			$interval     = $interval + 4;
		}

		return $result;
	}

	/**
	 * @param $repeat
	 * @param $num
	 * @param $submit_date
	 *
	 * @return array
	 */
	private function setProjections( $repeat, $num, $submit_date ) {
		$result   = [];
		$interval = 4;

		for ( $i = 0; $i < $repeat; $i ++ ) {
			$t            = strtotime( $submit_date . " +{$interval} months" );
			$result[ $t ] = $num;
			$interval     = $interval + 4;
		}

		return $result;
	}

	private function getNumSemesters( $csv ) {
		$result = explode( ',', $csv );

		return count( $result );
	}

	/**
	 * Each adoption refers to a course section within a specific term and year
	 * for which an open textbook has replaced a a primary textbook or
	 * educational resource that must be purchased.
	 *
	 * for project
	 */
	public function getTotalAdoptions() {
		$total = 0;
		$today = time();

		foreach ( $this->responses as $response ) {
			// past
			if ( isset( $response['past_adoptions'] ) ) {
				$total = $total + $response['past_adoptions'];
			}

			// present
			if ( isset( $response['current_semester'] ) && 0 === strcmp( $response['current_semester'], 'Yes' ) ) {
				$total = $total + intval( $response['num_courses'] );
			}

			// future
			if ( isset( $response['projected_adoptions'] ) ) {
				foreach ( $response['projected_adoptions'] as $key => $adoption ) {
					// only get those in the past
					if ( $today > $key ) {
						$total = $total + intval( $adoption );
					}
				}
			}
		}

		return $total + $this->baseline_num_adoptions;
	}

	/**
	 * @return int
	 */
	public function getNumInstitutions() {
		$total = 0;
		$tmp   = $this->baseline_institutions;

		// count the new ones only
		foreach ( $this->responses as $response ) {
			if ( ! empty( $response['institution_name'] ) && array_key_exists( $response['institution_name'], $tmp ) ) {
				$tmp[ $response['institution_name'] ] = $tmp[ $response['institution_name'] ] + 1;
			}
		}

		// each institution counts as one, except 'other'
		// where a specific institution is not specified
		foreach ( $tmp as $inst ) {
			if ( $inst > 0 && ! $inst['Other'] ) {
				$total ++;
			}
		}

		return $total + $tmp['Other'];
	}

	/**
	 *
	 * @return int
	 */
	public function getNumStudents() {
		$total = 0;
		$today = time();

		foreach ( $this->responses as $response ) {
			// past
			if ( isset( $response['past_students'] ) ) {
				$total = $total + $response['past_students'];
			}

			// present
			if ( isset( $response['current_semester'] ) && 0 === strcmp( $response['current_semester'], 'Yes' ) ) {
				$total = $total + $response['num_students'];
			}

			// future
			if ( isset( $response['projected_students'] ) ) {
				foreach ( $response['projected_students'] as $key => $student ) {
					// only get those in the past
					if ( $today > $key ) {
						$total = $total + $student;
					}
				}
			}
		}

		return $total + $this->baseline_num_students;

	}

	/**
	 * @return int
	 */
	public function getNumFaculty() {
		$faculty = [];

		foreach ( $this->responses as $response ) {
			$faculty[ $response['email'] ] = 1;
		}

		$total = count( $faculty );

		return $total + $this->baseline_num_faculty;
	}

	/**
	 * @param $num
	 *
	 * @return array
	 */
	public function getTopInstitutions( $num ) {
		$num   = intval( $num );
		$tmp   = $this->baseline_institutions;
		$limit = ( $num > 0 && $num < count( $tmp ) ) ? $num : 4;
		$top   = [];

		// count the new ones only
		foreach ( $this->responses as $response ) {
			if ( ! empty( $response['institution_name'] ) && array_key_exists( $response['institution_name'], $tmp ) ) {
				$tmp[ $response['institution_name'] ] = $tmp[ $response['institution_name'] ] + 1;
			}
		}

		array_multisort( $tmp, SORT_DESC, SORT_NUMERIC );
		$new = array_keys( $tmp );

		for ( $i = 0; $i < $limit; $i ++ ) {
			$top[] = array_shift( $new );
		}

		return $top;
	}

	/**
	 * @throws \Exception
	 */
	private function setFacultyInfo() {
		$serialize       = true;
		$file_name       = 'facultyinfo';
		$file_type       = $this->type;
		$results         = [];
		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$this->faculty = $persistent_data->load();

		} else {

			$db = $this->connection();
			if ( ! \is_object( $db ) ) {
				throw new \Exception( '\BCcampus\OpenTextBooks\Models\WebForm\SetFacultyInfo could not establish a connection to the database' );
			}

			$stmt = $db->prepare( "SELECT `field_name`,`field_value`,`submit_time` FROM `wp_9_cf7dbplugin_submits` WHERE `form_name` = 'Adoption of an Open Textbook'" );
			$stmt->execute();
			$res = $stmt->fetch( \PDO::FETCH_ASSOC );

			do {

				switch ( $res['field_name'] ) {
					case 'first-name':
						$results[ $res['submit_time'] ]['first_name'] = $res['field_value'];
						break;
					case 'last-name':
						$results[ $res['submit_time'] ]['last_name'] = $res['field_value'];
						break;
					case 'Institutions':
						$results[ $res['submit_time'] ]['institution_name'] = $res['field_value'];
						break;
					case 'publish-name':
						$results[ $res['submit_time'] ]['publish_name'] = $res['field_value'];
						break;
					case 'other-institution':
						$results[ $res['submit_time'] ]['other_inst'] = $res['field_value'];
						break;

				}
			} while ( $res = $stmt->fetch( \PDO::FETCH_ASSOC ) );

			$this->faculty = $results;
			$this->filterFacultyInfo();
			$this->saveToStorage( $this->location, $file_name, $file_type, $this->faculty, $serialize );

		}
	}

	/**
	 *
	 */
	private function filterFacultyInfo() {
		foreach ( $this->faculty as $key => $info ) {

			if ( 0 === strcmp( $info['publish_name'], 'No' ) ) {
				unset( $this->faculty[ $key ] );
			}
		}

	}

	/**
	 * @return array
	 */
	public function getFacultyInfo() {
		$results = [];
		$grouped = [];

		foreach ( $this->faculty as $member ) {
			if ( ! empty( $member['institution_name'] ) ) {
				$results[ $member['institution_name'] ][] = $member['first_name'] . ' ' . $member['last_name'];
			} elseif ( ! empty( $member['other_inst'] ) ) {
				$results[ $member['other_inst'] ][] = $member['first_name'] . ' ' . $member['last_name'];
			}
		}

		// get rid of multiple authors
		foreach ( $results as $key => $unique ) {
			$tmp = array_unique( $unique );
			sort( $tmp );
			$grouped[ $key ] = $tmp;
		}
		array_multisort( $grouped, SORT_DESC );

		return $grouped;
	}
}
