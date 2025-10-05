<?php
$id_user = get_current_user_id();
$list_membership = EL_Package::get_history_package_by_user_id($id_user);

?>

<h3 class="vendor_heading"><?php esc_html_e("Your package", "eventlist") ?></h3>
<div class="list-package-user">
	<table>
		<thead class="event_head">
			<tr>
				<td><?php esc_html_e('ID', 'eventlist') ?></td>
				<td><?php esc_html_e('Package', 'eventlist') ?></td>
				<td><?php esc_html_e( 'Expiration date', 'eventlist' ); ?></td>
				<td><?php esc_html_e( 'Total events', 'eventlist' ); ?></td>
				<td><?php esc_html_e( 'Total', 'eventlist' ); ?></td>
				<td><?php esc_html_e( 'Status', 'eventlist' ); ?></td>
			</tr>
		</thead>
		<tbody class="event_body">
			<?php if ( count( $list_membership ) > 0 ): ?>
				<?php foreach ( $list_membership as $key => $item ): ?>
				
					<tr>
						<td><?php echo $item['id']; ?></td>
						<td><?php echo $item['package']; ?></td>
						<td><?php echo $item['end_date']; ?></td>
						<td><?php echo esc_html( $item['total_events'] ); ?></td>
						<td><?php echo $item['total']; ?></td>
						<td><?php echo $item['status'].'<br/>'.$item['renew_link']; ?></td>
					</tr>

				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
