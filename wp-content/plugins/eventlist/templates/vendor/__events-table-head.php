<?php 
if ( !defined( 'ABSPATH' ) ) exit();
$user_id = wp_get_current_user()->ID;


$orderby = '';
$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';

$order = '';
(!isset($_GET['order']) || $_GET['order'] == 'DESC' ) ?  $order = 'ASC' :  $order = 'DESC';


$listing_type = '';
if(isset( $_GET['listing_type']) ) $listing_type = $_GET['listing_type'];

$cat_selected 	= isset( $_GET['cat'] ) ? $_GET['cat'] : '';
$name_event   	= isset( $_GET['name_event'] ) ? $_GET['name_event'] : '';

$args_parameters = array( 
	'vendor' 		=> 'listing', 
	'listing_type' 	=> $listing_type, 
	'orderby' 		=> $orderby, 
	'order' 		=> $order,
);

if ( isset( $_GET['cat'] ) ) $args_parameters['cat'] = $_GET['cat'];
if ( isset( $_GET['name_event'] ) ) $args_parameters['name_event'] = $_GET['name_event'];

?>
<table>

	<thead class="event_head">
		<tr>
			<th class="idcheck">

				<label for="check_all_event" class="el_input_checkbox" style="height: 20px;margin: 0;">
					<input type="checkbox" class="check_all_event" id="check_all_event">
					<span class="checkmark"></span>
				</label>
				
			</th>

			<td>
				<a href="<?php echo add_query_arg( $args_parameters, get_myaccount_page() ); ?>">
					<?php esc_html_e( 'Event', 'eventlist' ); ?>
					&nbsp; <i class="fas fa-sort"></i>
				</a>
			</td>

			<td><?php esc_html_e( 'Sales', 'eventlist' ); ?></td>

			<td><?php esc_html_e( 'Action', 'eventlist' ); ?></td>

		</tr>
	</thead>