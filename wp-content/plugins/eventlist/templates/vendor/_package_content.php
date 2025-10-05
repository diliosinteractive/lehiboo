<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<div class="packages_list">
			
	<?php
	$packages = EL_Package::instance()->list_packages();
	$currency = EL()->options->general->get('currency','USD');
	?>

	<?php if($packages->have_posts() ) : while ( $packages->have_posts() ) : $packages->the_post(); ?>

		<?php 
			$pid = get_the_id();

			$fee_register_package 	= floatval( get_post_meta( $pid, OVA_METABOX_EVENT.'fee_register_package', true ) );
			$package_time 			= get_post_meta( $pid, OVA_METABOX_EVENT.'package_time', true );
			

			$fee_percent_paid_ticket = get_post_meta( $pid, OVA_METABOX_EVENT.'fee_percent_paid_ticket', true );
			$fee_default_paid_ticket = get_post_meta( $pid, OVA_METABOX_EVENT.'fee_default_paid_ticket', true );

			$fee_percent_free_ticket = get_post_meta( $pid, OVA_METABOX_EVENT.'fee_percent_free_ticket', true );
			$fee_default_free_ticket = get_post_meta( $pid, OVA_METABOX_EVENT.'fee_default_free_ticket', true );

			$list_attendees 	= get_post_meta( $pid, OVA_METABOX_EVENT.'list_attendees', true );
			$export_attendees 	= get_post_meta( $pid, OVA_METABOX_EVENT.'export_attendees', true );

			$list_tickets 	= get_post_meta( $pid, OVA_METABOX_EVENT.'list_tickets', true );
			$export_tickets = get_post_meta( $pid, OVA_METABOX_EVENT.'export_tickets', true );

			$change_tax = get_post_meta( $pid, OVA_METABOX_EVENT.'change_tax', true );

			$package_total_event = get_post_meta( $pid, OVA_METABOX_EVENT.'package_total_event', true ) == '-1' ? esc_html__( 'Unlimit','eventlist' ) : (int)get_post_meta( $pid, OVA_METABOX_EVENT.'package_total_event', true ) ;

		 ?>
		<div class="item">

			<?php if( apply_filters( 'el_package_show_title', true ) ){ ?>
				<h3><?php the_title(); ?></h3>
			<?php } ?>

			<?php if( apply_filters( 'el_package_show_price', true ) ){ ?>
				<div class="price">
					<?php 
						$package_time = ( $package_time == '-1' ) ? esc_html__( 'Unlimit', 'eventlist' ) : $package_time.' '.esc_html__( 'Days','eventlist' );

					 ?>
					<?php echo el_price( $fee_register_package ).'<span class="slash">/</span><span class="time">'.$package_time.'</span>'; ?>
				</div>
			<?php } ?>

			<?php if( apply_filters( 'el_package_show_features', true ) ){ ?>
				<ul>
					<li class="paid_ticket">
						<?php esc_html_e( 'Total Event', 'eventlist' ); ?><br/>
						<span class="value"><?php echo $package_total_event ?></span>
					</li>
					
					<?php if( $fee_percent_paid_ticket || $fee_default_paid_ticket ){ ?>
						<li class="paid_ticket">
							<?php esc_html_e( 'Fee per paid ticket', 'eventlist' ); ?><br/>
							<span class="value">
								<?php
									if ( $fee_percent_paid_ticket ) {
										echo $fee_percent_paid_ticket .'%';
									}
									if ( $fee_percent_paid_ticket && $fee_default_paid_ticket ) {
										echo '<span>+</span>';
									}
									if ( $fee_default_paid_ticket ) {
										echo el_price( $fee_default_paid_ticket );
									}
								?>
							</span>
						</li>
					<?php } ?>

					<?php if( $fee_percent_free_ticket ){ ?>
						<li class="free_ticket">
							<?php esc_html_e( 'Fee per free ticket', 'eventlist' ); ?><br/>
							<span class="value"><?php echo $fee_percent_free_ticket .'%' .'<span>+</span>'.  el_price( $fee_default_free_ticket ); ?></span>
						</li>
					<?php } ?>

					<li class="list_attendees">
						<?php if( $list_attendees == 'yes' ){ ?>
							<i class="icon_check pcheck"></i>
						<?php }else{ ?>
							<i class="icon_close pclose"></i>
						<?php } ?>
						<?php esc_html_e( 'List attendees', 'eventlist' ); ?>
					</li>

					<li class="export_tickets">
						<?php if( $export_attendees == 'yes' ){ ?>
							<i class="icon_check pcheck"></i>
						<?php }else{ ?>
							<i class="icon_close pclose"></i>
						<?php } ?>
						<?php esc_html_e( 'Export attendees', 'eventlist' ); ?>
					</li>

					<li class="list_tickets">
						<?php if( $list_tickets == 'yes' ){ ?>
							<i class="icon_check pcheck"></i>
						<?php }else{ ?>
							<i class="icon_close pclose"></i>
						<?php } ?>
						<?php esc_html_e( 'List tickets', 'eventlist' ); ?>
					</li>

					<li class="export_tickets">
						<?php if( $export_tickets == 'yes' ){ ?>
							<i class="icon_check pcheck"></i>
						<?php }else{ ?>
							<i class="icon_close pclose"></i>
						<?php } ?>
						<?php esc_html_e( 'Export tickets', 'eventlist' ); ?>
					</li>

					<li class="change_tax">
						<?php if( $change_tax == 'yes' ){ ?>
							<i class="icon_check pcheck"></i>
						<?php }else{ ?>
							<i class="icon_close pclose"></i>
						<?php } ?>
						<?php esc_html_e( 'Change Tax', 'eventlist' ); ?>
					</li>
				</ul>

			<?php } ?>
			
			<div class="package_content">
				<?php echo do_shortcode( get_the_content() ); ?>
			</div>

			<?php 
				$user_id 				= wp_get_current_user()->ID;
				$current_user_package 	=  $user_id ? get_user_meta( $user_id, 'package', true ) : ''; 
				$package_pid 			= get_post_meta( $pid, OVA_METABOX_EVENT.'package_id', true );
				$member_ship_user 		= EL_Package::instance()->get_info_membership_by_user_id( $user_id );

				// Get Current Package ID of user
				$p_pid = get_post_meta( $member_ship_user['id_package'], OVA_METABOX_EVENT.'package_id', true );
				
			 ?>
			<?php if( $user_id && $p_pid == $package_pid && $package_pid && $p_pid ){ ?>
				
				<button class="current button">
					<?php esc_html_e( 'Your current package', 'eventlist' ); ?>
				</button>

			<?php }else{ ?>
				
				<button class="register_package button"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_register_package' ) ); ?>"
				data-currency="<?php echo esc_attr( strtolower( $currency ) ); ?>"
				data-price="<?php echo esc_attr( $fee_register_package ); ?>"
				data-package="<?php echo esc_attr( get_the_title() ); ?>"
				data-pid="<?php echo esc_attr($pid); ?>"><?php esc_html_e( 'Register Package', 'eventlist' ); ?></button>
				
			<?php } ?>
		</div>
	<?php endwhile; endif; wp_reset_postdata(); ?>
</div>