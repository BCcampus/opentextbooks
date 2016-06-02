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
 *
 * A view can be any output representation of information, such as a chart or a diagram.
 * Multiple views of the same information are possible
 */

namespace BCcampus\OpenTextBooks\Views;

class Errors{

    protected $data = array();

    public function __construct($args)
    {
        if ( ! array( $args ) ){
            return $this->displayDefault();
        }
        $this->data = $args;
        $this->displayErrors();

    }

    protected function displayErrors(){
        $html = '';

        foreach( $this->data as $data ){
            $html .= "<pre>";
            $html .= $data;
            $html .= "</pre>";
        }
        echo $html;
    }

    protected function displayDefault(){
        $html = '';

        $html .= '<p class="warning">sorry, something went wrong</p>';

        echo $html;
    }
}