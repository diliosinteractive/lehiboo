<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Localisation de l'événement
 * Refactored to match "Airbnb" style
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$venue             = get_post_meta( $post_id, $_prefix.'venue', true) ? get_post_meta( $post_id, $_prefix.'venue', true) : '';
$address           = get_post_meta( $post_id, $_prefix.'address', true) ? get_post_meta( $post_id, $_prefix.'address', true) : '';
$map_name          = get_post_meta( $post_id, $_prefix.'map_name', true) ? get_post_meta( $post_id, $_prefix.'map_name', true) : '';
$map_address       = get_post_meta( $post_id, $_prefix.'map_address', true) ? get_post_meta( $post_id, $_prefix.'map_address', true) : '';

if ( $post_id !== '' ) {
	$map_lat = get_post_meta( $post_id, $_prefix.'map_lat', true) ? get_post_meta( $post_id, $_prefix.'map_lat', true) : '';
	$map_lng = get_post_meta( $post_id, $_prefix.'map_lng', true) ? get_post_meta( $post_id, $_prefix.'map_lng', true) : '';
} else {
	$EL_Setting_Event = EL()->options->event;
	$EL_Setting_Event->get('latitude_map_default') == '' ? $map_lat = '48.8566' : $map_lat = $EL_Setting_Event->get('latitude_map_default');
	$EL_Setting_Event->get('longitude_map_default') == '' ? $map_lng = '2.3522' : $map_lng = $EL_Setting_Event->get('longitude_map_default');
}

$event_type = get_post_meta( $post_id, $_prefix.'event_type', true) ? get_post_meta( $post_id, $_prefix.'event_type', true) : 'classic';
$event_online_url = get_post_meta( $post_id, $_prefix.'event_online_url', true);
$event_online_notes = get_post_meta( $post_id, $_prefix.'event_online_notes', true);

?>

<div class="event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Localisation', 'eventlist' ); ?></h4>
	<p class="field_description">
		<?php esc_html_e( 'Définissez où se déroulera votre activité.', 'eventlist' ); ?>
	</p>

	<!-- Event Type Selection -->
	<div class="vendor_field">
		<label><?php esc_html_e( 'Type de lieu', 'eventlist' ); ?></label>
		<div class="event_type_options el_row">
			<div class="el_col_6">
				<label class="el_card_radio <?php echo ($event_type == 'classic') ? 'active' : ''; ?>" for="classic_event_type">
					<input type="radio" value="classic" name="<?php echo $_prefix.'event_type'; ?>" <?php checked($event_type, 'classic'); ?> class="event_type_radio" id="classic_event_type" />
					<span class="radio_content">
						<i class="icon_pin_alt"></i>
						<span class="radio_title"><?php esc_html_e( 'Lieu physique', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Les participants se rendent sur place', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>
			<div class="el_col_6">
				<label class="el_card_radio <?php echo ($event_type == 'online') ? 'active' : ''; ?>" for="online_event_type">
					<input type="radio" value="online" name="<?php echo $_prefix.'event_type'; ?>" <?php checked($event_type, 'online'); ?> class="event_type_radio" id="online_event_type" />
					<span class="radio_content">
						<i class="icon_desktop"></i>
						<span class="radio_title"><?php esc_html_e( 'En ligne', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Les participants se connectent à distance', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>
		</div>
	</div>

	<!-- Online Section -->
	<div class="online_location_section" style="<?php echo $event_type == 'online' ? 'display: block;' : 'display: none;'; ?>">
		<hr class="el_separator">
		<h4 class="heading_section"><?php esc_html_e( 'Détails de l\'événement en ligne', 'eventlist' ); ?></h4>
		
		<div class="vendor_field">
			<label for="event_online_url"><?php esc_html_e( 'Lien de l\'événement (URL)', 'eventlist' ); ?> <span class="el_req">*</span></label>
			<input type="url" name="<?php echo $_prefix.'event_online_url'; ?>" id="event_online_url" value="<?php echo esc_url($event_online_url); ?>" placeholder="https://zoom.us/j/..." />
		</div>

		<div class="vendor_field">
			<label for="event_online_notes"><?php esc_html_e( 'Instructions de connexion', 'eventlist' ); ?></label>
			<textarea name="<?php echo $_prefix.'event_online_notes'; ?>" id="event_online_notes" rows="4" placeholder="<?php esc_attr_e( 'Ex: Mot de passe, lien envoyé par mail...', 'eventlist' ); ?>"><?php echo esc_textarea($event_online_notes); ?></textarea>
		</div>
	</div>

	<!-- Physical Location Section -->
	<div class="physical_location_section" style="<?php echo $event_type == 'classic' ? 'display: block;' : 'display: none;'; ?>">
		<hr class="el_separator">
		
		<!-- Address Search -->
		<div class="vendor_field">
			<label for="pac-input"><?php esc_html_e( 'Rechercher une adresse', 'eventlist' ); ?></label>
			<div class="el_input_icon_wrapper">
				<i class="icon_search"></i>
				<input id="pac-input" name="<?php echo esc_attr( $_prefix.'map_address' ); ?>" value="<?php echo $post_id != '' ? $map_address : ''; ?>" class="controls" type="text" placeholder="<?php esc_html_e( 'Saisir une adresse...', 'eventlist' ); ?>" autocomplete="off">
			</div>
		</div>
		
		<div class="vendor_field" id="admin_show_map" style="height: 300px; border-radius: 12px; overflow: hidden; border: 1px solid #EBEBEB;"></div>
		
		<input type="hidden" value="<?php echo esc_attr( $map_name ); ?>" name="<?php echo esc_attr( $_prefix.'map_name' ); ?>" id="map_name" />
		<input type="hidden" value="<?php echo esc_attr( $map_lat ); ?>" name="<?php echo esc_attr( $_prefix.'map_lat' ); ?>" id="map_lat" />
		<input type="hidden" value="<?php echo esc_attr( $map_lng ); ?>" name="<?php echo esc_attr( $_prefix.'map_lng' ); ?>" id="map_lng" />

		<div class="el_row">
			<div class="el_col_6">
				<div class="vendor_field">
					<label for="add_venue"><?php esc_html_e( 'Nom du lieu', 'eventlist' ); ?></label>
					<input type="text" name="<?php echo esc_attr( $_prefix.'add_venue' ); ?>" id="add_venue" value="<?php echo esc_attr( $venue ? (is_array($venue) ? $venue[0] : $venue) : '' ); ?>" placeholder="<?php esc_attr_e( 'Ex: Salle des fêtes', 'eventlist' ); ?>">
				</div>
			</div>
			<div class="el_col_6">
				<div class="vendor_field">
					<label for="address"><?php esc_html_e( 'Adresse complète', 'eventlist' ); ?></label>
					<input type="text" id="address" name="<?php echo esc_attr( $_prefix.'address' ); ?>" value="<?php echo esc_attr( $address ); ?>" />
				</div>
			</div>
		</div>

		<hr class="el_separator">
		<h4 class="heading_section"><?php esc_html_e( 'Informations complémentaires', 'eventlist' ); ?></h4>

		<div class="el_row">
			<div class="el_col_6">
				<div class="vendor_field">
					<label for="venue_event_type"><?php esc_html_e( 'Type de lieu', 'eventlist' ); ?></label>
					<select name="<?php echo $_prefix.'venue_event_type'; ?>" id="venue_event_type" class="selectpicker">
						<option value=""><?php esc_html_e( 'Sélectionnez...', 'eventlist' ); ?></option>
						<option value="indoor" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'indoor' ); ?>><?php esc_html_e( 'Intérieur', 'eventlist' ); ?></option>
						<option value="outdoor" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'outdoor' ); ?>><?php esc_html_e( 'Extérieur', 'eventlist' ); ?></option>
						<option value="both" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'both' ); ?>><?php esc_html_e( 'Intérieur & Extérieur', 'eventlist' ); ?></option>
					</select>
				</div>
			</div>
			<div class="el_col_6">
				<div class="vendor_field">
					<label for="venue_parking"><?php esc_html_e( 'Stationnement', 'eventlist' ); ?></label>
					<input type="text" name="<?php echo $_prefix.'venue_parking'; ?>" id="venue_parking" placeholder="<?php esc_attr_e( 'Infos parking...', 'eventlist' ); ?>" value="<?php echo esc_attr( get_post_meta( $post_id, $_prefix.'venue_parking', true) ); ?>">
				</div>
			</div>
		</div>

		<div class="vendor_field">
			<label for="venue_access"><?php esc_html_e( 'Accès & Transports', 'eventlist' ); ?></label>
			<textarea name="<?php echo $_prefix.'venue_access'; ?>" id="venue_access" rows="2" placeholder="<?php esc_attr_e( 'Bus, Métro, Accès route...', 'eventlist' ); ?>"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_access', true) ); ?></textarea>
		</div>

		<div class="el_row">
			<div class="el_col_4">
				<div class="vendor_field">
					<label class="el_checkbox_wrapper">
						<input type="checkbox" id="venue_pmr_accessible" name="<?php echo $_prefix.'venue_pmr_accessible'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_pmr_accessible', true), '1' ); ?> />
						<span class="el_checkbox_label"><?php esc_html_e( 'Accessible PMR', 'eventlist' ); ?></span>
					</label>
				</div>
			</div>
			<div class="el_col_4">
				<div class="vendor_field">
					<label class="el_checkbox_wrapper">
						<input type="checkbox" id="venue_restaurant_available" name="<?php echo $_prefix.'venue_restaurant_available'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_restaurant_available', true), '1' ); ?> />
						<span class="el_checkbox_label"><?php esc_html_e( 'Restauration', 'eventlist' ); ?></span>
					</label>
				</div>
			</div>
			<div class="el_col_4">
				<div class="vendor_field">
					<label class="el_checkbox_wrapper">
						<input type="checkbox" id="venue_drinks_available" name="<?php echo $_prefix.'venue_drinks_available'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_drinks_available', true), '1' ); ?> />
						<span class="el_checkbox_label"><?php esc_html_e( 'Boissons', 'eventlist' ); ?></span>
					</label>
				</div>
			</div>
		</div>

	</div>
</div>
