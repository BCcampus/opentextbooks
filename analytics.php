<?php
/**
 * copy and paste master template for new pages
 */

use BCcampus\OpenTextBooks\Controllers\Analytics;
use BCcampus\OpenTextBooks\Controllers\Catalogue;

include_once 'autoloader.php';

include( OTB_DIR . 'assets/templates/partial/header.php' );
include( OTB_DIR . 'assets/templates/partial/head.php' );
include( OTB_DIR . 'assets/templates/partial/container-solr-start.php' );
?>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

	<?php

	if ( isset( $_GET['uuid'] ) ) {
		$_GET['type_of'] = 'book_stats';

		new Catalogue\Otb( $_GET );

		$open_args = [
			'site_id' => 12,
			'uuid'    => $_GET['uuid'],
		];

		new Analytics\Analytics( $open_args );

	} elseif ( isset( $_GET['site_id'] ) ) {

		new Analytics\Analytics( $_GET );

	}
	unset( $_GET );
	?>

</div>

<?php
include( OTB_DIR . 'assets/templates/partial/container-end.php' );
include( OTB_DIR . 'assets/templates/partial/scripts.php' );
include( OTB_DIR . 'assets/templates/partial/footer.php' );
?>
