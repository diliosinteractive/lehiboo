<?php 
$related_events = el_related_event_single( get_the_ID() );
$archive_type 	= el_archive_type_template( $_GET );


?>



<?php if( $related_events->have_posts() ): ?>
	<div class="event_related_wrap">
		<div class="event_related">

			<h3 class="title second_font"><?php esc_html_e("Related Events", "eventlist") ?></h3>
			<p class="desc"><?php esc_html_e("May you like events", "eventlist") ?></p>

			

				<div class="wrap_event owl-carousel owl-theme <?php echo esc_attr($archive_type); ?>">
					<?php while ( $related_events->have_posts() ) : $related_events->the_post(); ?>

						<?php el_get_template_part( 'content', 'event-'.sanitize_file_name( $archive_type ) ); ?>

					<?php endwhile; wp_reset_query(); // end of the loop. ?>

				</div>

			

		</div>
	</div>
<?php endif; ?>

