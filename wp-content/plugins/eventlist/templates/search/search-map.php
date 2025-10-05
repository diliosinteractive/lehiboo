<?php if( ! defined( 'ABSPATH' ) ) exit();

$archive_type = isset( $args['type'] ) ? sanitize_text_field( $args['type'] ) : EL_Setting::instance()->event->get( 'archive_type', 'type1' );
$archive_column = isset( $args['column'] ) ? sanitize_text_field( $args['column'] ) : EL_Setting::instance()->event->get( 'archive_column', 'two-column' );
$zoom = isset( $args['zoom'] ) ? (int)$args['zoom'] : '';
$marker_option = isset( $args['marker_option'] ) ? sanitize_text_field($args['marker_option']) : 'icon';
$marker_icon = isset( $args['marker_icon'] ) ? esc_url($args['marker_icon']) : '';

$show_featured = isset( $args['show_featured'] ) ? sanitize_text_field( $args['show_featured'] ) : '';

$events = el_search_event_map( $show_featured );

$format = el_date_time_format_js();
$first_day = el_first_day_of_week();

$selected_name_event = isset( $_GET['name_event'] ) ? sanitize_text_field($_GET['name_event']) : '';
$selected_cat = isset( $_GET['cat'] ) ? sanitize_text_field($_GET['cat']) : '';
$selected_start_date = isset( $_GET["start_date"] ) ? sanitize_text_field($_GET["start_date"]) : '';
$selected_end_date = isset( $_GET["end_date"] ) ? sanitize_text_field($_GET["end_date"]) : '';
$selected_event_state = isset( $_GET['event_state'] ) ? sanitize_text_field($_GET['event_state']) : '';
$selected_event_city = isset( $_GET['event_city'] ) ? sanitize_text_field($_GET['event_city']) : '';
$selected_state_city = isset( $_GET['loc_input'] ) ? sanitize_title($_GET['loc_input']) : '';
$selected_name_venue = isset( $_GET['name_venue'] ) ? sanitize_text_field($_GET['name_venue']) : '';

$get_time = isset( $_GET['time'] ) ? sanitize_text_field($_GET['time']) : '';
$selected_today = ($get_time == 'today') ? 'selected="selected"' : '';
$selected_tomorrow = ($get_time == 'tomorrow') ? 'selected="selected"' : '';
$selected_this_week = ($get_time == 'this_week') ? 'selected="selected"' : '';
$selected_this_week_end = ($get_time == 'this_week_end') ? 'selected="selected"' : '';
$selected_next_week = ($get_time == 'next_week') ? 'selected="selected"' : '';
$selected_next_month = ($get_time == 'next_month') ? 'selected="selected"' : '';

$selected_event_type = isset( $_GET['event_type'] ) ? sanitize_text_field($_GET['event_type']) : '';

$lat_default = EL_Setting::instance()->event->get( 'latitude_map_default' ) ? EL_Setting::instance()->event->get( 'latitude_map_default' ) : '39.177972';
$lng_default = EL_Setting::instance()->event->get( 'longitude_map_default' ) ? EL_Setting::instance()->event->get( 'longitude_map_default' ) : '-100.363750';

$list_taxonomy_register = EL_Post_Types::register_taxonomies_customize();
?>

<div class="el_search_filters wrap_search_map">

	<div class="toggle_wrap">
		<span data-value="result_search" class="active"><?php esc_html_e( 'Results', 'eventlist' ); ?></span>
		<span data-value="show_map"><?php esc_html_e( 'Map', 'eventlist' ); ?></span>
	</div>

	<div class="wrap_search">

		<div id="result_search" class="job_listings_search">

			<span class="toggle_filters ">
				<?php esc_html_e( 'Toggle Filters', 'eventlist' ); ?>
				<i class="icon_down arrow_triangle-down"></i>
				<i class="icon_up arrow_triangle-up"></i>
			</span>

			<form class="job_filters" autocomplete="off" autocorrect="off" autocapitalize="none">
				<div class="search_jobs">
					<?php
					$str_json_list_taxonomy = '';
					$data_taxonomy_custom = [];
					foreach ($args as $key => $value) {
						switch ( $args[$key] ) {

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

								<input type="hidden" name="map_lat" id="map_lat" autocomplete="off" autocorrect="off" autocapitalize="none"/>
								<input type="hidden" name="map_lng" id="map_lng" autocomplete="off" autocorrect="off" autocapitalize="none"/>

								<input type="text" id="pac-input" name="map_address" value="" class="controls" placeholder="<?php esc_attr_e( 'Location', 'eventlist' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
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
								<?php el_get_taxonomy2('event_cat', 'cat', $selected_cat); ?>
							
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
								<input class="el_select_date" placeholder="<?php esc_attr_e('Start date ...', 'eventlist'); ?>" name="start_date" value="<?php echo esc_attr($selected_start_date); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
							</div>
							<?php
							break;

							/* End Event */
							case 'end_event':
							?>
							<div class="label_search wrap_search_end_date">
								<input class="el_select_date" placeholder="<?php esc_attr_e('End date ...', 'eventlist'); ?>" name="end_date" value="<?php echo esc_attr($selected_end_date); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
							</div>
							<?php
							break;

							/* Venue */
							case 'venue':
							?>
							<div class="venue label_search">
								<input class="form-control" placeholder="<?php esc_html_e('Venue ...', 'eventlist'); ?>" name="name_venue" value="<?php echo esc_attr($selected_name_venue); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
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
									el_get_city($selected_event_state);
								} ?>
							</div>
							<?php
							break;

							case 'event_type':
							?>
								<div class="label_search event_type">
									<select name="event_type" id="">
										<option value="">
											<?php esc_html_e( 'Select Type Event', 'eventlist' ); ?>
										</option>
										<option value="online" <?php if( $selected_event_type == 'online' ) echo 'selected'; ?> >
											<?php esc_html_e( 'Online', 'eventlist' ); ?>
										</option>
										<option value="classic" <?php if( $selected_event_type == 'classic' ) echo 'selected'; ?>>
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
						// end switch
						


						$icon_tax = '';
						if( $key === 'taxonomy_customize' && ! empty( $value ) ) {

							if( isset( $args['icon9'] ) ) {
								$icon_tax = $args['icon9'];
							}
							?>
								<?php 

								$str_list_taxpnomy = $value;

								$arr_list_taxonomy = explode( ',', $str_list_taxpnomy );
								$arr_list_taxonomy = array_map( 'trim', $arr_list_taxonomy );

								$str_json_list_taxonomy = json_encode( $arr_list_taxonomy );
								 
								
								foreach( $arr_list_taxonomy as $taxo ) {
									$data_taxonomy_custom[$taxo] = '';
									$name_taxos_register = '';
									if( ! empty( $list_taxonomy_register ) && is_array( $list_taxonomy_register ) ) {
										foreach( $list_taxonomy_register as $taxonomy_register ) {
											if( $taxonomy_register['slug'] == $taxo ) {
												$name_taxos_register = $taxonomy_register['name'];
											}
										}
									}

									$taxos = el_get_taxonomy($taxo);
									$select_taxo = isset($_GET[$taxo]) ? sanitize_text_field( $_GET[$taxo] ) : '';

									?>
									<div class="label_search">
										<?php if ( $icon_tax ) { ?>
											<i class="icon_field <?php echo esc_attr( $icon_tax ); ?>"></i>
										<?php } ?>
										<select name="<?php echo esc_attr( $taxo ) ?>" class="selectpicker  ">
											<option value=""><?php echo sprintf( esc_html__( 'Select %s', 'eventlist' ), esc_html( $name_taxos_register ) ); ?></option>
											<?php foreach ($taxos as $tax) { 
												$class_selected = ( $select_taxo == $tax->slug ) ? 'selected' : '';
												?>
												<option value="<?php echo esc_attr( $tax->slug ); ?>" <?php echo esc_attr( $class_selected ); ?>  ><?php echo esc_html( $tax->name ); ?></option>
											<?php } ?>
										</select>
									</div>
									<?php
								}

								 ?>
								

							<?php
						}
						// end taxonomy taxonomy_customize
					}
					// end foreach
					?>
					

					<input type="hidden" id="el_search_map_list_taxonomy" value="<?php echo esc_attr( $str_json_list_taxonomy ) ?>" >
					<input type="hidden" id="data_taxonomy_custom" value="<?php echo esc_attr( json_encode( $data_taxonomy_custom ) ) ?>" >
				</div>

				<div class="wrap_search_radius">
					<span><?php esc_html_e( 'Radius:', 'eventlist' ); ?></span>
					<span class="result_radius"><?php esc_html_e( '50km', 'eventlist' ); ?></span>
					<div id="wrap_pointer"></div>
					<input type="hidden" value="<?php echo esc_attr( apply_filters( 'el_map_range_radius', 50 ) ); ?>" name="radius">
				</div>
`
				<div class="wrap_search_filter_title">
					<div class="listing_found">
						<?php if ($events->found_posts == 1) { ?>
							<span><?php echo sprintf( esc_html__( '%s Result Found', 'eventlist' ), esc_html( $events->found_posts ) ); ?></span>
						<?php } else { ?>
							<span><?php echo sprintf( esc_html__( '%s Results Found', 'eventlist' ), esc_html( $events->found_posts ) ); ?></span>
						<?php } ?>

						<?php if ( 1 == ceil($events->found_posts/$events->query_vars['posts_per_page']) && $events->have_posts() ) { ?>
							<span><?php echo sprintf( esc_html__( '(Showing 1-%s)', 'eventlist' ), esc_html( $events->found_posts ) ); ?></span>
						<?php } elseif( !$events->have_posts() ) { ?>
							<span></span>
						<?php } else {?>
							<span><?php echo sprintf( esc_html__( '(Showing 1-%s)', 'eventlist' ), esc_html( $events->query_vars['posts_per_page'] ) ); ?></span>
						<?php } ?>
					</div>

					<div id="search_sort">
						<select name="sort">
							<option value=""><?php esc_html_e( 'Sort By', 'eventlist' ); ?></option>
							<option value="near"><?php esc_html_e( 'Nearest', 'eventlist' ); ?></option>
							<option value="date-desc"><?php esc_html_e( 'Newest First', 'eventlist' ); ?></option>
							<option value="date-asc"><?php esc_html_e( 'Oldest First', 'eventlist' ); ?></option>
							<option value="start-date"><?php esc_html_e( 'Start Date', 'eventlist' ); ?></option>
							<option value="end-date"><?php esc_html_e( 'End Date', 'eventlist' ); ?></option>
							<option value="a-z"><?php esc_html_e( 'A-Z', 'eventlist' ); ?></option>
							<option value="z-a"><?php esc_html_e( 'Z-A', 'eventlist' ); ?></option>
						</select>
					</div>
				</div>


				<div class="wrap_load_more">
					<svg class="loader" width="50" height="50">
						<circle cx="25" cy="25" r="10" stroke="#e86c60"/>
						<circle cx="25" cy="25" r="20" stroke="#e86c60"/>
					</svg>
				</div>
			</form>

			<div id="search_result" class="search_result" data-type="<?php echo esc_attr($archive_type); ?>" data-column="<?php echo esc_attr($archive_column); ?>" data-zoom="<?php echo esc_attr($zoom); ?>" data-lat="<?php echo esc_attr($lat_default); ?>" data-lng="<?php echo esc_attr($lng_default); ?>" data-marker_option="<?php echo esc_attr($marker_option); ?>" data-marker_icon="<?php echo esc_attr($marker_icon); ?>" data-show_featured="<?php echo esc_attr($show_featured); ?>">
				<div class="event_archive <?php echo esc_attr($archive_type . ' '. $archive_column); ?> " >

					<?php
					if($events->have_posts() ) : while ( $events->have_posts() ) : $events->the_post();
						el_get_template_part( 'content', 'event-'.$archive_type );
						$id = get_the_id();
						?>
						<div class="data_event" style="display: none;"
						data-link_event="<?php echo esc_attr( get_the_permalink() ); ?>"
						data-title_event="<?php echo esc_attr( get_the_title() ); ?>"
						data-date="<?php echo esc_attr( get_event_date_el() ); ?>"
						data-average_rating="<?php echo esc_attr( get_average_rating_by_id_event( get_the_id() ) ); ?>"
						data-number_comment="<?php echo esc_attr( get_number_coment_by_id_event( get_the_id() ) ); ?>"
						data-map_lat_event="<?php echo esc_attr( get_post_meta( get_the_ID(), OVA_METABOX_EVENT.'map_lat', true ) ); ?>"
						data-map_lng_event="<?php echo esc_attr( get_post_meta( get_the_ID(), OVA_METABOX_EVENT.'map_lng', true ) ); ?>"
						data-thumbnail_event="<?php echo esc_attr( ( has_post_thumbnail() && get_the_post_thumbnail() ) ? esc_url( wp_get_attachment_image_url( get_post_thumbnail_id() , 'el_img_squa' ) ) : esc_url( EL_PLUGIN_URI.'assets/img/no_tmb_square.png' ) ); ?>"
						data-marker_price="<?php echo esc_attr( get_price_ticket_by_id_event( array( 'id_event' => $id ) ) ); ?>"
						data-marker_date="<?php echo esc_attr( get_event_date_el() ); ?>"
						data-show_featured="<?php echo esc_attr($show_featured); ?>"
						></div>

						<?php
					endwhile; wp_reset_postdata(); else: ?>

					<div class="not_found_event"> <?php esc_html_e( 'Not found event', 'eventlist' ); ?> </div>

					<?php ; endif; ?>
				</div>

				<?php 
				$total = $events->max_num_pages;
				if ( $total > 1 ) {  ?>
					<div class="el-pagination">
						<?php 
						el_pagination_event_ajax($events->found_posts, $events->query_vars['posts_per_page'], 1);
						?>
					</div>
				<?php } ?>
			</div>
		</div>

		<div id="show_map" class="short_code"></div>
	</div>

</div>

