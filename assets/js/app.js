import $ from 'jquery';

window.$ = window.jQuery = $;
import "../../node_modules/tablesorter/dist/js/jquery.tablesorter.min.js";

jQuery(document).ready(function ($) {
        $("#reviews").tablesorter({sortList: [[0, 0]]});
        $("#opentextbc").tablesorter({sortList: [[0, 0]]});
    }
);