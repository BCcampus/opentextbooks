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
 * A view can be any output representation of information, such as a chart or a diagram.
 * Multiple views of the same information are possible
 */

namespace BCcampus\OpenTextBooks\Views;

use BCcampus\OpenTextBooks\Config;
use BCcampus\OpenTextBooks\Models\OtbReviews;

class BookReviews {

	/**
	 * The main data object that this view uses
	 *
	 * @var OtbReviews
	 */
	private $data;

	/**
	 * These questions have to be hardcoded in here
	 * because I think there was no way to get them from the API? (long time ago)
	 *
	 * @var array
	 */
	private $questionGroups = [
		'Comprehensiveness' => 'The text covers all areas and ideas of the subject appropriately and provides an effective index and/or glossary',
		'Content Accuracy' => 'Content is accurate, error-free and unbiased',
		'Relevance' => 'Content is up-to-date, but not in a way that will quickly make the text obsolete within a short period of time. The text is written and/or arranged in such a way that necessary updates will be relatively easy and straightforward to implement',
		'Clarity' => 'The text is written in lucid, accessible prose, and provides adequate context for any jargon/technical terminology used',
		'Consistency' => 'The text is internally consistent in terms of terminology and framework',
		'Modularity' => 'The text is easily and readily divisible into smaller reading sections that can be assigned at different points within the course (i.e., enormous blocks of text without subheadings should be avoided). The text should not be overly self-referential, and should be easily reorganized and realigned with various subunits of a course without presenting much disruption to the reader.',
		'Organization' => 'The topics in the text are presented in a logical, clear fashion',
		'Interface' => 'The text is free of significant interface issues, including navigation problems, distortion of images/charts, and any other display features that may distract or confuse the reader',
		'Grammar' => 'The text contains no grammatical errors',
		'Cultural Relevance' => 'The text is not culturally insensitive or offensive in any way. It should make use of examples that are inclusive of a variety of races, ethnicities, and backgrounds',
		'Final Thoughts' => 'Are there any other comments you would like to make about this book, for example, its appropriateness in a Canadian context or specific updates you think need to be made?',
	];

	/**
	 * questions and answers start at a specific place in
	 * the results array. Modifying this will affect how each
	 * answer lines up with each question
	 *
	 * @var int
	 */
	private $slice = 22;

	/**
	 * Reviews constructor
	 * Displays a summary of all the reviews associated with a particular book
	 *
	 * @param OtbReviews $data
	 */
	public function __construct( OtbReviews $data ) {
		if ( is_object( $data ) ) {
			$this->data = $data;
		}

	}

	/**
	 * will display all reviews from one textbook
	 */
	public function displayReviews() {
		$institution_ids = $this->data->getInstitutionIDs();
		$cut_off         = 1521158399; // March 15, 2018
		$html            = $this->displaySummary();

		$num = 1;

		foreach ( $this->data->getResponses() as $response ) {

			if ( is_array( $response ) && in_array( $this->data->getUuid(), $response ) ) {

				// just grab the questions and answers
				$q_and_a = array_slice( $response, $this->slice, null, false );

				// for collaboration projects
				if ( 'N' == $response['info7'] ) {
					$names        = $response['info2'];
					$institutions = $institution_ids[ $response['info6'] ];
				} else {
					$names        = $this->data->getNames( $response );
					$institutions = $this->data->getInstitutions( $response );
				}

				// get the book average
				$overall_avg = $this->getOverallAvg( $q_and_a );

				// change license based on date (March 15, 2018)
				$license = ( strtotime( $response['datestamp'] ) < $cut_off ) ? "<a rel='license' href='https://creativecommons.org/licenses/by-nd/3.0/deed.en_US'><img alt='Creative Commons License' style='border-width:0' src='https://i.creativecommons.org/l/by-nd/3.0/80x15.png' /></a>" : "<a rel='license' href='https://creativecommons.org/licenses/by/3.0/deed.en_US'><img alt='Creative Commons License' style='border-width:0' src='https://i.creativecommons.org/l/by/3.0/80x15.png' /></a>";

				$html           .= "<details itemprop='review' itemscope itemtype='https://schema.org/Review'>
                <summary class='text-info'><strong>" . $num . ". Reviewed by:</strong> <span itemprop='author copyrightHolder'>" . $names . '</span></summary>
                <ul>
                    <li><b>Institution:</b> ' . $institutions . '</li>
                    <li><b>Title/Position:</b> ' . $response['info5'] . "</li>
                    <li itemprop='reviewRating' itemscope itemtype='https://schema.org/Rating'><b>Overall Rating:</b> <meter min='0' low='0' high='5' max='5' value='" . $overall_avg . "'></meter> <span itemprop='ratingValue'>" . $overall_avg . "</span> out of <span itemprop='bestRating'>5</span></span></li>
		            <li><b>Date:</b><time itemprop='datePublished'> " . date( 'M j, Y', strtotime( $response['datestamp'] ) ) . '</time></li>
                    <li><b>License:</b> ' . $license . "</li>
                </ul>
                <div class='tabbable tabs-left'>";
				$group_keys      = array_keys( $this->questionGroups );
				$group_questions = array_values( $this->questionGroups );

				$html  .= "<ul class='nav nav-tabs reviews'>";
				$active = 1;

				// create the left nav sidebar
				foreach ( $this->questionGroups as $group => $question ) {
					( $active == 1 ) ? $html .= "<li class=nav-item'>" : $html .= '<li>';

					$html  .= "<a class='nav-link' href='#" . substr( $group, 0, 5 ) . $num . "' data-toggle='tab'>" . $group . '</a></li>';
					$active = 0;
				}
				$active = '';
				$html  .= '</ul></div>';

				// create the content
				$html .= "<div class='tab-content' itemprop='reviewBody'>";
				$i     = 0;

				foreach ( $q_and_a as $key => $val ) {

					if ( is_numeric( $val ) ) {
						$html .= '
                        <p><strong>' . $group_keys[ $i - 1 ] . ' Rating:</strong> ' . $val . " out of 5<aside><meter min='0' low='0' high='5' max='5' value='" . $val . "'></meter><aside></p></section>";
					}
					if ( ! is_numeric( $val ) ) {
						( $i == 0 ) ? $active .= ' active' : $active = '';
						$html                 .= "
                        <section class='tab-pane " . $active . "' id='" . substr( $group_keys[ $i ], 0, 5 ) . $num . "'>";

						$html .= '
                        <h4>Q: ' . $group_questions[ $i ] . '</h4>
                        <p>' . nl2br( $val ) . '</p>';
						$i++;
					}
				}
				$html .= '</section></div></details></span>';
				$num++;
			}

		} // end foreach
		$html .= '';
		echo $html;
	}

	/**
	 * give it a book_uuid and it'll give you a summary
	 * @return bool|string - an html blob if successful, false otherwise.
	 * @internal param string $book_uuid
	 * @internal param $data
	 */
	function displaySummary() {
		$env            = Config::getInstance()->get();
		$total          = 0;
		$min            = 1;
		$max            = 4;
		$adaptation     = \BCcampus\Utility\has_canadian_edition( $this->data->getUuid() );

		if ( is_array( $this->data->getResponses() ) ) {

			foreach ( $this->data->getResponses() as $response ) {
				if ( is_array( $response ) && in_array( $this->data->getUuid(), $response ) ) {
					$total++;
				}
			}

			$bookAvg = $this->getOverallAvg( $this->data->getResponses(), $this->data->getUuid() );

			$html = '<hr/>';

			if ( 0 == $total ) {
				$html .= "<p class='text-success'>There are currently no reviews for this book.</p>"
					. "<p>Be the first to <a href='/{$env['domain']['reviews_path']}/'>Review this book</a></p>";
			} // limit to books that have 4 or less
			elseif ( $total < $max ) {
				$html .= "<p><a href='/{$env['domain']['reviews_path']}/'>Review this book</a></p>";
			}

			// only want to send them to canadian version if there is one, and less than max reviews
			if ( $adaptation && $total < $max ) {
				$domain = "//{$env['domain']['host']}/{$env['domain']['app_path']}/?uuid=";
				$html  .= "<h4 class='alert alert-success'>Review the Canadian edition of this book ";
				$html  .= "<a href='{$domain}{$adaptation}'> here </a>";
				$html  .= '</h4>';
			}

			// only print if there is a review to print
			if ( $total >= $min ) {
				$html .= "<span itemprop='aggregateRating' itemscope itemtype='https://schema.org/AggregateRating'>
                <h5>Reviews (<span itemprop='reviewCount'>{$total}</span>) 
                <span class='float-right'>Avg: <meter min='0' low='0' high='5' max='5' value='{$bookAvg}'></meter> <span itemprop='ratingValue'>{$bookAvg}</span> / 5</span></h5>";
			}

			$html .= '<hr/>';

			return $html;
		}
		return false;
	}

	/**
	 * looks for numerical values in an array, and creates an average.
	 *
	 * @param array $array
	 * @param null $book_uuid
	 * @return bool|int - integer if successful, false otherwise
	 * @internal param string $bookUid
	 */
	private function getOverallAvg( array $array, $book_uuid = null ) {
		if ( is_array( $array ) ) {
			$sum   = '0';
			$count = 0;
			if ( ! isset( $book_uuid ) ) {
				foreach ( $array as $val ) {
					if ( is_numeric( $val ) ) {
						$sum = $sum + intval( $val );
						$count++;
					}
				}
				$result = round( $sum / $count, 2 );
				return ( $result );
			} else {
				foreach ( $array as $val ) {
					if ( is_array( $val ) && in_array( $book_uuid, $val ) ) {
						// need to lop off the first bit of array to get just Q&A
						$q_and_a = array_slice( $val, $this->slice, null, false );
						foreach ( $q_and_a as $key => $value ) {
							if ( is_numeric( $value ) ) {
								$sum = $sum + intval( $value );
								$count++;
							}
						}
					}
				}
				if ( $count == 0 ) {
					return $count;
				} else {
					$result = round( $sum / $count, 2 );
					return ( $result );
				}
			}
		}
		return false;
	}

}
