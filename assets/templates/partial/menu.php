<?php $alpha       = true;
	$summary       = false;
	$subject_areas = \BCcampus\OpenTextBooks\Controllers\Catalogue\Otb::getSubjectStats( $summary, $alpha );
?>
<div class="accordion mt-2" id="catalogue-menu">
	<ul class="accordion-group list-unstyled">
		<div class="accordion-group">
			<li class="accordion-heading"><a class="accordion-toggle" href="?subject=">All Subjects</a></li>
		</div>
		<?php foreach ( $subject_areas as $sub1 => $sub2 ) { ?>
			<div class="accordion-group">
				<?php $abr = substr( $sub1, 0, 4 ) ?>
				<?php $abr = str_replace( [ '/', ' ' ], '', $abr ); ?>
				<li class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#catalogue-menu" href="#collapse<?php echo( $abr ) ?>"><?php echo( $sub1 ) ?></a></li>
				<div id="collapse<?php echo( $abr ) ?>" class="accordion-body collapse">
					<ul class="children">
						<?php foreach ( $sub2 as $k => $num ) { ?>
							<li><a href="?subject=<?php echo( $k ) ?>"><?php echo( $k ) ?></a></li>
						<?php } ?>
					</ul>
				</div>
			</div>
		<?php } ?>
	</ul>
</div>
