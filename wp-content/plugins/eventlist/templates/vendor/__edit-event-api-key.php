<?php 
if( !defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
$_prefix = OVA_METABOX_EVENT;

$api_key = get_post_meta( $post_id, $_prefix.'api_key', true) ? get_post_meta( $post_id, $_prefix.'api_key', true) : '';

?>

<div class="wrap_api_key">
	
	<label for="api_key"><?php esc_html_e('User name scan QR Code in App Mobile:', 'eventlist'); ?></label>
	<input 
	type="text" 
	class="api_key" 
	value="<?php echo esc_attr( $api_key ); ?>" 
	name="<?php echo esc_attr( $_prefix.'api_key' ); ?>" 
	autocomplete="off" autocorrect="off" autocapitalize="none" 
	placeholder="<?php esc_attr_e( 'username', 'eventlist' ); ?>" 
	/>

</div>