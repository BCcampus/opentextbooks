<script src="<?php echo OTB_URL; ?>dist/scripts/jquery.min.js"></script>
<script src="<?php echo OTB_URL; ?>dist/scripts/bootstrap.min.js"></script>
<script src="<?php echo OTB_URL; ?>assets/js/jquery.tablesorter.js"></script>
<script>
	jQuery(document).ready(function($)
		{
			$("#reviews").tablesorter({sortList: [[0,0]]});
			$("#opentextbc").tablesorter({sortList: [[0,0]]});
		}
	);
</script>
