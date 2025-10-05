<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php

$id = get_the_id();

$prefix = OVA_METABOX_EVENT;

$map_lat = floatval( get_post_meta( $id, $prefix.'map_lat', true ) );
$map_lng = floatval( get_post_meta( $id, $prefix.'map_lng', true ) );
$map_address = (string)get_post_meta( $id, $prefix.'address', true );

$event_zoom_map = EL()->options->event->get( 'event_zoom_map', 17 );

if( $map_lat && $map_lng && $map_address ){
?>
<div class="event_map_section event_section_white">
	
	<h3 class="second_font heading map">
		<?php esc_html_e( 'Map', 'eventlist' ); ?>

		<a class="btn text-center" href="https://maps.google.com?saddr=Current+Location&daddr=<?php echo esc_attr($map_lat); ?>,<?php echo esc_attr($map_lng); ?>" target="_blank" >
			<i class="icon_cursor_alt"></i><?php esc_html_e( 'Get Direction', 'eventlist' ); ?> 
		</a>
	</h3>

	<div id="event_map" class="event_map" data-lat="<?php echo esc_attr($map_lat); ?>" data-lng="<?php echo esc_attr($map_lng); ?>" data-address="<?php echo esc_attr($map_address); ?>" data-zoom="<?php echo esc_attr($event_zoom_map); ?>"></div>
	
</div>
<?php } ?>