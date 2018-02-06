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
		$env = include( OTB_DIR . '.env.php' );

		foreach ( $this->books->getPrunedResults() as $book ) {
			$tmp[ $book['uuid'] ] = $book['name'];
		}

		array_multisort( $tmp, SORT_ASC | SORT_NATURAL );

		$html = '<table class="table table-striped">';
		$html .= '<thead><tr><th>Title</th><th>Download Stats</th></tr></thead><tbody>';
		foreach ( $tmp as $uuid => $name ) {
			$html .= '<tr>';
			$html .= "<td><a href='//{$env['domain']['HOST']}/{$env['domain']['APP_PATH']}/?uuid={$uuid}' target='_blank'><i class='glyphicon glyphicon-book'></i></a> — {$name}</td>";
			$html .= "<td><a href='" . OTB_URL . "analytics.php?uuid={$uuid}&view=single'><i class='glyphicon glyphicon-stats'></i></a></td>";
			$html .= '</tr>';

		}
		$html .= '</tbody></table>';
		echo $html;
	}

	/**
	 *
	 */
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

	/**
	 *
	 */
	public function displaySubjectStats() {
		$env = include( OTB_DIR . '.env.php' );
		$html       = '';
		$cumulative = 0;
		$base_url   = "//{$env['domain']['HOST']}/{$env['domain']['APP_PATH']}/?subject=";
		$num_sub1   = count( $this->books->getSubjectAreas() );
		$num_sub2   = 0;

		foreach ( $this->books->getSubjectAreas() as $key => $val ) {
			$total    = 0;
			$second   = '';
			$num_sub2 = $num_sub2 + count( $val );

			foreach ( $val as $k => $sub2 ) {
				$url = $base_url . \BCcampus\Utility\url_encode( $k );
				$second .= "<li><a href='{$url}'>{$k}</a>: {$sub2}</li>";
				$total = $total + intval( $sub2 );

			}
			$cumulative = $cumulative + $total;

			$first = "<h4>{$key} ({$total})</h4>";
			$html .= $first . '<ul class="list-unstyled">' . $second . '</ul>';

		}

		echo "<h2>Summary</h2><h4>Number of books in the collection: {$cumulative}</h4><h4>Number of main subject areas: {$num_sub1}</h4><h4>Number of secondary subject areas: {$num_sub2}</h4><hr>" . $html;

	}


}
