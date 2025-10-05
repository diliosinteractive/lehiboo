<?php if( ! defined( 'ABSPATH' ) ) exit();


$event_id 		= isset( $_REQUEST['ide'] ) ? wp_unslash( $_REQUEST['ide'] ) : '';
$seat_option 	= get_post_meta( $event_id, OVA_METABOX_EVENT.'seat_option', true );
$seating_map 	= get_post_meta( $event_id, OVA_METABOX_EVENT.'seating_map', true );

?>

<?php if ( ! empty( $seating_map ) && $seat_option == 'simple' ): ?>
	
	<div class="el_seating_map_wrap">
		<button type="button" class="el_view_seating_map">
			<span class="text">
				<?php esc_html_e( 'View Global Regional Image', 'eventlist' ); ?>
			</span>
			<span class="icon"><i class="fas fa-minus"></i></span>
			</button>
		<div class="seating_map">
			<?php echo wp_get_attachment_image( $seating_map, 'large' ); ?>
		</div>
	</div>

<?php endif; ?>