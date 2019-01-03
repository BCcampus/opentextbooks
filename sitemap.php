<?php
/*
 * Create a sitemap for the open textbook catalogue
 */
use BCcampus\OpenTextBooks\Controllers\Sitemap;

include_once 'autoloader.php';

$r = new Sitemap\Textbooks();

header( 'Content-Type: text/xml' );
echo $r->xml;


