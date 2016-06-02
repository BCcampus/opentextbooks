<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2012-2016 Brad Payne <https://bradpayne.ca>
 * Date: 2016-05-31
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2012-2016, Brad Payne
 */

namespace BCcampus\OpenTextBooks\Views;

use BCcampus\OpenTextBooks\Views;

class BooksPdfRedirect
{
    /**
     * @var string
     */
    private $base_redirect_pdf_url = 'http://solr.bccampus.ca:8001/bcc/items/';

    public function __construct($args)
    {
        if(! array($args)){
            new Views\Errors(['msg' => 'Sorry, I don not have any arguments I can work with']);
        }

        $this->displayRedirect($args);
    }

    protected function displayRedirect($args){

        $url = $this->base_redirect_pdf_url . $args['uuid'] . '/1/?attachment.uuid='. $args['attachment_uuid'];

        header('Location:'.$url,true,302);
        exit();

    }
}