<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>


<select name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>"
    <?php echo $this->render_atts( $field['atts'] ); ?>>
    <?php if ( $field['options'] ): ?>
        <?php foreach ( $field['options'] as $key => $value ):
            $val = $this->get( $field['name'] ) ? $this->get( $field['name'] ) : '';

            if ( empty( $val ) && isset( $field['default'] ) ) {
            	$val = $field['default'];
            }
        ?>
         	<option value="<?php echo esc_attr( $key ); ?>"<?php echo $val == $key ? esc_attr( ' selected="selected"' ) : ''; ?>>
                <?php echo esc_html( $value ); ?>
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>