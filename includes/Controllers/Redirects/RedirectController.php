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

namespace BCcampus\OpenTextBooks\Controllers\Redirects;

use BCcampus\OpenTextBooks\Views;
use BCcampus\OpenTextBooks\Models;

class RedirectController
{

    /**
     * Needs at least this, or nothing works
     *
     * @var array
     */
    private $defaultArgs = array(
        'type_of' => '',
        'uuid' => '',
        'attachment_uuid' => '',
    );

    /**
     * @var array
     */
    private $args = array();

    /**
     * @var array
     */
    private $expected = ['citation_pdf_url'];

    /**
     * RedirectController constructor.
     * @param $args
     */
    public function __construct($args)
    {
        // sanity check
        if (!is_array($args)) {
            // TODO: add proper error handling
            new Views\Errors(['msg' => 'Sorry, this does not pass the smell test']);
        }

        $args_get = array(
            // Strips characters that have a numerical value >127.
            'uuid' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FLAG_STRIP_HIGH
            ),
            // Strips characters that have a numerical value >127.
            'type_of' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FLAG_STRIP_HIGH,
            ),
            // Strips characters that have a numerical value >127.
            'attachment_uuid' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_FLAG_STRIP_HIGH,
            ),

        );

        // filter get input, delete empty values
        $get = (false !== filter_input_array(INPUT_GET, $args_get, false)) ? filter_input_array(INPUT_GET, $args_get, false) : '';

        // let the filtered get variables override the default arguments
        if (is_array($get)) {
            // filtered get overrides default
            $this->args = array_merge($this->defaultArgs, $get);
            // programmer arguments override everything
            $this->args = array_merge($this->args, $args);

        } else {
            // programmers can override everything if it's hardcoded
            $this->args = array_merge($this->defaultArgs, $args);
        }

        if (in_array($this->args['type_of'], $this->expected)) {
            $this->decider();
        } else {
            return 'no args';
        }


    }

    /**
     *
     */
    private function decider(){
        // check the uuid argument against a list of uuids
        if (!empty($this->args['uuid'])){
            $ok = $this->checkUuids();
            
            if ($ok){
                new Views\BooksPdfRedirect($this->args);
            }
        } else {
            new Views\Errors(['msg' => 'Sorry, I can not locate that uuid in our collection. P.S. Thanks for being a hard worker.']);
        }


    }

    /**
     * Verify that the uuid being requested is actually in our collection
     * 
     * @return bool
     */
    private function checkUuids(){
        $result = false;
        $rest_api = new Models\EquellaApi();
        $data = new Models\OtbBooks($rest_api, '');

        if (in_array($this->args['uuid'],$data->getUuids())){
            $result = true;
        }

        return $result;
        
    }

}