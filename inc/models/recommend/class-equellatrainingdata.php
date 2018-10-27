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

class EquellaTrainingData {

	private $data;
	private $training_data;
	private $report_data = [];
	private $subjects = [];

	public function __construct( OtbBooks $books ) {
		if ( is_object( $books ) ) {
			$this->data = $books->getResponses();
		}
	}

	/**
	 * sets instance variables for targets and training data
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

		$this->subjects      = $subjects;
		$this->training_data = $subjects;
	}

	/**
	 * @return array
	 */
	public function separateReportDataFromTraining() {
		$classification_report = [];
		if ( empty ( $this->subjects ) ) {
			return $classification_report;
		}

		foreach ( $this->subjects as $k1 => $sub ) {
			foreach ( $sub as $k2 => $s ) {
				if ( count( $s ) > 2 ) {
					$classification_report[ $k1 ][ $k2 ][] = array_pop( $this->subjects[ $k1 ][ $k2 ] );
					continue;
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
	 * @param bool $bi_gram
	 *
	 * @return array
	 */
	public function getBagOfWords( $data, $bi_gram = false ) {
		$samples = [];
		foreach ( $data as $sub1 => $sub2 ) {
			foreach ( $sub2 as $class_name => $values ) {
				foreach ( $values as $v ) {
					$samples[] = sprintf( '%s %s %s %s', $sub1, $class_name, $v['title'], $v['description'] );
				}
			}
		}
		if ( true === $bi_gram ) {
			$samples = $this->getBigram( $samples );
		}

		return $samples;
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	private function getBigram( $data ) {
		$bi_gram = [];
		foreach ( $data as $datum ) {
			$words = [];
			preg_match_all( '/\w\w+/u', $datum, $words );
			$bi_gram[] = $this->makeBigram( $words[0] );

		}

		return $bi_gram;
	}

	/**
	 * @param $words
	 *
	 * @return string
	 */
	private function makeBigram( $words ) {
		$bi_grams = '';
		$length   = count( $words );
		for ( $i = 0; $i < $length; $i ++ ) {
			if ( ! isset( $words[ $i ] ) || ! isset( $words[ $i + 1 ] ) ) {
				continue;
			}
			$bi_grams .= sprintf( '%1$s %2$s', $words[ $i ], $words[ $i + 1 ] );
		}

		return $bi_grams;
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	public function getTargets( $data ) {
		$targets = [];
		foreach ( $data as $sub1 => $sub2 ) {
			foreach ( $sub2 as $sub_name => $textbook ) {
				foreach ( $textbook as $v ) {
					$targets[] = $sub1;
				}
			}
		}

		return $targets;
	}

	/**
	 * @param $file_path
	 *
	 * @param bool $bi_gram
	 *
	 * @return array
	 */
	public function getPbJson( $file_path, $bi_gram = false ) {
		$results = [];
		if ( file_exists( $file_path ) ) {
			$json = file_get_contents( $file_path );
			$arr  = json_decode( $json, true );
			foreach ( $arr as $a ) {
				if ( isset( $a['metadata']['about'] ) && is_array( $a['metadata']['about'] ) ) {
					$identifier = '';
					foreach ( $a['metadata']['about'] as $id ) {
						$identifier .= $id['identifier'] . ' ';
					}
				}
				$keywords = isset( $a['metadata']['keywords'] ) ? $a['metadata']['keywords'] : '';
				$name     = isset( $a['metadata']['name'] ) ? $a['metadata']['name'] : '';
				$desc     = isset( $a['metadata']['description'] ) ? $a['metadata']['description'] : '';
				// backup
				$desc      = ( empty( $desc ) && isset( $a['metadata']['disambiguatingDescription'] ) ) ? $a['metadata']['disambiguatingDescription'] : $desc;
				$keywords2 = isset( $identifier ) ? $identifier : '';

				$results[] = sprintf( '%s %s %s %s', $keywords, $keywords2, $name, strip_tags( $desc ) );
			}
			if ( true === $bi_gram ) {
				$results = $this->getBigram( $results );
			}
		}

		return $results;


	}
}
