#Open Textbooks

The application is currently hosted on open.bccampus.ca and is built and maintained to support the [Open Textbook Project](https://open.bccampus.ca/2016/06/01/the-b-c-open-textbook-project-celebrates-another-milestone-151-open-textbooks/) by [BCcampus](https://bccampus.ca/)
- [books](https://open.bccampus.ca/find-open-textbooks/)
- [stats](https://open.bccampus.ca/open-textbook-stats)
- [sitemap](https://open.bccampus.ca/wp-content/opensolr/opentextbooks/sitemap.php)

The application is embedded in a WordPress environment, and while there are WP integrations, there are **zero** WordPress dependencies. It can be used as a standalone app.

##Requirements
- PHP version > 5 (though has not been tested with PHP 7)

###Will be useful if you also have any instances of:
- an instance of LimeSurvey
- an instance of Piwik
- an instance of WordPress
- an instance of Equella

##Functionality
This application can consume API's from
 1. A soon-to-be-open-source book repository (Equella) to display books from a collection
 2. [DSpace](http://dspace.org/) to display books from a collection 
 2. [LimeSurvey](https://www.limesurvey.org/) to display book reviews
 3. [Piwik Analytics](https://piwik.org/) to access book statistics

It also
 1. creates Google Scholar metadata for each book
 2. generates a sitemap of all textbooks which integrates with [Better WordPress Google XML Sitemaps](https://wordpress.org/plugins/bwp-google-xml-sitemaps/)
 3. pulls data from [Contact Form DB](https://wordpress.org/plugins/contact-form-7-to-database-extension/)
 4. waxes your neckbeard :neckbeard:

##Purpose
Built to support the wide dissemination of open textbooks.

##Quick Start
- download the zip files to a web server
- change `env.sample.php` to `.env.php`
- ensure apache has write permissions to `cache` directory and all subdirectories (`cache/webform`, `cache/analytics`, etc)
- modify values in `.env.php` to connect your instances of LimeSurvey, Wordpress, Equella and Piwik

##Copyright and License
Unless otherwise noted, this code is copyright (c) 2012-2016 Brad Payne, released under a [GPLv3 license](https://www.gnu.org/licenses/gpl.html), or any later version

Otherwise noted:
- PHP Class `Cache` licensed under BSD, (compatible with GPL)
- PHP Class `PiwikApi` licensed under Apache, (compatible with GPL)
- PHP Class `LimeSurveyApi` licensed under GPL, or any later version
- PHP Class `SitemapAbstract` Licensed under GPLv3, or any later version
- Bootstrap licensed under MIT, (compatible with GPL)
- Table Sorter is dual licensed, MIT and GPL
