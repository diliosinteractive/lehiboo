<?php if( !defined( 'ABSPATH' ) ) exit(); 

$EL_Setting = EL()->options->general;

$status_pay = $this->get_mb_value( 'status_pay' );
$status_pay = (!empty($status_pay)) ? $status_pay : 'pending';

$EL_Setting_Event = EL()->options->event;

$EL_Setting_Event->get('latitude_map_default') == '' ? $lat_default = '39.177972' : $lat_default = $EL_Setting_Event->get('latitude_map_default');
$EL_Setting_Event->get('longitude_map_default') == '' ? $lng_default = '-100.363750' : $lng_default = $EL_Setting_Event->get('longitude_map_default');


?>

<input type="hidden" id="event_active" class="event_active" value="<?php echo esc_attr( $this->get_mb_value( 'event_active', '0' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'event_active' ) ); ?>" />
<input type="hidden" id="event_status_pay" class="event_status_pay" value="<?php echo esc_attr( $status_pay ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'status_pay' ) ); ?>" />

<input type="hidden" class="prefix" value="<?php echo esc_attr(OVA_METABOX_EVENT); ?>">


<div class="ova_row">
	<label class="label"><strong><?php esc_html_e( 'Featured:', 'eventlist' ); ?></strong></label>
	<select name="<?php echo esc_attr($this->get_mb_name( 'event_feature' )); ?>" id="">
		<option value="no" <?php echo $this->get_mb_value( 'event_feature', 'no' ) == 'no' ? 'selected' : '' ?>>
			<?php esc_html_e( 'No','eventlist' ); ?>
		</option>
		<option value="yes" <?php echo $this->get_mb_value( 'event_feature', 'no' ) == 'yes' ? 'selected' : '' ?>>	
			<?php esc_html_e( 'Yes','eventlist' ); ?>
		</option>
	</select>
	
</div>

<!-- Time zone -->
<?php if( apply_filters( 'el_show_timezone', true ) ){ 
	$time_zone = $this->get_mb_value('time_zone') ? $this->get_mb_value('time_zone') : apply_filters( 'el_set_timezone_default', '' );
?>

	<div class="ova_row">
		<label class="label"><strong><?php esc_html_e( 'Time zone:', 'eventlist' ); ?></strong></label>
		<select class="time_zone" id="time_zone" name="<?php echo esc_attr( $this->get_mb_name('time_zone') ); ?>">
			<?php echo wp_timezone_choice( $time_zone, get_user_locale() ); ?>
		</select>
	</div>
<?php } ?>

<!-- Contact -->
<hr>
<div class="ova_row">

	<div id="mb_contact">

		<div class="ova_row">
			<label class="label" for="info_organizer"><strong><?php esc_html_e( 'Use other information without your profile info', 'eventlist' ); ?>: </strong></label>
			<input type="checkbox" id="info_organizer" class="info_organizer" value="<?php echo esc_attr($this->get_mb_value('info_organizer')); ?>" name="<?php echo esc_attr($this->get_mb_name('info_organizer')); ?>" <?php echo esc_attr($this->get_mb_value('info_organizer')); ?> />
		</div>

		<div id="show_rewrite" style="display: none">
			<div class="ova_row">
				<label class="label" for="name_organizer"><strong><?php esc_html_e( 'Name', 'eventlist' ); ?>: </strong></label>
				<input type="text" id="name_organizer" class="name_organizer" value="<?php echo esc_attr($this->get_mb_value('name_organizer')); ?>" placeholder="<?php esc_attr_e( 'Name Organizer', 'eventlist' ); ?>"  name="<?php echo esc_attr($this->get_mb_name('name_organizer')); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
			</div>

			<div class="ova_row">
				<label class="label" for="phone_organizer"><strong><?php esc_html_e( 'Phone', 'eventlist' ); ?>: </strong></label>
				<input type="text" id="phone_organizer" class="phone_organizer" value="<?php echo esc_attr($this->get_mb_value('phone_organizer')) ?>" placeholder="<?php esc_attr_e( '+123 456 7890', 'eventlist' ); ?>"  name="<?php echo esc_attr($this->get_mb_name('phone_organizer')); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
			</div>

			<div class="ova_row">
				<label class="label" for="mail_organizer"><strong><?php esc_html_e( 'E-mail', 'eventlist' ); ?>: </strong></label>
				<input type="text" id="mail_organizer" class="mail_organizer" value="<?php echo esc_attr($this->get_mb_value('mail_organizer')) ?>" placeholder="<?php esc_attr_e( 'example@email.com', 'eventlist' ); ?>" name="<?php echo esc_attr($this->get_mb_name('mail_organizer')); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
			</div>

			<div class="ova_row">
				<label class="label" for="job_organizer"><strong><?php esc_html_e( 'Job', 'eventlist' ); ?>: </strong></label>
				<input type="text" id="job_organizer" class="job_organizer" value="<?php echo esc_attr($this->get_mb_value('job_organizer')) ?>" placeholder="<?php esc_attr_e( 'CEO', 'eventlist' ); ?>" name="<?php echo esc_attr($this->get_mb_name('job_organizer')); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
			</div>

			<div class="ova_row" >
				<div id="social_organizer">
					<label class="label"><strong><?php esc_html_e( 'Social', 'eventlist' ); ?>: </strong></label>
					<div id="social_list">

						<?php if ($this->get_mb_value('social_organizer')){ ?>
							<?php foreach ($this->get_mb_value('social_organizer') as $key => $value) { ?>
								<?php if ($value['link_social'] != ''): ?> 
									<div class="social_item">
										<input type="text" name="<?php echo esc_attr($this->get_mb_name('social_organizer['.$key.'][link_social]') ); ?>" value="<?php echo esc_attr($value['link_social']); ?>" class="link_social" placeholder="<?php echo esc_attr( 'https://' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">

										<select name="<?php echo esc_attr($this->get_mb_name('social_organizer['.$key.'][icon_social]') ); ?>" class="icon_social">
											<?php foreach (el_get_social() as $key_icon_social => $value_icon_social) { ?>
												<option value="<?php echo esc_attr( $key_icon_social ); ?>" <?php echo esc_attr( $value['icon_social'] == $key_icon_social ? esc_attr('selected') : ''); ?>><?php echo esc_html( $value_icon_social ); ?></option>
											<?php } ?>
										</select>
										<a href="#" class="button remove_social"><?php esc_html_e( 'x', 'eventlist' ); ?></a>
									</div>
								<?php endif ?>
							<?php } ?>
						<?php } ?>

					</div>
					<a href="#" class="button add_social"><i class="fa fa-plus"></i><?php esc_html_e( 'Add Social', 'eventlist' ); ?></a>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="ova_row">
	<?php $event_type   = $this->get_mb_value( 'event_type', apply_filters( 'el_event_type_default', 'classic' ) ); ?>
	
	<h4 class="heading_section"><?php esc_html_e( 'Event Type:', 'eventlist' ); ?></h4>

	<div class="event_type">

		<input type="radio" value="classic" name="<?php echo esc_attr( $this->get_mb_name('event_type') ); ?>" <?php echo $event_type == 'classic' ? 'checked' : ''; ?> /><span><?php esc_html_e( 'Physical location', 'eventlist' ); ?></span>
		<input type="radio" value="online" name="<?php echo esc_attr( $this->get_mb_name('event_type') ); ?>" <?php echo $event_type == 'online' ? 'checked' : ''; ?> /><span><?php esc_html_e( 'Online', 'eventlist' ); ?></span>
		
	</div>

</div>

<hr>
<!-- Map -->
<div class="ova_row">
	<div id="mb_location">
		
		<div id="mb_venue">
			<div class="ova_row">
				<label class="label" for="add_venue"><strong><?php esc_html_e( 'Venue Name:', 'eventlist' ); ?></strong></label>

				<input type="text" name="<?php echo esc_attr( $this->get_mb_name( 'add_venue' ) ); ?>" id="add_venue" value='' autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="<?php esc_attr_e( 'White Palace', 'eventlist' ); ?>"></input>
				<button class="button check_venue"><?php esc_html_e( 'Add', 'eventlist' ); ?></button>

			</div>

			<!-- List Venue -->
			<ul id="data_venue">
				<?php if ( $this->get_mb_value( 'venue' ) ) {
					foreach ( $this->get_mb_value( 'venue' ) as $key => $value) {
						$post_venue = el_get_page_by_title( $value, OBJECT, 'venue' );
						if ($post_venue) { ?>
							<li>
								<input type="hidden" name="<?php echo esc_attr( $this->get_mb_name( 'venue' ).'['.$key.']' ); ?>" value="<?php echo esc_attr($value); ?>">
								<i class="dashicons dashicons-dismiss remove_venue"></i>
								<span><?php echo esc_html( $post_venue->post_title ); ?></span>
							</li>
						<?php }  
					}
				} ?>
			</ul>
		</div>


		<div class="place-autocomplete-card" id="place-autocomplete-card">
	      <p><?php esc_html_e( 'Search for a place here:', 'eventlist' ); ?></p>
	    </div>
		<div id="admin_show_map"></div>
		<label>
			<?php esc_html_e( 'Latitude: ', 'eventlist' ); ?>
			<input type="text" value="<?php echo esc_attr(trim($this->get_mb_value( 'map_lat', $lat_default ))); ?>" name="<?php echo esc_attr($this->get_mb_name( 'map_lat' )); ?>" id="map_lat"  autocomplete="off" autocorrect="off" autocapitalize="none"/>
		</label>

		<label>
			<?php esc_html_e( 'Longitude: ', 'eventlist' ); ?>
			<input type="text" value="<?php echo esc_attr(trim($this->get_mb_value( 'map_lng', $lng_default ))); ?>" name="<?php echo esc_attr($this->get_mb_name( 'map_lng' )); ?>" id="map_lng" autocomplete="off" autocorrect="off" autocapitalize="none"/>
		</label>

		<input type="text" id="pac-input" name="<?php echo esc_attr($this->get_mb_name( 'map_address' )); ?>" value="<?php echo esc_attr($this->get_mb_value( 'map_address', 'New York, NY, USA' )); ?>" class="controls" placeholder="<?php esc_attr_e( 'Enter a venue', 'eventlist' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">

		<div class="ova_row">
			<span class="edit_address">
				<label class="label" for="edit_full_address"><strong><?php esc_html_e( 'Edit Full Address:', 'eventlist' ); ?></strong></label>
				<input type="checkbox" id="edit_full_address" class="edit_full_address" value="<?php echo esc_attr( $this->get_mb_value( 'edit_full_address', '' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'edit_full_address' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" <?php echo esc_attr( $this->get_mb_value( 'edit_full_address', '' ) ); ?> />
				<?php $edit_full_address = $this->get_mb_value( 'edit_full_address', '' ); ?>
				<input type="text" id="address" class="address" value="<?php echo esc_attr( $this->get_mb_value( 'address' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'address' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="<?php esc_attr_e( '123 Jones Street', 'eventlist' ); ?>" <?php echo esc_attr($edit_full_address != 'checked' ? 'readonly' : ''); ?> />
			</span>
		</div>

		<input type="hidden" value="<?php echo esc_attr($this->get_mb_value( 'map_name', 'New York' )); ?>" name="<?php echo esc_attr($this->get_mb_name( 'map_name' )); ?>" id="map_name"  autocomplete="off" autocorrect="off" autocapitalize="none"/>
	</div>
</div>
