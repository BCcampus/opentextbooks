<?php
/**
 * Get open.bccampus.ca stats using matamos(piwik) REST API
 * Made searchable and pretty with datatables
 */
include_once 'autoloader.php';
include( OTB_DIR . 'assets/templates/partial/header.php' );
include( OTB_DIR . 'assets/templates/partial/head.php' );
?>

<div class="col-md-12">

	<?php

	// token to authenticate API request.
	$env        = include( OTB_DIR . '.env.php' );
	$token_auth = $env['piwik']['TOKEN_AUTH'];

	// call REST API and request the actions for idsite=12
	$url = $env['piwik']['SITE_URL'];
	$url .= "?module=API&method=Events.getAction&flat=1";
	$url .= "&idSite=12&period=range&date=2012-01-01,today";
	$url .= "&format=php&filter_limit=-1";
	$url .= "&token_auth=$token_auth";

	$fetched = file_get_contents( $url );
	$content = unserialize( $fetched );

	// error checking
	if ( ! $content ) {
		print( "Error, content fetched = " . $fetched );
	}

	?>

    <table id="stats" class="table table-bordered table-striped table-hover">

        <thead>
        <tr>
            <th class="col-xs-4">Book Title</th>
            <th class="col-xs-4">File Type</th>
            <th class="col-xs-4">Downloads</th>
        </tr>
        </thead>
		<?php
		foreach ( $content as $row ) {
			$title     = substr( $row['label'], 0, - 5 );
			$type      = strtoupper( substr( $row['label'], - 3, 3 ) );
			$downloads = $row['nb_events'];

			// give the file type an icon
			switch ( $type ) {
				case "PDF":
					$icon = "document-pdf";
					break;
				case "URL":
					$icon = "document-code";
					break;
				case "DOC":
					$icon = "document-word";
					break;
				case "ZIP":
					$icon = "document-zipper";
					break;
				case "XML":
					$icon = "document-xml";
					break;
				case "PUB":
					$icon = "document-epub";
					break;
				default;
					$icon = "document";
					break;
			}

			print( "<tr><td>$title</td> <td>$type <img src='" . OTB_URL . "assets/images/$icon.png'></td> <td>$downloads</td></tr>" );
		}
		?>

    </table>
</div>

<?php
include( OTB_DIR . 'assets/templates/partial/container-end.php' );
include( OTB_DIR . 'assets/templates/partial/scripts-stats.php' );
include( OTB_DIR . 'assets/templates/partial/footer.php' );
?>
