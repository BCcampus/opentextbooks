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
 * @copyright (c) 2012-2017, Brad Payne, Alex Paredes
 */

namespace BCcampus\OpenTextBooks\Views;

use BCcampus\OpenTextBooks\Config;
use BCcampus\OpenTextBooks\Models;

class DspaceBooks {

	/**
	 * Make the object holding the data available
	 * to this View Class
	 *
	 * @var Models\DspaceBooks
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
	 * Display HTML for one textbook record
	 *
	 * @return string $html
	 */
	public function displayOneTextbook() {
		$env         = Config::getInstance()->get();
		$html        = '';
		$data        = $this->books->getResponses();
		$title       = $this->metadataToCsv( $data, 'dc.title' );
		$description = $this->metadataToCsv( $data, 'dc.description.abstract' );
		$authors     = $this->metadataToCsv( $data, 'dc.contributor.author' );
		$date        = date( 'M j, Y', strtotime( $this->metadataToCsv( $data, 'dc.date.issued' ) ) );

		$html .= "<h2 itemprop='name'>" . $title . '</h2>';
		$html .= "<p><strong>Description</strong>: <span itemprop='description'>" . $description . '</span></p>';
		$html .= "<p><strong>Author</strong>: <span itemprop='author copyrightHolder'>" . $authors . '</span></p>';
		$html .= "<p><strong>Adoption (faculty): </strong><a href='/{$env['domain']['adoption']['path']}/'>Contact us if you are using this textbook in your course <i class='glyphicon glyphicon-book'></i></a></p>";
		$html .= "<p><strong>Adaptations: </strong><a href='{$env['domain']['adaptation']['path']}'>Support for adapting an open textbook <i class='glyphicon glyphicon-book'></i></a></p>";
		$html .= "<p><strong>Date Issued</strong>: <span itemprop='issued'>" . $date . '<br></span></p>';
		$html .= "<p><strong>Need help? </strong>Visit our <a href='//{$env['domain']['host']}/help/'>Help page</a> for FAQ and helpdesk assistance.</p>";
		$html .= "<p><strong>Accessibility: </strong>Textbooks flagged as accessible meet the criteria noted on the <a href='https://opentextbc.ca/accessibilitytoolkit/back-matter/appendix-checklist-for-accessibility-toolkit/'>Accessibility Checklist.<i class='glyphicon glyphicon-book'></i></a></p>";
		$html .= '<h3>Open Textbook(s):</h3>';
		$html .= $this->displayBitStreamFiles( $data );
		//send it to the picker for evaluation
		$substring = $this->licensePicker( $data, $authors );
		//include it, depending on what license it is
		$html .= $substring;

		echo $html;
	}

	/**
	 * Displays HTML for a search form
	 *
	 * @param string $post_value
	 *
	 * @return string $html
	 */
	public function displaySearchForm( $post_value = '' ) {

		$string = \BCcampus\Utility\array_to_string( $post_value );
		$html   = "
      <fieldset name='dspace' class='pull-right'>
      <form class='form-search form-inline' action='' method='get'>
        <input type='text' class='input-small' name='search' id='dspaceSearchTerm' value='" . $string . "'/> 
        <button type='submit' formaction='' class='btn' name='dspaceSearchSubmit' id='dspaceSearchSubmit'>Search</button>
      </form>
      </fieldset>";

		if ( $this->books->getSize() > 0 ) {
			$html .= '<h5>Available results: ' . $this->books->getSize() . '</h5>';
		} else {
			$html .= "<h5>Available: <span style='color:red;'>sorry, your search returned no results</span></h5>";
		}
		echo $html;
	}

	/**
	 * Displays HTML for a catalogue of books
	 *
	 * @param $args
	 *
	 * @return string $html
	 */
	public function displayBooks( $args ) {
		$limit = ( ! empty( $args['limit'] ) ) ? $args['limit'] : 10;
		$html  = '';

		// always display search form
		$html .= $this->displaySearchForm( $this->args['search'] );

		// if the search term is empty, display pagination links
		if ( empty( $this->args['search'] ) ) {
			$html .= $this->displayLinks( $args['start'], $this->args['search'] );
			$html .= $this->displayBySubject( $limit );
		} // otherwise, no pagination links
		else {
			$html .= $this->displayBySubject( $limit );
		}
		echo $html;

	}

	/**
	 * @return string $html
	 */
	public function displayTitlesByType() {
		$html = '';

		// TODO: Implement displayTitlesByType()

		echo $html;
	}

	/**
	 * Displays HTML of books, based on a common subject area
	 *
	 * @param int $limit
	 *
	 * @return string $html
	 */
	public function displayBySubject( $limit = 0 ) {
		$html = '';
		$i    = 0;
		$data = $this->books->getResponses();
		$size = ( $this->books->getSize() > $limit ) ? $limit : $this->books->getSize();

		$html .= "<ul class='no-bullets'>";
		// check if it's been reviewed
		while ( $i < $size ) {
			$title       = $this->metadataToCsv( $data[ $i ], 'dc.title' );
			$description = $this->metadataToCsv( $data[ $i ], 'dc.description.abstract' );
			$authors     = $this->metadataToCsv( $data[ $i ], 'dc.contributor.author' );
			$date        = date( 'M j, Y', strtotime( $this->metadataToCsv( $data[ $i ], 'dc.date.issued' ) ) );
			$desc        = ( strlen( $description ) > 500 ) ? mb_substr( $description, 0, 499 ) . ' <a href=?uuid=' . $data[ $i ]['uuid'] . '>...[more]</a>' : $description;

			$html .= '<li>';
			$html .= '<h4><a href="?uuid=' . $data[ $i ]['uuid'] . '">' . $title . '</a></h4>';
			//$html .= $this->getCustomMeta( $data_adj[ $i ] );
			$html .= '<b>Author(s):</b> ' . $authors . '<br>';
			$html .= '<b>Date Issued:</b> ' . $date . '<br>';
			$html .= '<p><b>Description:</b> ' . $desc . '</p>';
			$html .= '</li>';
			$i ++;
		}

		$html .= '</ul>';
		echo $html;

	}

	/**
	 * Looks for custom metadata
	 * returns HTML
	 *
	 * @param $data
	 */
	private function getCustomMeta( $data ) {
		// TODO: Implement getCustomMeta();
	}

	/**
	 *
	 * @param int $start_here
	 * @param string $search_term
	 *
	 * @return string $html
	 */
	private function displayLinks( $start_here, $search_term ) {
		$by_ten = 0;

		//reduce startHere to a multiple of 10
		$start_here = ( 10 * intval( $start_here / 10 ) );

		//reduce limit to an integer value
		$limit = intval( $this->books->getSize() / 10 );

		//if it is less than 10 or equal to 10, just return (all the links are on the page)
		if ( $limit == 0 || $this->books->getSize() == 10 ) {
			return;
		}
		$html = '<p>';
		//otherwise, produce as many links as there are results divided by 10
		while ( $limit >= 0 ) {
			if ( $start_here == $by_ten ) {
				$html .= '<strong>' . $by_ten . '</strong> | ';
			} else {
				$html .= "<a href='?start=" . $by_ten . '&subject=' . $this->args['subject'] . '&search=' . $search_term . "'>" . $by_ten . '</a> | ';
			}
			$by_ten = $by_ten + 10;
			$limit --;
		}
		$html .= ' <em>' . $this->books->getSize() . ' available results</em></p>';

		//return html blob
		echo $html;
	}

	/**
	 * Uses the Creative Commons API to return a properly
	 * formed license with Title, Author, Link
	 * @see https://api.creativecommons.org/docs/readme_15.html
	 *
	 * @param $dspace_array
	 * @param $authors
	 *
	 * @return type|mixed|string
	 */
	private function licensePicker( $dspace_array, $authors ) {

		$license  = $this->metadataToCsv( $dspace_array, 'dc.rights.uri' );
		$endpoint = 'https://api.creativecommons.org/rest/1.5/';
		$expected = array(
			'zero'     => array(
				'license'     => 'zero',
				'commercial'  => 'y',
				'derivatives' => 'y',
			),
			'by'       => array(
				'license'     => 'standard',
				'commercial'  => 'y',
				'derivatives' => 'y',
			),
			'by-sa'    => array(
				'license'     => 'standard',
				'commercial'  => 'y',
				'derivatives' => 'sa',
			),
			'by-nd'    => array(
				'license'     => 'standard',
				'commercial'  => 'y',
				'derivatives' => 'n',
			),
			'by-nc'    => array(
				'license'     => 'standard',
				'commercial'  => 'n',
				'derivatives' => 'y',
			),
			'by-nc-sa' => array(
				'license'     => 'standard',
				'commercial'  => 'n',
				'derivatives' => 'sa',
			),
			'by-nc-nd' => array(
				'license'     => 'standard',
				'commercial'  => 'n',
				'derivatives' => 'n',
			),
		);

		if ( $dspace_array && $license ) {
			$license = strtolower( $license );
			$license = explode( '/', $license );
			// get keys of expected values
			$keys = array_keys( $expected );
			// check license value exists and that it's value matches an expected license
			if ( $license[4] && isset( $license[4], $keys ) ) {
				$license = $license[4];
			} else {
				$license = 'Unknown license';
			}
			// proceed if license is one of the expected
			if ( isset( $license, $keys ) ) {
				$title = $this->metadataToCsv( $dspace_array, 'dc.title' );
				$lang  = $this->metadataToCsv( $dspace_array, 'dc.language' );
				;
				$key = array_keys( $expected[ $license ] );
				$val = array_values( $expected[ $license ] );

				// build the url
				$url = $endpoint . $key[0] . '/' . $val[0] . '/get?' . $key[1] . '=' . $val[1] . '&' . $key[2] . '=' . $val[2] .
					   '&creator=' . urlencode( $authors ) . '&title=' . urlencode( $title ) . '&locale=' . $lang;

				// go and get it
				$c = curl_init( $url );
				curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $c, CURLOPT_TIMEOUT, 20 );

				$response = curl_exec( $c );
				curl_close( $c );

				// if server response is not ok, return semi-meaningful string
				if ( false == $response ) {
					return 'license information currently unavailable from https://api.creativecommons.org/rest/1.5/';
				}

				// in case response is not xml/invalid xml
				libxml_use_internal_errors( true );

				$obj = simplexml_load_string( $response );
				$xml = explode( "\n", $response );

				// catch errors, give them to php log
				if ( ! $obj ) {
					$errors = libxml_get_errors(); //@TODO do something with errors
					foreach ( $errors as $error ) {
						$msg = $this->displayXmlError( $error, $xml );
						\error_log( $msg, 0 );
					}
					libxml_clear_errors();
				}

				// ensure instance of SimpleXMLElement, to avoid fatal error
				if ( is_object( $obj ) ) {
					$result = $this->getWebLicenseHtml( $obj->html );
				} else {
					$result = '';
				}

				return $result;
			}
		}
	}

	/**
	 * Helper function customizes html response from cc api
	 *
	 * @param \SimpleXMLElement $response
	 *
	 * @return string $html
	 */
	private function getWebLicenseHtml( \SimpleXMLElement $response ) {
		$html = '';

		if ( is_object( $response ) ) {
			$content = $response->asXML();
			$content = trim( str_replace( array(
				'<p xmlns:dct="http://purl.org/dc/terms/">',
				'</p>',
				'<html>',
				'</html>',
			), array( '', '', '', '' ), $content ) );
			$content = preg_replace( '/http:\/\/i.creativecommons/iU', 'https://i.creativecommons', $content );

			$html = '<div class="license-attribution" xmlns:cc="http://creativecommons.org/ns#"><p class="muted" xmlns:dct="http://purl.org/dc/terms/">'
					. rtrim( $content, '.' ) . ', except where otherwise noted.</p></div>';
		}

		return html_entity_decode( $html, ENT_XHTML, 'UTF-8' );
	}

	/**
	 * Helper function to list file attachments
	 * for a textbook
	 *
	 * @param $dspace_array
	 *
	 * @return string $html
	 */
	private function displayBitStreamFiles( $dspace_array ) {
		$html = '';
		// return empty, return early
		if ( ! is_array( $dspace_array ) || ! isset( $dspace_array['bitstreams'] ) ) {
			return $html;
		}
		$env      = Config::getInstance()->get();
		$base_url = parse_url( $env['dspace']['url'], PHP_URL_HOST );

		$html .= '<ol>';
		// just deals with metadata
		foreach ( $dspace_array['bitstreams'] as $item ) {
			if ( 0 === strcmp( $item['format'], 'License' ) ) {
				continue;
			}
			$html .= "<li><a href='//" . $base_url . $item['retrieveLink'] . "'><i class='glyphicon glyphicon-download'></i>  Download </a>";
			$html .= $this->addLogo( $item['mimeType'] ) . $item['format'];
			$html .= \BCcampus\Utility\determine_file_size( $item['sizeBytes'] ) . '</li>';
		}

		$html .= '</ol>';

		return $html;

	}

	/**
	 * Helper function to display an appropriate logo
	 * for different mime types
	 *
	 * @param $mimeType
	 *
	 * @return string $logo
	 */
	private function addLogo( $mimeType ) {
		if ( empty( $mimeType ) || ! is_string( $mimeType ) ) {
			return '';
		}
		$copyright = 'This icon is licensed under a Creative Commons Attribution 3.0 License. Copyright Yusuke Kamiyamane.';

		// get the logo image for each mimetype we an image for
		switch ( $mimeType ) {
			case 'application/pdf':
				$logo = "<img src='" . OTB_URL . "assets/images/document-pdf.png' alt='PDF file. " . $copyright . "'/>";
				break;
			case 'application/epub+zip':
				$logo = "<img src='" . OTB_URL . "assets/images/document-epub.png' alt='EPUB file. " . $copyright . "'/>";
				break;
			case 'application/zip':
				$logo = "<img src='" . OTB_URL . "assets/images/document-zipper.png' alt='ZIP file. " . $copyright . "'/>";
				break;
			case 'application/rdf+xml; charset=utf-8':
				$logo = "<img src='" . OTB_URL . "assets/images/document-xml.png' alt='XML file. " . $copyright . "'/>";
				break;
			case 'application/mobi':
				$logo = "<img src='" . OTB_URL . "assets/images/document-mobi.png' alt='MOBI file. " . $copyright . "'/>";
				break;
			case 'application/html':
				$logo = "<img src='" . OTB_URL . "assets/images/document-code.png' alt='HTML file. " . $copyright . "'/>";
				break;
			case 'application/x-tex':
				$logo = "<img src='" . OTB_URL . "assets/images/document-tex.png' alt='TEX file. " . $copyright . "'/>";
				break;
			case 'application/vnd.oasis.opendocument.text':
				$logo = "<img src='" . OTB_URL . "assets/images/document-word.png' alt='ODT file. " . $copyright . "'/>";
				break;
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				$logo = "<img src='" . OTB_URL . "assets/images/document-word.png' alt='WORD file. " . $copyright . "'/>";
				break;
			case 'application/msword':
				$logo = "<img src='" . OTB_URL . "assets/images/document-word.png' alt='WORD file. " . $copyright . "'/>";
				break;
			case 'text/richtext':
				$logo = "<img src='" . OTB_URL . "assets/images/document-word.png' alt='RTF file. " . $copyright . "'/>";
				break;
			default:
				$logo = "<img src='" . OTB_URL . "assets/images/document.png' alt='Document File. " . $copyright . "'/>";
				break;
		}

		return $logo;
	}

	/**
	 *
	 * @param array $error
	 * @param type $xml
	 *
	 * @return type
	 */
	protected function displayXmlError( $error, $xml ) {
		$return = $xml[ $error->line - 1 ];
		$return .= str_repeat( '-', $error->column );

		switch ( $error->level ) {
			case LIBXML_ERR_WARNING:
				$return .= "Warning $error->code: ";
				break;
			case LIBXML_ERR_ERROR:
				$return .= "Error $error->code: ";
				break;
			case LIBXML_ERR_FATAL:
				$return .= "Fatal Error $error->code: ";
				break;
		}

		$return .= trim( $error->message ) .
				   "  Line: $error->line" .
				   "  Column: $error->column";

		if ( $error->file ) {
			$return .= "  File: $error->file";
		}

		return "$return--------------------------------------------END";
	}

	/**
	 * Extracts the value of metadata items returned by the
	 * Dspace API. A CSV is returned if there is more than one value (example: authors)
	 *
	 * @param $dspace_array
	 * @param $dc_type
	 *
	 * @return string $list
	 */
	private function metadataToCsv( $dspace_array, $dc_type ) {
		$expected = array(
			'dc.contributor.advisor',
			'dc.contributor.author',
			'dc.contributor.editor',
			'dc.contributor.illustrator',
			'dc.contributor.other',
			'dc.contributor',
			'dc.coverage.spatial',
			'dc.coverage.temporal',
			'dc.creator',
			'dc.date.accessioned',
			'dc.date.available',
			'dc.date.copyright',
			'dc.date.created',
			'dc.date.issued',
			'dc.date.submitted',
			'dc.date.updated',
			'dc.date',
			'dc.description.abstract',
			'dc.description.provenance',
			'dc.description.sponsorship',
			'dc.description.statementofresponsibility',
			'dc.description.tableofcontents',
			'dc.description.uri',
			'dc.description.version',
			'dc.description',
			'dc.format.extent',
			'dc.format.medium',
			'dc.format.mimetype',
			'dc.format',
			'dc.identifier.citation',
			'dc.identifier.govdoc',
			'dc.identifier.isbn',
			'dc.identifier.ismn',
			'dc.identifier.issn',
			'dc.identifier.other',
			'dc.identifier.sici',
			'dc.identifier.slug',
			'dc.identifier.uri',
			'dc.identifier',
			'dc.language.iso',
			'dc.language.rfc3066',
			'dc.language',
			'dc.provenance',
			'dc.publisher',
			'dc.relation.haspart',
			'dc.relation.hasversion',
			'dc.relation.isbasedon',
			'dc.relation.isformatof',
			'dc.relation.ispartof',
			'dc.relation.ispartofseries',
			'dc.relation.isreferencedby',
			'dc.relation.isreplacedby',
			'dc.relation.isversionof',
			'dc.relation.replaces',
			'dc.relation.requires',
			'dc.relation.uri',
			'dc.relation',
			'dc.rights.holder',
			'dc.rights.license',
			'dc.rights.uri',
			'dc.rights',
			'dc.source.uri',
			'dc.source',
			'dc.subject.classification',
			'dc.subject.ddc',
			'dc.subject.lcc',
			'dc.subject.lcsh',
			'dc.subject.mesh',
			'dc.subject.other',
			'dc.subject',
			'dc.title.alternative',
			'dc.title',
			'dc.type',
			'dcterms.abstract',
			'dcterms.accessRights',
			'dcterms.accrualMethod',
			'dcterms.accrualPeriodicity',
			'dcterms.accrualPolicy',
			'dcterms.alternative',
			'dcterms.audience',
			'dcterms.available',
			'dcterms.bibliographicCitation',
			'dcterms.conformsTo',
			'dcterms.contributor',
			'dcterms.coverage',
			'dcterms.created',
			'dcterms.creator',
			'dcterms.date',
			'dcterms.dateAccepted',
			'dcterms.dateCopyrighted',
			'dcterms.dateSubmitted',
			'dcterms.description',
			'dcterms.educationLevel',
			'dcterms.extent',
			'dcterms.format',
			'dcterms.hasFormat',
			'dcterms.hasPart',
			'dcterms.hasVersion',
			'dcterms.identifier',
			'dcterms.instructionalMethod',
			'dcterms.isFormatOf',
			'dcterms.isPartOf',
			'dcterms.isReferencedBy',
			'dcterms.isReplacedBy',
			'dcterms.isRequiredBy',
			'dcterms.issued',
			'dcterms.isVersionOf',
			'dcterms.language',
			'dcterms.license',
			'dcterms.mediator',
			'dcterms.medium',
			'dcterms.modified',
			'dcterms.provenance',
			'dcterms.publisher',
			'dcterms.references',
			'dcterms.relation',
			'dcterms.replaces',
			'dcterms.requires',
			'dcterms.rights',
			'dcterms.rightsHolder',
			'dcterms.source',
			'dcterms.spatial',
			'dcterms.subject',
			'dcterms.tableOfContents',
			'dcterms.temporal',
			'dcterms.title',
			'dcterms.type',
			'dcterms.valid',
			'eperson.firstname',
			'eperson.language',
			'eperson.lastname',
			'eperson.phone',
		);
		$list     = '';
		// return empty, return early
		if ( ! is_array( $dspace_array ) || ! in_array( $dc_type, $expected ) ) {
			return $list;
		}

		// just deals with metadata
		foreach ( $dspace_array['metadata'] as $book ) {
			if ( 0 === strcmp( $book['key'], $dc_type ) ) {
				$list .= $book['value'] . ', ';
			}
		}

		return rtrim( $list, ', ' );

	}

}
