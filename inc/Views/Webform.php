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

use BCcampus\OpenTextBooks\Models;

class Webform {

	protected $data;

	public function __construct( Models\WebForm $data ) {

		if ( is_object( $data ) ) {
			$this->data = $data;
		}

	}

	/**
	 *
	 */
	public function displayOtbStats() {
		setlocale( LC_MONETARY, 'en_CA' );
		$html     = '';
		$savings  = $this->data->getStudentSavings();
		$low      = money_format( '%n ', $savings['100'] );
		$high     = money_format( '%n ', $savings['actual'] );
		$limit    = 5;
		$top_inst = $this->data->getTopInstitutions( $limit );
		$top      = \BCcampus\Utility\array_to_csv( $top_inst );
		$this_year = date( 'Y', time() );

		$html .= "<h2>Known adoptions in B.C.</h2><h4>Date range: 2012 - {$this_year}</h4><table class='table table-striped'><tbody>";
		$html .= "<tr><td>Student savings</td><td>{$low} - {$high}</td></tr>";
		$html .= "<tr><td>Number of B.C. students using open textbooks</td><td>{$this->data->getNumStudents()}</td></tr>";
		$html .= "<tr><td>Number of B.C. institutions currently adopting</td><td>{$this->data->getNumInstitutions()}</td></tr>";
		$html .= "<tr><td>Top {$limit} adopting institutions (in order)</td><td>{$top}</td></tr>";
		$html .= "<tr><td>Number of known B.C. faculty adopting</td><td>{$this->data->getNumFaculty()}</td></tr>";
		$html .= "<tr><td>Number of known B.C. adoptions</td><td>{$this->data->getTotalAdoptions()}</td></tr>";
		$html .= '</tbody></table>';

		$html .= '<dl class="dl-horizontal">';
		$html .= '<dt>Adoption</dt><dd>Each adoption refers to a course section within a specific term and year for which an open textbook has replaced a a primary textbook or educational resource that must be purchased.</dd>';
		$html .= '<dt>Faculty</dt><dd>The number of individual instructors who have adopted one or more open textbooks for one or more course sections. A faculty member is only counted once.</dd>';
		$html .= '<dt>Savings</dt><dd>Savings include a range as reported in our blog, <a href="https://open.bccampus.ca/2015/02/18/calculating-student-savings/">Calculating Student Savings</a>.</dd>';
		$html .= '<dd>The number at the lower end is calculated as follows: number of students (see "Students") x $100 (This number was derived by OpenStax College based on a formula that takes into account used textbook purchases and rental costs as well as new textbook costs.)</dd>';
		$html .= '<dd>The number at the upper end is calculated as follows: number of students (see "Students") x actual cost of the textbook being replaced if purchased as hard copy and new.</dd>';
		$html .= '<dt>Students</dt><dd>The total number of students in all course sections within which an open textbook is used as the primary educational resource.</dd>';
		$html .= '</dl>';

		echo $html;
	}

	/**
	 *
	 */
	public function displaySummaryStats() {
		$html = '';
		setlocale( LC_MONETARY, 'en_CA' );
		$savings = $this->data->getStudentSavings();

		$html .= "<div id='bcc-box'><ul class='center'>";

		$html .= "<li><span class='bcc-main-text'>Student Savings: <b>" . money_format( '%.0n ', $savings['100'] ) . ' - ' . money_format( '%.0n ', $savings['actual'] ) . '</b></span></li>';
		$html .= "<li><span class='bcc-main-text'>Adoptions: <b>" . $this->data->getTotalAdoptions() . '</b></span></li>';
		$html .= "<li><span class='bcc-main-text'>Participating Institutions: <b>" . $this->data->getNumInstitutions() . '</b></span></li>';

		$html .= '</ul></div>';

		echo $html;
	}

	/**
	 * quick, dirty solution to expose stats at a rest endpoint
	 *
	 */
	public function restSummaryStats() {
		$savings = $this->data->getStudentSavings();
		$stats = array(
			'savings-min'  => $savings['100'],
			'savings-max'  => $savings['actual'],
			'adoptions'    => $this->data->getTotalAdoptions(),
			'institutions' => $this->data->getNumInstitutions(),
		);

		echo json_encode( $stats, JSON_PRETTY_PRINT );
	}

	/**
	 *
	 */
	public function displayFacultyNames() {
		$html = '<h3>Known Adopters</h3><dl>';

		foreach ( $this->data->getFacultyInfo() as $inst => $names ) {
			$html .= "<dt>{$inst}</dt>";
			foreach ( $names as $person ) {
				$html .= "<dd>{$person}</dd>";
			}
		}

		$html .= '</dl>';
		echo $html;
	}

}
