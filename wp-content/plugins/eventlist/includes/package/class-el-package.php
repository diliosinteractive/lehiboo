<?php
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EL_Package', false ) ) {
	return EL_Package::instance();
}

class EL_Package {

	protected static $_instance = null;

	protected $_prefix = OVA_METABOX_EVENT;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct(){
		add_action( 'user_register', array( $this, 'add_package_new_user' ), 10, 1 );
		add_action( 'profile_update', array($this, 'add_package_new_user' ), 10, 1 );
		
	}

	public function add_package_new_user( $user_id = null ){

		$enable_package = EL()->options->package->get( 'enable_package', 'no' );
		$default_package = EL()->options->package->get( 'package' );
		$current_package = get_user_meta( $user_id, 'package', true );

		if ( isset( $_POST['ova_type_user']) && $_POST['ova_type_user'] === 'vendor' ) {
			$user = new WP_User( $user_id );
			$user->set_role( 'el_event_manager' );
		} elseif( isset( $_POST['ova_type_user']) && $_POST['ova_type_user'] === 'user' ) {
			$user = new WP_User( $user_id );
			$user->set_role( 'subscriber' );
		}

		$user_meta = get_userdata($user_id);
		$roles = $user_meta->roles;

		if ( in_array("el_event_manager", $roles) ) {

			if( $enable_package == 'yes' && $default_package && !$current_package ){
				
				$pid = $this->get_package( $default_package );
				if( $this->add_membership( $pid['id'], $user_id, $status = 'new' ) ){
					update_user_meta( $user_id, 'package', $default_package );	
				}
				
			}
		}
	}

	

	public function list_packages( ){

		$args = array(
			'post_type' 		=> 'package',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'orderby' 			=> 'meta_value_num',
			'meta_key' 			=> $this->_prefix.'order_package',
			'order'				=> 'ASC'
		);
		$packages = new WP_Query( $args );
		return $packages;

	}

	public function get_package( $package_id ){
		$args = array(
			'post_type'			=> 'package',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> 1,
			'meta_query' 		=> array(
				array(
					'key' 		=> $this->_prefix.'package_id',
					'value' 	=> (string)$package_id,
					'compare' 	=> '='
				)
			)
		);
		$package = new WP_Query( $args );
		if( $package->have_posts() ): while( $package->have_posts() ): $package->the_post(); 
			return array( 'id' => get_the_id(), 'title' => get_the_title() );
		endwhile;endif; wp_reset_postdata(); wp_reset_query();
	}
	
	function add_membership( $pid, $user_id, $status = 'renew' ){

		$package_name 	= get_the_title( $pid );
		$package_id 	= get_post_meta( $pid, $this->_prefix.'package_id', true );
		$package_time 	= get_post_meta( $pid, $this->_prefix.'package_time', true );
		
		$event_limit 	= absint( get_post_meta( $pid, $this->_prefix.'package_total_event', true ) );

		$total 				= floatval( get_post_meta( $pid, $this->_prefix.'fee_register_package', true ) );
		$user_meta_package 	= get_user_meta( $user_id, 'package', true );

		$fee_register_package = floatval( get_post_meta( $pid, OVA_METABOX_EVENT.'fee_register_package', true ) );
		$post_status = $fee_register_package ? 'Pending' : 'Publish';

		if( $status == 'new' ){
			$post_status = 'Publish';
		}

		$post_data['post_type'] 	= 'manage_membership';
		$post_data['post_title'] 	= $package_name;
		$post_data['post_status'] 	= $post_status;
		$post_data['post_name'] 	= $package_name;
		$post_data['post_author'] 	= $user_id;

		$end_date_membership_current = EL_Package::get_end_date_membership( $user_id );
		if( $end_date_membership_current['end_date'] > current_time( 'timestamp' ) && $user_meta_package == $package_id ){
			$membership_start_date =  $end_date_membership_current['start_date'];
			$membership_end_date = ( $package_time == '-1' ) ? '-1' : $end_date_membership_current['end_date'] + $package_time*24*60*60;
		}else{
			$membership_start_date =  current_time( 'timestamp' ) ;	
			$membership_end_date = ( $package_time == '-1' ) ? '-1' : current_time( 'timestamp' ) + $package_time*24*60*60;
		}

		$meta_input = array(
			$this->_prefix.'total' 					=> $total,
			$this->_prefix.'membership_package_id' 	=> $package_id,
			$this->_prefix.'membership_start_date' 	=> $membership_start_date,
			$this->_prefix.'membership_end_date' 	=> $membership_end_date,
			$this->_prefix.'membership_user_id' 	=> $user_id,
			$this->_prefix.'event_limit' 			=> $event_limit,
		);

		$post_data['meta_input'] = apply_filters( 'el_package_metabox_input', $meta_input );

		if( $memship_id = wp_insert_post( $post_data, false ) ){
			$arr_post = [
				'ID' => $memship_id,
				'post_title' => $memship_id . ' - ' . $package_name,
			];
			wp_update_post($arr_post);

			return $memship_id;
		}else{
			return;	
		}
		
	}

	function booking_package_success( $memship_id, $method, $orderid ){
		$membership = array(
			'ID'           	=> $memship_id,
			'post_status'   => 'Publish',
			'meta_input'	=> array(
				OVA_METABOX_EVENT.'payment' => $method,
				OVA_METABOX_EVENT.'wooid'	=> $orderid
			)
		);
		wp_update_post( $membership );
		
	}


	public static function get_id_membership_by_current_user() {
		$agrs_membership = array(
			'post_type' 	=> 'manage_membership',
			'post_status' 	=> 'publish',
			'order' 		=> 'DESC',
			'orderby' 		=> 'id',
			'numberposts' 	=> 1,
			'meta_query' 	=> array(
				array(
					'key' 		=> OVA_METABOX_EVENT . 'membership_user_id',
					'value' 	=> get_current_user_id(),
					'compare' 	=> '='
				)
			),
		);
		$membership 	= get_posts( $agrs_membership );
		$id_membership 	= ! empty( $membership[0] ) ? $membership[0]->ID : '';
		return $id_membership;
	}

	public static function get_package_id_default(){
		$package_slug = EL()->options->package->get('package', '');
		$package_id = self::get_package_id_by_slug( $package_slug );

		return $package_id;
	}

	public static function get_package_id_by_slug( $package_slug = '' ){
		$args = array(
			'post_type' 	=> 'package',
			'post_status' 	=> 'publish',
			'numberposts' 	=> 1,
			'meta_query' => array(
				array(
					'key' 		=> OVA_METABOX_EVENT . 'package_id',
					'value' 	=> $package_slug,
					'compare' 	=> '='
				)
			),
			'fields' => 'ids',
		);
		$package_ids = get_posts( $args );
		$package_id = '';
		if ( ! empty( $package_ids ) ) {
			$package_id = $package_ids[0];
		}
		return $package_id;
	}

	public static function get_id_membership_by_user_id( $user_id ){
		$args = array(
			'post_type' 	=> 'manage_membership',
			'post_status' 	=> 'publish',
			'order' 		=> 'DESC',
			'orderby' 		=> 'id',
			'numberposts' 	=> 1,
			'meta_query' 	=> array(
				array(
					'key' 		=> OVA_METABOX_EVENT . 'membership_user_id',
					'value' 	=> $user_id,
					'compare' 	=> '='
				)
			),
			'fields' => 'ids',
		);
		$membership_ids = get_posts( $args );
		$membership_id 	= count( $membership_ids ) > 0 ? $membership_ids[0] : "";
		return $membership_id;
	}

	public static function get_membership_ids(){
		$args = array(
			'post_type' 		=> 'manage_membership',
			'post_status' 		=> 'any',
			'order' 			=> 'DESC',
			'orderby' 			=> 'id',
			'posts_per_page' 	=> -1,
			'fields' 			=> 'ids',
		);

		$membership_ids = get_posts( $args );

		return $membership_ids;
	}

	function get_number_created_event_by_current_user( $date ) {
		
		$agrs_event = [
			'post_type' 	=> 'event',
			'post_status' 	=> 'any',
			'author' 		=> get_current_user_id(),
			'numberposts' 	=> -1,
			'nopaging' 		=> true,
			'date_query' 	=> array(
				'after' => wp_date('Y-m-d H:i:s', $date)
			),
		];

		$events = get_posts( $agrs_event );
		
		return count($events);
	}

	static function get_id_package_by_id_meta( $id_meta ) {
		$package = get_posts(
			[
				'post_type' 		=> 'package',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> 1,
				'meta_query' 		=> [
					[
						'key' => OVA_METABOX_EVENT . 'package_id',
						'value' => $id_meta,
					]
				],
			]
		);
		$id_package = !empty($package[0]) ? $package[0]->ID : '';
		return $id_package;
	}


	function get_info_membership_by_user_id( $id_user = null ) {

		if ( $id_user == null ) $id_user = get_current_user_id();
		
		$agrs_base = [
			'post_type' 		=> 'manage_membership',
			'post_status' 		=> 'publish',
			'orderby' 			=> 'id',
			'order' 			=> 'DESC',
			"posts_per_page" 	=> 1,
			'meta_query' => [
				
				[
					'key' => OVA_METABOX_EVENT . 'membership_user_id',
					'value' => $id_user
				]
			]
		];


		$list_membership = get_posts($agrs_base);

		$date_format = get_option('date_format');
		$currency = EL()->options->general->get('currency','USD');

		$id_member = $package = $start_date_time = $end_date = $total = $status = $renew_link = $id_package = $total_events = '';

		if( $list_membership ){
			foreach ($list_membership as $value) {
				$mark = 1;
				$id_member = $value->ID;

				$start_date_time 	= get_post_meta($id_member, OVA_METABOX_EVENT . 'membership_start_date', true);
				$end_date_time 		= get_post_meta($id_member, OVA_METABOX_EVENT . 'membership_end_date', true);

				$membership_package_id 	= get_post_meta($id_member, OVA_METABOX_EVENT . 'membership_package_id', true);
				$id_package 			= EL_Package::get_id_package_by_id_meta( $membership_package_id );
				$fee_register_package 	= floatval( get_post_meta( $id_package, OVA_METABOX_EVENT.'fee_register_package', true ) );

				$event_limit = get_post_meta( $id_member, OVA_METABOX_EVENT.'event_limit', true );

				if ( $event_limit == '-1' ) {
					$total_events = esc_html__('Unlimit', 'eventlist');
				} else {
					$total_event_created = count( EL_Event::get_event_ids_by_membership_id( $id_member ) );
					$total_events = $total_event_created.'/'.absint( $event_limit );
				}

				if ( $end_date_time == '-1') {
					$end_date = esc_html__('Unlimit', 'eventlist');
				} else {
					$end_date = date_i18n($date_format, $end_date_time);
				}

				$total = el_price(get_post_meta($id_member, OVA_METABOX_EVENT . 'total', true));

				if ( get_post_status() == 'publish' ) {
					if ($mark === 1) {
						$status = esc_html__( 'Active', 'eventlist' );

						$renew_link = '<a href="#" class="register_package" data-pid="'.esc_attr( $id_package ).'"';
						$renew_link .= 'data-nonce="'.wp_create_nonce( 'el_register_package' ).'"';
						$renew_link .= 'data-currency="'.strtolower( $currency ).'"';
						$renew_link .= 'data-price="'.$fee_register_package.'"';
						$renew_link .= '>';
						$renew_link .= esc_html__( 'Renew Package', 'eventlist' );
						$renew_link .= '</a>';
					} else {
						$status = esc_html__( 'Deactive', 'eventlist' );
						$renew_link = '';
					}
					$mark++;
				} else {
					$status = get_post_status();
				}

				$title = explode('-', $value->post_title); 
				$package = '';
				for( $i = 1; $i < count( $title ); $i++ ){
					$package .= $title[$i];
				}

				$id = isset( $title[0] ) ? $title[0] : esc_html_e( 'not found', 'eventlist' );

				
			}
		}

		return array(
			'id' 				=> $id_member,
			'package' 			=> $package,
			'start_date_tmp' 	=> $start_date_time,
			'end_date' 			=> $end_date,
			'total' 			=> $total,
			'total_events' 		=> $total_events,
			'status' 			=> $status,
			'renew_link' 		=> $renew_link,
			'id_package' 		=> $id_package
		);
	}

	public static function get_package_ids_by_user_id( $user_id, $limit = 10 ){
		
		$args = array(
			'post_type' 		=> 'manage_membership',
			'post_status' 		=> 'any',
			'posts_per_page' 	=> $limit,
			'orderby' 			=> 'ID',
			'fields' 			=> 'ids',
			'meta_key' 			=> OVA_METABOX_EVENT.'membership_user_id',
			'meta_value' 		=> $user_id,
		);
		$membership_ids = get_posts( $args );
		return $membership_ids;
	}


	public static function get_history_package_by_user_id( $user_id ){
		$membership_ids = self::get_package_ids_by_user_id( $user_id );
		$history_package = [];
		$date_format = get_option('date_format');
		$currency = EL()->options->general->get('currency','USD');
		if ( count( $membership_ids ) > 0 ) {
			$j = 0;
			foreach ( $membership_ids as $id_member ) {

				$start_date_time 		= get_post_meta($id_member, OVA_METABOX_EVENT . 'membership_start_date', true);
				$end_date_time 			= get_post_meta($id_member, OVA_METABOX_EVENT . 'membership_end_date', true);

				$membership 			= get_post( $id_member );

				$membership_package_id 	= get_post_meta($id_member, OVA_METABOX_EVENT . 'membership_package_id', true);
				$id_package 			= EL_Package::get_id_package_by_id_meta( $membership_package_id );
				$fee_register_package 	= floatval( get_post_meta( $id_package, OVA_METABOX_EVENT.'fee_register_package', true ) );

				$event_limit = get_post_meta( $id_member, OVA_METABOX_EVENT.'event_limit', true );
				$renew_link = '';

				if ( $event_limit == '-1' ) {
					$total_events = esc_html__('Unlimit', 'eventlist');
				} else {
					$total_event_created = count( EL_Event::get_event_ids_by_membership_id( $id_member ) );
					$total_events = $total_event_created.'/'.absint( $event_limit );
				}

				if ( $end_date_time == '-1') {
					$end_date = esc_html__('Unlimit', 'eventlist');
				} else {
					$end_date = date_i18n($date_format, $end_date_time);
				}

				$total = el_price(get_post_meta($id_member, OVA_METABOX_EVENT . 'total', true));

				if ( get_post_status( $id_member ) == 'publish' ) {
					if ( $j === 0 ) {
						$status = esc_html__( 'Active', 'eventlist' );

						$renew_link = '<a href="#" class="register_package" data-pid="'.esc_attr( $id_package ).'"';
						$renew_link .= 'data-nonce="'.wp_create_nonce( 'el_register_package' ).'"';
						$renew_link .= 'data-currency="'.strtolower( $currency ).'"';
						$renew_link .= 'data-price="'.$fee_register_package.'"';
						$renew_link .= '>';
						$renew_link .= esc_html__( 'Renew Package', 'eventlist' );
						$renew_link .= '</a>';
					} else {
						$status = esc_html__( 'Deactive', 'eventlist' );
						$renew_link = '';
					}
				} else {
					$status = get_post_status( $id_member );
				}

				$package = get_the_title( $id_package );

				$row_data = array(
					'id' 				=> $id_member,
					'package' 			=> $package,
					'start_date_tmp' 	=> $start_date_time,
					'end_date' 			=> $end_date,
					'total' 			=> $total,
					'total_events' 		=> $total_events,
					'status' 			=> $status,
					'renew_link' 		=> $renew_link,
					'id_package' 		=> $id_package
				);

				$history_package[] = $row_data;

				$j++;
			}
		}

		return $history_package;
	}


	public function el_list_packages( ){

		$args = array(
			'post_type' 		=> 'package',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'orderby' 			=> 'meta_value_num',
			'meta_key' 			=> $this->_prefix.'order_package',
			'order'				=> 'ASC'
		);
		$packages = get_posts( $args );
		
		return $packages;
	}

	static function get_end_date_membership( $id_user = null) {
		if ( $id_user == null ) $id_user = get_current_user_id();
		$agrs = [
			'post_type' 	=> 'manage_membership',
			'post_status' 	=> 'publish',
			'orderby' 		=> 'id',
			'order' 		=> 'DESC',
			'fields' 		=> 'ids',
			'numberposts' 	=> -1,
			'meta_query' => [
				
				[
					'key' => OVA_METABOX_EVENT . 'membership_user_id',
					'value' => $id_user
				]
			]
		];

		$membership_ids = get_posts($agrs);
		
		$membership_start_date = isset($membership_ids[0]) ? get_post_meta( $membership_ids[0], OVA_METABOX_EVENT.'membership_start_date', true ) : 0;
		$membership_end_date = isset($membership_ids[0]) ? get_post_meta( $membership_ids[0], OVA_METABOX_EVENT.'membership_end_date', true ) : 0;
	
		return array( 'start_date' => $membership_start_date, 'end_date' => $membership_end_date );
	}
	

}
EL_Package::instance();