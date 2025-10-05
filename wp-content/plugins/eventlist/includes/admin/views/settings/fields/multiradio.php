<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<?php if ( !empty( $field['options'] ) ): ?>
	<div class="ova_setting_radio_field">
		<?php $i = 0;
			foreach ( $field['options'] as $key => $val ):
				$checked = $this->get( $field['name'] );
				if ( $i == 0 && ! $this->get( $field['name'] ) ) {
					$checked = $field['default'];
				}
				?>
			<p>
				<input type="radio"
				name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>"
				value="<?php echo esc_attr( $key ); ?>"
				id="<?php echo esc_attr( $this->get_field_id( $field['name'] ).'_'. $key ); ?>"
				<?php checked( $checked, $key); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( $field['name'] ).'_'. $key ); ?>">
					<?php echo esc_attr( $val ); ?></label>
			</p>
		<?php $i+= 1; endforeach; ?>
	</div>
<?php endif; ?>