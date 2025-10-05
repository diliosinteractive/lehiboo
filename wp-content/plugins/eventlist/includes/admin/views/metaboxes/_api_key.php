<?php if( !defined( 'ABSPATH' ) ) exit(); ?>

<div class="wrap_api_key">

	<label for="api_key"><?php esc_html_e('User name scan QR Code:', 'eventlist'); ?></label>
	<input 
	type="text" 
	id="api_key" 
	class="api_key" 
	value="<?php echo esc_attr( $this->get_mb_value( 'api_key' ) ? $this->get_mb_value( 'api_key') : '' ); ?>" 
	name="<?php echo esc_attr( $this->get_mb_name( 'api_key' ) ); ?>" 
	autocomplete="off" autocorrect="off" autocapitalize="none" 
	placeholder="<?php esc_attr_e( 'username', 'eventlist' ); ?>" 
	/>

</div>