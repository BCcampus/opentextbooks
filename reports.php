<?php
/**
 * displays adoption reports from piwik, limesurvey and solr
 */
include_once 'autoloader.php';

use BCcampus\OpenTextBooks\Controllers\Reviews;
use BCcampus\OpenTextBooks\Controllers\Catalogue;
use BCcampus\OpenTextBooks\Controllers\Analytics;
use BCcampus\OpenTextBooks\Controllers\Webform;
use BCcampus\OpenTextBooks\Models;

include( OTB_DIR . 'assets/templates/partial/style.php' );
?>
<style type="text/css">
	article.page ul, article.post ul {
		padding: 0;
	}
	#second-menu ul.nav > li > a{
		padding: 10px 0 0 0;
	}
	.post_content{
		padding-top: 0px;
	}
</style>
<?php
include( OTB_DIR . 'assets/templates/partial/error-level.php' );
include( OTB_DIR . 'assets/templates/partial/container-start.php' );
include( OTB_DIR . 'assets/templates/partial/nav-stats.php' );

?>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

	<?php
	$args = $_GET;
	if ( ! isset( $_GET['type_of'] ) ) {
		$args['type_of'] = 'book_stats';
	}
	?>
	<div class="tab-content">

		<div role="tabpanel" id="webform_stats" class="tab-pane active">
			<img src="<?php echo OTB_URL ?>assets/images/webform.png" class="pull-right img-responsive img-rounded" alt="webform" />

			<?php

			$wf_args = array(
				'type_of' => 'webform_stats',
			);

			new Webform\AdoptionController( $wf_args );
			?>

		</div>

		<div role="tabpanel" id="opentext_stats" class="tab-pane">
			<img src="<?php echo OTB_URL ?>assets/images/opentext.png" class="pull-right img-responsive img-rounded" alt="open text bc site"/>

			<?php

			$opentext_args = array(
				'site_id' => 8,
			);
			new Analytics\PiwikController( $opentext_args );
			?>

		</div>

		<div role="tabpanel" id="open_stats" class="tab-pane">
			<img src="<?php echo OTB_URL ?>assets/images/open.png" class="pull-right img-responsive img-rounded" alt="open site"/>
			<?php
			$open_args = array(
				'site_id' => 12,
			);
			new Analytics\PiwikController( $open_args );
			new Catalogue\OtbController( $args );
			?>

		</div>

		<div role="tabpanel" id="review_stats" class="tab-pane">
			<img src="<?php echo OTB_URL ?>assets/images/reviews.png" class="pull-right img-responsive img-rounded" alt="lime survey site" />

			<?php
			$ls_args['type_of'] = 'review_stats';
			new Reviews\LimeSurveyController( $ls_args );
			?>

		</div>

		<?php
		unset( $_GET );
		?>

	</div>
</div>

<?php
include( OTB_DIR . 'assets/templates/partial/container-end.php' );
include( OTB_DIR . 'assets/templates/partial/scripts.php' );
?>
