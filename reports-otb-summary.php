<?php
/**
 * copy and paste master template for new pages
 */
include_once 'autoloader.php';

use BCcampus\OpenTextBooks\Controllers\Webform;

$wf_args = [
	'type_of' => 'webform_summary',
];

new Webform\Adoption( $wf_args );



