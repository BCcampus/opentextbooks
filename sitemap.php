<?php
/*
 * Create a sitemap for the open textbook catalogue
 */
include_once 'autoloader.php';
use BCcampus\OpenTextBooks\Controllers\Sitemap;

$r = new Sitemap\Textbooks();

header( 'Content-Type: text/xml' );
echo $r->xml;


