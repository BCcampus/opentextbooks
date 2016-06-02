<script src="//code.jquery.com/jquery.js"></script>
<script src="<?= OTB_URL; ?>assets/js/bootstrap.min.js"></script>
<script src="<?= OTB_URL; ?>assets/js/jquery.tablesorter.js"></script>
<script>
    jQuery(document).ready(function($)
        {
            $("#reviews").tablesorter({sortList: [[0,0]]});
            $("#opentextbc").tablesorter({sortList: [[0,0]]});
        }
    );
</script>
