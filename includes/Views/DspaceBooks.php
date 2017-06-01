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
	 * DspaceBooks constructor.
	 *
	 * @param Models\DspaceBooks $books
	 */
	public function __construct( Models\DspaceBooks $books ) {

		// TODO: Implement more robust constructor

		if ( is_object( $books ) ) {
			$this->books = $books;
		}

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
		return $html;
	}

	/**
	 * @return string
	 */
	public function displaySearchForm() {
		$html = '';

		// TODO: Implement displaySearchForm()
		return $html;
	}

	/**
	 * @return string
	 */
	public function displayBooks() {
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
		return $html;
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
		return $html;
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