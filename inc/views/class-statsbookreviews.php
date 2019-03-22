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

namespace BCcampus\OpenTextBooks\Views;

use BCcampus\OpenTextBooks\Models\OtbReviews;

class StatsBookReviews {

	/**
	 * @var OtbReviews
	 */
	private $data;

	/**
	 * @var
	 */
	private $responseByUid;

	/**
	 * @var
	 */
	private $uniqueInstitutions;
	/**
	 * questions and answers start at a specific place in
	 * the results array. Modifying this will affect how each
	 * answer lines up with each question
	 */

	/**
	 * @var
	 */
	private $uniqueBookTitles;

	/**
	 * @var int
	 */
	private $slice = 21;

	/**
	 * Reports constructor.
	 *
	 * @param OtbReviews $data
	 */
	public function __construct( OtbReviews $data ) {
		if ( is_object( $data ) ) {
			$this->data = $data;
		}
		$this->setInfoByUid();
		$this->setUniqueInstitutions();
		$this->setUniqueTextbooks();
	}

	/**
	 * @return string
	 */
	public function displayReports() {
		$html        = '';
		$num_reviews = count( $this->data->getAvailableReviews() );
		$num_inst    = count( $this->uniqueInstitutions );
		$num_books   = count( $this->uniqueBookTitles );
		$name_inst   = '';
		$name_books  = '';
		foreach ( $this->uniqueInstitutions as $inst ) {
			$name_inst .= "<li>{$inst}</li>";
		}
		foreach ( $this->uniqueBookTitles as $title ) {
			$name_books .= "<li>{$title}</li>";
		}

		// number of reviews per book
		$html    .= '<hgroup><h2>Summary</h2>';
		$html    .= "<p>Number of completed reviews: {$num_reviews}</p>";
		$html    .= "<p>Number of textbooks reviewed: {$num_books} ";
		$html    .= "<a class='btn btn btn-outline-primary' role='button' tabindex='0' data-target='#book_titles' data-toggle='modal' title='Book Titles'>Which Books?</a></h4>";
		$html    .= '<div class="modal fade" id="book_titles" tabindex="-1" role="dialog" aria-labelledby="book_titles_label">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                      <h4 class="modal-title" id="myModalLabel">Reviewed Books</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      </div>
                      <div class="modal-body"><ol>' . $name_books . '</ol></div>
                </div>
              </div>
            </div>';
		$html    .= "<p>Number of participating institutions: {$num_inst} </p>";
		$html    .= '</hgroup>';
		$rev_perc = round( 100 * ( $num_books / $num_reviews ) );
		$html    .= "<p>Percentage of books in the collection that have been reviewed: </p>
                <div class='progress'>
                <div class='progress-bar progress-bar-success progress-bar-striped active' role='progressbar' aria-valuemin='0'
                     aria-valuenow='{$num_reviews}' aria-valuemax='{$num_books}'
                     style='width:{$rev_perc}%;'>{$rev_perc}%</div>
                </div>";

		$html .= "<div id='table'>";
		$html .= "<table id='reviews' class='table table-striped tablesorter'>";
		$html .= "<thead><tr>
        <th>Title&nbsp;<i class='fa fa-sort'></i></th>
        <th>Num of Reviews&nbsp;<i class='fa fa-sort'></i></th>
        <th>Overall Score<br>(max 5)&nbsp;<i class='fa fa-sort'></i></th>
        <th>Details</th>
        </tr></thead><tbody>";
		foreach ( $this->data->getAvailableReviews() as $uid => $book ) {

			if ( ! isset( $this->responseByUid[ $uid ]['num_reviews'] ) ) {
				//continue;
				$html .= "<tr class='table-warning'>";
				$html .= "<td>{$book}</td>";
				$html .= '<td>0</td>';
				$html .= '<td>0</td>';
				$html .= '<td>-</td>';
			} else {
				$html .= '<tr>';
				$html .= "<td>{$book}</td>";
				$html .= "<td>{$this->responseByUid[$uid]['num_reviews']}</td>";
				$avg   = round( $this->responseByUid[ $uid ]['avg_score'] / $this->responseByUid[ $uid ]['num_reviews'], 2 );
				$html .= "<td>{$avg}</td>";
				$html .= '<td><details><summary>Details</summary>';
				foreach ( $this->responseByUid[ $uid ] as $review ) {
					if ( is_array( $review ) ) {
						$html          .= '<ul>';
						$html          .= "<li><b>Avg Score:</b> {$review['avg_score']}</li>";
						$html          .= "<li><b>Reviewers:</b> {$review['reviewers']}</li>";
						$html          .= "<li><b>Institutions:</b> {$review['institutions']}</li>";
						$human_readable = date( 'M d, Y', strtotime( $review['date'] ) );
						$html          .= "<li><b>Date Published:</b> {$human_readable}</li>";
						$html          .= '</ul>';
					}
				}
				$html .= '</details></td>';
			}

			$html .= '</tr>';
		}
		$html .= '</tbody></table></div>';

		echo $html;
	}

	/**
	 *
	 */
	private function setUniqueTextbooks() {
		$book_titles = [];

		foreach ( $this->data->getAvailableReviews() as $uid => $book ) {
			if ( ! isset( $this->responseByUid[ $uid ]['num_reviews'] ) ) {
				continue;
			} else {
				$book_titles[] = $book;
			}
		}

		$this->uniqueBookTitles = array_unique( $book_titles );
	}

	/**
	 *
	 */
	private function setUniqueInstitutions() {
		$institutions = [];

		foreach ( $this->responseByUid as $reviews ) {
			foreach ( $reviews as $review ) {
				if ( is_array( $review ) ) {
					$institutions[] = $review['institutions'];
				}
			}
		}

		$this->uniqueInstitutions = array_unique( $institutions );
	}

	/**
	 *
	 */
	private function setInfoByUid() {

		$institution_ids = $this->data->getInstitutionIDs();

		// set score and total amount
		$this->setAvgAndTotal( $this->data->getResponses() );

		foreach ( $this->data->getResponses() as $response ) {
			// something is throwing a space in the key when it gets here, so 'id' presents as ' id'
			$sid = ( isset( $response['id'] ) ) ? intval( $response['id'] ) : array_shift( $response );

			// set reviewers and institutions
			if ( 0 === strcmp( 'N', $response['info7'] ) ) {
				$this->responseByUid[ $response['info1'] ][ $sid ]['reviewers']    = $response['info2'];
				$this->responseByUid[ $response['info1'] ][ $sid ]['institutions'] = $institution_ids[ $response['info6'] ];
			} else {
				$this->responseByUid[ $response['info1'] ][ $sid ]['reviewers']    = $this->data->getNames( $response );
				$this->responseByUid[ $response['info1'] ][ $sid ]['institutions'] = $this->data->getInstitutions( $response );
			}

			// set date
			$this->responseByUid[ $response['info1'] ][ $sid ]['date'] = $response['datestamp'];

		}

	}

	/**
	 * group responses by uuid (book)
	 *
	 * @param array $response
	 */
	private function setAvgAndTotal( $response ) {

		foreach ( $response as $val ) {
			$sum   = 0;
			$count = 0;
			// multiple reviews, one book
			// need to lop off the first bit of array to get just Q&A
			$q_and_a = array_slice( $val, $this->slice, null, false );

			foreach ( $q_and_a as $key => $value ) {
				if ( is_numeric( $value ) ) {
					$sum = $sum + intval( $value );
					$count ++;
				}
			}

			// something is throwing a space in the key when it gets here, so 'id' presents as ' id'
			$s = ( isset( $val['id'] ) ) ? intval( $val['id'] ) : array_shift( $val );
			// set the reviewer's average
			$this->responseByUid[ $val['info1'] ][ $s ]['avg_score'] = round( $sum / $count, 2 );
		}

		// set the average score and total reviews for each book
		if ( is_array( $this->responseByUid ) ) {
			foreach ( $this->responseByUid as $uid => $book ) {
				$this->responseByUid[ $uid ]['num_reviews'] = count( $book );
				$avg                                        = 0;
				foreach ( $book as $score ) {
					$avg += $score['avg_score'];
				}
				$this->responseByUid[ $uid ]['avg_score'] = $avg;
			}
		}

	}

}
