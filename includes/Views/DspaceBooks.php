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
		$html        = '';
		$data        = $this->books->getResponses();
		$title       = $this->metadataToCsv( $data, 'dc.title' );
		$description = $this->metadataToCsv( $data, 'dc.description.abstract' );
		$authors     = $this->metadataToCsv( $data, 'dc.contributor.author' );
		$date        = date( 'M j, Y', strtotime( $this->metadataToCsv( $data, 'dc.date.issued' ) ) );

		$html .= "<h2 itemprop='name'>" . $title . "</h2>";
		$html .= "<p><strong>Description</strong>: <span itemprop='description'>" . $description . "</span></p>";
		$html .= "<p><strong>Author</strong>: <span itemprop='author copyrightHolder'>" . $authors . "</span></p>";
		$html .= "<p><strong>Adoption (faculty): </strong><a href='/adoption-of-an-open-textbook/'>Contact us if you are using this textbook in your course <i class='glyphicon glyphicon-book'></i></a></p>";
		$html .= "<p><strong>Adaptations: </strong><a href='/open-textbook-101/adapting-an-open-textbook/'>Support for adapting an open textbook <i class='glyphicon glyphicon-book'></i></a></p>";
		$html .= "<b><strong>Date Issued</strong></b>: <span itemprop='issued'>" . $date . "<br>";
		$html .= "<h3>Open Textbook(s):</h3>";
		$html .= $this->displayBitStreamFiles( $data );

		echo $html;
	}

	/**
	 * @return string
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
			$html .= "<h5>Available results: " . $this->books->getSize() . "</h5>";
		} else {
			$html .= "<h5>Available: <span style='color:red;'>sorry, your search returned no results</span></h5>";
		}
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

	/**
	 * @param int $start
	 * @param int $limit
	 */
	public function displayBySubject( $start = 0, $limit = 0 ) {
		$html = '';
		$i    = 0;
		$data = $this->books->getResponses();

		// account for different array structures resulting from
		// different API calls
		$data_adj = ( isset( $data['items'] ) ) ? $data['items'] : $data;
		$size     = count( $data_adj );

		// necessary to see the last record
		$start = ( $start == $size ? $start = $start - 1 : $start = $start );


		$html .= "<ul class='no-bullets'>";
		// check if it's been reviewed
		while ( $i < $size ) {
			$title       = $this->metadataToCsv( $data_adj[ $start ], 'dc.title' );
			$description = $this->metadataToCsv( $data_adj[ $start ], 'dc.description.abstract' );
			$authors     = $this->metadataToCsv( $data_adj[ $start ], 'dc.contributor.author' );
			$date        = date( 'M j, Y', strtotime( $this->metadataToCsv( $data_adj[ $start ], 'dc.date.issued' ) ) );
			$desc        = ( strlen( $description ) > 500 ) ? mb_substr( $description, 0, 499 ) . " <a href=?uuid=" . $data_adj[ $start ]['uuid'] . ">...[more]</a>" : $description;

			$html .= "<li>";
			$html .= "<h4><a href=?uuid=" . $data_adj[ $start ]['uuid'] . ">" . $title . "</a></h4>";
			//$html .= $this->getCustomMeta( $data_adj[ $start ] );
			$html .= "<b>Author(s):</b> " . $authors . "<br>";
			$html .= "<b>Date Issued:</b> " . $date . "<br>";
			$html .= "<p><b>Description:</b> " . $desc . "</p>";
			$html .= "</li>";
			$start ++;
			$i ++;
		}

		$html .= "</ul>";
		echo $html;

	}

	/**
	 * Looks for custom metadata
	 * returns HTML
	 *
	 * @param $data
	 */
	private function getCustomMeta( $data ) {
		// TODO: Implement displayLinks();
	}

	/**
	 * @param $start_here
	 * @param $search_term
	 */
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

	private function addLogo() {

		// TODO: Implement addLogo();

	}

	/**
	 * @param \SimpleXMLElement $response
	 */
	private function getWebLicenseHtml( \SimpleXMLElement $response ) {

		// TODO: Implement getWebLicenseHtml();

	}

	/**
	 * @param $dspace_array
	 *
	 * @return string
	 */
	private function displayBitStreamFiles( $dspace_array ) {
		$html = '';
		// return empty, return early
		if ( ! is_array( $dspace_array ) || ! isset( $dspace_array['bitstreams'] ) ) {
			return $html;
		}
		$env          = include( OTB_DIR . '.env.php' );
		$api_endpoint = $env['dspace']['SITE_URL'];
		$base_url     = parse_url( $api_endpoint, PHP_URL_HOST );

		$html .= '<ol>';
		// just deals with metadata
		foreach ( $dspace_array['bitstreams'] as $item ) {
			if ( 0 === strcmp( $item['format'], 'License' ) ) {
				continue;
			}
			$html .= "<li><a href='//" . $base_url . $item['retrieveLink'] . "'><i class='glyphicon glyphicon-download'></i>  Download </a>";
			$html .= $item['format'];
			$html .= \BCcampus\Utility\determine_file_size( $item['sizeBytes'] ) . "</li>";
		}

		$html .= '</ol>';

		return $html;

	}

	/**
	 * @param $dspace_array
	 * @param $dc_type
	 *
	 * @return string
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