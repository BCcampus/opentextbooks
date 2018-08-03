# Open Textbooks
[![Build Status](https://travis-ci.com/BCcampus/opentextbooks.svg?branch=dev)](https://travis-ci.com/BCcampus/opentextbooks)

The application is currently hosted on open.bccampus.ca, open.campusmanitoba.ca, openlibary.ecampusontario.ca and is built and maintained to support the [Open Textbook Project](https://open.bccampus.ca/2016/06/01/the-b-c-open-textbook-project-celebrates-another-milestone-151-open-textbooks/) by [BCcampus](https://bccampus.ca/)
- [books](https://open.bccampus.ca/find-open-textbooks/)
- [stats](https://open.bccampus.ca/open-textbook-stats)
- [sitemap](https://open.bccampus.ca/wp-content/opensolr/opentextbooks/sitemap.php)

The application is embedded in a WordPress environment, and while there are WP integrations, there are **zero** WordPress dependencies. It can be used as a standalone app.

## Requirements
- PHP version > 7

### Will be useful if you also have any instances of:
- an instance of LimeSurvey
- an instance of Matomo
- an instance of WordPress
- an instance of Equella

## Functionality
This application can consume API's from
 1. [Equella](https://github.com/equella/Equella) to display books from a collection OR
 2. [DSpace](http://dspace.org/) to display books from a collection 
 2. [LimeSurvey](https://www.limesurvey.org/) to display book reviews
 3. [Matomo Analytics](https://matomo.org/) to access book statistics

It also
 1. creates Google Scholar metadata for each book
 2. generates a sitemap of all textbooks which integrates with [Better WordPress Google XML Sitemaps](https://wordpress.org/plugins/bwp-google-xml-sitemaps/)
 3. pulls data from [Contact Form DB](https://wordpress.org/plugins/contact-form-7-to-database-extension/)
 4. waxes your neckbeard :neckbeard:

## Purpose
Built to support the wide dissemination of open textbooks.

## Quick Start (developers)
Uses [yarn](https://yarnpkg.com/en/) to build front end dependencies and [composer](https://getcomposer.org/) to build php dependencies. Once you've cloned the repo, you'll need to build:
- `yarn && yarn build` 
- `composer install`

###*THEN*:
- rename `config/environments/env.sample.php` to `.env.mydomain.com.php` 
- add config files per domain (ie. `.env.localhost.php`, `.env.myotherdomain.com.php`)
- modify config values to connect your instances of LimeSurvey, Wordpress, Equella and Matomo
- to test how the app will behave with a configuration file that is different than the domain you're testing with, use the override in `env.php` at the root of the site.
- ensure the web server user (apache, _www) has write permissions to `cache` directory and all subdirectories (`cache/webform`, `cache/analytics`, etc)

## Copyright and License
Unless otherwise noted, this code is copyright (c) 2012-2016 Brad Payne, released under a [GPLv3 license](https://www.gnu.org/licenses/gpl.html), or any later version

Otherwise noted:
- PHP Class `Cache` licensed under BSD, (compatible with GPL)
- PHP Class `SitemapAbstract` Licensed under GPLv3, or any later version
- Bootstrap licensed under MIT, (compatible with GPL)
- Table Sorter is dual licensed, MIT and GPL
