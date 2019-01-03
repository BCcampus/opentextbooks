<?php
/**
 * copy and paste master template for new pages
 */

use BCcampus\OpenTextBooks\Controllers\Webform;

include_once 'autoloader.php';

$wf_args = [
	'type_of' => 'webform_summary',
];

new Webform\Adoption( $wf_args );



