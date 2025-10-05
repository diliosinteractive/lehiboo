<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}


class EL_Booking_Package{

	public $id = 'booking_package';

    public $_title = '';

    // public $memship_id = null;
	
    function __construct(){
        
        $this->_title = esc_html__( 'Booking Package', 'eventlist' );

        
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'el_add_extra_data_to_cart_item' ), 10, 3 );
        

        // Display Extra fields to cart
        add_filter( 'woocommerce_get_item_data', array( $this, 'el_display_extra_data_cart' ), 10, 2 );

        // Change before calculate cart
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'el_woo_before_calculate_totals' ), 10, 1 );
        
        // Add Extra Fields to checkout
        add_filter( 'woocommerce_checkout_fields' , array( $this, 'el_override_checkout_fields' ) );
        
        // Add Extra fields to Order in woo
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'el_add_extra_data_to_order_items' ), 10, 4 );

        // Payment success
        $allow_active_package_by_order = EL()->options->package->get( 'allow_active_package_by_order', array( 'wc-completed', 'wc-processing' ) );
        


        if( in_array( 'wc-completed', $allow_active_package_by_order ) ){
            
            add_action( 'woocommerce_order_status_completed', array( $this, 'el_order_status_completed' ), 10, 1 );    

        }

        if( in_array( 'wc-processing', $allow_active_package_by_order ) ){

            add_action( 'woocommerce_order_status_processing', array( $this, 'el_order_status_completed' ), 10, 1 );

        }

        if( in_array( 'wc-on-hold', $allow_active_package_by_order ) ){
            
            add_action( 'woocommerce_order_status_on-hold', array( $this, 'el_order_status_completed' ), 10, 1 );    

        }


        // Filter key in Order Frontend & Backend
        add_filter( 'woocommerce_display_item_meta', array( $this, 'el_filter_woocommerce_display_item_meta' ), 10, 3 ); 
        add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'el_change_order_item_meta_title' ), 20, 3 );
        add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'el_change_order_item_meta_value' ), 20, 3 );

        if( apply_filters( 'el_hidden_fields_checkout_woo_package', true ) ){
            add_action( 'wp_head', array( $this, 'el_hook_checkout_hide' ) );
        }


        // Thank you page after booking succesfully
        if( apply_filters( 'el_payment_package_thankyou', true ) ){

            add_action( 'woocommerce_thankyou', array( $this, 'el_payment_package_thankyou' ) );

        }

    }


    public function el_payment_package_thankyou( $order_id ){

        $order = wc_get_order( $order_id );
        
        $myaccount_page_id = EL()->options->general->get( 'myaccount_page_id' );
        $url = add_query_arg( 'vendor', 'package', get_the_permalink( $myaccount_page_id ) ); ;

        $memship_id = null;

      // Loop through order line items
        foreach( $order->get_items() as $item ){

            $memship_id = $item->get_meta( 'memship_id', true );
        }

        if( (int)$memship_id ){

            if ( ! $order->has_status( 'failed' ) ) {
                wp_safe_redirect( $url );
                exit;
            }
            
        }
        
    }



    function el_add_extra_data_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
        

        // Get membership id from url
        $memship_id = ( isset( $_GET['membership_id'] ) && $_GET['membership_id'] ) ? $_GET['membership_id'] : '';

        
        if( $memship_id ){
            
            WC()->cart->empty_cart();

            $package_name   = get_the_title($memship_id);
            $membership_start_date = get_post_meta( $memship_id, OVA_METABOX_EVENT.'membership_start_date', true ) ;
            $membership_start_date = $membership_start_date ? date_i18n( get_option( 'date_format' ), $membership_start_date ) : '';

            $membership_end_date = get_post_meta( $memship_id, OVA_METABOX_EVENT.'membership_end_date', true );
            $membership_end_date = $membership_end_date == '-1' ? esc_html__( 'Unlimit', 'eventlist' ) : date_i18n( get_option( 'date_format' ), $membership_end_date );

            $cart_extra_fields = array(
                'memship_id'                => $memship_id,
                'package_name'              => $package_name, 
                'membership_start_date'     => $membership_start_date, 
                'membership_end_date'       => $membership_end_date, 
            );

            $cart_extra_fields = apply_filters( 'el_cart_extra_fields_package', $cart_extra_fields );
            
            foreach ($cart_extra_fields as $key => $value) {
                if ($value == '') {
                    unset($cart_item_data[$key]);
                }
                $cart_item_data[$key]      = $value;
            }

        }

        return $cart_item_data;
    }

    
    function el_display_extra_data_cart( $item_data, $cart_item ) {

        if ( empty( $cart_item['memship_id'] ) ) {
            return $item_data;
        }

        $memship_id = $cart_item['memship_id'];

        if( $memship_id ){
    
            $package_name   = get_the_title($memship_id);
            $membership_start_date = get_post_meta( $memship_id, OVA_METABOX_EVENT.'membership_start_date', true ) ;
            $membership_start_date = $membership_start_date ? date_i18n( get_option( 'date_format' ), $membership_start_date ) : '';

            $membership_end_date = get_post_meta( $memship_id, OVA_METABOX_EVENT.'membership_end_date', true );
            $membership_end_date = $membership_end_date == '-1' ? esc_html__( 'Unlimit', 'eventlist' ) : date_i18n( get_option( 'date_format' ), $membership_end_date );

            $cart_extra_fields = array(
                esc_html__('Package','eventlist')       => $package_name, 
                esc_html__('Start Date','eventlist')    => $membership_start_date, 
                esc_html__('End Date','eventlist')      => $membership_end_date, 
                
            );

            $cart_extra_fields = apply_filters( 'el_cart_extra_fields_package', $cart_extra_fields );

            foreach ($cart_extra_fields as $key => $value) {

                $item_data[] = array(
                    'key'     => $key,
                    'value'   => wc_clean( $value),
                    'display' => '',
                );

            }
            
           

        }

      
        return $item_data;
    }

    
    
    function el_override_checkout_fields( $fields ) {

        $memship_id = null;

        if( WC()->cart && WC()->cart->get_cart() ){
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                if( isset( $cart_item['memship_id'] ) && $cart_item['memship_id'] ){
                    $memship_id     = $cart_item['memship_id'];
                    break;  
                }
            }
        }

        if( !$memship_id ) return $fields;
        
        if( apply_filters( 'el_package_unset_billing_first_name', true ) ){
            unset($fields['billing']['billing_first_name']);
        }
        if( apply_filters( 'el_package_unset_billing_last_name', true ) ){
            unset($fields['billing']['billing_last_name']);
        }
        if( apply_filters( 'el_package_unset_billing_company', true ) ){
            unset($fields['billing']['billing_company']);
        }

        if( apply_filters( 'el_package_unset_billing_address_1', true ) ){
            unset($fields['billing']['billing_address_1']);
        }

        if( apply_filters( 'el_package_unset_billing_address_2', true ) ){
            unset($fields['billing']['billing_address_2']);
        }
        if( apply_filters( 'el_package_unset_billing_city', true ) ){
            unset($fields['billing']['billing_city']);
        }

        if( apply_filters( 'el_package_unset_billing_postcode', true ) ){
            unset($fields['billing']['billing_postcode']);
        }
        if( apply_filters( 'el_package_unset_billing_country', true ) ){
            unset($fields['billing']['billing_country']);
        }
        if( apply_filters( 'el_package_unset_billing_state', true ) ){
            unset($fields['billing']['billing_state']);
        }
        if( apply_filters( 'el_package_unset_billing_phone', true ) ){
            unset($fields['billing']['billing_phone']);
        }
        if( apply_filters( 'el_package_unset_order_comments', true ) ){
            unset($fields['order']['order_comments']);
        }
        if( apply_filters( 'el_package_unset_billing_email', true ) ){
            unset($fields['billing']['billing_email']);
        }
        if( apply_filters( 'el_package_unset_account_username', true ) ){
            unset($fields['account']['account_username']);
        }
        if( apply_filters( 'el_package_unset_account_password', true ) ){
            unset($fields['account']['account_password']);
        }
        if( apply_filters( 'el_package_unset_account_password_2', true ) ){
            unset($fields['account']['account_password-2']);
        }

        // Add default value
        $fields['billing'] = $this->el_package_default_value_billing_fields($fields['billing']);

        return $fields;
    }

    function el_package_default_value_billing_fields( $fields ){

        $phone = '';
        $email = '';
        $name = '';

        $memship_id = null;
        
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $name   = isset( $cart_item['name'] ) ? $cart_item['name'] : '';
            $phone  = isset( $cart_item['phone'] ) ? $cart_item['phone'] : '' ;
            $email  = isset( $cart_item['email'] ) ? $cart_item['email'] : '';
            $memship_id     = $cart_item['memship_id'];

        }
        if( !$memship_id ) return $fields;

        $fields['billing_first_name']['default'] = $name;
        $fields['billing_phone']['default'] = $phone;
        $fields['billing_email']['default'] = $email;


        return $fields;

    }
    
    function el_add_extra_data_to_order_items( $item, $cart_item_key, $values, $order ) {

        if ( empty( $values['memship_id'] ) ) {
            return;
        }

        
        $item->add_meta_data( 'memship_id', $values['memship_id'] );
        $item->add_meta_data( 'package_name', $values['package_name'] );
        $item->add_meta_data( 'membership_start_date', $values['membership_start_date'] );
        $item->add_meta_data( 'membership_end_date', $values['membership_end_date'] );
        
        
    }

  

    
    function el_woo_before_calculate_totals( $cart ) {

        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;

        if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
            return;

        $memship_id = '';

        if( WC()->cart && WC()->cart->get_cart() ){
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                if( isset( $cart_item['memship_id'] ) && $cart_item['memship_id'] ){
                    $memship_id     = $cart_item['memship_id'];
                    break;  
                }
            }
        }

        if( $memship_id ){
          
            $total_price = get_post_meta( $memship_id, OVA_METABOX_EVENT.'total', true );


            // Loop through cart items
            foreach ( $cart->get_cart() as $cart_item ) {

                // Get an instance of the WC_Product object
                $product = $cart_item['data'];

                // Get the product name (Added Woocommerce 3+ compatibility)
                $original_name = method_exists( $product, 'get_name' ) ? $product->get_name() : $product->post->post_title;

                $memship_title = get_the_title( $memship_id );

                // SET THE NEW NAME
                if( $memship_title ){
                    
                     // Set the new name (WooCommerce versions 2.5.x to 3+)
                    if( method_exists( $product, 'set_name' ) )
                        $product->set_name( $memship_title );
                    else
                        $product->post->post_title = $memship_title;
                }
                

               

                $cart_item['data']->set_price( $total_price );

            }

        }



    }


    function el_order_status_completed( $orderid ){
        

        $memship_id = null;

        $order = wc_get_order( $orderid );
        

         // Loop through order line items
        foreach( $order->get_items() as $item ){
            // get order item data (in an unprotected array)
            $item_data = $item->get_data();

            // get order item meta data (in an unprotected array)
            $item_meta_data = $item->get_meta_data();

            // get only additional meta data (formatted in an unprotected array)
            $formatted_meta_data = $item->get_formatted_meta_data();

            $memship_id = $item->get_meta( 'memship_id', true );
        }

        if( $memship_id ){

            $current_user_id = get_post_meta( $memship_id, OVA_METABOX_EVENT.'membership_user_id', true );
            $package = get_post_meta( $memship_id, OVA_METABOX_EVENT.'membership_package_id', true );
            update_user_meta( $current_user_id, 'package', $package );
            return EL_Package::instance()->booking_package_success( $memship_id, 'woo', $orderid );
        }
       
        return false;
    }




    /* Change at frontend */
    
    function el_filter_woocommerce_display_item_meta( $html, $item, $args ) { 

        $html = str_replace('memship_id', esc_html__('Membership ID', 'eventlist') , $html );
        $html = str_replace('package_name', esc_html__('Package', 'eventlist') , $html );
        $html = str_replace('membership_start_date', esc_html__('Start Date', 'eventlist') , $html );
        $html = str_replace('membership_end_date', esc_html__('End Date', 'eventlist') , $html );
        
        return $html;
    }



    /* Change Order at backend */
   

    /**
     * Changing a meta title
     * @param  string        $key  The meta key
     * @param  WC_Meta_Data  $meta The meta object
     * @param  WC_Order_Item $item The order item object
     * @return string        The title
     */
    function el_change_order_item_meta_title( $key, $meta, $item ) {
        
        // By using $meta-key we are sure we have the correct one.
        if ( 'memship_id' === $meta->key ) { $key = esc_html__('Membership ID', 'eventlist'); }
        if ( 'package_name' === $meta->key ) { $key = esc_html__('Package', 'eventlist'); }
        if ( 'membership_start_date' === $meta->key ) { $key = esc_html__('Start Date', 'eventlist'); }
        if ( 'membership_end_date' === $meta->key ) { $key = esc_html__('End Date', 'eventlist'); }
        
         
        return $key;
    }

   

    /**
     * Changing a meta value
     * @param  string        $value  The meta value
     * @param  WC_Meta_Data  $meta   The meta object
     * @param  WC_Order_Item $item   The order item object
     * @return string        The title
     */
    /* Change in mail */
    function el_change_order_item_meta_value( $value, $meta, $item ) {
        
        // By using $meta-key we are sure we have the correct one.
    if ( 'memship_id' === $meta->key ) { $key = esc_html__('Membership ID', 'eventlist'); }
    if ( 'package_name' === $meta->key ) { $key = esc_html__('Package', 'eventlist'); }
    if ( 'membership_start_date' === $meta->key ) { $key = esc_html__('Start Date', 'eventlist'); }
    if ( 'membership_end_date' === $meta->key ) { $key = esc_html__('End Date', 'eventlist'); }
         
        return $value;
    }

    public function el_hook_checkout_hide(){

        $memship_id = null;

        if( WC()->cart && WC()->cart->get_cart() ){
            if( WC()->cart && WC()->cart->get_cart() ){
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    if( isset( $cart_item['memship_id'] ) && $cart_item['memship_id'] ){
                        $memship_id     = $cart_item['memship_id'];
                        break;  
                    }
                }
            }
        }

        if( $memship_id ){ ?>
            <style>
                body .checkout.woocommerce-checkout #customer_details {
                    display: none!important;
                }
            </style>

        <?php }
        

    }
   

}
new EL_Booking_Package();
