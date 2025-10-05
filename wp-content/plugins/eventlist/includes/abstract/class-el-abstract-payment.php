<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

abstract class EL_Abstract_Payment{

    public $_is_active = false;
    public $_title = '';

	function __construct() {

        // Only render settings in admin
        if ( is_admin() ) {
            add_filter( 'el_admin_setting_fields', array( $this, 'generate_fields' ), 10, 2 );
        }

        $this->is_active();
    }

    public function generate_fields( $groups, $id ) {
        if ( $id === 'checkout' && $this->id ) {

            $groups[$id . '_' . $this->id] = apply_filters( 'el_admin_setting_fields_checkout', $this->fields(), $this->id );
        }

        return $groups;
    }

    

    function fields(){
    	return array();
    }

    function is_active(){
         if ( EL()->options->checkout->get( $this->id . '_active', 'no' ) === 'yes' ) {
            return $this->_is_active = true;
        }
        return $this->_is_active = false;
    }

    function get_title(){
        return $this->_title;
    }

    // Render Form
    function render_form(){

    }

}