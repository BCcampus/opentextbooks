<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/dt-1.10.16/datatables.min.css"/>
<script src="//code.jquery.com/jquery.js"></script>
<script src="<?php echo OTB_URL; ?>assets/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs/dt-1.10.16/datatables.min.js"></script>
<script>
    jQuery(document).ready(function ($) {
            $('#stats').dataTable({
                "order": [[2, 'desc']]
            });
        }
    );
</script>
