<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<?php
$multiple = false;
if ( isset( $field['atts'], $field['atts']['multiple'] ) && $field['atts']['multiple'] ) {
    $multiple = true;
}
?>

<select name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ) . ( $multiple ? '[]' : '' ); ?>"
    <?php echo $this->render_atts( $field['atts'] ); ?>>
    <?php if ( $field['options'] ): ?>
        <?php foreach ( $field['options'] as $key => $value ):
            $val = $this->get( $field['name'] ) ? $this->get( $field['name'] ) : array();

            if ( $val == '' && isset( $field['default'] ) ) {
            	$val = $field['default'];
            }
        ?>
            <?php if ( $multiple ): ?>
    			<!--Multi select-->
               <option value="<?php echo esc_attr( $key ); ?>" <?php echo in_array( $key, $val ) ? esc_attr(' selected="selected"') : ''; ?>>
                    <?php echo esc_html( $value ); ?>
                </option>
            <?php else: ?>
             	<option value="<?php echo esc_attr( $key ); ?>"<?php echo $val == $key ? esc_attr(' selected="selected"') : ''; ?>>
                    <?php echo esc_html( $value ); ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</select>