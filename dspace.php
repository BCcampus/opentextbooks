<?php

use BCcampus\OpenTextBooks\Controllers\Catalogue;

include_once 'autoloader.php';
include( OTB_DIR . 'assets/templates/partial/style.php' );
?>
<style type="text/css">
	.solrContainer-fluid {
		padding-left: 0px;
		padding-right: 0px;
	}

	#second-menu ul.nav > li > a {
		padding: 10px 0 0 0;
	}

	.post_content {
		padding-top: 0px;
	}
</style>
<?php
include( OTB_DIR . 'assets/templates/partial/container-solr-start.php' );
include( OTB_DIR . 'assets/templates/partial/menu.php' );
?>

<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12" itemscope itemtype="https://schema.org/Book">

	<?php
	$args            = $_GET;
	$args['type_of'] = 'books';


	new Catalogue\Dspace( $args );

	unset( $_GET );
	?>
</div>
<?php
include( OTB_DIR . 'assets/templates/partial/container-end.php' );
include( OTB_DIR . 'assets/templates/partial/scripts.php' );
?>
