<?php
// If not a WordPress Environment then
// rely on better webpack build, transpile, extract and concatenate
if ( ! defined( 'WPINC' ) ) { ?>
	<script src="<?php echo OTB_URL; ?>dist/scripts/manifest.js"></script>
	<script src="<?php echo OTB_URL; ?>dist/scripts/vendor.js"></script>
	<script async src="<?php echo OTB_URL; ?>dist/scripts/app.js"></script>
<?php } else { ?>
	<script src="<?php echo OTB_URL; ?>dist/scripts/popper.min.js"></script>
	<script src="<?php echo OTB_URL; ?>dist/scripts/bootstrap.min.js"></script>
	<script src="<?php echo OTB_URL; ?>dist/scripts/jquery.tablesorter.min.js"></script>
	<script>
		jQuery(document).ready(function ($) {
				$("#reviews").tablesorter({sortList: [[0, 0]]});
				$("#opentextbc").tablesorter({sortList: [[0, 0]]});
				$("#opentext").tablesorter({sortList: [[0, 0]]});
			}
		);
	</script>
<?php } ?>
