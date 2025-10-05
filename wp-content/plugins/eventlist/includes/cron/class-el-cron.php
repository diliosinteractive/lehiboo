<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EL_Cron' ) ) {

	/**
	 * Class EL_Mail
	 */
	class EL_Cron {

		// Remind
		public $hook_remind_event_time = 'el_cron_hook_remind_event_time';
		public $time_repeat_remind_event_time = 'time_repeat_remind_event_time';

		// Update Start event for event
		public $hook_update_start_date_event = 'el_cron_hook_update_start_date_event';
		public $time_repeat_update_start_date_event = 'time_repeat_update_start_date_event';

		// Holding Ticket
		public $hook_update_holding_ticket = 'el_cron_hook_update_holding_ticket';
		public $time_repeat_update_holding_ticket = 'time_repeat_update_holding_ticket';

		public $hook_update_event_status = 'el_cron_hook_update_event_status';

		/**
		 * EL_Cron constructor.
		 */
		public function __construct() {

			add_filter( 'cron_schedules', array( $this, 'el_add_cron_interval' ) );
			add_action( 'init', array( $this, 'el_check_scheduled' ) );
			register_deactivation_hook( __FILE__, array( $this, 'el_deactivate_cron' ) ); 

			add_action( $this->hook_remind_event_time, array( $this, 'el_remind_event_time' ) );
			add_action( $this->hook_update_start_date_event, array( $this, 'el_update_start_event_event_time' ) );
			add_action( $this->hook_update_holding_ticket, array( $this, 'el_update_holding_ticket' ) );

			add_action( $this->hook_update_event_status, array( $this, 'el_update_event_status' ) );


		}

		public function el_check_scheduled(){
			// Remind
			if ( !wp_next_scheduled( $this->hook_remind_event_time ) ) {
			    wp_schedule_event( time(), $this->time_repeat_remind_event_time, $this->hook_remind_event_time );
			}

			// Update start date
			if ( !wp_next_scheduled( $this->hook_update_start_date_event ) ) {
			    wp_schedule_event( time(), $this->time_repeat_update_start_date_event, $this->hook_update_start_date_event );
			}

			// Holding Ticket
			if ( !wp_next_scheduled( $this->hook_update_holding_ticket ) ) {
			    wp_schedule_event( time(), $this->time_repeat_update_holding_ticket, $this->hook_update_holding_ticket );
			}
			// Event status
			if ( ! wp_next_scheduled( $this->hook_update_event_status ) ) {
				$interval = EL()->options->general->get( 'schedule_event_status','hourly' );
				wp_schedule_event( time(), $interval, $this->hook_update_event_status );
			}
		}

		/**
		 * init time repeat hook
		 * @param  array $schedules 
		 * @return array schedule
		 */
		public function el_add_cron_interval( $schedules ) {
			// Remind
			$remind_mail_send_per_seconds = intval( EL()->options->mail->get( 'remind_mail_send_per_seconds', 86400 ) );

		    $schedules[$this->time_repeat_remind_event_time] = array(
		        'interval' => $remind_mail_send_per_seconds,
		        'display' => sprintf( esc_html__( 'Every % seconds', 'eventlist' ), $remind_mail_send_per_seconds )
		    );

		    // Update start date event
			$update_start_date_send_per_seconds = intval( apply_filters( 'el_time_repeat_update_start_date_event', 86400 ) );

		    $schedules[$this->time_repeat_update_start_date_event] = array(
		        'interval' => $update_start_date_send_per_seconds,
		        'display' => sprintf( esc_html__( 'Every % seconds', 'eventlist' ), $update_start_date_send_per_seconds )
		    );

		    // Holding Ticket
			$update_holding_ticket_per_seconds = intval( EL()->options->checkout->get('check_ticket_hold_per_seconds', 600) );

		    $schedules[$this->time_repeat_update_holding_ticket] = array(
		        'interval' => $update_holding_ticket_per_seconds,
		        'display' => sprintf( esc_html__( 'Every % seconds', 'eventlist' ), $update_holding_ticket_per_seconds )
		    );

		    return $schedules;
		}

		public function el_deactivate_cron() {
		    wp_clear_scheduled_hook( $this->hook_remind_event_time );
		    wp_clear_scheduled_hook( $this->hook_update_start_date_event );
		    wp_clear_scheduled_hook( $this->hook_update_holding_ticket );
		    wp_clear_scheduled_hook( $this->hook_update_event_status );
		}

		public function el_remind_event_time(){
			if( EL()->options->mail->get( 'remind_mail_enable', 'yes' ) !== 'yes' ) return;

			$send_x_day = intval( EL()->options->mail->get( 'remind_mail_before_xday', 3 ) );

			$curren_time = current_time('timestamp');

			$args = array(
				'post_type' 	=> 'el_tickets',
				'post_status' 	=> 'publish',
				'numberposts' 	=> -1,
				'meta_query' 	=> array(
					array(
						'relation' => 'AND',
						array(
							'key' 		=> OVA_METABOX_EVENT.'date_start',
							'value' 	=> array( $curren_time, $curren_time + $send_x_day*24*60*60 ),
							'compare' 	=> 'BETWEEN'
						),
						array(
							'key' 		=> OVA_METABOX_EVENT.'ticket_status',
							'value' 	=> '',
							'compare' 	=> '='
						)
					)
				)
			);

			$tickets = get_posts( $args );

			$date = get_option( 'date_format' ).' '.get_option( 'time_format' );
			$ticket_unique = array();
			$booking_email_pairs = array();

			if ( !empty($tickets) && is_array( $tickets ) ) {
				foreach( $tickets as $ticket ) {
					$ticket_id 			= $ticket->ID;
					$booking_id 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'booking_id', true);
					$email_customer 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'email_customer', true);
					$event_id 			= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'event_id', true);
					$event_name 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'name_event', true);
					$event_start_time 	= date_i18n( $date, get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'date_start', true) );

					$pair = $booking_id . '|' . $email_customer;

					if ( ! in_array( $pair, $booking_email_pairs ) ) {
			            $ticket_unique[] = array(
							'email' 		=> $email_customer,
							'event_id'		=> $event_id,
							'event_name'	=> $event_name,
							'start_time'	=> $event_start_time,
						);
			            $booking_email_pairs[] = $pair;
			        }
				}
			}

			if ( ! empty( $ticket_unique ) ) {
				foreach ( $ticket_unique as $ticket ) {

					$mail_customer 		= $ticket['email'];
					$event_id 			= $ticket['event_id'];
					$event_name 		= $ticket['event_name'];
					$event_start_time 	= $ticket['start_time'];
					
					el_mail_remind_event_time( $mail_customer, $event_id, $event_name, $event_start_time );
				}
			}
			
		}

		public function el_update_start_event_event_time(){
			$today_tmp = strtotime( gmdate( 'Y-m-d', current_time( 'timestamp' ) ) );

			$args = array(
			 	'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'fields' 			=> 'ids',
				'meta_query'		=> array(
					array(
						'key' 		=> OVA_METABOX_EVENT . 'end_date_str',
						'value' 	=> current_time('timestamp'),
						'compare' 	=> '>',
					),
				)
			);

			$events = get_posts( $args );

			foreach( $events as $key => $id ) {
			 	$option_calendar 		= get_post_meta( $id, OVA_METABOX_EVENT.'option_calendar', true );
			 	$calendar 				= get_post_meta( $id, OVA_METABOX_EVENT.'calendar', true );
			 	$calendar_recurrence 	= get_post_meta( $id, OVA_METABOX_EVENT.'calendar_recurrence', true );
			 	$arr_start_date 		= array();

				if ( $option_calendar == 'manual' ) {
					if ( $calendar ) {
						foreach( $calendar as $value ) {
							$start_date = strtotime( $value['date'] .' '. $value['start_time'] );

							if ( $start_date >= $today_tmp ) {
								$arr_start_date[] = $start_date;	
							}
						}
					}
				} elseif ( $calendar_recurrence ) {
					foreach( $calendar_recurrence as $value ) {
						$start_date = strtotime( $value['date'] .' '. $value['start_time'] );

						if ( $start_date >= $today_tmp ) {
							$arr_start_date[] = $start_date;	
						}
					}
				}

				if ( $arr_start_date ) {
					$start_date_str = min($arr_start_date);
			 		update_post_meta( $id, OVA_METABOX_EVENT.'start_date_str', $start_date_str );	
				}
			}
		}

		public function el_update_holding_ticket() {
			if ( EL()->options->checkout->get('checkout_holding_ticket', 'no') !== 'yes' ) return;

			$max_time_complete_checkout = intval( EL()->options->checkout->get('max_time_complete_checkout', 600) );
			$curren_time = current_time('timestamp');

			$args = array(
				'post_type' 		=> 'holding_ticket',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'fields' 			=> 'ids',
			);

			$holding_ticket = get_posts( $args );

			if ( ! empty( $holding_ticket ) && is_array( $holding_ticket ) ) {
				foreach( $holding_ticket as $ht_id ) {
					$ht_current_time 	= intval( get_post_meta( $ht_id, OVA_METABOX_EVENT . 'current_time', true ) );
					$past_time 			= $curren_time - $ht_current_time;
					$booking_id 		= get_post_meta( $ht_id, OVA_METABOX_EVENT . 'booking_id', true );

					if ( $past_time > $max_time_complete_checkout ) {
						// Delete holding ticket
						wp_delete_post( $ht_id );

						if ( (int)$booking_id ) {
							$status_holding_ticket = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'status_holding_ticket', true );

							if ( $status_holding_ticket === 'Pending' ) {
								update_post_meta( $booking_id, OVA_METABOX_EVENT . 'status', 'Expired' );
							}
						}
					}
				}
			}
		}

		public function el_update_event_status(){
			$args = array(
				'post_type' 		=> 'event',
				'posts_per_page' 	=> -1,
				'post_status' 		=> 'publish',
				'fields' 			=> 'ids',
			);
			$events = get_posts( $args );

			if ( count( $events ) > 0 ) {
				foreach ( $events as $event_id ) {
					
					$end_date_time 		= (int) get_post_meta( $event_id, OVA_METABOX_EVENT.'end_date_str', true );
					$start_date_time 	= (int) get_post_meta( $event_id, OVA_METABOX_EVENT.'start_date_str', true );
					$option_calendar 	= get_post_meta( $event_id, OVA_METABOX_EVENT.'option_calendar', true );
					$current_time 		= (int) current_time( 'timestamp' );
					$event_status 		= '';

					if ( $end_date_time < $current_time ) {
						$event_status = 'past';
					} elseif ( $end_date_time > $current_time && ( $start_date_time >  $current_time || $option_calendar == 'auto' ) ) {
						$event_status = 'upcoming';
					} elseif ( $start_date_time <= $current_time && $end_date_time >= $current_time ) {
						$event_status = 'opening';
					}

					update_post_meta( $event_id, OVA_METABOX_EVENT.'event_status', $event_status );
				}
				do_action( 'el_after_update_event_status_automatic' );
			}
			
		}
	}
}

new EL_Cron();