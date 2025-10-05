<?php if( !defined( 'ABSPATH' ) ) exit();

if ( isset( $_ticket ) ) {
	$ticket = $_ticket;
}

$code 	= isset( $_val['code'] ) ? $_val['code'] : '';
$from 	= isset( $_val['from'] ) ? $_val['from'] : '';
$to 	= isset( $_val['to'] ) ? $_val['to'] : '';
?>

<li class="setup_item">
	<div class="setup_field">
		<label><?php esc_html_e( 'Code', 'eventlist' ); ?></label>
		<input type="text"
			class="input_code"
			placeholder="A"
			name="<?php echo esc_attr( $ticket.'['.$key.'][seat_code_setup]['.$_k.'][code]' ); ?>" required
			value="<?php echo esc_attr( $code ); ?>"
		/>
	</div>
	<div class="setup_field">
		<label><?php esc_html_e( 'From', 'eventlist' ); ?></label>
		<input type="number"
			class="input_from"
			name="<?php echo esc_attr( $ticket.'['.$key.'][seat_code_setup]['.$_k.'][from]' ); ?>"
			min="1" class="regular-number"
			placeholder="1"
			required
			value="<?php echo esc_attr( $from ); ?>"
		/>
	</div>
	<div class="setup_field">
		<label><?php esc_html_e( 'To', 'eventlist' ); ?></label>
		<input type="number"
			class="input_to"
			name="<?php echo esc_attr( $ticket.'['.$key.'][seat_code_setup]['.$_k.'][to]' ); ?>"
			placeholder="100"
			min="2" class="regular-number" required
			value="<?php echo esc_attr( $to ); ?>"
		/>
	</div>
	<a href="#" class="remove_seat_code_row">
		<span class="dashicons dashicons-trash"></span>
	</a>
	
</li>