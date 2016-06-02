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

use BCcampus\OpenTextBooks\Models\OtbBooks;

class StatsBooks {
	private $books;

	public function __construct( OtbBooks $books ) {
		if ( is_object( $books ) ) {
			$this->books = $books;
		}

	}

	public function displayStatsTitles() {

		foreach ( $this->books->getPrunedResults() as $book ) {
			$tmp[ $book['uuid'] ] = $book['name'];
		}

		array_multisort( $tmp, SORT_ASC | SORT_NATURAL );


		$html = '<table class="table table-striped">';
		$html .= '<thead><tr><th>Title</th><th>Download Stats</th></tr></thead><tbody>';
		foreach ( $tmp as $uuid => $name ) {
			$html .= '<tr>';
			$html .= "<td><a href='https://open.bccampus.ca/find-open-textbooks/?uuid={$uuid}' target='_blank'><i class='glyphicon glyphicon-book'></i></a> — {$name}</td>";
			$html .= "<td><a href='".OTB_URL."analytics.php?uuid={$uuid}&view=single'><i class='glyphicon glyphicon-stats'></i></a></td>";
			$html .= '</tr>';

		}
		$html .= '</tbody></table>';
		echo $html;
	}

	public function displayStatsUuid() {
		$book        = $this->books->getPrunedResults();
		$today       = time();
		$then        = strtotime( $book['createdDate'] );
		$difference  = $today - $then;
		$days_online = round( $difference / 86400 );

		$html = "<h2>{$book['name']}</h2>";
		$html .= "<h6>Days online: {$days_online}</h6>";

		echo $html;
	}


}