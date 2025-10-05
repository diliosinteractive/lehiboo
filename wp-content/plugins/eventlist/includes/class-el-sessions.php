<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );

if ( !session_id() ) {
    @session_start(['read_and_close' => true]);
}

/**
 * Session classes
 */
class EL_Sessions{

	protected static $_instance = null;

	public $prefix = null;

	public $session = null;


	/**
	 * Constructor
	 */
	public function __construct( $prefix ){

		if ( !$prefix ) return;

		$this->prefix = $prefix;

		// get all session
        $this->session = $this->load();

	}


	public function load(){

		if ( isset( $_SESSION[$this->prefix] ) ) {
			return $_SESSION[$this->prefix];
		}
		return array();

	}

	public function set( $name, $value = '' ){
		
		if( !$name ) return;

		if( !$value ){
			unset( $this->session[$name] );
		}else{
			$this->session[$name] = $value;
		}


		$_SESSION[$this->prefix] = $this->session;
		
	}

	public function remove(){

		if ( isset( $_SESSION[$this->prefix] ) ) {
            unset( $_SESSION[$this->prefix] );
        }

	}

	public function get( $name, $default = '' ){

		if( isset( $this->session[$name] ) ){
			return $this->session[$name];
		}

	}

	public static function instance( $prefix = '' ) {
		if ( !empty( self::$_instance[$prefix] ) )
            return self::$_instance[$prefix];

        return self::$_instance[$prefix] = new self( $prefix );
	}
	
}

