<?php
/**
 * copy and paste master template for new pages
 */
include_once 'autoloader.php';

use BCcampus\OpenTextBooks\Controllers\Catalogue;
use BCcampus\OpenTextBooks\Controllers\Analytics;
use BCcampus\OpenTextBooks\Models;

include( OTB_DIR . 'assets/templates/partial/header.php' );
include( OTB_DIR . 'assets/templates/partial/head.php' );
include( OTB_DIR . 'assets/templates/partial/error-level.php' );
include( OTB_DIR . 'assets/templates/partial/container-solr-start.php' );
?>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

	<?php

	if ( isset( $_GET['uuid'] ) ) {
		$_GET['type_of'] = 'book_stats';

		new Catalogue\OtbController( $_GET );

		$open_args = array(
			'site_id' => 12,
			'uuid'    => $_GET['uuid'],
		);

		new Analytics\PiwikController( $open_args );

	} elseif ( isset( $_GET['site_id'] ) ) {

		new Analytics\PiwikController( $_GET );

	}
	unset( $_GET );
	?>

</div>

<?php
include( OTB_DIR . 'assets/templates/partial/container-end.php' );
include( OTB_DIR . 'assets/templates/partial/scripts.php');
include( OTB_DIR . 'assets/templates/partial/footer.php' );
?>

