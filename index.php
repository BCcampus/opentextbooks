<?php

use BCcampus\OpenTextBooks\Controllers\Catalogue;
use BCcampus\OpenTextBooks\Controllers\Reviews;

include_once 'autoloader.php';

include( OTB_DIR . 'assets/templates/partial/style.php' );
?>
<style type="text/css">
	.solrContainer-fluid {
		padding-left: 0px;
		padding-right: 0px;
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
$args= [];
$default = [
	'type_of' => 'books',
	'lists' => 'latest_additions',
	'subject_class_level_2' => 'Guides,Toolkits',
	'limit' => 4
];

$merged = array_merge($default, $args );
new Catalogue\Otb( $merged );

