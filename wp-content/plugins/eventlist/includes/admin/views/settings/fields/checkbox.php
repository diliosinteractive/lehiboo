<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>
<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>" >
<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>" value="1" <?php echo $this->render_atts( $field['atts'] ); ?>

<?php checked( $this->get( $field['name'], $field['default'] ), 1 ); ?>
/>