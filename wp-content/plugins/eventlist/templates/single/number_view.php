<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
global $event;
$number_view = $event->getPostViews(get_the_ID());
?>
<span class="event-single-number-view item-meta">
	<i class="far fa-eye"></i>
	<?php echo wp_kses_post( $number_view ); ?>
</span>