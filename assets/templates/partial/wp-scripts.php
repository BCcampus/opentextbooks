<script src="<?php echo OTB_URL; ?>dist/scripts/bootstrap.min.js"></script>
<script src="<?php echo OTB_URL; ?>dist/scripts/jquery.tablesorter.min.js"></script>
<script>
	jQuery(document).ready(function($)
		{
			$("#reviews").tablesorter({sortList: [[0,0]]});
			$("#opentextbc").tablesorter({sortList: [[0,0]]});
		}
	);
</script>
