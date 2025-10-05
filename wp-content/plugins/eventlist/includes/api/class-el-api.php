<?php 
defined( 'ABSPATH' ) || exit;

use \Firebase\JWT\JWT;

if ( class_exists( 'El_API', false ) ) {
	return new El_API();
}

/**
 * Admin Assets classes
 */
class El_API{

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Constructor
	 */

	public function __construct(){

		add_action( 'rest_api_init', array( $this, 'el_register_routes' ) );

	}

	public function el_register_routes(){

		register_rest_route( 'meup/v1', '/login/', array(
			'methods' 	=> 'POST',
		    'callback' 	=> array( $this, 'el_login' ),
		    'permission_callback' => '__return_true'
		  ) );

		register_rest_route( 'meup/v1', '/check_login/', array(
			'methods' 	=> 'POST',
		    'callback' 	=> array( $this, 'el_check_login' ),
		    'permission_callback' => '__return_true'
		  ) );

		

		 register_rest_route( 'meup/v1', '/event_accepted/', array(
		    'methods' 	=> 'POST',
		    'callback' 	=> array( $this, 'el_event_accepted' ),
		    'permission_callback' => '__return_true'
		  ) );

		  register_rest_route( 'meup/v1', '/validate_ticket/', array(
		    'methods' 	=> 'POST',
		    'callback' 	=> array( $this, 'el_validate_ticket' ),
		    'permission_callback' => '__return_true'
		  ) );
	}

	// User Login 
	public function el_login( WP_REST_Request $request ){

		$user = sanitize_user( $request->get_param('user') );
		$pass = trim( $request->get_param('pass') );

		// Check acount
		$aut = wp_authenticate_username_password( NULL, $user, $pass );	
		if( is_wp_error( $aut ) ){
			return array(  'status' => 'FAIL' , 'msg' => esc_html__( 'Error Username or Password', 'eventlist' ) );
		}


        $userid = $aut->ID;
        $email = $aut->user_email;
        $user_login = $aut->user_login;

        try {
        	$jwt = $this->el_make_token( $userid, $email, $user_login ) ;
			return array(  'status' => 'SUCCESS', 'token' => $jwt, 'msg' => esc_html__( 'Login success', 'eventlist' ) );
        } catch (Exception $e) {
        	return array(  'status' => 'FAIL' , 'msg' => esc_html__( 'Error make token', 'eventlist' ) );
        }

        return array(  'status' => 'FAIL' , 'msg' => esc_html__( 'Error Something', 'eventlist' ) );

	}


	public function el_check_login( WP_REST_Request $request ){

		// Get Token
		$token = $request->get_param('token');

		// Key serect qrcode
		$key = EL()->options->general->get('serect_key_qrcode', 'xxxdfsferefdfd');

		try{

			$decoded = JWT::decode($token, $key, array('HS256') );

			$user = get_user_by( 'ID', $decoded->uid );

			if($user && $user->user_email == $decoded->uemail ){
				$jwt = $this->el_make_token( $user->ID, $user->user_email, $user->user_login );
				return array(  'status' => 'SUCCESS', 'token' => $jwt );
			}

		} catch(Exception $e){
			
			return array( 'status' => 'FAIL', 'msg' => $e->getMessage() );

		}
		
		return array( 'status' => 'FAIL', 'msg' => esc_html__( 'Error', 'eventlist' ) );

		
	}
	

	// Gel All Tickets of an Event
	public function el_event_accepted( WP_REST_Request $request ){


		$token = $request->get_param('token');
		// Key serect qrcode
		$key = EL()->options->general->get('serect_key_qrcode', 'xxxdfsferefdfd');
		
		try{

			$decoded = JWT::decode($token, $key, array('HS256') );

			$eids = $decoded->eids ;
			$eids_arr = explode( ',', $eids );

			$args = array(
				'post_type' => 'event',
				'post_status' => 'publish',
				'numberposts' => '-1',
				'post__in'	=> $eids_arr
				
			);
			return array( 'status' => 'SUCCESS', 'events' => get_posts ( $args ) );
			
			
		}
		catch(Exception $e){
			return array( 'status' => 'FAIL', 'msg' => $e->getMessage() );
		}

		return array( 'status' => 'FAIL', 'msg' => esc_html__( 'Error', 'eventlist' ) );

	}
	

	
	// Validate Ticket
	public function el_validate_ticket( WP_REST_Request $request ){

		$token 	= $request->get_param('token');
		
		$qrcode = $request->get_param('qrcode');

		$eid = $request->get_param('eid');

		// Key serect qrcode
		$key = EL()->options->general->get('serect_key_qrcode', 'xxxdfsferefdfd');

		
		
		// Validate Token
		try{

			$decoded = JWT::decode($token, $key, array('HS256') );

			$eids_arr = explode( ',', $decoded->eids );

			if( in_array(intval($eid), $eids_arr) ){

				$args = array(
					'post_type' => 'el_tickets',
					'post_status' => 'publish',
					'numberposts' => '-1',
					'fields'	=> 'ids',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => OVA_METABOX_EVENT . 'qr_code',
							'value' => $qrcode,
							'compare'	=> '=',
						),
						array(
							'key' => OVA_METABOX_EVENT . 'event_id',
							'value' => strval( $eid ),
							'compare'	=> '=',
						)
					)
					
				);

				$ticket_id = get_posts ( $args );

				if ( !$ticket_id ) {
					return array( 
						'status' 		=> 'FAIL', 
						'msg' 			=> esc_html__( 'Not Found QR Code', 'eventlist' ) .': '. $qrcode,
						'checkin_time' 	=> '',
						'name_customer' => '',
						'seat'			=> '',
						'e_cal' 		=> '',
						'extra_service'	=> '',
					);
				}

				$ticket_status = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'ticket_status', true );
				$times_checked = absint( get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'times_checked', true ) );
				$ticket_start_date 	= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_start', true );
				$ticket_end_date 	= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_end', true );

				# Convert time
				$check_start_date 	= strtotime( gmdate('Y-m-d', $ticket_start_date) );
				$check_end_date 	= strtotime( gmdate('Y-m-d', $ticket_end_date) ) + 24*60*60 - 1;
				$between_date 		= apply_filters( 'el_filter_between_date', absint( ceil( ( $check_end_date - $check_start_date ) / 86400 ) ) );
				$checks_remaining 	= apply_filters( 'el_filter_checks_remaining', absint( $between_date - $times_checked ) );
				
				if ( $ticket_status === 'checked' ) {
					$checkin_time_tmp = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'checkin_time', true ) ;
					$checkin_time =  $checkin_time_tmp ? date_i18n( get_option( 'date_format' ).' '. get_option( 'time_format' ), $checkin_time_tmp ) : '';

					$name_customer = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'name_customer', true ) ;
					$seat = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'seat', true ) ;
					$person_type = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'person_type', true );
					if ( $person_type ) {
						$seat.= ' - '.$person_type;
					}
					// Extra service
					$extra_service = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'extra_service', true );
					$str_extra_service = el_extra_sv_ticket( $extra_service );
					$str_extra_service = wp_strip_all_tags( $str_extra_service );

					// Event Calendar
					$date_format = get_option('date_format');
					$time_format = get_option('time_format');

					$start_date = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_start', true );
					$start_date_day = date_i18n($date_format, $start_date);
					$start_date_time = date_i18n($time_format, $start_date);

					$end_date = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_end', true );
					$end_date_day = date_i18n($date_format, $end_date);
					$end_date_time = date_i18n($time_format, $end_date);
					

					$event_calendar = $start_date_day === $end_date_day ? $start_date_day.' '.$start_date_time.'-'.$end_date_time : $start_date_day.'-'.$end_date_day.' '.$start_date_time.'-'.$end_date_time;

					return array( 
						'status' => 'FAIL', 
						'msg' => esc_html__( 'Already Checked In', 'eventlist' ), 
						'checkin_time' => $checkin_time,
						'name_customer' => $name_customer,
						'seat'	=> $seat,
						'e_cal' => $event_calendar,
						'extra_service' => $str_extra_service,
					);
				} elseif ( !$ticket_status ) {
						if ( $checks_remaining === 1 ) {
							$ticket = array(
								'ID' 			=> $ticket_id[0],
								'meta_input' 	=> array(
									OVA_METABOX_EVENT.'ticket_status' 	=> 'checked',
									OVA_METABOX_EVENT.'checkin_time' 	=> current_time('timestamp'),
									OVA_METABOX_EVENT.'times_checked' 	=> $times_checked + 1,
								)
							);
						} else {
							$ticket = array(
								'ID' 			=> $ticket_id[0],
								'meta_input' 	=> array(
									OVA_METABOX_EVENT.'checkin_time' 	=> current_time('timestamp'),
									OVA_METABOX_EVENT.'times_checked' 	=> $times_checked + 1,
								)
							);
						}

						if ( wp_update_post( $ticket ) ) {
							$checkin_time_tmp = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'checkin_time', true ) ;
							$checkin_time =  $checkin_time_tmp ? date_i18n( get_option( 'date_format' ).' '. get_option( 'time_format' ), $checkin_time_tmp ) : '';

							$name_customer = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'name_customer', true ) ;
							$seat = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'seat', true ) ;
							$person_type = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'person_type', true );
							if ( $person_type ) {
								$seat.= ' - '.$person_type;
							}

							// Event Calendar
							$date_format = get_option('date_format');
							$time_format = get_option('time_format');

							$start_date = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_start', true );
							$start_date_day = date_i18n($date_format, $start_date);
							$start_date_time = date_i18n($time_format, $start_date);

							$end_date = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_end', true );
							$end_date_day = date_i18n($date_format, $end_date);
							$end_date_time = date_i18n($time_format, $end_date);
							
							$event_calendar = $start_date_day === $end_date_day ? $start_date_day.' '.$start_date_time.'-'.$end_date_time : $start_date_day.'-'.$end_date_day.' '.$start_date_time.'-'.$end_date_time;

							return array( 
								'status' => 'SUCCESS', 
								'msg' => esc_html__( 'Success', 'eventlist' ), 
								'checkin_time' => $checkin_time,
								'name_customer' => $name_customer,
								'seat'	=> $seat,
								'e_cal' => $event_calendar,
								'extra_service' => $str_extra_service,
								
							);
						} else {
							return array( 
								'status' => 'FAIL', 
								'msg' => esc_html__( 'Can\'t update ticket status', 'eventlist' ),
								'checkin_time' => '',
								'name_customer' => '',
								'seat'	=> '',
								'e_cal' => '',
								'extra_service' => '',	
							);
						}
				} else {
					return array( 
						'status' => 'FAIL', 
						'msg' => esc_html__( 'Not Found QR Code', 'eventlist' ) .': '. $qrcode,
						'checkin_time' => '',
						'name_customer' => '',
						'seat'	=> '',
						'e_cal' => '',
						'extra_service' => '',
					);
				}
			} else { // Not found Event Id in Token
				return array( 
					'status' => 'FAIL', 
					'msg' => esc_html__( 'Please check surely, you have permission to scan this ticket', 'eventlist' ),
					'checkin_time' => '',
					'name_customer' => '',
					'seat'	=> '',
					'e_cal' => '',
					'extra_service' => '',
				);
			}
		}

		catch(Exception $e){
			return array( 
				'status' => 'FAIL', 
				'msg' => esc_html__( 'Exception Error', 'eventlist' ),
				'checkin_time' => '',
				'name_customer' => '',
				'seat'	=> '',
				'e_cal' => '',
				'extra_service' => '',
			);
		}
		
		return array( 
			'status' => 'FAIL', 
			'msg' => esc_html__( 'Error', 'eventlist' ),
			'checkin_time' => '',
			'name_customer' => '',
			'seat'	=> '',
			'e_cal' => '',
			'extra_service' => '',
		);
	}

	function el_make_token($userid, $email, $user_login ){

		// Key Serect			
		$key = EL()->options->general->get('serect_key_qrcode', 'xxxdfsferefdfd');
		
        $issuedAt = time();
        $expire = apply_filters('meup_auth_expire', $issuedAt + (7*24*60*60), $issuedAt);

        $args1 = array(
			'post_type' => 'event',
			'post_status' => 'publish',
			'numberposts' => '-1',
			'fields'	=> 'ids',
			'meta_query' => array(
				array(
					'key' => OVA_METABOX_EVENT . 'api_key',
					'value' => $user_login,
					'compare'	=> '=',
				)
			)
			
		);
		
		$events_ids1 = get_posts ( $args1 );


		$args2 = array(
			'post_type' => 'event',
			'post_status' => 'publish',
			'numberposts' => '-1',
			'fields'	=> 'ids',
			'author_name' => $user_login
		);

		$events_ids2 = get_posts ( $args2 );

		$events_ids = array_merge( $events_ids1, $events_ids2 );

        $payload = array(
            'uid' => $userid,
            'uemail' => $email,
            'eids'	=> implode( ',', $events_ids ),
            'exp' => $expire
        );
        
        return JWT::encode($payload, $key, 'HS256');	
		
	}

	
	
}

El_API::instance();