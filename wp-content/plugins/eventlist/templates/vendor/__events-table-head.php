<?php
if ( !defined( 'ABSPATH' ) ) exit();

$user_id = wp_get_current_user()->ID;

$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
$order = (!isset($_GET['order']) || $_GET['order'] == 'DESC' ) ? 'ASC' : 'DESC';

$listing_type = isset( $_GET['listing_type'] ) ? $_GET['listing_type'] : '';
$cat_selected = isset( $_GET['cat'] ) ? $_GET['cat'] : '';
$name_event = isset( $_GET['name_event'] ) ? $_GET['name_event'] : '';

$args_parameters = array(
	'vendor' 		=> 'listing',
	'listing_type' 	=> $listing_type,
	'orderby' 		=> $orderby,
	'order' 		=> $order,
);

if ( isset( $_GET['cat'] ) ) $args_parameters['cat'] = $_GET['cat'];
if ( isset( $_GET['name_event'] ) ) $args_parameters['name_event'] = $_GET['name_event'];

?>
<table class="el-events-listing-table">
	<thead class="event_head">
		<tr>
			<!-- Checkbox -->
			<th class="col-checkbox">
				<label for="check_all_event" class="el_input_checkbox" style="height: 20px; margin: 0;">
					<input type="checkbox" class="check_all_event" id="check_all_event">
					<span class="checkmark"></span>
				</label>
			</th>

			<!-- Image -->
			<th class="col-image">
				<?php esc_html_e( 'Image', 'eventlist' ); ?>
			</th>

			<!-- Événement -->
			<th class="col-event">
				<a href="<?php echo add_query_arg( $args_parameters, get_myaccount_page() ); ?>">
					<?php esc_html_e( 'Événement', 'eventlist' ); ?>
					<i class="fas fa-sort"></i>
				</a>
			</th>

			<!-- Prochaines dates -->
			<th class="col-dates">
				<?php esc_html_e( 'Prochaines dates', 'eventlist' ); ?>
			</th>

			<!-- Réservations -->
			<th class="col-reservations">
				<?php esc_html_e( 'Réservations', 'eventlist' ); ?>
			</th>

			<!-- Actions -->
			<th class="col-actions">
				<?php esc_html_e( 'Actions', 'eventlist' ); ?>
			</th>
		</tr>
	</thead>
