<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists('EL_Payout_Mail') ) {
	
	class EL_Payout_Mail {

		public static function send_update_withdrawal_status_email( $post_id, $post, $update ){
			if ( ! $update ) {
				return;
			}

			// bail out if this is an autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( 'payout' !== $post->post_type ) {
				return;
			}

			$withdrawal_status 	= get_post_meta( $post_id, 'ova_mb_event_withdrawal_status', true );
			$extra_info 		= get_post_meta( $post_id, 'ova_mb_event_extra_info', true );
			$payout_method 		= get_post_meta( $post_id, 'ova_mb_event_payout_method', true );
			$amount 			= get_post_meta( $post_id, 'ova_mb_event_amount', true );
			$vendor_id 			= $post->post_author;
			$payout_id 			= $post->ID;

			if ( ! $payout_method || $payout_method == 'bank' ) {
				$payout_method = esc_html__( 'Bank', 'eventlist' );
			} elseif ( $payout_method == 'paypal' ) {
				$payout_method = esc_html__( 'Paypal', 'eventlist' );
			}

			if ( $withdrawal_status === "Pending" ) {
				return;
			} elseif ( $withdrawal_status === "Completed" ) {
				
				if ( enable_send_payout_completed_email() === true ) {
					$result = el_send_mail_vendor_payout_completed( $vendor_id, $payout_id, $amount, $payout_method, $extra_info );
					if ( ! $result ) {
						wp_die( esc_html__( 'Error sending email to vendor', 'eventlist' ), esc_html__( 'Error', 'eventlist' ), array( 'response' => 403, 'back_link' => true ) );
					}
				}

			} elseif ( $withdrawal_status === "Canceled" ) {
				
				if ( enable_send_payout_canceled_email() === true ) {
					$result = el_send_mail_vendor_payout_canceled( $vendor_id, $payout_id, $amount, $payout_method, $extra_info );
					if ( ! $result ) {
						wp_die( esc_html__( 'Error sending email to vendor', 'eventlist' ), esc_html__( 'Error', 'eventlist' ), array( 'response' => 403, 'back_link' => true ) );
					}
				}
			}

		}
	}
	add_action( 'save_post', 'EL_Payout_Mail::send_update_withdrawal_status_email',20,3);
}