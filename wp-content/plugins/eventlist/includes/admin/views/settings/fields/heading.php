<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<h3 class="heading_settings">
	<?php echo wp_kses_post( $this->get( $field['name'], $field['default'] ) ); ?>
</h3>
