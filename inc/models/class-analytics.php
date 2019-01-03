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
 *
 * This gets and stores data and is designed to work
 * with how Models/MatomoAPI was constructed. It makes one request to either
 * the MatomoAPI or persistent storage to get each piece of analytics.
 * Calling `getResponses()` will return the entire MatomoAPI Object, whereas
 *     calling public functions of this class will return either a stored
 *     version of the results, or make a request to MatomoAPI to get updated
 *     results
 */

namespace BCcampus\OpenTextBooks\Models;

use BCcampus\OpenTextBooks\Polymorphism;
use VisualAppeal\Matomo;

/**
 * Class Analytics
 *
 * @package BCcampus\OpenTextBooks\Models
 */
class Analytics extends Polymorphism\DataAbstract {

	private $matomo_api;
	private $location = 'cache/analytics';
	private $type     = 'txt';
	private $uid      = '';
	private $args     = [];

	/**
	 * Matomo constructor.
	 *
	 * @param Matomo $api
	 * @param $args
	 */
	public function __construct( Matomo $api, $args ) {

		if ( is_object( $api ) ) {
			$this->matomo_api = $api;
		}
		// needs a unique id, based on unique set of parameters passed to it
		if ( is_array( $args ) ) {
			foreach ( $args as $val ) {
				$this->uid = $this->uid . $val;
			}
		} else {
			// plan b
			$this->uid = rand( 100, 10000 );
		}
		$this->args = $args;

	}

	/**
	 * @return Matomo
	 */
	public function getResponses() {
		return $this->matomo_api;
	}

	/**
	 * @param string $segment
	 *
	 * @return string $visits
	 */
	public function getVisits( $segment = '' ) {
		$serialize = true;
		$file_name = $this->uid . 'visits' . $segment;
		$file_type = $this->type;

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$visits = $persistent_data->load();

		} else {
			$visits = $this->matomo_api->getVisits( $segment );
			$this->saveToStorage( $this->location, $file_name, $file_type, $visits, $serialize );
		}

		return $visits;
	}

	/**
	 * @param $api_module
	 * @param $api_action
	 * @param $graph_type
	 *
	 * @return string $image
	 */
	public function getImageGraph( $api_module, $api_action, $graph_type ) {
		$serialize = true;
		$file_name = $this->uid . 'imageGraph' . $api_module . $api_action . $graph_type;
		$file_type = $this->type;

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$image = $persistent_data->load();

		} else {
			$image = $this->matomo_api->getImageGraph( $api_module, $api_action, $graph_type, $output_type = '0', $columns = '', $labels = '', $show_legend = '1', $width = '780', $height = '', $font_size = '9', $legend_font_size = '', $aliased_graph = '1', $id_goal = '', $colors = '' ); //@codingStandardsIgnoreLine
			$this->saveToStorage( $this->location, $file_name, $file_type, $image, $serialize );
		}

		return $image;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getMultiSites() {
		$serialize   = true;
		$file_name   = $this->uid . 'multisite';
		$file_type   = $this->type;
		$multi_array = [];

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$multi_array = $persistent_data->load();

		} else {
			// get all sites from piwik
			$multi = $this->matomo_api->getMultiSites();
			// get all public sites from opentextbc
			$results = $this->getPublicOpentextbc();

			if ( ! empty( $results ) ) {
				$flipped = array_flip( $results );
			} else {
				throw new \Exception( '\BCcampus\OpenTextBooks\Models\Matomo\getMultiSites failed to retrieve books from opentextbc.ca API' );
			}

			if ( is_array( $multi ) ) {
				foreach ( $multi as $site ) {
					if ( false !== strpos( $site->main_url, 'opentextbc.ca' ) ) {
						$path = trim( parse_url( $site->main_url, PHP_URL_PATH ), '/' );

						// cross check that the book is marked as public
						if ( array_key_exists( $path, $flipped ) ) {
							$multi_array[] = [
								'label'     => $site->label,
								'id'        => $site->idsite,
								'path'      => $path,
								'visits'    => $site->nb_visits,
								'actions'   => $site->nb_actions,
								'pageviews' => $site->nb_pageviews,
							];
						}
					}
				}
			}
			$this->saveToStorage( $this->location, $file_name, $file_type, $multi_array, $serialize );
		}

		return $multi_array;
	}

	/**
	 * fetches all download events from each site
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getNumDownloads() {
		$results   = [];
		$serialize = true;
		$file_name = 'getnumdownloads';
		$file_type = $this->type;

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$results = $persistent_data->load();

		} else {
			// this works, but takes time to process
			foreach ( $this->getMultiSites() as $site ) {
				$total = 0;
				$this->matomo_api->setSiteId( $site['id'] );
				$tmp = $this->getEventName( $site['id'] );

				foreach ( $tmp as $obj ) {
					$total = $total + $obj->nb_events;
				}
				$results[ $site['id'] ] = $total;
			}
			$this->saveToStorage( $this->location, $file_name, $file_type, $results, $serialize );
		}

		return $results;
	}

	/**
	 * @return array
	 */
	public function getPublicOpentextbc() {
		$serialize        = true;
		$file_name        = 'opentextbc.ca';
		$file_type        = $this->type;
		$public_books_url = [];

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$public_books_url = $persistent_data->load();

		} else {
			$public_books = json_decode( file_get_contents( 'https://opentextbc.ca/api/v1/books' ), true );
			foreach ( $public_books['data'] as $book ) {
				if ( isset( $book['book_url'] ) ) {
					$path               = parse_url( $book['book_url'], PHP_URL_PATH );
					$public_books_url[] = trim( $path, '/' );
				}
			}

			$this->saveToStorage( $this->location, $file_name, $file_type, $public_books_url, $serialize );
		}

		return $public_books_url;
	}

	/**
	 * @param string $segment
	 *
	 * @return array|obj|bool|string|void
	 */
	public function getEventActions( $segment = '' ) {
		$serialize = true;
		$file_name = $this->uid . $segment;
		$file_type = $this->type;

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$actions = $persistent_data->load();

		} else {
			$actions = $this->matomo_api->getEventAction(
				$segment, 'eventName', [
					'expanded' => 1,
					'flat'     => 0,
				]
			);
			$this->saveToStorage( $this->location, $file_name, $file_type, $actions, $serialize );
		}

		return $actions;
	}

	/**
	 * @param string $uid
	 *
	 * @return array|bool|object
	 */
	public function getEventName( $uid = '' ) {
		$serialize = true;
		$file_name = ( empty( $uid ) ) ? $this->uid . 'geteventname' : $uid . 'geteventname';
		$file_type = $this->type;

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		if ( $persistent_data ) {
			$events = $persistent_data->load();

		} else {
			if ( intval( $this->uid ) !== intval( $this->args['site_id'] ) && empty( $uid ) ) {
				$this->matomo_api->setSiteId( $this->args['site_id'] );
			}
			$events = $this->matomo_api->getEventName();
			$this->saveToStorage( $this->location, $file_name, $file_type, $events, $serialize );
		}

		return $events;
	}

	/**
	 * @return mixed
	 */
	public function getDateRange() {
		$result['start'] = $this->args['range_start'];
		$result['end']   = $this->args['range_end'];

		return $result;
	}


}
