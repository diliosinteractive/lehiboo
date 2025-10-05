<?php if( ! defined( 'ABSPATH' ) ) exit();

$class_share = $args['icon_share'];
$class_wl = $args['icon_wishtlist'];
$number_character = $args['number_character'];

$class_active = "";
if (isset($_COOKIE['el_wl_event'])) {
	$value_cookie = $_COOKIE['el_wl_event'];
	$value_cookie = str_replace( "\\", "", $value_cookie );
	$value_cookie = json_decode( $value_cookie, true );
	if ( ! empty( $value_cookie ) && is_array( $value_cookie ) && in_array( get_the_id(), $value_cookie) ) {
		$class_active = "active";
	}
}
?>

<?php if ( ! post_password_required( get_the_ID() ) ): ?>

	<div class="el-menu-event-detail">
		<div class="icon-menu">
			<?php if ($class_share) : ?>
				<div class="el-share-social">
					<span class="share-social">
						<a href="#" rel="nofollow" aria-label="<?php esc_attr_e( 'share', 'eventlist' ); ?>">
							<i class="<?php echo esc_attr($class_share) ?>"></i>
						</a>
						<?php echo apply_filters('ova_share_social', get_the_permalink(), get_the_title() ); ?>
					</span>
				</div>
			<?php endif ?>
			<?php if ($class_wl) : ?>
				<a href="#" class="event-loop-favourite el-wishlist <?php echo esc_attr($class_active) ?>" data-id="<?php echo esc_attr( get_the_id() ); ?>" rel="nofollow" aria-label="<?php esc_attr_e( 'wishlist', 'eventlist' ); ?>" >
					<i class="<?php echo esc_attr($class_wl) ?>"></i>
				</a>
			<?php endif ?>
		</div>
		<div class="content-menu-event">
			<h3 class="title-event-menu"><?php echo esc_html( sub_string_word( get_the_title(), (int)$number_character ) ); ?></h3>
			<p class="date-menu-event"><?php echo get_event_date_el(); ?></p>
		</div>
	</div>

<?php endif; ?>

