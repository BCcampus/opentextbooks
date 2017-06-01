<?php

// discover the domain
$domain = parse_url(OTB_URL, PHP_URL_HOST );
// if it is local then report all errors
if(0 === strcmp( 'localhost', $domain )){
    ini_set('error_reporting','E_ALL' );
    ini_set('display_errors', false );
}

