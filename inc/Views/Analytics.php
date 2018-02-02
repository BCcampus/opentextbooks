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

class Analytics {

	/**
	 * @var Models\Piwik
	 */
	private $data;

	/**
	 * Analytics constructor.
	 *
	 * @param Models\Piwik $data
	 */
	public function __construct( Models\Piwik $data ) {

		if ( is_object( $data ) ) {
			$this->data = $data;
		}

	}

	/**
	 * @param $num_of_books
	 */
	public function displayOpenSummary( $num_of_books ) {
		$segment_title = \BCcampus\Utility\url_encode( 'pageTitle==Find Open Textbooks | BCcampus OpenEd Resources' );
		$page_visits   = $this->data->getVisits( $segment_title );
		$visits        = $this->data->getVisits();
		$percentage    = round( 100 * ( $page_visits / $visits ) );
		//image accepted values are: 'evolution', 'verticalBar', 'pie' and '3dPie'
		$graphType   = 'verticalBar';
		$apiModule   = 'UserCountry';
		$apiAction   = 'getRegion';
		$image_graph = $this->data->getImageGraph( $apiModule, $apiAction, $graphType );

		$html = "
		<h2>Summary</h2>
            <h4>Number of books in the collection: <b>{$num_of_books}</b></h4>
            <h4>Number of visits to the site in the last 4 months: <b>{$visits}</b></h4>
            <h4>Number of visits to the page 'find-open-textbooks': <b>{$page_visits}</b>
            
                <a class='btn btn-default' type='button' tabindex='0' data-target='#region' data-toggle='modal'
                   title='Which Region'>Which regions?</a></h4>
                  <div class='modal fade' id='region' tabindex='-1' role='dialog' aria-labelledby='region'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span
                                    aria-hidden='true'>&times;</span></button>
                            <h4 class='modal-title' id='myModalLabel'>Location of site visitors</h4>
                        </div>
                        ";
		$html .= "<div class='modal-body'>
                           <img src='{$image_graph}'/>
                        </div>
                    </div>
                </div>
            </div>";
		$html .= "<hr><h3>Percentage of total visits to the page find-open-textbooks: </h3>
            <div class='progress'>
                <div class='progress-bar progress-bar-success progress-bar-striped active' role='progressbar'
                     aria-valuemin='0'
                     aria-valuenow='{$page_visits}' aria-valuemax='{$visits}'
                     style='width:{$percentage}%;'>{$percentage}%
                </div>
            </div>";

		echo $html;

	}

	/**
	 * @param $num_of_books
	 */
	public function displayAdoptionsByVisits( $num_of_books ) {
		$multi     = $this->data->getMultiSites();
		$low_prob  = 0.0006; // 1 out of every 1500
		$high_prob = 0.002; // 1 out of every 500
		$total     = array(
			'low'    => 0,
			'high'   => 0,
			'visits' => 0,
			'count'  => count( $multi ),
		);
		$html      = '';
		$range     = $this->data->getDateRange();
		$days      = round( ( time() - strtotime( $range['start'] ) ) / 84600, 2 );

		foreach ( $multi as $site ) {
			$total['low']    = round( $total['low'] + ( $site['visits'] * $low_prob ) );
			$total['high']   = round( $total['high'] + ( $site['visits'] * $high_prob ) );
			$total['visits'] = $total['visits'] + $site['visits'];
		}

		// Predictions
		$freq_of_visits   = round( $total['visits'] / $days, 2 );
		$low_prob_future  = ( 0 == $freq_of_visits ) ? 0 : 24 * ( round( 1500 / $freq_of_visits, 2 ) );
		$high_prob_future = ( 0 == $freq_of_visits ) ? 0 : 24 * ( round( 500 / $freq_of_visits, 2 ) );

		$html .= "<hr><h2>Likely adoptions</h2><h3>Based on visits <a class='btn btn-default' type='button' tabindex='0' data-target='#likely' data-toggle='modal'
                   title='Likely adoptions explained'>What is this?</a></h3></h3><h4>Date range: {$range['start']} - {$range['end']}</h4><h5>Site: opentextbc.ca</h5><table class='table table-striped'><tbody>";
		$html .= "<tr><td>Number of books in the collection</td><td>{$num_of_books}</td></tr>";
		$html .= "<tr><td>Number of web-based books <small><a href='//opentextbc.ca'>opentextbc.ca <i class='glyphicon glyphicon-new-window small'></a></small></td><td>{$total['count']}</td></tr>";
		$html .= "<tr><td>Number of visits to all {$total['count']} web-based books <small><a href='//opentextbc.ca'>opentextbc.ca <i class='glyphicon glyphicon-new-window small'></a></small></td><td>{$total['visits']}</td></tr>";
		$html .= "<tr><td>Number of likely adoptions in the last 4 months <small><a href='//opentextbc.ca'>opentextbc.ca <i class='glyphicon glyphicon-new-window small'></a></small></td><td>{$total['low']} - {$total['high']}</td></tr>";
		$html .= "<tr><td>Predictions <small><a href='//opentextbc.ca'>opentextbc.ca <i class='glyphicon glyphicon-new-window small'></a></small></td><td>1 adoption is likely to occur every {$high_prob_future} - {$low_prob_future} hours</td></tr>";

		$html .= '</tbody></table>';
		$html .= "<div class='modal fade' id='likely' tabindex='-1' role='dialog' aria-labelledby='likely'>	
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span
                                    aria-hidden='true'>&times;</span></button>
                            <h4 class='modal-title' id='myModalLabel'>Likely adoptions based on visits</h4>
                        </div>";
		$html .= "<div class='modal-body'>
                           <dl><dt>Assumptions</dt><dd>Knowing that an adoption is not possible without first downloading a file or viewing a webpage, this analysis assumes a correlation 
                           between online activities (visits) and adoption. Since there is no way of confirming what percentage of visits translates to an actual adoption, adjusting the 
                           probability is going to affect both the number of adoptions counted and the prediction of future adoptions. A liberal estimate is that 1 in every 500 visits <b>(0.002)</b> 
                           translates to an adoption, and a conservative estimate is that 1 in every 1500 <b>(0.0006)</b> visits translates to an adoption.</dd>
                           <dt>Visits vs Downloads</dt><dd>Downloading a file is treated as a different measurement of a likely adoption than a 'visit' to a web-based book. 
                           For instance, when faculty members and students access a web-based book throughout the duration of the course, the volume of tracked events will be higher than they would be with a downloaded file. 
                           Thus, the probability is adjusted to make a more realistic estimate of how many visits translates to 1 adoption. </dd></dl>
                        </div>
                    </div>
                </div>
            </div>";

		echo $html;

	}

	/**
	 * @param $num_of_books
	 */
	public function displayAdoptionsByDownloads( $num_of_books ) {
		$html       = '';
		$cumulative = 0;
		$num_books  = count( $this->data->getNumDownloads() );
		$range      = $this->data->getDateRange();
		$days       = round( ( time() - strtotime( $range['start'] ) ) / 84600, 2 );
		foreach ( $this->data->getNumDownloads() as $download ) {
			$cumulative = $cumulative + $download;
		}

		// Prediction
		$freq_of_downloads  = round( $cumulative / $days, 2 );
		$low_prob_adoption  = round( 0.02 * $cumulative );
		$high_prob_adoption = round( 0.1 * $cumulative );
		$low_prob_future    = ( 0 == $freq_of_downloads ) ? 0 : 24 * ( round( 50 / $freq_of_downloads, 2 ) );
		$high_prob_future   = ( 0 == $freq_of_downloads ) ? 0 : 24 * ( round( 10 / $freq_of_downloads, 2 ) );

		$html .= "<h3>Based on downloads <a class='btn btn-default' type='button' tabindex='0' data-target='#likely-downloads' data-toggle='modal'
                   title='Likely adoptions explained'>What is this?</a></h3></h3><h4>Date range: {$range['start']} - {$range['end']}</h4><h5>Site: opentextbc.ca</h5><table class='table table-striped'><tbody>";
		$html .= "<tr><td>Number of books in the collection</td><td>{$num_of_books}</td></tr>";
		$html .= "<tr><td>Number of web-based books <small><a href='//opentextbc.ca'>opentextbc.ca <i class='glyphicon glyphicon-new-window small'></i></a></small></td><td>{$num_books}</td></tr>";
		$html .= "<tr><td>Number of downloads of all {$num_books} web-based books <small><a href='//opentextbc.ca'>opentextbc.ca <i class='glyphicon glyphicon-new-window small'></a></small></td><td>{$cumulative}</td></tr>";
		$html .= "<tr><td>Number of likely adoptions in the last 4 months <small><a href='//opentextbc.ca'>opentextbc.ca <i class='glyphicon glyphicon-new-window small'></a></small></td><td>{$low_prob_adoption} - {$high_prob_adoption}</td></tr>";
		$html .= "<tr><td>Predictions <small><a href='//opentextbc.ca'>opentextbc.ca <i class='glyphicon glyphicon-new-window small'></a></small></td><td>1 adoption is likely to occur every {$high_prob_future} - {$low_prob_future} hours</td></tr>";

		$html .= '</tbody></table>';
		$html .= "<div class='modal fade' id='likely-downloads' tabindex='-1' role='dialog' aria-labelledby='likely-downloads'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span
                                    aria-hidden='true'>&times;</span></button>
                            <h4 class='modal-title' id='myModalLabel'>Likely adoptions based on downloads</h4>
                        </div>";
		$html .= "<div class='modal-body'>
                           <dl><dt>Assumptions</dt><dd>Knowing that an adoption is not possible without first downloading a file or viewing a webpage, this analysis assumes a correlation 
                           between online activities (downloads) and adoption. Since there is no way of confirming what percentage of downloads translates to an actual adoption, adjusting the 
                           probability is going to affect both the number of adoptions counted and the prediction of future adoptions. A liberal estimate is that 1 in every 10 downloads <b>(0.01)</b> 
                           translates to an adoption, and a conservative estimate is that 1 in every 50 <b>(0.02)</b> downloads translates to an adoption.</dd>
                           <dt>Visits vs Downloads</dt><dd>Downloading a file is treated as a different measurement of a likely adoption than a 'visit' to a web-based book. 
                           For instance, a faculty member can download a file once (which is tracked) then make it available to their class through an LMS (not tracked). 
                           Conversely, a faculty member can download the file once then instruct their class to download the same file from the same location, in which case all 
                           35 students downloading the file will trigger a tracked event. In both scenarios, downloading a file has less tracked events than visiting a website.</dd></dl>
                        </div>
                    </div>
                </div>
            </div>";

		echo $html;
	}

	/**
	 * @param $num_of_books
	 *
	 * @throws \Exception
	 */
	public function displayOpenTextSummary( $num_of_books ) {

		$multi   = $this->data->getMultiSites();
		$flipped = array_flip( $this->data->getPublicOpentextbc() );
		$range   = $this->data->getDateRange();

		$html = "<div id='table-responsive'>";
		$html .= "<table id='opentextbc' class='table table-responsive table-striped table-hover table-condensed tablesorter'>";
		$html .= "<caption>Stats below based on the date range: {$range['start']} to {$range['end']}</caption>";
		$html .= "<thead><tr>
        <th>Title&nbsp;<i class='glyphicon glyphicon-sort'></i></th>
        <th>Num of Visits&nbsp;<i class='glyphicon glyphicon-sort'></i></th>
        <th>Num Actions<i class='glyphicon glyphicon-sort'></i></th>
        <th>Num Pageviews<i class='glyphicon glyphicon-sort'></i></th>
        <th>Download Stats</th>
        </tr></thead><tbody>";

		$otb_count = 0;
		foreach ( $multi as $site ) {
			if ( array_key_exists( $site['path'], $flipped ) ) {
				$otb_count ++;
				$html .= '<tr>';
				$html .= "<td><a href='https://opentextbc.ca/{$site['path']}' target='_blank'><i class='glyphicon glyphicon-book'></i></a> — {$site['label']}</td>";
				$html .= "<td>{$site['visits']}</td>";
				$html .= "<td>{$site['actions']}</td>";
				$html .= "<td>{$site['pageviews']}</td>";
				$html .= "<td><a href='" . OTB_URL . "analytics.php?site_id={$site['id']}&view=single'><i class='glyphicon glyphicon-stats'></i></a></td>";
				$html .= '</tr>';
			}
		}

		$html .= '</tbody></table></div>';

		$summary  = '<h2>Summary</h2>';
		$summary  .= "<h4>Number of books in the collection: <b>{$num_of_books}</b></h4>";
		$summary  .= "<h4>Number of books in Pressbooks: <b>{$otb_count}</b></h4>";
		$otb_perc = round( 100 * ( $otb_count / $num_of_books ) );
		$summary  .= "<hr><h3>Percentage of books in the collection that have been imported into Pressbooks:</h3>
                <div class='progress'>
                <div class='progress-bar progress-bar-success progress-bar-striped active' role='progressbar' aria-valuemin='0'
                     aria-valuenow='{$otb_count}' aria-valuemax='{$num_of_books}'
                     style='width:{$otb_perc}%;'>{$otb_perc}%</div>
                </div>";

		echo $summary;
		echo $html;

	}

	/**
	 * @param $range_start
	 * @param array $book_data
	 */
	public function displayOpenSingleBook( $range_start, array $book_data ) {
		$days                   = round( ( time() - strtotime( $range_start ) ) / 84600, 2 );
		$event_actions_resource = array();
		$segment                = 'eventAction==' . urlencode( $book_data['name'] );
		$event_actions          = $this->data->getEventActions( $segment );

		// iterate through outlinks generated on open site
		if ( is_object( $event_actions[0] ) ) {
			foreach ( $event_actions[0]->subtable as $obj ) {
				$event_actions_resource[ $obj->label ] = $obj->nb_events;
			}
		}

		$html          = '<h1>open.bccampus.ca</h1><table class="table table-striped">';
		$html          .= "<caption>How many times was each resource downloaded since {$range_start}?</caption>";
		$num_downloads = 0;
		foreach ( $event_actions_resource as $label => $num ) {

			$num_downloads += $num;
			// get the description of the resource
			$html .= '<tr><td><i>' . $label . '</i></td><td><b>' . $num . '</b> times</td></tr>';
		}
		$html .= '</table>';

		// Prediction
		$freq_of_downloads  = round( $num_downloads / $days, 2 );
		$low_prob_adoption  = ( 0.02 * $num_downloads );
		$high_prob_adoption = ( 0.1 * $num_downloads );
		$low_prob_future    = ( 0 == $freq_of_downloads ) ? 0 : round( 50 / $freq_of_downloads, 2 );
		$high_prob_future   = ( 0 == $freq_of_downloads ) ? 0 : round( 10 / $freq_of_downloads, 2 );

		$html .= $this->displayPredictions( $days, $num_downloads, $freq_of_downloads, $low_prob_adoption, $high_prob_adoption, $low_prob_future, $high_prob_future );
		echo $html;
	}

	public function displaySingleSite( $range_start ) {
		$days = round( ( time() - strtotime( $range_start ) ) / 84600, 2 );

		$downloads     = $this->data->getEventName();
		$html          = '<h1>opentextbc.ca</h1><table class="table table-striped">';
		$html          .= "<caption>How many times was each resource downloaded since {$range_start}?</caption>";
		$num_downloads = 0;
		foreach ( $downloads as $d ) {
			$num_downloads = $num_downloads + $d->nb_events;
			$html          .= "<tr><td>{$d->label}</td><td>{$d->nb_events}</td></tr>";
		}
		$html .= '</table>';

		// Prediction
		$freq_of_downloads  = round( $num_downloads / $days, 2 );
		$low_prob_adoption  = ( 0.02 * $num_downloads );
		$high_prob_adoption = ( 0.1 * $num_downloads );
		$low_prob_future    = ( 0 == $freq_of_downloads ) ? 0 : round( 50 / $freq_of_downloads, 2 );
		$high_prob_future   = ( 0 == $freq_of_downloads ) ? 0 : round( 10 / $freq_of_downloads, 2 );

		$html .= $this->displayPredictions( $days, $num_downloads, $freq_of_downloads, $low_prob_adoption, $high_prob_adoption, $low_prob_future, $high_prob_future );
		echo $html;

	}

	/**
	 * @param $days
	 * @param $num_downloads
	 * @param $freq_of_downloads
	 * @param $low_prob_adoption
	 * @param $high_prob_adoption
	 * @param $low_prob_future
	 * @param $high_prob_future
	 *
	 * @return string
	 */
	private function displayPredictions( $days, $num_downloads, $freq_of_downloads, $low_prob_adoption, $high_prob_adoption, $low_prob_future, $high_prob_future ) {
		$html = '<div class="row">
  <div class="col-sm-4 col-md-4">
    <div class="thumbnail"><h3>Frequency</h3>';
		$html .= "<div class='modal fade' id='frequency' tabindex='-1' role='dialog' aria-labelledby='frequency'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span
                                    aria-hidden='true'>&times;</span></button>
                            <h4 class='modal-title' id='myModalLabel'>Calculating Relative Frequency</h4>
                        </div>";
		$html .= "<div class='modal-body'>
                           <dl><dt>Relative frequency</dt><dd>Cumulative summation of  an action over time as a measurement of adoption or the likelihood of adoption favours books that were released 
                           earlier on in the process. A more accurate representation of how well a book may be doing is clicks/actions over a function of time. For instance, if a book was released 3 
                           years ago and only recorded 1000 downloads, it could be deemed less successful (1000/1095 days = 0.913) than a book that was released 1 year ago and received 1000 downloads 
                           (1000/365 days = 2.73). This measurement of frequency is relative to the time that the book has been available, providing a basis for better comparison between resources with 
                           varying online availability. It is presumed that the better the relative frequency rate, the more likely it is that an adoption will occur.</dd></dl>
                        </div>
                    </div>
                </div>
            </div>";
		$html .= '<h4>' . $freq_of_downloads . '</h4>
      <div class="caption">';
		$html .= '<div class="panel panel-info">';
		$html .= '<div class="panel-heading">Frequency of Downloads <a class="btn btn-default" type="button" tabindex="0" data-target="#frequency" data-toggle="modal"
                   title="Relative Frequency Explained">What is this?</a></div><div class="panel-body">';
		$html .= "<p>This book has been accessed at least <b>{$num_downloads}</b> times over the past <b>{$days}</b> days.</p>";
		$html .= "<p>The frequency of downloads for this book is <b>{$freq_of_downloads}</b> per day.</p>";
		$html .= '</div></div>';
		$html .= '</div>
    </div>
  </div>';
		$html .= '<div class="col-sm-4 col-md-4">
    <div class="thumbnail"><h3>Adoptions</h3>';
		$html .= "<div class='modal fade' id='adoptions' tabindex='-1' role='dialog' aria-labelledby='adoptions'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span
                                    aria-hidden='true'>&times;</span></button>
                            <h4 class='modal-title' id='myModalLabel'>Calculating Likely Adoptions</h4>
                        </div>";
		$html .= "<div class='modal-body'>
                           <dl>
                           <dt>Assumptions</dt><dd>Knowing that an adoption is not possible without first downloading a file or viewing a webpage, this analysis assumes a correlation 
                           between online activities (downloads) and adoption. Since there is no way of confirming what percentage of downloads or visits translates to an actual adoption, adjusting the 
                           probability is going to affect both the number of adoptions counted and the prediction of future adoptions. A conservative estimate
                            is that one in every 50 (0.02) downloads translates to an actual adoption and a more liberal estimate would have that number be 1 in every 10 (0.1). </dd>
                           <dt>Calculating Likely Adoptions</dt><dd>The number of likely adoptions is calculated by multiplying the probability of adoptions (1 in every 50 downloads = <b>0.02</b>) 
                           by the number of downloads (1000). So, <b>0.02</b> * 1000 = 20 adoptions. Modifying the probability that an adoption has occurred changes the number of likely 
                           adoptions. So, <b>0.1</b> * 1000 = 100 adoptions.</dd>
                           </dl>
                        </div>
                    </div>
                </div>
            </div>";
		$html .= '<h4>' . $low_prob_adoption . ' - ' . $high_prob_adoption . ' </h4>
      <div class="caption">';
		$html .= '<div class="panel panel-info">';
		$html .= '<div class="panel-heading">Number of likely adoptions <a class="btn btn-default" type="button" tabindex="0" data-target="#adoptions" data-toggle="modal"
                   title="Counting Likely Adoptions Explained">What is this?</a></div><div class="panel-body">';
		$html .= "<p>If one in every 50 downloads is likely an adoption, then <b>{$low_prob_adoption}</b> 
adoptions have occurred.</p><p>If one in every 10 downloads is likely an adoption, then <b>{$high_prob_adoption}</b> adoptions have occurred over the past <b>{$days}</b> days.</p>";
		$html .= '</div></div>';
		$html .= '</div>
    </div>
  </div>
  </div>';

		$html .= '<h2>Predictions</h2>';
		$html .= '<div class="row">';
		$html .= '<div class="col-sm-4 col-md-4">
    <div class="thumbnail"><h3>Future</h3>';
		$html .= "<div class='modal fade' id='future' tabindex='-1' role='dialog' aria-labelledby='future'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span
                                    aria-hidden='true'>&times;</span></button>
                            <h4 class='modal-title' id='myModalLabel'>Calculating Future Adoptions</h4>
                        </div>";
		$html .= "<div class='modal-body'>
                           <dl>
                           <dt>Future Adoptions</dt><dd>These are calculated by using both the number of downloads that it takes for 1 adoption to occur and the relative frequency rate. 
                           If it is determined that 1 in every 50 downloads is likely an adoption, and the relative frequency rate is 2.73, then (50 / 2.73 = 18 days). Every 18 days, a likely adoption will occur. 
                           If it is determined that 1 in every 10 downloads is likely an adoption, then (10 / 2.73 = 3.6 days). Therefore, the range of future adoptions is 1 every 3 - 18 days.</dd>
                           </dl>
                        </div>
                    </div>
                </div>
            </div>";
		$html .= '<h4>1 every ' . $high_prob_future . ' - ' . $low_prob_future . ' days</h4>
      <div class="caption">';
		$html .= '<div class="panel panel-info">';
		$html .= '<div class="panel-heading">Future Adoptions <a class="btn btn-default" type="button" tabindex="0" data-target="#future" data-toggle="modal"
                   title="Relative Frequency Explained">What is this?</a></div><div class="panel-body">';
		$html .= "<p>If one in every 50 downloads is likely an adoption, then one adoption will occur every <b>{$low_prob_future}</b> days. 
    </p><p>If one in every 10 downloads is likely an adoption, then one adoption will occur every <b>{$high_prob_future}</b> days.";
		$html .= '</div></div>';
		$html .= '</div>
    </div>
  </div>
</div>
</div>';

		return $html;
	}

}
