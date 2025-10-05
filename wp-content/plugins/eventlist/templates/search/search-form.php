<?php if( ! defined( 'ABSPATH' ) ) exit();


$format 		= el_date_time_format_js();
$first_day 		= el_first_day_of_week();

$get_time 		= isset( $_GET['time'] ) ? sanitize_text_field($_GET['time']) : '';

$event_loc 		= is_tax('event_loc') ? get_queried_object()->slug : '';
$event_cat 		= is_tax('event_cat') ? get_queried_object()->slug : '';
$event_venue 	= is_singular('venue') ? get_queried_object()->post_title : '';


$selected_name_event 	= isset( $_GET['name_event'] ) ? sanitize_text_field($_GET['name_event']) : '';
$selected_cat 			= isset( $_GET['cat'] ) ? sanitize_text_field($_GET['cat']) : $event_cat;
$selected_event_state 	= isset( $_GET['event_state'] ) ? sanitize_text_field($_GET['event_state']) : $event_loc;
$selected_event_city 	= isset( $_GET['event_city'] ) ? sanitize_text_field($_GET['event_city']) : $event_loc;
$selected_loc_input 	= isset( $_GET['loc_input'] ) ? sanitize_text_field($_GET['loc_input']) : '';
$selected_name_venue 	= isset( $_GET['name_venue'] ) ? sanitize_text_field($_GET['name_venue']) : $event_venue;

$selected_event_cat 	= isset(get_queried_object()->slug) ? sanitize_text_field(get_queried_object()->slug) : '';

$selected_event_type 	= isset( $_GET['event_type'] ) ? sanitize_text_field( $_GET['event_type'] ) : '';

$start_date 	= isset( $_GET["start_date"] ) ? sanitize_text_field($_GET["start_date"]) : '';
$end_date 		= isset( $_GET["end_date"] ) ? sanitize_text_field($_GET["end_date"]) : '';

$list_taxonomy_register = EL_Post_Types::register_taxonomies_customize();

$start_slider 	= $args['start_slider'];
$end_slider 	= $args['end_slider'];
$min_slider 	= $args['min_slider'];
$max_slider 	= $args['max_slider'];

$el_max_price = isset( $_GET['el_max_price'] ) ? sanitize_text_field( $_GET['el_max_price'] ) : $end_slider;
$el_min_price = isset( $_GET['el_min_price'] ) ? sanitize_text_field( $_GET['el_min_price'] ) : $start_slider;

if ( $el_min_price ) {
	$start_slider = $el_min_price;
}

if ( $el_max_price ) {
	$end_slider = $el_max_price;
}
?>
<div class="el_search_filters wrap_form_search <?php echo ' '.esc_attr($args['type']); ?> <?php echo ' '.esc_attr(  $args['class']); ?> ">
	
	<form enctype="multipart/form-data" method="GET" name="search_event" action="<?php echo esc_url(get_search_result_page()); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
		<div class="wp_form">
			<?php
			$i = 1;
			foreach ($args as $key => $value) {

				$pos = $key[-1];
				if ( strpos($key,'pos') !== false ) {
				
					switch ( $args[$key] ) {

						/* Name Event */
						case 'name_event':
						?>
						<div class="name_event field_search">
							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>
							<input type="text" class="form-control" placeholder="<?php esc_attr_e('Enter name ...', 'eventlist'); ?>" name="name_event" value="<?php echo esc_attr($selected_name_event); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
						</div>
						<?php
						break;

						/* Categories */
						case 'cat':
						?>
						<div class="categories field_search">
							

							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>

							<?php el_get_taxonomy2('event_cat', 'cat', $selected_cat); ?>
						
						</div>
						<?php
						break;

						/* Location Autocomplete */
						case 'loc_input':
						?>
						<div class="loc_input field_search">
							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>
							<input type="text" class="form-control" placeholder="<?php esc_attr_e('State, City ...', 'eventlist'); ?>" name="loc_input" value="<?php echo esc_attr($selected_loc_input); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
						</div>
						<?php
						break;

						/* Location State */
						case 'loc_state':
						?>
						<div class="loc_state field_search">
							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>
							<?php el_get_state($selected_event_state); ?>
						</div>
						<?php
						break;

						/* Location City */
						case 'loc_city':
						?>
						<div class="loc_city field_search">
							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>
							<?php el_get_city($selected_event_city); ?>
						</div>
						<?php
						break;

						/* Venue */
						case 'venue':
						?>
						<div class="venue field_search">
							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>
							<input type="text" class="form-control" placeholder="<?php esc_html_e('Venue ...', 'eventlist'); ?>" name="name_venue" value="<?php echo esc_attr($selected_name_venue); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
						</div>
						<?php
						break;

						/* All Time */
						case 'all_time':
						$select_today = ($get_time == 'today') ? 'selected="selected"' : '';
						$select_tomorrow = ($get_time == 'tomorrow') ? 'selected="selected"' : '';
						$select_this_week = ($get_time == 'this_week') ? 'selected="selected"' : '';
						$select_this_week_end = ($get_time == 'this_week_end') ? 'selected="selected"' : '';
						$select_next_week = ($get_time == 'next_week') ? 'selected="selected"' : '';
						$select_next_month = ($get_time == 'next_month') ? 'selected="selected"' : '';
						?>
						<div class="el_all_time field_search">
							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>
							<select name="time">
								<option value="" ><?php esc_html_e('All Time', 'eventlist'); ?></option>
								<option value="today" <?php echo esc_attr( $select_today ); ?> ><?php esc_html_e('Today', 'eventlist'); ?></option>
								<option value="tomorrow" <?php echo esc_attr( $select_tomorrow ); ?> ><?php esc_html_e('Tomorrow', 'eventlist'); ?></option>
								<option value="this_week" <?php echo esc_attr( $select_this_week ); ?> ><?php esc_html_e('This Week', 'eventlist'); ?></option>
								<option value="this_week_end" <?php echo esc_attr( $select_this_week_end ); ?> ><?php esc_html_e('This Weekend', 'eventlist'); ?></option>
								<option value="next_week" <?php echo esc_attr( $select_next_week ); ?> ><?php esc_html_e('Next Week', 'eventlist'); ?></option>
								<option value="next_month" <?php echo esc_attr( $select_next_month ); ?> ><?php esc_html_e('Next Month', 'eventlist'); ?></option>
							</select>
						</div>
						<?php
						break;

						/* Start Event */
						case 'start_event':
						?>
						<div class="el_start_date field_search">
							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>
							<input class="el_select_date form-control" placeholder="<?php esc_attr_e('Start date ...', 'eventlist'); ?>" name="start_date" data-format="<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr($start_date); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
						</div>
						<?php
						break;

						/* End Event */
						case 'end_event':
						?>
						<div class="el_end_date field_search">
							<?php if ($args['icon'.$i]) { ?>
								<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
							<?php } ?>
							<input class="el_select_date form-control" placeholder="<?php esc_attr_e('End date ...', 'eventlist'); ?>" name="end_date" data-format="<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr($end_date); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
						</div>
						<?php
						break;

						case 'event_type':
						?>
							<div class="field_search event_type">
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

						case 'range_slider':
						?>
							<div class="field_search wrap_range_price">
								<?php if ( $args['icon'.$i] ) { ?>
										<i class="icon_field <?php echo esc_attr( $args['icon'.$i] ); ?>"></i>
									<?php } ?>						
								<input type="hidden" name="el_max_price" value="<?php echo esc_attr( $el_max_price ); ?>">
									<input type="hidden" name="el_min_price" value="<?php echo esc_attr( $el_min_price ); ?>">
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

						default:
						// code...
						break;
					}
				$i+= 1;
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
						 
						foreach( $arr_list_taxonomy as $taxo ) {

							$name_taxos_register = '';
							if( ! empty( $list_taxonomy_register ) && is_array( $list_taxonomy_register ) ) {
								foreach( $list_taxonomy_register as $taxonomy_register ) {
									if( $taxonomy_register['slug'] == $taxo ) {
										$name_taxos_register = $taxonomy_register['name'];
									}
								}
							}

							$taxos = el_get_taxonomy($taxo);
							$default_tax = is_tax( $taxo ) ? get_queried_object()->slug : '';
							$select_taxo = isset($_GET[$taxo]) ? sanitize_text_field( $_GET[$taxo] ) : $default_tax;

							?>
							<div class="el_tax_<?php echo esc_attr( $taxo ); ?> field_search">
								<?php if ( $icon_tax ) { ?>
									<i class="icon_field <?php echo esc_attr( $icon_tax ); ?>"></i>
								<?php } ?>
								<select name="<?php echo esc_attr( $taxo ) ?>" class="selectpicker">
									<option value=""><?php echo sprintf( esc_html__( 'Select %s', 'eventlist' ), esc_html($name_taxos_register) ); ?></option>
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
			}
			// end foreach
			?>
		
		</div>


		<?php 
		
			$lang = '';
			if( defined( 'ICL_LANGUAGE_CODE' ) ){

				global $sitepress;
				if ( $sitepress != Null && $sitepress->get_default_language() != ICL_LANGUAGE_CODE ){
					$lang = ICL_LANGUAGE_CODE;
				}

			}


		?>
		
		<?php if(  $lang){ ?>
			<input type="hidden" value="<?php echo esc_attr( $lang ); ?>" name="lang" />
		<?php } ?>

		<div class="el_submit_search">
			<input type="submit" value="<?php esc_html_e('Search', 'eventlist'); ?>" class="second_font" />
		</div>
	</form>

</div>