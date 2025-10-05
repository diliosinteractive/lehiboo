<?php if( ! defined( 'ABSPATH' ) ) exit();  ?>

<?php
$author_id = get_query_var( 'author' );
$eid = get_the_ID();

if( is_singular( 'event' ) ){
	$author_id = get_the_author_meta('ID');
}



if( $author_id ){

	$author_data = get_userdata( $author_id );

	$author_id_image = get_user_meta( $author_id, 'author_id_image', true ) ? get_user_meta( $author_id, 'author_id_image', true ) : '';
	if ( $author_id_image ) {
		$img_path = wp_get_attachment_image_url($author_id_image, 'el_thumbnail') ? wp_get_attachment_image_url($author_id_image, 'el_thumbnail') : wp_get_attachment_image_url($author_id_image, 'full');
	} else {

		$img_path = get_avatar_url($author_id);

	}

	$display_name = get_user_meta( $author_id, 'display_name', true ) ? get_user_meta( $author_id, 'display_name', true ) : get_the_author_meta( 'display_name', $author_id );
	$user_phone = get_user_meta( $author_id, 'user_phone', true ) ? get_user_meta( $author_id, 'user_phone', true ) : '';
	$user_profile_social = get_user_meta( $author_id, 'user_profile_social', true ) ? get_user_meta( $author_id, 'user_profile_social', true ) : '';
	$user_description = get_user_meta( $author_id, 'description', true ) ? get_user_meta( $author_id, 'description', true ) : '';
	$user_address = get_user_meta( $author_id, 'user_address', true ) ? get_user_meta( $author_id, 'user_address', true ) : '';

	$user_email = get_user_meta( $author_id, 'user_email', true ) ? get_user_meta( $author_id, 'user_email', true ) : get_the_author_meta( 'user_email', $author_id );

	$user_job = get_user_meta( $author_id, 'user_job', true ) ? get_user_meta( $author_id, 'user_job', true ) : '';

	$info_organizer = get_post_meta( $eid, OVA_METABOX_EVENT.'info_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'info_organizer', true ) : '';
	$name_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'name_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'name_organizer', true ) : '' );
	$phone_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'phone_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'phone_organizer', true ) : '' );
	$mail_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'mail_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'mail_organizer', true ) : '' );
	$job_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'job_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'job_organizer', true ) : '' );
	$social_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'social_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'social_organizer', true ) : array() );

	?>

	<!-- Info -->
	<div class="info_user event_section_white">

		<div class="top">

			<div class="user_image">
				<img src="<?php echo esc_url( $img_path ); ?>" alt="<?php echo esc_html( $display_name ); ?>" />
			</div>

			<div class="author_name second_font">
				<a class="name" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?> ">
					<?php if (is_singular('event') && $info_organizer == 'checked') {
						echo esc_html( $name_organizer ); 
					} else {
						echo esc_html( $display_name ); 
					} ?>
				</a>
				
				<div class="user_job second_font">
					<?php if (is_singular('event') && $info_organizer == 'checked') {
						echo esc_html( $job_organizer ); 
					} else {
						echo esc_html( $user_job ); 
					} ?>
				</div>
				
			</div>

		</div>

		<!-- rating star -->
		<?php ova_event_author_rating_display_by_id( $author_id ); ?>

		<div class="contact">
			<?php if( apply_filters( 'el_show_phone_info', true ) ){ ?>
				<div class="phone">
					<?php if (is_singular('event') && $info_organizer == 'checked') { ?>
						<?php if( $phone_organizer ){ ?>
							<i class="icon_phone"></i>
							<?php $phone = preg_replace('/[^0-9]/', '', $phone_organizer ); ?>
							<a href="<?php echo esc_attr('tel:'.$phone); ?>"><?php echo esc_html( $phone_organizer ); ?></a>
						<?php } ?>
					<?php } else { ?>
						<?php if( $user_phone ){ ?>
							<i class="icon_phone"></i>
							<?php $phone = preg_replace('/[^0-9]/', '', $user_phone ); ?>
							<a href="<?php echo esc_attr('tel:'.$phone); ?>"><?php echo esc_html( $user_phone ); ?></a>
						<?php } ?>
					<?php	} ?>
				</div>
			<?php } ?>
			
			<?php if( apply_filters( 'el_show_mail_info', true ) ){ ?>
				<div class="mail">
					<i class="icon_mail"></i>
					<?php if (is_singular('event') && $info_organizer == 'checked') { ?>
						<a href="<?php echo esc_attr('mailto:'.$mail_organizer); ?>"><?php echo esc_html( $mail_organizer ); ?></a>
					<?php } else { ?>
						<a href="<?php echo esc_attr('mailto:'.$user_email); ?>"><?php echo esc_html( $user_email ); ?></a>
					<?php	} ?>
				</div>
			<?php } ?>

			<?php if ( apply_filters( 'el_show_website_info', true ) ): ?>
				<?php if ( $author_data->user_url ): ?>
					<div class="website">
						<i class="fas fa-link"></i>
						<a href="<?php echo esc_url( $author_data->user_url ); ?>" rel="nofollow" target="_blank"><?php echo esc_html( $author_data->user_url ); ?></a>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( is_author() && $user_address && apply_filters( 'el_show_address_info', true ) ) { ?>
				<div class="address">
					<i class="icon_pin_alt"></i>
					<span style="display: block;"><?php echo esc_html($user_address); ?></span>
				</div>
			<?php } ?>
			

		</div>
		

		<?php if ( is_singular('event') ) { ?>
			<?php if ( $social_organizer && $info_organizer == 'checked' ) { ?>
				<div class="social">
					<?php foreach ($social_organizer as $k_social => $v_social) { 
						if ($v_social['link_social'] != '') { ?>

							<div class="social_item">
								<a href="<?php echo esc_attr($v_social['link_social']); ?>" target="_blank">
									<i class="<?php echo esc_html($v_social['icon_social']); ?>"></i>
									<?php foreach (el_get_social() as $k => $v) {
										if ( $v_social['icon_social'] == $k ) {
											echo esc_html($v);
										}
									} ?>
								</a>
							</div>
						<?php } 
					} ?>
				</div>
			<?php } elseif ( $user_profile_social && $info_organizer == '' ) { ?>
				<div class="social">
					<?php foreach ($user_profile_social as $k_social => $v_social) { 
						if ($v_social[0] != '') { ?>

							<div class="social_item">
								<a href="<?php echo esc_attr($v_social[0]); ?>" target="_blank" rel="nofollow">
									<i class="<?php echo esc_html($v_social[1]); ?>"></i>
									<?php foreach (el_get_social() as $k => $v) {
										if ( $v_social[1] == $k ) {
											echo esc_html($v);
										}
									} ?>
								</a>
							</div>
						<?php } 
					} ?>
				</div>
			<?php	} ?>
		<?php } elseif ( !is_singular('event') && $user_profile_social ) { ?>
			<div class="social">
				<?php foreach ($user_profile_social as $k_social => $v_social) { 
					if ($v_social[0] != '') { ?>

						<div class="social_item">
							<a href="<?php echo esc_attr($v_social[0]); ?>" target="_blank" rel="nofollow">
								<i class="<?php echo esc_html($v_social[1]); ?>"></i>
								<?php foreach (el_get_social() as $k => $v) {
									if ( $v_social[1] == $k ) {
										echo esc_html($v);
									}
								} ?>
							</a>
						</div>
					<?php } 
				} ?>
			</div>
		<?php	} ?>

		<?php if ($user_description) { ?>
			<p class="description">
				<?php echo esc_html($user_description); ?>
			</p>
		<?php } ?>

		<?php if( apply_filters( 'el_single_event_show_send_message_btn', true ) ){ ?>
			<a href="#" rel="nofollow" class="send_mess">
				<i class="icon_mail_alt"></i>
				<?php esc_html_e( 'Send Message', 'eventlist' ); ?>
			</a>
		<?php } ?>

		<?php 
		$current_user_email = $current_user_name = $current_user_phone = '';


		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$current_user_id = $current_user->ID;

			$current_user_email = $current_user->user_email;
			$current_user_name = get_user_meta( $current_user_id, 'display_name', true );
			$current_user_phone = get_user_meta( $current_user_id, 'user_phone', true );
			
		}
		?>
		<form class="el-sendmail-author">
			<input class="input-field" type="text" name="name_customer" value="<?php echo esc_attr($current_user_name); ?>" placeholder="<?php esc_attr_e('Name', 'eventlist') ?>" required />
			<input class="input-field" type="text" name="email_customer" placeholder="<?php esc_attr_e('Email', 'eventlist') ?>" value="<?php echo esc_attr($current_user_email); ?>" required />
			<input class="input-field" type="text" name="phone_customer" value="<?php echo esc_attr($current_user_phone); ?>" placeholder="<?php esc_attr_e('Phone', 'eventlist') ?>" required />

			<input class="input-field" type="text" name="subject_customer" placeholder="<?php esc_attr_e('Subject', 'eventlist') ?>" required />
			<textarea class="input-field" name="content"  cols="30" rows="10" placeholder="<?php esc_attr_e('Content', 'eventlist') ?>"></textarea>

			<?php do_action( 'meup_send_mail_vendor_recapcha' ); ?>

			<button type="submit" data-id="<?php echo esc_attr( get_the_id() ); ?>" class="submit-sendmail" >
				<?php esc_html_e('Send Mail', 'eventlist'); ?>
				<div class="submit-load-more">
					<div class="load-more">
						<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
					</div>
				</div>
			</button>
		</form>
		<div class="el-notify">
			<p class="success"><?php esc_html_e('Send mail success', 'eventlist') ?></p>
			<p class="error"><?php esc_html_e('Send mail failed', 'eventlist') ?></p>
			<p class="error-require"><?php esc_html_e('Please enter input field', 'eventlist') ?></p>
			<p class="recapcha-vetify"><?php esc_html_e( 'reCAPTCHA verification failed. Please try again.', 'eventlist' ) ?></p>
		</div>

	</div>

<?php

	}

?>
