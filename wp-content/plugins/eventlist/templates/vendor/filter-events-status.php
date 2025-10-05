<?php 
if ( !defined( 'ABSPATH' ) ) exit();
$user_id = wp_get_current_user()->ID;

$El_event_all = get_vendor_events( 'ASC' , 'title', 'any', $user_id );
$El_event_publish = get_vendor_events( 'ASC' , 'title', 'publish', $user_id );
$El_event_pending = get_vendor_events( 'ASC' , 'title', 'pending', $user_id );
$El_event_trash = get_vendor_events( 'ASC' , 'title', 'trash', $user_id );

$El_event_open = get_vendor_events( 'ASC' , 'title', 'open', $user_id );
$El_event_closed = get_vendor_events( 'ASC' , 'title', 'closed', $user_id );

$listing_type = isset( $_GET['listing_type'] ) ? (string)$_GET['listing_type'] : '';
?>
<div class="header_listing">

	<button type="button" class="btn btn_toggle_filter"><span><?php esc_html_e( 'Filter', 'eventlist' ); ?></span><i class="fas fa-caret-down"></i></button>

	<ul class="menu_tab">
		<li class="<?php echo ($listing_type == 'any' || $listing_type == '') ?  esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo add_query_arg( array( 'vendor' => 'listing', 'listing_type' => 'any' ), get_myaccount_page() ); ?>">
				<?php esc_html_e( 'All', 'eventlist' ); ?>
				<span>(<?php echo $El_event_all->found_posts; ?>)</span>
			</a>
		</li>
		
		<li class="<?php echo ($listing_type == 'open') ?  esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo add_query_arg( array( 'vendor' => 'listing', 'listing_type' => 'open' ), get_myaccount_page() ); ?>">
				<?php esc_html_e( 'Open', 'eventlist' ); ?>
				<span>(<?php echo $El_event_open->found_posts; ?>)</span>
			</a>
		</li>

		<li class="<?php echo ($listing_type == 'closed') ?  esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo add_query_arg( array( 'vendor' => 'listing', 'listing_type' => 'closed' ), get_myaccount_page() ); ?>">
				<?php esc_html_e( 'Closed', 'eventlist' ); ?>
				<span>(<?php echo $El_event_closed->found_posts; ?>)</span>
			</a>
		</li>

		<li class="<?php echo ($listing_type == 'publish') ?  esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo add_query_arg( array( 'vendor' => 'listing', 'listing_type' => 'publish' ), get_myaccount_page() ); ?>">
				<?php esc_html_e( 'Publish', 'eventlist' ); ?>
				<span>(<?php echo esc_html($El_event_publish->found_posts); ?>)</span>
			</a>
		</li>

		<li class="<?php echo ($listing_type == 'pending') ?  esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo add_query_arg( array( 'vendor' => 'listing', 'listing_type' => 'pending' ), get_myaccount_page() ); ?>">
				<?php esc_html_e( 'Pending', 'eventlist' ); ?>
				<span>(<?php echo esc_html($El_event_pending->found_posts); ?>)</span>
			</a>
		</li>

		<li class="<?php echo ($listing_type == 'trash') ?  esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo add_query_arg( array( 'vendor' => 'listing', 'listing_type' => 'trash' ), get_myaccount_page() ); ?>">
				<?php esc_html_e( 'Trash', 'eventlist' ); ?><span>(<?php echo esc_html( $El_event_trash->found_posts); ?>)</span>
			</a>
		</li>
	</ul>

</div>