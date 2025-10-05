<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
$list_type_ticket = get_post_meta( get_the_ID(), OVA_METABOX_EVENT . 'ticket', true);
$seat_option = get_post_meta( get_the_ID(), OVA_METABOX_EVENT . 'seat_option', true);
$class_empty_ticket = ( ! empty( $list_type_ticket ) || $seat_option === 'map' )  ? 'no-empty-ticket' : 'empty-ticket';
?>
<h1 class="title-event-single second_font <?php echo esc_attr($class_empty_ticket) ?>" >
	<?php the_title(); ?>
</h1>
