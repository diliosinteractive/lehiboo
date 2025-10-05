<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
	$class_active = "";
	if (isset($_COOKIE['el_wl_event'])) {
		$value_cookie = $_COOKIE['el_wl_event'];
		$value_cookie = str_replace("\\", "", $value_cookie);
		$value_cookie = json_decode($value_cookie, true);
		if (!empty($value_cookie) && is_array($value_cookie) && in_array(get_the_id(), $value_cookie)) {
			$class_active = "active";
		}
	}
	
?>
<a href="javascript: void(0)"
	class="event-loop-favourite el-wishlist <?php echo esc_attr($class_active) ?>"
	role="button" aria-label="<?php esc_attr_e( 'add to wishlist', 'eventlist' ); ?>"
	data-id="<?php echo esc_attr( get_the_id() ); ?>">
	<i class="fa fa-heart-o"></i>
</a>