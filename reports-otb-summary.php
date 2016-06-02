<?php
/**
 * copy and paste master template for new pages
 */
include_once 'autoloader.php';

use BCcampus\OpenTextBooks\Controllers\Webform;


$wf_args = array(
	'type_of' => 'webform_summary',
);

new Webform\AdoptionController( $wf_args );



