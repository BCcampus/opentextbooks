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
	private $report_data;
	private $subjects;
	protected $english = [
		'a',
		'about',
		'above',
		'after',
		'again',
		'against',
		'all',
		'am',
		'an',
		'and',
		'any',
		'are',
		'aren\'t',
		'as',
		'at',
		'be',
		'because',
		'been',
		'before',
		'being',
		'below',
		'between',
		'both',
		'but',
		'by',
		'can\'t',
		'cannot',
		'could',
		'couldn\'t',
		'did',
		'didn\'t',
		'do',
		'does',
		'doesn\'t',
		'doing',
		'don\'t',
		'down',
		'during',
		'each',
		'few',
		'for',
		'from',
		'further',
		'had',
		'hadn\'t',
		'has',
		'hasn\'t',
		'have',
		'haven\'t',
		'having',
		'he',
		'he\'d',
		'he\'ll',
		'he\'s',
		'her',
		'here',
		'here\'s',
		'hers',
		'herself',
		'him',
		'himself',
		'his',
		'how',
		'how\'s',
		'i',
		'i\'d',
		'i\'ll',
		'i\'m',
		'i\'ve',
		'if',
		'in',
		'into',
		'is',
		'isn\'t',
		'it',
		'it\'s',
		'its',
		'itself',
		'let\'s',
		'me',
		'more',
		'most',
		'mustn\'t',
		'my',
		'myself',
		'no',
		'nor',
		'not',
		'of',
		'off',
		'on',
		'once',
		'only',
		'or',
		'other',
		'ought',
		'our',
		'oursourselves',
		'out',
		'over',
		'own',
		'same',
		'shan\'t',
		'she',
		'she\'d',
		'she\'ll',
		'she\'s',
		'should',
		'shouldn\'t',
		'so',
		'some',
		'such',
		'than',
		'that',
		'that\'s',
		'the',
		'their',
		'theirs',
		'them',
		'themselves',
		'then',
		'there',
		'there\'s',
		'these',
		'they',
		'they\'d',
		'they\'ll',
		'they\'re',
		'they\'ve',
		'this',
		'those',
		'through',
		'to',
		'too',
		'under',
		'until',
		'up',
		'very',
		'was',
		'wasn\'t',
		'we',
		'we\'d',
		'we\'ll',
		'we\'re',
		'we\'ve',
		'were',
		'weren\'t',
		'what',
		'what\'s',
		'when',
		'when\'s',
		'where',
		'where\'s',
		'which',
		'while',
		'who',
		'who\'s',
		'whom',
		'why',
		'why\'s',
		'with',
		'won\'t',
		'would',
		'wouldn\'t',
		'you',
		'you\'d',
		'you\'ll',
		'you\'re',
		'you\'ve',
		'your',
		'yours',
		'yourself',
		'yourselves',
	];
	protected $stop_words;

	public function __construct( OtbBooks $books ) {
		if ( is_object( $books ) ) {
			$this->data = $books->getResponses();
		}
		$this->stop_words = array_fill_keys( $this->english, true );
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
	public function getBigram( $data ) {
		$bigram = [];
		foreach ( $data as $datum ) {
			$words = [];
			preg_match_all( '/\w\w+/u', $datum, $words );
			$clean    = $this->stripStopWords( $words[0] );
			$bigram[] = $this->makeBigram( $clean );

		}

		return $bigram;
	}

	/**
	 * @param $words
	 *
	 * @return array
	 */
	public function stripStopWords( $words ) {
		$new = [];
		foreach ( $words as $maybe_add ) {
			if ( array_key_exists( $maybe_add, $this->stop_words ) ) {
				continue;
			} else {
				$new[] = $maybe_add;
			}
		}

		return $new;
	}

	/**
	 * @param $words
	 *
	 * @return string
	 */
	protected function makeBigram( $words ) {
		$bigrams = '';
		$length  = count( $words );
		for ( $i = 0; $i < $length; $i ++ ) {
			if ( ! isset( $words[ $i ] ) || ! isset( $words[ $i + 1 ] ) ) {
				continue;
			}
			$bigrams .= sprintf( '%1$s-%2$s ', $words[ $i ], $words[ $i + 1 ] );
		}

		return $bigrams;
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
}
