<?php
if( !defined( 'ABSPATH' ) ) exit();
global $post;

$extra_services = get_post_meta( $post->ID, OVA_METABOX_EVENT.'extra_service', true );

?>
<ul class="el_extra_services">
	<?php if ( ! empty( $extra_services ) ): ?>
		<?php foreach ( $extra_services as $k => $val ): ?>
			<li class="el_service_item">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e( 'Service Name', 'eventlist' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][name]' ); ?>" type="text" value="<?php echo esc_attr( $val['name'] ); ?>" class="extra_service_name regular-text" placeholder="<?php esc_attr_e( 'Enter Name', 'eventlist' ); ?>">
								<input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][id]' ); ?>" type="hidden" class="extra_service_id" value="<?php echo esc_attr( $val['id'] ); ?>">
							</td>
						</tr>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e( 'Price', 'eventlist' ); ?></label></th>
							<td><input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][price]' ); ?>" type="number" min="0" step="0.1" value="<?php echo esc_attr( $val['price'] ); ?>" placeholder="<?php echo esc_attr_e( '10', 'eventlist' ); ?>" class="extra_service_price regular-number"></td>
						</tr>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e( 'Max Quantity/Calendar', 'eventlist' ); ?></label></th>
							<td><input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][qty]' ); ?>" type="number" min="0" step="1" value="<?php echo esc_attr( $val['qty'] ); ?>" placeholder="<?php esc_attr_e( '100', 'eventlist' ); ?>" class="extra_service_qty regular-number"></td>
						</tr>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e( 'Max Quantity/Ticket', 'eventlist' ); ?></label></th>
							<td><input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][max_qty]' ); ?>" type="number" min="0" step="1" value="<?php echo esc_attr( $val['max_qty'] ); ?>" placeholder="<?php esc_attr_e( '10', 'eventlist' ); ?>" class="extra_service_max_qty regular-number"></td>
						</tr>
					</tbody>
				</table>
				<button type="button" class="el_remove_service button button-small button-secondary">&#x2715;</button>
			</li>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>

<a href="#" id="mb_add_services" data-nonce="<?php echo esc_attr( wp_create_nonce('mb_add_services') ); ?>" class="button button-primary"><?php esc_html_e( 'Add Service', 'eventlist' ); ?></a>