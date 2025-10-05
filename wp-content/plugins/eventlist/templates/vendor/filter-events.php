<?php 
if ( !defined( 'ABSPATH' ) ) exit();
$cat_selected 	= isset( $_GET['cat'] ) ? $_GET['cat'] : '';
$name_event   	= isset( $_GET['name_event'] ) ? $_GET['name_event'] : '';

$listing_type 	= isset( $_GET['listing_type'] ) ? $_GET['listing_type'] : '';
$orderby 		= isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
$order 			= isset( $_GET['order'] ) ? $_GET['order'] : '';
$action 		= get_permalink();

?>
<div class="filter_events">
	<form method="GET" name="filter_events_form" action="<?php echo esc_url( $action ); ?>" class="filter_events_form" autocomplete="off" autocorrect="off" autocapitalize="none">
		<input type="hidden" name="vendor" class="vendor" value="listing">

		<?php if ( $listing_type ): ?>
			<input type="hidden" name="listing_type" class="listing_type" value="<?php echo esc_attr( $listing_type ); ?>">
		<?php endif; ?>
		<div class="filter_name">
			<input type="text" name="name_event" id="name_event" class="name_event" value="<?php echo esc_attr( $name_event ); ?>" placeholder="<?php esc_html_e( 'Name Event', 'eventlist' ); ?>">
		</div>
		<div class="filter_cat">
			<?php el_get_taxonomy2('event_cat', 'cat', $cat_selected, false); ?>
		</div>
		<div class="filter_orderby">
			<select name="orderby">
				<option value="" <?php selected( $orderby, "" ); ?> ><?php esc_html_e( 'Sort by', 'eventlist' ); ?></option>
				<option value="ID" <?php selected( $orderby, "ID" ); ?> ><?php echo esc_html("ID"); ?></option>
				<option value="title" <?php selected( $orderby, "title" ); ?> ><?php esc_html_e("Title",'eventlist'); ?></option>
				<option value="start_date" <?php selected( $orderby, "start_date" ); ?> ><?php esc_html_e("Start Date",'eventlist'); ?></option>
			</select>
		</div>
		<div class="filter_order">
			<select name="order">
				<option value="" <?php selected( $order, "" ); ?> ><?php esc_html_e( 'Sort order', 'eventlist' ); ?></option>			
				<option value="ASC" <?php selected( $order, "ASC" ); ?> ><?php esc_html_e( 'Ascending', 'eventlist' ); ?></option>			
				<option value="DESC" <?php selected( $order, "DESC" ); ?> ><?php esc_html_e( 'Descending', 'eventlist' ); ?></option>			
			</select>
		</div>

		<input type="submit" value="<?php esc_html_e('Search', 'eventlist'); ?>" class="submit_filter_events" />
	</form>
</div>