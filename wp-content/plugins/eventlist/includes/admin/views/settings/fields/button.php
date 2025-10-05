<?php
if ( ! defined('ABSPATH') ) {
	exit();
}

?>
<a href="#"
data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_setting_action' ) ); ?>"
name="<?php echo esc_attr( $field['name'] ); ?>"
<?php echo $this->render_atts( $field['atts'] ); ?> >
	<span class="dashicons dashicons-update"></span><?php echo esc_html( $field['label'] ); ?>
</a>