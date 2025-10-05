<?php if ( !defined( 'ABSPATH' ) ) exit();
wp_editor(
	$this->get( $field['name'], $field['default'] ), $this->get_field_id( $field['name'] ), array(
		'textarea_name' => $this->get_field_name( $field['name'] ),
		'wpautop' 		=> false
	)
);
?>