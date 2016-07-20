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

class StatsBookReviews
{
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
     * @param OtbReviews $data
     */
    public function __construct(OtbReviews $data)
    {
        if (is_object($data)) {
            $this->data = $data;
        }
        $this->setInfoByUid();
        $this->setUniqueInstitutions();
        $this->setUniqueTextbooks();
    }

    /**
     * @return string
     */
    public function displayReports()
    {
        $html = $not_reviewed = '';
        $num_reviews = count($this->data->getAvailableReviews());
        $num_inst = count($this->uniqueInstitutions);
        $num_books = count($this->uniqueBookTitles);
        $name_inst = '';
        $name_books = '';
        foreach ($this->uniqueInstitutions as $inst) {
            $name_inst .= "<li>{$inst}</li>";
        }
        foreach ($this->uniqueBookTitles as $title) {
            $name_books .= "<li>{$title}</li>";
        }

        // number of reviews per book
        $html .= "<hgroup><h2>Summary</h2>";
        $html .= "<h4>Number of completed reviews: {$num_reviews}</h4>";
        $html .= "<h4>Number of textbooks reviewed: {$num_books} ";
        $html .= "<a class='btn btn-default' type='button' tabindex='0' data-target='#book_titles' data-toggle='modal' title='Book Titles'>Which Books?</a></h4>";
        $html .= '<div class="modal fade" id="book_titles" tabindex="-1" role="dialog" aria-labelledby="book_titles_label">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Reviewed Books</h4>
                      </div>
                      <div class="modal-body"><ol>' . $name_books . '</ol></div>
                </div>
              </div>
            </div>';
        $html .= "<h4>Number of participating institutions: {$num_inst} </h4>";
//        $html .= "<a class='btn btn-default' type='button' tabindex='0' data-target='#inst' data-toggle='modal' title='Institution Names'>Which Institutions?</a></h4>";
//        $html .= '<div class="modal fade" id="inst" tabindex="-1" role="dialog" aria-labelledby="inst_label">
//                  <div class="modal-dialog" role="document">
//                    <div class="modal-content">
//                      <div class="modal-header">
//                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
//                        <h4 class="modal-title" id="instLabel">Participating Institutions</h4>
//                      </div>
//                      <div class="modal-body"><ol>' . $name_inst . '</ol></div>
//                </div>
//              </div>
//            </div>';
        $html .= "</hgroup>";
        $rev_perc = round(100*($num_books/$num_reviews));
        $html .= "<h3>Percentage of books in the collection that have been reviewed: </h3>
                <div class='progress'>
                <div class='progress-bar progress-bar-success progress-bar-striped active' role='progressbar' aria-valuemin='0'
                     aria-valuenow='{$num_reviews}' aria-valuemax='{$num_books}'
                     style='width:{$rev_perc}%;'>{$rev_perc}%</div>
                </div>";

        $html .= "<div id='table-responsive'>";
        $html .= "<table id='reviews' class='table table-responsive table-striped table-hover table-condensed tablesorter'>";
        $html .= "<thead><tr>
        <th>Title&nbsp;<i class='glyphicon glyphicon-sort'></i></th>
        <th>Num of Reviews&nbsp;<i class='glyphicon glyphicon-sort'></i></th>
        <th>Overall Score<br>(max 5)&nbsp;<i class='glyphicon glyphicon-sort'></i></th>
        <th>Details</th>
        </tr></thead><tbody>";
        foreach ($this->data->getAvailableReviews() as $uid => $book) {

            if (!isset($this->responseByUid[$uid]['num_reviews'])) {
                //continue;
                $html .= "<tr class='warning'>";
                $html .= "<td>{$book}</td>";
                $html .= "<td>0</td>";
                $html .= "<td>0</td>";
                $html .= "<td>-</td>";
            } else {
                $html .= "<tr>";
                $html .= "<td>{$book}</td>";
                $html .= "<td>{$this->responseByUid[$uid]['num_reviews']}</td>";
                $avg = round($this->responseByUid[$uid]['avg_score'] / $this->responseByUid[$uid]['num_reviews'], 2);
                $html .= "<td>{$avg}</td>";
                $html .= "<td><details><summary>Details</summary>";
                foreach ($this->responseByUid[$uid] as $review) {
                    if (is_array($review)) {
                        $html .= "<ul>";
                        $html .= "<li><b>Avg Score:</b> {$review['avg_score']}</li>";
                        $html .= "<li><b>Reviewers:</b> {$review['reviewers']}</li>";
                        $html .= "<li><b>Institutions:</b> {$review['institutions']}</li>";
                        $human_readable = date('M d, Y', strtotime($review['date']));
                        $html .= "<li><b>Date Published:</b> {$human_readable}</li>";
                        $html .= "</ul>";
                    }
                }
                $html .= "</details></td>";
            }

            $html .= "</tr>";
        }
        $html .= "</tbody></table></div>";

        echo $html;
    }

    /**
     * @return int
     */
    private function setUniqueTextbooks()
    {
        $book_titles = array();

        foreach ($this->data->getAvailableReviews() as $uid => $book) {
            if (!isset($this->responseByUid[$uid]['num_reviews'])) {
                continue;
            } else {
                $book_titles[] = $book;
            }
        }

        $this->uniqueBookTitles = array_unique($book_titles);
    }

    /**
     *
     */
    private function setUniqueInstitutions()
    {
        $institutions = array();

        foreach ($this->responseByUid as $reviews) {
            foreach ($reviews as $review) {
                if (is_array($review)) {
                    $institutions[] = $review['institutions'];
                }
            }
        }

        $this->uniqueInstitutions = array_unique($institutions);
    }

    /**
     *
     */
    private function setInfoByUid()
    {

        $institution_ids = $this->data->getInstitutionIDs();

        // set score and total amount
        $this->setAvgAndTotal($this->data->getResponses());

        foreach ($this->data->getResponses() as $response) {

            // set reviewers and institutions
            if ('N' == $response['info7']) {
                $this->responseByUid[$response['info1']][$response['id']]['reviewers'] = $response['info2'];
                $this->responseByUid[$response['info1']][$response['id']]['institutions'] = $institution_ids[$response['info6']];
            } else {
                $this->responseByUid[$response['info1']][$response['id']]['reviewers'] = $this->data->getNames($response);
                $this->responseByUid[$response['info1']][$response['id']]['institutions'] = $this->data->getInstitutions($response);
            }

            // set date
            $this->responseByUid[$response['info1']][$response['id']]['date'] = $response['datestamp'];

        }

    }

    /**
     * group responses by uuid (book)
     *
     * @param array $response
     */
    private function setAvgAndTotal( $response )
    {

        foreach ($response as $val) {
            $sum = '';
            $count = 0;
            // multiple reviews, one book
            // need to lop off the first bit of array to get just Q&A
            $q_and_a = array_slice($val, $this->slice, NULL, FALSE);
            while (list($key, $value) = each($q_and_a)) {
                if (is_numeric($value)) {
                    $sum = $sum + intval($value);
                    $count++;
                }
            }
            // set the reviewer's average
            $this->responseByUid[$val['info1']][$val['id']]['avg_score'] = round($sum / $count, 2);
        }

        // set the average score and total reviews for each book
        foreach ($this->responseByUid as $uid => $book) {
            $this->responseByUid[$uid]['num_reviews'] = count($book);
            $avg = 0;
            foreach ($book as $score) {
                $avg += $score['avg_score'];
            }
            $this->responseByUid[$uid]['avg_score'] = $avg;
        }

    }

}