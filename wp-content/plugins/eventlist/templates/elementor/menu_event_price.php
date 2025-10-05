<?php if( ! defined( 'ABSPATH' ) ) exit();

$format = $args['type_format'];

$price = get_price_ticket_by_id_event( array( 'id_event' => get_the_ID(), 'display_price' => $format ) );

?>

<?php if ( ! post_password_required( get_the_ID() ) ): ?>
	<div class="el-menu-event-price">
		<span class="event_loop_price"><?php echo esc_html( $price ) ?></span>
	</div>
<?php endif; ?>
