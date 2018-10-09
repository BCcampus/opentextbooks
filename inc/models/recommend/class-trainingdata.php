<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright Brad Payne <https://bradpayne.ca>
 * Date: 2018-10-08
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) Brad Payne
 */

namespace BCcampus\Opentextbooks\Models\Recommend;

use BCcampus\OpenTextBooks\Models\OtbBooks;

class TrainingData {

	private $data;
	private $training_data;
	private $report_data;
	private $subjects;

	public function __construct( OtbBooks $books ) {
		if ( is_object( $books ) ) {
			$this->data = $books->getResponses();
		}
	}

	/**
	 * returns a bag-of-words based on title, subject(keywords) and description
	 *
	 * @return array
	 */
	public function prepareData() {

		foreach ( $this->data as $book ) {
			$xml  = new \SimpleXMLElement( $book['metadata'] );
			$sub1 = $xml->xpath( 'item/subject_class_level1' );
			$sub2 = $xml->xpath( 'item/subject_class_level2' );

			foreach ( $sub2 as $obj ) {
				$tmp = $obj->__toString();
			}
			$subjects[ $sub1[0]->__toString() ][ $tmp ][] = [
				'title'       => $book['name'],
				'description' => $book['description'],
			];

		}

		// arrange from most books per subject to least
		array_multisort( $subjects, SORT_DESC );

		$this->subjects = $subjects;
	}

	/**
	 * @return array
	 */
	public function setTrainingAndReportData() {
		$classification_report = [];
		if ( empty ( $this->subjects ) ) {
			return $classification_report;
		}

		foreach ( $this->subjects as $k1 => $sub ) {
			foreach ( $sub as $k2 => $s ) {
				if ( count( $s ) > 2 ) {
					$classification_report[ $k1 ][ $k2 ][] = $s[0];
					// remove data from training sample
					unset( $this->subjects[ $k1 ][ $k2 ][0] );
				}
			}
		}
		$this->training_data = $this->subjects;
		$this->report_data   = $classification_report;

	}

	public function getTraining() {
		return $this->training_data;
	}

	public function getReporting() {
		return $this->report_data;
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	public function getDataArray( $data ) {
		$samples = [];
		foreach ( $data as $sub1 => $sub2 ) {
			foreach ( $sub2 as $class_name => $values ) {
				foreach ( $values as $v ) {
					$samples[] = sprintf( '%s %s %s %s', $sub1, $class_name, $v['title'], $v['description'] );
				}
			}
		}

		return $samples;
	}


	/**
	 * @param $data
	 *
	 * @return array
	 */
	public function getTargets( $data ) {
		$targets = [];
		foreach ( $data as $sub1 => $sub2 ) {
			foreach ( $sub2 as $class_name => $values ) {
				foreach ( $values as $v ) {
					$targets[] = $class_name;
				}
			}
		}

		return $targets;
	}
}
