<?php if( ! defined( 'ABSPATH' ) ) exit();

$category_included = $args['category_included'];

$archive_type 		= isset( $args['type'] ) ? sanitize_text_field( $args['type'] ) : EL_Setting::instance()->event->get( 'archive_type', 'type1' );
$archive_column 	= isset( $args['column'] ) ? sanitize_text_field( $args['column'] ) : EL_Setting::instance()->event->get( 'archive_column', 'two-column' );
$zoom 				= isset( $args['zoom'] ) ? (int)$args['zoom'] : '';
$marker_option 		= isset( $args['marker_option'] ) ? sanitize_text_field($args['marker_option']) : 'icon';
$marker_icon 		= isset( $args['marker_icon']['url'] ) ? esc_url($args['marker_icon']['url']) : '';
$show_featured 		= isset( $args['show_featured'] ) ? sanitize_text_field( $args['show_featured'] ) : '';
$radius_unit 		= isset( $_GET['radius_unit'] ) ? sanitize_text_field( $_GET['radius_unit'] ) : $args['radius_unit'];

$show_map 			= ( 'yes' == $args['show_map'] ) ? ' search_show_map' : '';


$events 	= el_search_event_map($show_featured);

$format 	= el_date_time_format_js();
$first_day 	= el_first_day_of_week();

$map_address 			= isset( $_GET['map_address'] ) ? sanitize_text_field( $_GET['map_address'] ) : '';
$map_lat 				= isset( $_GET['map_lat'] ) ? sanitize_text_field( $_GET['map_lat'] ) : '';
$map_lng 				= isset( $_GET['map_lng'] ) ? sanitize_text_field( $_GET['map_lng'] ) : '';
$selected_event_type 	= isset( $_GET['event_type'] ) ? sanitize_text_field( $_GET['event_type'] ) : '';
$radius 				= isset( $_GET['radius'] ) ? sanitize_text_field( $_GET['radius'] ) : 50;

$selected_name_event 	= isset( $_GET['name_event'] ) ? sanitize_text_field($_GET['name_event']) : '';
$selected_cat 			= isset( $_GET['cat'] ) ? sanitize_text_field($_GET['cat']) : '';
$selected_start_date 	= isset( $_GET["start_date"] ) ? sanitize_text_field($_GET["start_date"]) : '';
$selected_end_date 		= isset( $_GET["end_date"] ) ? sanitize_text_field($_GET["end_date"]) : '';
$selected_event_state 	= isset( $_GET['event_state'] ) ? sanitize_text_field($_GET['event_state']) : '';
$selected_event_city 	= isset( $_GET['event_city'] ) ? sanitize_text_field($_GET['event_city']) : '';
$selected_state_city 	= isset( $_GET['loc_input'] ) ? sanitize_title($_GET['loc_input']) : '';
$selected_name_venue 	= isset( $_GET['name_venue'] ) ? sanitize_text_field($_GET['name_venue']) : '';

$get_time 				= isset( $_GET['time'] ) ? sanitize_text_field($_GET['time']) : '';
$selected_today 		= ($get_time == 'today') ? 'selected="selected"' : '';
$selected_tomorrow 		= ($get_time == 'tomorrow') ? 'selected="selected"' : '';
$selected_this_week 	= ($get_time == 'this_week') ? 'selected="selected"' : '';
$selected_this_week_end = ($get_time == 'this_week_end') ? 'selected="selected"' : '';
$selected_next_week 	= ($get_time == 'next_week') ? 'selected="selected"' : '';
$selected_next_month 	= ($get_time == 'next_month') ? 'selected="selected"' : '';

$lat_default = EL_Setting::instance()->event->get( 'latitude_map_default' ) ? EL_Setting::instance()->event->get( 'latitude_map_default' ) : '39.177972';
$lng_default = EL_Setting::instance()->event->get( 'longitude_map_default' ) ? EL_Setting::instance()->event->get( 'longitude_map_default' ) : '-100.363750';
$list_taxonomy_register = EL_Post_Types::register_taxonomies_customize();

$data_taxonomy_custom = array();

$restrictions 	= EL()->options->general->get('event_retrict') ? EL()->options->general->get('event_retrict') : array();
$event_bound 	= EL()->options->general->get('event_bound');
$event_lat 		= EL()->options->general->get('event_lat');
$event_lng 		= EL()->options->general->get('event_lng');
$event_radius 	= EL()->options->general->get('event_radius');

// price range slider
$start_slider 	= $args['start_slider'];
$end_slider 	= $args['end_slider'];
$min_slider 	= $args['min_slider'];
$max_slider 	= $args['max_slider'];
?>

<div class="elementor_search_map">
	
	<div class="el_search_filters wrap_search_map">

		<?php if ($args['show_map'] == 'yes') { ?>
			<div class="toggle_wrap">
				<span data-value="result_search" class="active"><?php esc_html_e( 'Results', 'eventlist' ); ?></span>
				<span data-value="show_map"><?php esc_html_e( 'Map', 'eventlist' ); ?></span>
			</div>
		<?php } ?>

		<div class="wrap_search">

			<div id="result_search" class="job_listings_search" style="<?php echo esc_attr($args['show_map'] == '' ? 'width: 100%; padding: 0;' : ''); ?>">

				<?php if ($args['show_filter'] == 'yes') { ?>
					<span class="toggle_filters ">
						<?php esc_html_e( 'Toggle Filters', 'eventlist' ); ?>
						<i class="icon_down arrow_triangle-down"></i>
						<i class="icon_up arrow_triangle-up"></i>
					</span>

					<form class="job_filters" autocomplete="off" autocorrect="off" autocapitalize="none">
						<div class="search_jobs">
							<?php
							
							foreach ($args as $key => $value) {
								if( strpos($key,'pos') !== false ){
									switch ( $args[$key] ) {
										
										case 'range_slider':
										?>
										<div class="label_search wrap_range_price">
											
											<input type="hidden" name="el_max_price">
											<input type="hidden" name="el_min_price">
											<div class="range_price_box">
												<div class="range_price_box_wrap"
													data-start="<?php echo esc_attr( $start_slider ); ?>"
													data-end="<?php echo esc_attr( $end_slider ); ?>"
													data-min="<?php echo esc_attr( $min_slider ); ?>"
													data-max="<?php echo esc_attr( $max_slider ); ?>">
													<div id="init_range_price"></div>
												</div>
											</div>
										</div>
										<?php
											break;
										/* Name Event */
										case 'name_event':
										?>
										<div class="label_search wrap_search_keywords">
											<input type="text" name="keywords" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="<?php esc_attr_e( 'What are you looking for?', 'eventlist' ); ?>" value="<?php echo esc_attr($selected_name_event); ?>"/>
										</div>
										<?php
										break;

										/* Location */
										case 'location':
										?>
										<div class="label_search wrap_search_location">

											<input type="hidden" name="map_lat" id="map_lat" value="<?php echo esc_attr( $map_lat ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none"/>
											<input type="hidden" name="map_lng" id="map_lng" value="<?php echo esc_attr( $map_lng ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none"/>

											<input type="text" id="pac-input" name="map_address" value="<?php echo esc_attr( $map_address ); ?>" class="controls" placeholder="<?php esc_attr_e( 'Location', 'eventlist' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
											<i class="locate-me icon_circle-slelected" id="locate-me" data-tippy-content="<?php echo esc_attr__( 'Find my location', 'eventlist' ); ?>"></i>


											<div id="infowindow-content">
												<span id="place-name" class="title"></span>
												<span id="place-address"></span>
											</div>

											<input type="hidden" value="" name="map_name" id="map_name"  autocomplete="off" autocorrect="off" autocapitalize="none"/>
										</div>
										<?php
										break;

										/* Categories */
										case 'cat':
										?>
										<div class="label_search wrap_search_cat">
											<?php el_get_taxonomy4('event_cat', 'cat', $selected_cat, 'false', $category_included ); ?>
										</div>
										<?php
										break;

										/* All Time */
										case 'all_time'
										: ?>
										<div class="label_search wrap_search_time">
											<select name="time">
												<option value="" ><?php esc_html_e('All Time', 'eventlist'); ?></option>
												<option value="today" <?php echo esc_attr( $selected_today ); ?> ><?php esc_html_e('Today', 'eventlist'); ?></option>
												<option value="tomorrow" <?php echo esc_attr( $selected_tomorrow ); ?> ><?php esc_html_e('Tomorrow', 'eventlist'); ?></option>
												<option value="this_week" <?php echo esc_attr( $selected_this_week ); ?> ><?php esc_html_e('This Week', 'eventlist'); ?></option>
												<option value="this_week_end" <?php echo esc_attr( $selected_this_week_end ); ?> ><?php esc_html_e('This Weekend', 'eventlist'); ?></option>
												<option value="next_week" <?php echo esc_attr( $selected_next_week ); ?> ><?php esc_html_e('Next Week', 'eventlist'); ?></option>
												<option value="next_month" <?php echo esc_attr( $selected_next_month ); ?> ><?php esc_html_e('Next Month', 'eventlist'); ?></option>
											</select>
										</div>
										<?php 
										break;

										/* Start Event */
										case 'start_event':
										?>
										<div class="label_search wrap_search_start_date">
											<input type="text" class="el_select_date" placeholder="<?php esc_attr_e('Start date ...', 'eventlist'); ?>" name="start_date" value="<?php echo esc_attr($selected_start_date); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
										</div>
										<?php
										break;

										/* End Event */
										case 'end_event':
										?>
										<div class="label_search wrap_search_end_date">
											<input type="text" class="el_select_date" placeholder="<?php esc_attr_e('End date ...', 'eventlist'); ?>" name="end_date" value="<?php echo esc_attr($selected_end_date); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
										</div>
										<?php
										break;

										/* Venue */
										case 'venue':
										?>
										<div class="venue label_search">
											<input type="text" class="form-control" placeholder="<?php esc_html_e('Venue ...', 'eventlist'); ?>" name="name_venue" value="<?php echo esc_attr($selected_name_venue); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
										</div>
										<?php
										break;

										/* Location State */
										case 'loc_state':
										?>
										<div class="loc_state label_search">
											<?php if ($selected_state_city) {
												el_get_state($selected_state_city);
											} else {
												el_get_state($selected_event_state);
											} ?>
										</div>
										<?php
										break;

										/* Location City */
										case 'loc_city':
										?>
										<div class="loc_city label_search">
											<?php if ($selected_state_city) {
												el_get_city($selected_state_city);
											} else {
												el_get_city($selected_event_city);
											} ?>
										</div>
										<?php
										break;

										// Event Type
										case 'event_type':
										?>
											<div class="label_search event_type">
												<select name="event_type" id="">
													<option value="">
														<?php esc_html_e( 'Select Type Event', 'eventlist' ); ?>
													</option>
													<option value="online" <?php echo selected( $selected_event_type, "online" ); ?>>
														<?php esc_html_e( 'Online', 'eventlist' ); ?>
													</option>
													<option value="classic" <?php echo selected( $selected_event_type, "classic" ); ?>>
														<?php esc_html_e( 'Offline', 'eventlist' ); ?>
													</option>
												</select>
											</div>
										<?php
										break;

										default:
										// code...
										break;
									}
								}
							}
							// end foreach args
							$list_taxonomy_custom = $args['list_taxonomy_custom'];


							$arr_list_taxonomy = [];
							if( $list_taxonomy_custom && is_array( $list_taxonomy_custom ) && $args['show_filter'] === 'yes' ) {
								foreach( $list_taxonomy_custom as $taxo ) {			
									$taxos = el_get_taxonomy($taxo['taxonomy_custom']);
									$name_taxos_register = '';
									if( ! empty( $list_taxonomy_register ) && is_array( $list_taxonomy_register ) ) {
										foreach( $list_taxonomy_register as $taxonomy_register ) {
											if( $taxonomy_register['slug'] == $taxo['taxonomy_custom'] ) {
												$name_taxos_register = $taxonomy_register['name'];
											}
										}
									}


									if( $taxo['taxonomy_custom'] ) {
										$selected_taxonomy = isset( $_GET[$taxo['taxonomy_custom']] ) ? sanitize_text_field($_GET[$taxo['taxonomy_custom']]) : ''; 
										$data_taxonomy_custom[$taxo['taxonomy_custom']] = $selected_taxonomy;
										$arr_list_taxonomy[] = $taxo['taxonomy_custom'];
									?>
									<div class="label_search ">
										<select name="<?php echo esc_attr( $taxo['taxonomy_custom'] ) ?>" class="selectpicker">
											<option value=""><?php echo sprintf( esc_html__( 'Select %s', 'eventlist' ), esc_html($name_taxos_register) ); ?></option>
											<?php foreach( $taxos as $tax ) {
												$tax_slug = isset( $tax->slug ) ? apply_filters( 'editable_slug', $tax->slug, $tax ) : '';
												?>
												<option value="<?php echo esc_attr( $tax_slug ); ?>" <?php echo selected($tax->slug ,$selected_taxonomy) ?>  ><?php echo esc_html( $tax->name ); ?></option>
											<?php } ?>
										</select>
									</div>
									<?php
									}
								}
							}
							$str_json_list_taxonomy = json_encode( $arr_list_taxonomy );

							
							?>

							

							<input type="hidden" id="el_search_map_list_taxonomy" value="<?php echo esc_attr( $str_json_list_taxonomy ) ?>" >
							<input type="hidden" id="data_taxonomy_custom" value="<?php echo esc_attr( json_encode( $data_taxonomy_custom ) ) ?>" >
						</div>

						<div class="wrap_search_radius">
							<span><?php esc_html_e( 'Radius:', 'eventlist' ); ?></span>
							<span class="result_radius"><?php echo sprintf( esc_html__( '%1$s %2$s', 'eventlist' ), esc_html($radius), esc_html( $radius_unit ) ); ?></span>
							<div id="wrap_pointer"></div>
							<input type="hidden" value="<?php echo esc_attr( apply_filters( 'el_map_range_radius', $radius ) ); ?>" name="radius" data-radius-unit="<?php echo esc_attr( $radius_unit ); ?>">
						</div>

						<div class="wrap_search_filter_title">
							
							<div class="listing_found"></div>

							<div id="search_sort">
								<?php 
									$sort_event_setting = EL_Setting::instance()->event->get( 'archive_order_by' );
									switch ( $sort_event_setting ) {
										case 'title':
											$search_event_sort_default = 'a-z';
											break;
										case 'ID':
											$search_event_sort_default = 'near';
											break;	
										case 'start_date':
											$search_event_sort_default = 'start-date';
											break;
										case 'end_date':
											$search_event_sort_default = 'end-date';
										break;
										case 'near':
											$search_event_sort_default = 'near';
										break;
										case 'date_desc':
											$search_event_sort_default = 'date-desc';
										break;
										case 'date_asc':
											$search_event_sort_default = 'date-asc';
										break;
										default:
											$search_event_sort_default = 'start-date';
											break;
									}
								 ?>
								<?php $search_event_sort_default = $search_event_sort_default ? $search_event_sort_default : apply_filters( 'search_event_sort_default', 'date-desc' ); ?>
								<select name="sort">
									<option value=""><?php esc_html_e( 'Sort By', 'eventlist' ); ?></option>
									<option value="near" <?php if( $search_event_sort_default == 'near' ) echo 'selected'; ?> ><?php esc_html_e( 'Nearest', 'eventlist' ); ?></option>
									<option value="date-desc" <?php if( $search_event_sort_default == 'date-desc' ) echo 'selected'; ?> ><?php esc_html_e( 'Newest First', 'eventlist' ); ?></option>
									<option value="date-asc" <?php if( $search_event_sort_default == 'date-asc' ) echo 'selected'; ?> ><?php esc_html_e( 'Oldest First', 'eventlist' ); ?></option>
									<option value="start-date" <?php if( $search_event_sort_default == 'start-date' ) echo 'selected'; ?> ><?php esc_html_e( 'Start Date', 'eventlist' ); ?></option>
									<option value="end-date" <?php if( $search_event_sort_default == 'end-date' ) echo 'selected'; ?> ><?php esc_html_e( 'End Date', 'eventlist' ); ?></option>
									<option value="a-z" <?php if( $search_event_sort_default == 'a-z' ) echo 'selected'; ?> ><?php esc_html_e( 'A-Z', 'eventlist' ); ?></option>
									<option value="z-a" <?php if( $search_event_sort_default == 'z-a' ) echo 'selected'; ?> ><?php esc_html_e( 'Z-A', 'eventlist' ); ?></option>
								</select>
							</div>
						</div>
					</form>
				<?php } ?>
				<div class="wrap_load_more" style="display: none;">
					<svg class="loader" width="50" height="50">
						<circle cx="25" cy="25" r="10" stroke="#e86c60"/>
						<circle cx="25" cy="25" r="20" stroke="#e86c60"/>
					</svg>
				</div>

				<div id="search_result" class="search_result<?php echo esc_attr( $show_map ); ?>" data-type="<?php echo esc_attr($archive_type); ?>" data-column="<?php echo esc_attr($archive_column); ?>" data-zoom="<?php echo esc_attr($zoom); ?>" data-lat="<?php echo esc_attr($lat_default); ?>" data-lng="<?php echo esc_attr($lng_default); ?>" data-marker_option="<?php echo esc_attr($marker_option); ?>" data-marker_icon="<?php echo esc_attr($marker_icon); ?>"  data-show_featured="<?php echo esc_attr($show_featured); ?>" data-restrictions="<?php echo esc_attr( json_encode($restrictions) ); ?>" data-bound="<?php echo esc_attr( $event_bound ); ?>" data-bound-lat="<?php echo esc_attr( $event_lat ); ?>" data-bound-lng="<?php echo esc_attr( $event_lng ); ?>" data-bound-radius="<?php echo esc_attr( $event_radius ); ?>"></div>

			</div>

			<?php $show_map = ( $args['show_map'] == 'yes' ? 'display: block;' : 'display: none'); ?>
			<div class="wrap_show_map" style="<?php echo esc_attr( $show_map ); ?>" >
				<div id="show_map"></div>
			</div>
		</div>

	</div>

</div>