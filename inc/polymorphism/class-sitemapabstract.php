<?php

/**
 * This is a a modified version of an original class BWP_Sitemaps_Sitemap
 * Original by Khang Minh from WP plugin BWP Sitemaps http://betterwp.net/
 * Copyright (c) 2015 Khang Minh
 * @modified by Brad Payne, 2016
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU GENERAL PUBLIC LICENSE VERSION 3.0 OR LATER
 *
 */

namespace BCcampus\OpenTextBooks\Polymorphism;

//use BCcampus\OpenTextBooks\Catalogue\EquellaFetch;

/**
 * Description of Sitemap
 *
 * @author bayne
 */
abstract class SitemapAbstract {

	/**
	 * @var
	 */
	public $xml;

	/**
	 * @var string
	 */
	protected $xml_root_tag = 'urlset';

	/**
	 * @var array
	 */
	protected $freq_to_pri = [
		'always' => 1.0,
		'hourly' => 0.8,
		'daily' => 0.7,
		'weekly' => 0.6,
		'monthly' => 0.4,
		'yearly' => 0.3,
		'never' => 0.2,
	];

	/**
	 * @var float
	 */
	protected $default_freq = 0.5;

	/**
	 * Sitemap namespaces
	 *
	 * @var array
	 */
	protected $xml_headers = [
		'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
		'xsi:schemaLocation' => 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd',
		'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
	];

	/**
	 * @var
	 */
	protected $now;

	/**
	 * Sitemap constructor.
	 */
	public function __construct() {

		$this->getXml();

	}


	/**
	 * How frequently the page is likely to change.
	 * This value provides general information to search engines and may not correlate exactly
	 * to how often they crawl the page. Valid values are: always, hourly, daily, weekly, monthly, yearly, never
	 *
	 * @param $lastmod
	 * @param $now
	 * @return float|string
	 */
	protected function calcChangeFreq( $lastmod, $now ) {
		if ( empty( $now ) ) {
			$now = time();
		}
		//set it to default
		$freq = $this->default_freq;

		$time = $this->now - strtotime( $lastmod );

		$freq = $time <= 30000000
			? ( $time > 2592000 ? 'monthly'
				: ( $time > 604800 ? 'weekly'
				: ( $time > 86400 ? 'daily'
				: ( $time > 43200 ? 'hourly'
				: 'always' ) ) ) )
			: 'yearly';

		return $freq;
	}

	/**
	 * The priority of this URL relative to other URLs on your site.
	 * Valid values range from 0.0 to 1.0.
	 * This value does not affect how your pages are compared to pages on other sites—it only lets the search engines know
	 * which pages you deem most important for the crawlers. The default priority of a page is 0.5.
	 *
	 * @param $freq
	 * @return float
	 * @internal param type $mod_date
	 */
	protected function calcPriority( $freq ) {
		if ( key_exists( $freq, $this->freq_to_pri ) ) {
			$score = $this->freq_to_pri[ $freq ];
		} else {
			$score = 0.1;
		}
		return $score;
	}

	/**
	 * Get xml body of a sitemap item
	 *
	 * @return string
	 */
	abstract protected function getXmlItemBody();

	/**
	 * Get an xml representation of the sitemap
	 *
	 * @return string
	 */
	public function getXml() {
		// use cached xml if available
		if ( ! is_null( $this->xml ) ) {
			return $this->xml;
		}

		$xml = '';

		$xml .= $this->getXmlHeader() . "\n\n";

		$xml .= $this->getXmlItemBody() . "\n";

		$xml .= $this->getXmlFooter();

		$this->xml = $xml;

		return $xml;
	}

	/**
	 * @return string
	 */
	public function getXmlHeader() {
		$xml = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n";

		$xml .= '<' . $this->xml_root_tag;

		foreach ( $this->xml_headers as $header => $value ) {
			$xml .= "\n\t" . $header . '="' . $value . '"';
		}

		$xml .= '>';

		return $xml;
	}

	/**
	 * @return string
	 */
	public function getXmlFooter() {
		return '</' . $this->xml_root_tag . '>';
	}

}
