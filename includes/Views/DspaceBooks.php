<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2012-2017 Brad Payne <https://bradpayne.ca>
 * Date: 2017-05-29
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2012-2017, Brad Payne
 */

namespace BCcampus\OpenTextBooks\Views;

use BCcampus\OpenTextBooks\Models;

class DspaceBooks {

	/**
	 * @var $books
	 */
	private $books;

	/**
	 * @var
	 */
	protected $args;

	/**
	 * DspaceBooks constructor.
	 *
	 * @param Models\DspaceBooks $books
	 */
	public function __construct( Models\DspaceBooks $books ) {

		// TODO: Implement more robust constructor

		if ( is_object( $books ) ) {
			$this->books = $books;
		}

		$this->args = $books->getArgs();

	}

	/**
	 * @return string
	 */
	public function displayOneTextbook() {
		$html = '';

		// TODO: Implement displayOneTextbook();
		echo "<pre>";
		print_r( $this->books );
		echo "</pre>";

		echo $html;
	}

	/**
	 * @return string
	 */
	public function displaySearchForm() {
		$html = '<p>search form placeholder</p>';

		// TODO: Implement displaySearchForm()

		echo $html;
	}

	/**
	 * @return string
	 */
	public function displayBooks( $start_here ) {
		$limit = 10;
		$html  = '';

		if ( is_int( $start_here ) ) {
			//set the limit if there are less than 10 results based on where we start
			if ( ( $this->books->getSize() - $start_here ) < 10 ) {
				//add a limit to the results, but avoid setting the limit to 0, since that'll give you more than you want
				$limit = ( $this->books->getSize() - $start_here ) == 0 ? $limit = 1 : $this->books->getSize() - $start_here;
			}

			$html .= $this->displaySearchForm( $this->args['search'] );

			//if the search term is empty, then set where it starts and limit it to ten
			if ( empty( $this->args['search'] ) ) {
				$html .= $this->displayLinks( $start_here, $this->args['search'] );
				$html .= $this->displayBySubject( $start_here, $limit );
			} //otherwise, display all the results starting at the first one (from a search form)
			else {
				$html .= $this->displayBySubject( 0, 0 );
			}
			echo $html;
		}
		echo "<pre>";
		print_r( $this->books );
		echo "</pre>";

	}

	/**
	 * @return string
	 */
	public function displayTitlesByType() {
		$html = '';

		// TODO: Implement displayTitlesByType()

		echo $html;
	}

	public function displayBySubject( $start = 0, $limit = 0 ) {
		$html = '';
		$i    = 0;
		$data = $this->books->getResponses();

		// necessary to see the last record
		$start = ( $start == $this->books->getSize() ? $start = $start - 1 : $start = $start );

		// if we're displaying all of the results (from a search form request)

		$html .= "<ul class='no-bullets'>";
		// check if it's been reviewed
		while ( $i < $limit ) {
			$title       = \BCcampus\Utility\dc_metadata_to_csv( $data[ $start ], 'dc.title' );
			$description = \BCcampus\Utility\dc_metadata_to_csv( $data[ $start ], 'dc.description.abstract' );
			$authors     = \BCcampus\Utility\dc_metadata_to_csv( $data[ $start ], 'dc.contributor.author' );
			//$desc     = ( strlen( $description ) > 500 ) ? mb_substr( $description, 0, 499 ) . "<a href=" . $this->baseURL . "?uuid=" . $data[ $start ]['uuid'] . "&contributor=" . $this->args['contributor'] . "&keyword=" . $this->args['keyword'] . "&subject=" . $this->args['subject'] . ">...[more]</a>" : $description;

			//$metadata = $this->getMetaData( $data[ $start ]['metadata'] );
			$html .= "<li>";
			//$html .= "<h4><a href='" . $this->baseURL . "?uuid=" . $data[ $start ]['uuid'] . "&contributor=" . $this->args['contributor'] . "&keyword=" . $this->args['keyword'] . "&subject=" . $this->args['subject'] . "'>" . $data[ $start ]['name'] . "</a></h4> "
			//. "<h4>" . $metadata . " </h4>";
			//$html .= "<strong>Author(s):</strong> " . \BCcampus\Utility\array_to_csv( $data[ $start ]['drm']['options']['contentOwners'], 'name' ) . "<br>";
			//$html .= "<strong>Date:</strong> " . date( 'M j, Y', strtotime( $data[ $start ]['modifiedDate'] ) );
			$html .= "<p><strong>Title:</strong> " . $title . "</p>";

			$html .= "<p><strong>Description:</strong> " . $description . "</p>";
			$html .= "<p><b>Authors:</b> " . $authors . "</p>";
			$html .= "</li>";
			$start ++;
			$i ++;
		}
		if ( $limit == $this->books->getSize() ) {
			$html .= "</ol>";
		} else {
			$html .= "</ul>";
		}

		echo $html;
	}

	public function displayLinks( $start_here, $search_term ) {
		$html = '<p>display links placeholder</p>';

		// TODO: Implement displayLinks();

		echo $html;
	}

	/**
	 * @param $string
	 * @param $authors
	 *
	 * @return string
	 */
	private function licensePicker( $string, $authors ) {
		$html = '';

		// TODO: Implement licensePicker
		echo $html;
	}

	/**
	 * @param $number
	 *
	 * return float
	 */
	private function determineFileSize( $number ) {

		// TODO: Implement determineFileSize

	}


	private function addLogo() {

		// TODO: Implement addLogo();

	}

	/**
	 * @param \SimpleXMLElement $response
	 */
	private function getWebLicenseHtml( \SimpleXMLElement $response ) {

		// TODO: Implement getWebLicenseHtml();

	}

}