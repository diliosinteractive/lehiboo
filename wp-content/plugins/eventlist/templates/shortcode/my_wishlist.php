<?php if( ! defined( 'ABSPATH' ) ) exit();

if (isset($_COOKIE['el_wl_event'])) {
	$list_event_wishlist = $_COOKIE['el_wl_event'];
	$list_event_wishlist = str_replace("\\", "", $list_event_wishlist);
	$list_event_wishlist = json_decode($list_event_wishlist, true);
	
	$date_format = get_option('date_format');
	$time_format = get_option('time_format');

	?>
	<div class="el-my-wishlist">
		<table>
			<thead class="event_head">
				<tr>
					<td><?php esc_html_e("Event Name", "eventlist") ?></td>
					<td><?php esc_html_e("Start date", "eventlist") ?></td>
					<td><?php esc_html_e("End date", "eventlist") ?></td>
					<td><?php esc_html_e("Address", "eventlist") ?></td>
				</tr>
			</thead>
			<tbody class="event_body">
				<?php
				if (!empty($list_event_wishlist) && is_array($list_event_wishlist)) {
					foreach($list_event_wishlist as $id_event) {
						$id_event 	= sanitize_text_field($id_event);
						$address 	= get_post_meta( $id_event, OVA_METABOX_EVENT . 'address', true);
						$time_start = get_post_meta( $id_event, OVA_METABOX_EVENT . 'start_date_str', true  );
						$time_end 	= get_post_meta( $id_event, OVA_METABOX_EVENT . 'end_date_str', true  );

						?>
						<tr>
							<td><a data-id="<?php echo esc_attr($id_event) ?>" class="close-wl" href="javascript: void(0)"><i class="fa fa-times" ></i></a><a class="title" href="<?php echo esc_url( get_permalink($id_event) ); ?>">
								<?php echo esc_html( get_the_title($id_event) ); ?>
								</a></td>
							<?php if(!empty($time_start)) : ?>
								<td><?php echo esc_html( date_i18n($date_format, $time_start) . ' @ ' . date_i18n($time_format, $time_start) ); ?></td>
							<?php endif ?>
							<?php if(!empty($time_end)) : ?>
								<td><?php echo esc_html( date_i18n($date_format, $time_end) . ' @ ' . date_i18n($time_format, $time_end) ); ?></td>
							<?php endif ?>
							<?php if( !empty($address) ) { ?>
								<td><?php echo esc_html($address); ?></td>
							<?php } ?>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
}else{
	esc_html_e( 'You don\'t have any event in wishlist.', 'eventlist' );
}
?>