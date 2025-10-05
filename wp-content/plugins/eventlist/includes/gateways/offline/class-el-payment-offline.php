<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

class EL_Payment_Offline extends EL_Abstract_Payment{

	public $id = 'offline';

    function __construct(){
        $this->_title = esc_html__( 'Offline', 'eventlist' );
        parent::__construct();
    }

	function fields(){
    	return array(
            'title' => esc_html__('Offline','eventlist'), // tab title
            'fields' => array(
                'fields' => array(
                    array(
                        'type' => 'select',
                        'label' => __( 'Active', 'eventlist' ),
                        'desc' => __( 'You have to active to use this gateway', 'eventlist' ),
                        'atts' => array(
                            'id' => 'offline_active',
                            'class' => 'offline_active'
                        ),
                        'name' => 'offline_active',
                        'options' => array(
                            'no' => __( 'No', 'eventlist' ),
                            'yes' => __( 'Yes', 'eventlist' )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => __( 'Send Tickets after registering successfully', 'eventlist' ),
                        'atts' => array(
                            'id' => 'offline_send_ticket',
                            'class' => 'offline_send_ticket'
                        ),
                        'name' => 'offline_send_ticket',
                        'options' => array(
                            'no' => __( 'No', 'eventlist' ),
                            'yes' => __( 'Yes', 'eventlist' )
                        )
                    ),
                   
                ),
            )
        );
		
    }


    function render_form(){
        echo esc_html( 'You have to transfer money to my bank after booking event successfully, then I will send Ticket to your email. For purpose test: we still send ticket although you don\'t transfer money. Administrator can change this option in backend.', 'eventlist' );
    }

    function process( ){
        $result = true;
        $offline_send_ticket = EL()->options->checkout->get( 'offline_send_ticket' );
        $booking_id = EL()->cart_session->get( 'booking_id' );

        if( $offline_send_ticket == 'yes' ){
            $result = EL_Booking::instance()->booking_success( $booking_id, $this->_title );    
        }else{
            $result = EL_Booking::instance()->booking_hold( $booking_id );
        }

        return array(
            'status' => 'success',
            'url' => apply_filters( 'el_offline_booking_event_url_thankyou', get_thanks_page(), 'success', $booking_id )
        );
    }

}
