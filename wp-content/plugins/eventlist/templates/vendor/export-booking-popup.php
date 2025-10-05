<?php 
if ( !defined( 'ABSPATH' ) ) exit();

?>

<div id="el_export_booking_popup" class="el_export_popup_wrapper overlay">
	<div class="el_export_container">
		<input type="hidden" name="booking_ids" value="<?php echo json_encode( $booking_ids ); ?>">
		<a href="#" class="el_close_popup" data-id="el_export_booking_popup">&times;</a>
		<p class="description">
			<?php echo sprintf( __( 'There are a total of %d records', 'eventlist' ), count( $booking_ids ) ); ?>
		</p>


			<ol class="el_export_options">
				<li>
					<a href="#" class="el_export_all_in_one">
						<?php esc_html_e( 'Download all in 1 file', 'eventlist' ); ?>
					</a>
				</li>
				<li>
					<a href="#" class="el_export_multiple_files">
						<?php esc_html_e( 'Split into multiple files', 'eventlist' ); ?>
					</a>
				</li>
			</ol>
			
			<div class="el_enter_number_file">
				<p class="el_message"></p>

				<p class="el_description"><?php esc_html_e( 'Enter number of records in a file', 'eventlist' ); ?></p>
				<div class="el_inner_group">
					<input type="number" name="number_file" value="1" />
					<button type="button" class="el_export_file">
						<?php esc_html_e( 'Submit', 'eventlist' ); ?>
					</button>
				</div>
			</div>

			<div class="el_export_pagination"></div>
			
		</div>
	</div>
</div>