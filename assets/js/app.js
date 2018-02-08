import $ from 'jquery';

window.$ = window.jQuery = $;

jQuery(document).ready(function ($) {
        $("#reviews").tablesorter({sortList: [[0, 0]]});
        $("#opentextbc").tablesorter({sortList: [[0, 0]]});
    }
);