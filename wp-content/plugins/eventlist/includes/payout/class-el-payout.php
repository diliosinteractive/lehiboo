<?php
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EL_Payout', false ) ) {
	return new EL_Payout();
}

/**
 * Admin Assets classes
 */
class EL_Payout{


	protected static $_instance = null;

	protected $_prefix = OVA_METABOX_EVENT;

	/**
	 * Constructor
	 */
	public function __construct(){
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}



	public function get_list_payout ( $paged = 1, $user_id = 1 ) {

		$agrs = [
			'post_type' 	=> 'payout',
			'post_status' 	=> 'publish',
			'author' 		=> $user_id,
			"paged" 		=> $paged,
		];


		return new WP_Query( $agrs );
	}


	// Get total earning of user
	public function get_total_profit( $user_id ){

		// Get all bookings completed
		$bookings = EL_Booking::instance()->get_list_bookings( $user_id );
		$total_profit  = 0;
		

		if( $bookings->have_posts() ) : while ( $bookings->have_posts() ) : $bookings->the_post();

			$id_booking = get_the_id();

			if( get_post_meta( $id_booking, OVA_METABOX_EVENT . 'profit', true ) ){ // Use from version 1.3.7
				$profit = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'profit', true );
			}else{
				$profit = EL_Booking::instance()->get_profit_by_id_booking( $id_booking );
			}

			$total_profit += floatval( $profit );
		

		endwhile; endif; wp_reset_postdata();

		return $total_profit;

	}


	// Get Total Amount Payout for Current User
	public function get_total_amount_payout( $user_id ) {


			$total_amount_payout  = 0;

			$agrs = [
				'post_type' 		=> 'payout',
				'post_status' 		=> 'publish',
			    'author'         	=> $user_id,
				'posts_per_page' 	=> -1, 
				'numberposts' 		=> -1,
				'nopaging' 			=> true,
				'fields' 			=> 'ids',
				'meta_query' => [
					[
						'key' => OVA_METABOX_EVENT . 'withdrawal_status',
						'value' => array( 'Pending','Completed' ),
					],
					
				],
			];

			$list_payouts = new WP_Query( $agrs );

			if($list_payouts->have_posts() ) : while ( $list_payouts->have_posts() ) : $list_payouts->the_post();

				$id = get_the_id();
				$amount = floatval( get_post_meta( $id, OVA_METABOX_EVENT . 'amount', true ) );
				$total_amount_payout += floatval( $amount );

			endwhile; endif; wp_reset_postdata(); 


		return $total_amount_payout;
	}

}
