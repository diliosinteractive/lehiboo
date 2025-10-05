<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php

$el_get_event_type = el_get_event_type();

if( $el_get_event_type == 'online' ){ ?>

	<div class="event-address">
		<i class="icon_pin_alt"></i>
		<div class="wp-address">
			<span class="el-address-general el-venue">
				<?php esc_html_e( 'Online', 'eventlist' ); ?>
			</span>
		</div>
	</div>

<?php }else{


	$venue = get_post_meta( get_the_id(), OVA_METABOX_EVENT . 'venue', true );
	$address = get_post_meta( get_the_id(), OVA_METABOX_EVENT . 'address', true );
	if (is_array($venue)) {
		$venue = implode(', ',$venue);
	}

	$has_venue = 'no_venue';
	?>
	<?php if(!empty($venue) || !empty($address)) : ?>
	<div class="event-address">
		<i class="icon_pin_alt"></i>
		<div class="wp-address">
			<?php if($venue) : ?>
				<?php $has_venue = 'has_venue'; ?>	
				<span class="el-address-general el-venue">
					<?php
					echo esc_html( stripslashes_deep($venue) );
					?>
					<br>
				</span>
			<?php endif ?>
			<?php if($address) : ?>
				<span class="el-address-general el-address <?php echo esc_attr($has_venue); ?>">
					<?php
					echo esc_html( stripslashes_deep( $address ) );
					?>
				</span>
			<?php endif ?>
		</div>
	</div>
	<?php endif ?>

<?php } ?>