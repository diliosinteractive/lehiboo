<?php

/**
 * EventList Setup
 * @package EventList
 * @since 1.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Settings Class
 */
class EL_Setting{

	/**
	 * instance
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * $_options
	 * @var null
	 */
	public $_options = null;

	public $_id = null;

	/**
	 * prefix option name
	 * @var string
	 */
	public $_prefix = 'ova_eventlist';

	public function __construct( $prefix = null, $id = null ) {

		if ( $prefix ) {
			$this->_prefix = $prefix;
		}

		$this->_id = $id;

		// load options
		$this->options();

		add_action( 'admin_init', array( $this, 'register_setting' ) );
	}

	public function __get( $id = null ) {
		$settings = apply_filters( 'el_settings_field', array() );
		if ( array_key_exists( $id, $settings ) ) {
			return $settings[ $id ];
		}

		return null;
	}

	public function register_setting() {
		register_setting( $this->_prefix, $this->_prefix );
	}

	/**
	 * options load options
	 * @return array || null
	 */
	protected function options() {
		if ( $this->_options ) {
			return $this->_options;
		}

		return $this->_options = get_option( $this->_prefix, null );
	}

	/**
	 * get option value
	 *
	 * @param  $name
	 *
	 * @return option value. array, string, boolean
	 */
	public function get( $name = null, $default = null ) {
		if ( ! $this->_options ) {
			$this->_options = $this->options();
		}

		if ( $name && isset( $this->_options[ $name ] ) ) {
			return $this->_options[ $name ];
		}

		return $default;
	}
	
	static function instance( $prefix = null, $id = null ) {

		if ( ! empty( self::$_instance[ $prefix ] ) ) {
			return self::$_instance[ $prefix ];
		}

		return self::$_instance[ $prefix ] = new self( $prefix, $id );
	}
	
}