<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
	

	$el_get_event_type = el_get_event_type();

	if( $el_get_event_type == 'online' ){ ?>

		<div class="event_location">
			<span class="event-icon"><i class="icon_pin_alt"></i></span>
			<?php esc_html_e( 'Online', 'eventlist' ); ?>
		</div>

	<?php } else{

		$location = get_the_terms( get_the_id(), 'event_loc' );

		$array_loc = array();

		if ( ! empty( $location ) && is_array( $location ) ) {
			foreach ($location as $key => $value) {
				$array_loc[$value->parent] = $value->term_id;
			}
		}
		
		krsort( $array_loc );

		
		$link = $name = "";
		$count_loc = 0;
		if (is_array($array_loc)) {
			$count_loc = count( $array_loc );
	?>

			<div class="event_location">
				<?php
				
				if ( !empty( $array_loc ) && is_array( $array_loc ) ) {

					$i = 0; $separator = ",";
					?>
					
					<span class="event-icon"><i class="icon_pin_alt"></i></span>

					<?php
						foreach ($array_loc as $loc) {
							$i++;
							$separator = ( $count_loc !== $i ) ? "," : "";
							$link = get_term_link($loc);
							// $name = $loc->name;
							$name = get_term( $loc )->name;
							?>
							
							<a href="<?php echo esc_url( $link ) ?>">
								<?php echo esc_html( $name ) ?>
							</a>
							<span class="separator">
								<?php echo esc_html( $separator ) ?>
							</span>

							<?php
						}
				}

				?>
			</div>
	<?php } } ?>

	