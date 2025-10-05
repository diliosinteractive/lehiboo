<?php if ( !defined( 'ABSPATH' ) ) exit();
	$thumbnail_id = $this->get( $field['name'], $field['default'] );
?>

<div class="el-image">
	<input
		type="hidden"
		name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>"
		value="<?php echo esc_attr( $thumbnail_id ); ?>"
		<?php echo $this->render_atts( $field['atts'] ); ?>
	/>
	<div class="img">
		<?php if ( $thumbnail_id ): ?>
			<img 
				class="image-preview" 
				src="<?php echo esc_url( wp_get_attachment_url( $thumbnail_id ) ); ?>" 
				alt="<?php echo esc_attr( get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) ); ?>"
			/>
		<?php endif; ?>
	</div>
	<div class="btn-actions">
		<span class="button btn-remove"><?php esc_html_e( 'Remove image', 'eventlist' ); ?></span>
		<span class="button btn-add"><?php esc_html_e( 'Set image', 'eventlist' ); ?></span>
	</div>
</div>