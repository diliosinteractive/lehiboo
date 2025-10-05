<?php 
if ( !defined( 'ABSPATH' ) ) exit();
$format = get_option( 'date_format' );

$listing_type 	= isset( $_GET['listing_type'] ) ? sanitize_text_field( $_GET['listing_type'] ) : 'any';

$order 		= isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
$orderby 	= isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'ID';

$cat_selected 	= isset( $_GET['cat'] ) ? $_GET['cat'] : '';
$name_event   	= isset( $_GET['name_event'] ) ? $_GET['name_event'] : '';
$user_id 		= wp_get_current_user()->ID;
$paged 			= ( get_query_var('paged') ) ? get_query_var('paged') : 1;

$listing_events = get_vendor_events( $order , $orderby, $listing_type, $user_id, $paged, $name_event, $cat_selected );

?>
<tbody class="event_body">

	<?php if( $listing_events->have_posts() ) : foreach( $listing_events->posts as $post_id ) : 
		
		setup_postdata( $post_id );
		$_prefix = OVA_METABOX_EVENT;

		$event_active = get_post_meta( $post_id, $_prefix.'event_active', true ) ? get_post_meta( $post_id, $_prefix.'event_active', true ) : '0';

		$start_date 	= get_post_meta( $post_id, $_prefix.'start_date_str', true ) ? date_i18n( get_option( 'date_format' ), get_post_meta( $post_id, $_prefix.'start_date_str', true ) ) : '';
		
		$start_time 	= get_post_meta( $post_id, $_prefix.'start_date_str', true ) ? date( get_option( 'time_format' ), get_post_meta( $post_id, $_prefix.'start_date_str', true ) ) : '';

		$end_date 		= get_post_meta( $post_id, $_prefix.'end_date_str', true ) ? date_i18n( get_option( 'date_format') , get_post_meta( $post_id, $_prefix.'end_date_str', true ) ) : '';
		
		$end_time 		= get_post_meta( $post_id, $_prefix.'end_date_str', true ) ? date( get_option( 'time_format' ), get_post_meta( $post_id, $_prefix.'end_date_str', true ) ) : '';

		$status_post 	= get_post_status( $post_id );
		
		$address 		= get_post_meta( $post_id, $_prefix.'address', true );

		switch( $status_post ) {
			case 'private':{
				$status = esc_html__('private', 'eventlist');
				break;
			}
			case 'publish':{
				$status = esc_html__('publish', 'eventlist');
				break;
			}
			case 'pending':{
				$status = esc_html__('pending', 'eventlist');
				if ( $event_active == 0 ) {
					$status = esc_html__('awaiting review', 'eventlist');
				}
				break;
			}
			case 'trash':{
				$status = esc_html__('trash', 'eventlist');
				break;
			}
			case 'draft':{
				$status = esc_html__('draft', 'eventlist');
				break;
			}
			default : {
				$status = $status_post;
				break;
			}
		}


		?>
		
		<tr>
			<!-- Check Event -->
			<th class="idcheck">
				<div class="check_event">
					<label for="<?php echo esc_attr( 'select-'.$post_id ); ?>" class="el_input_checkbox">
						<input id="<?php echo esc_attr( 'select-'.$post_id ); ?>" type="checkbox" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
						<span class="checkmark"></span>
					</label>
				</div>
			</th>

			<!-- Title -->
			<td class="column-title">
				<input type="hidden" id="<?php echo esc_attr( $_prefix.'event_active' ); ?>" class="<?php echo esc_attr( $_prefix.'event_active' ); ?>" value="<?php echo esc_attr( $event_active ); ?>" name="<?php echo esc_attr( $_prefix.'event_active' ); ?>" />
				<div class="title">

					<div class="info">
						<h4 class="title">
							<a href="<?php the_permalink( $post_id ); ?>" target="_blank">
								<?php echo get_the_title( $post_id ); ?>
							</a>

							<small> - <?php echo $status; ?></small>

							<span class="status">
								<?php 
								global $event;
								$status_event = $event->get_status_event();
								echo $status_event;
								?>
							</span>
							
						</h4>

						<div class="date">
							<i class="icon_calendar"></i>
							<?php 

							EL_Vendor::instance()->display_date_event( $start_date, $start_time, $end_date, $end_time );
							?>
						</div>
						<div class="address">
							<i class="icon_building"></i>
							<?php echo esc_html( $address ); ?>
						</div>
					</div>

					<div class="action">
						<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
						<ul>
							<li>
								<a class="duplicate" href= "#" title="<?php esc_html_e( 'Duplicate this item', 'eventlist' ); ?>" rel="permalink">
									<?php esc_html_e( 'Duplicate', 'eventlist' ); ?>
								</a>
								<?php wp_nonce_field( 'el_duplicate_post_nonce', 'el_duplicate_post_nonce' ); ?>
								<div class="submit-load-more">
									<div class="load-more">
										<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
									</div>
								</div>
							</li>
							
							<li>
								<a class="edit" href="<?php echo add_query_arg( array( 'vendor' => 'listing-edit', 'id' => $post_id  ), get_myaccount_page() ); ?>">
									<?php esc_html_e( 'Edit', 'eventlist' ); ?>
								</a>
							</li>

							<?php if ($status_post != 'pending' && $status_post != 'trash') { ?>
								<li>
									<a class="pending" href="#">
										<?php esc_html_e( 'Pending', 'eventlist' ); ?>
									</a>
									<?php wp_nonce_field( 'el_pending_post_nonce', 'el_pending_post_nonce' ); ?>
								</li>
							<?php } ?>


							<?php if ($status_post == 'pending') { ?>
								<li>
									<a class="publish" href="#">
										<?php esc_html_e( 'Publish', 'eventlist' ); ?>
									</a>
									<?php wp_nonce_field( 'el_publish_post_nonce', 'el_publish_post_nonce' ); ?>
								</li>
							<?php } ?>


							<?php if ($status_post != 'trash') { ?>
								<li>
									<a class="trash" href="#">
										<?php esc_html_e( 'Trash', 'eventlist' ); ?>
									</a>
									<?php wp_nonce_field( 'el_trash_post_nonce', 'el_trash_post_nonce' ); ?>
								</li>
							<?php } ?>



							<?php if ($status_post == 'trash') { ?>
								<li>
									<a class="delete" href="#">
										<?php esc_html_e( 'Delete Permanently', 'eventlist' ); ?>
									</a>
									<?php wp_nonce_field( 'el_delete_post_nonce', 'el_delete_post_nonce' ); ?>
								</li>
								<li>
									<a class="restore" href="#">
										<?php esc_html_e( 'Restore', 'eventlist' ); ?>
									</a>
									<?php wp_nonce_field( 'el_pending_post_nonce', 'el_pending_post_nonce' ); ?>
								</li>
							<?php } ?>
							

						</ul>

					</div>
				</div>
			</td>


			<!-- Tickets -->
			<td class="column-tickets" data-colname="<?php esc_attr_e('Tickets', 'eventlist'); ?>">
				<?php
					ob_start();
			
					el_get_template( '/vendor/__events_table_tickets.php', array( 'post_id' => $post_id ) );
					
					echo ob_get_clean();
				?>
				
			</td>

			
			<!-- Action -->
			<td class="column-action" data-colname="<?php esc_attr_e('Action', 'eventlist'); ?>">
				<a href="<?php echo add_query_arg(
				array( 
				'vendor' => 'manage_event',
				'eid'	=> $post_id
				),
				get_myaccount_page() ); ?>" class="button">

				<?php esc_html_e( 'Manage Event', 'eventlist' ); ?>
				</a>
			</td>



		</tr>

	<?php endforeach; else : ?> 
		<tr>
			<th></th>
			<td colspan="3">
				
				<?php esc_html_e( 'Not Found Event', 'eventlist' ); ?>
				
			</td>
		</tr>
	<?php ; endif; wp_reset_postdata(); ?>



</tbody>

</table>

<?php 
$total = $listing_events->max_num_pages;
if ( $total > 1 ) {
	?>
	<div colspan="4" class="my_list_pagination">
		<?php echo pagination_vendor($total) ?>
	</div>
<?php } ?>
