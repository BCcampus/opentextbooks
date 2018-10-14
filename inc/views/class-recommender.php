<?php

namespace BCcampus\OpenTextBooks\Views;

use Phpml\Metric\ClassificationReport;

class Recommender {

	public function __construct() {

	}

	public function displayReport( ClassificationReport $report ) {

		$html = "<h2>Average</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>Average</th></tr>";
		foreach ( $report->getAverage() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%s</td><td class="border">%s</td></tr>', $k, $v );
		};
		$html .= '</table>';

		$html .= "<h2>Precision</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>Precision</th></tr>";
		foreach ( $report->getPrecision() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%1$s</td><td class="border">%2$s</td></tr>', $k, $v );
		}
		$html .= '</table>';

		$html .= "<h2>Recall</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>Recall</th></tr>";
		foreach ( $report->getRecall() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%s</td><td class="border">%s</td></tr>', $k, $v );
		};
		$html .= '</table>';

		$html .= "<h2>F1 Score</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>F1 Score</th></tr>";
		foreach ( $report->getF1score() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%s</td><td class="border">%s</td></tr>', $k, $v );
		};
		$html .= '</table>';

		$html .= "<h2>Support</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>Support</th></tr>";
		foreach ( $report->getSupport() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%s</td><td class="border">%s</td></tr>', $k, $v );
		};
		$html .= '</table>';

		echo $html;

	}
}
