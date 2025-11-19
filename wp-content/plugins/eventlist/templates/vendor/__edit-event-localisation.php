<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Localisation de l'événement
 * Contient: Type d'événement (Physique/Online), Pays/Ville, Venue, Carte, Coordonnées, Adresse
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$venue             = get_post_meta( $post_id, $_prefix.'venue', true) ? get_post_meta( $post_id, $_prefix.'venue', true) : '';
$address           = get_post_meta( $post_id, $_prefix.'address', true) ? get_post_meta( $post_id, $_prefix.'address', true) : '';
$map_name          = get_post_meta( $post_id, $_prefix.'map_name', true) ? get_post_meta( $post_id, $_prefix.'map_name', true) : '';
$map_address       = get_post_meta( $post_id, $_prefix.'map_address', true) ? get_post_meta( $post_id, $_prefix.'map_address', true) : '';
$edit_full_address = get_post_meta( $post_id, $_prefix.'edit_full_address', true) ? get_post_meta( $post_id, $_prefix.'edit_full_address', true) : '';

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

$location_selected = get_the_terms( $post_id, 'event_loc' ) ? get_the_terms( $post_id, 'event_loc' ) : '';
$el_country = '';
$el_city = '';

if ( $location_selected ) {
	foreach ($location_selected as $key => $value) {
		if ($value->parent == '0') {
			$el_country = $value->slug;
		} else {
			$el_city = $value->slug;
		}
	}
}

?>

<!-- Event Type Selection -->
<div class="event_basic_block event_type_section">
	<h4 class="heading_section">
		<?php esc_html_e( 'Où se déroule l\'événement ?', 'eventlist' ); ?>
	</h4>
	<div class="event_type_options">
		<label class="el_input_radio" for="classic_event_type">
			<?php esc_html_e( 'Dans un lieu physique', 'eventlist' ); ?>
			<input type="radio" value="classic" name="<?php echo $_prefix.'event_type'; ?>" <?php checked($event_type, 'classic'); ?> class="event_type_radio" id="classic_event_type" />
			<span class="checkmark"></span>
		</label>

		<label class="el_input_radio el_ml_10px" for="online_event_type">
			<?php esc_html_e( 'En ligne', 'eventlist' ); ?>
			<input type="radio" value="online" name="<?php echo $_prefix.'event_type'; ?>" <?php checked($event_type, 'online'); ?> class="event_type_radio" id="online_event_type" />
			<span class="checkmark"></span>
		</label>
	</div>
</div>

<!-- Online Section -->
<div class="online_location_section" style="<?php echo $event_type == 'online' ? 'display: block;' : 'display: none;'; ?>">
	<div class="event_basic_block">
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
</div>

<!-- Physical Location Section -->
<div class="physical_location_section" style="<?php echo $event_type == 'classic' ? 'display: block;' : 'display: none;'; ?>">
	
	<div class="event_basic_block">
		<h4 class="heading_section"><?php esc_html_e( 'Adresse du lieu', 'eventlist' ); ?></h4>

		<!-- Address Source -->
		<div class="address_source_choice vendor_field">
			<label class="label"><strong><?php esc_html_e( 'Source de l\'adresse :', 'eventlist' ); ?></strong></label>
			<div class="address_source_options">
				<label class="el_input_radio" for="address_source_entity">
					<?php esc_html_e( 'Mon adresse d\'entité', 'eventlist' ); ?>
					<input type="radio" value="entity" name="<?php echo $_prefix.'address_source'; ?>" id="address_source_entity" class="address_source_radio" <?php checked(get_post_meta( $post_id, $_prefix.'address_source', true), 'entity'); ?> />
					<span class="checkmark"></span>
				</label>

				<label class="el_input_radio el_ml_10px" for="address_source_new">
					<?php esc_html_e( 'Nouvelle adresse', 'eventlist' ); ?>
					<input type="radio" value="new" name="<?php echo $_prefix.'address_source'; ?>" id="address_source_new" class="address_source_radio" <?php checked(get_post_meta( $post_id, $_prefix.'address_source', true), 'new'); ?> />
					<span class="checkmark"></span>
				</label>
			</div>
		</div>

		<!-- Map & Address Fields -->
		<div class="el_map_wrapper">
			<div class="vendor_field">
				<label for="pac-input"><?php esc_html_e( 'Rechercher une adresse', 'eventlist' ); ?></label>
				<input id="pac-input" name="<?php echo esc_attr( $_prefix.'map_address' ); ?>" value="<?php echo $post_id != '' ? $map_address : ''; ?>" class="controls" type="text" placeholder="<?php esc_html_e( 'Saisir une adresse...', 'eventlist' ); ?>" autocomplete="off">
			</div>
			
			<div class="vendor_field" id="admin_show_map" style="height: 300px;"></div>
			
			<input type="hidden" value="<?php echo esc_attr( $map_name ); ?>" name="<?php echo esc_attr( $_prefix.'map_name' ); ?>" id="map_name" />
			<input type="hidden" value="<?php echo esc_attr( $map_lat ); ?>" name="<?php echo esc_attr( $_prefix.'map_lat' ); ?>" id="map_lat" />
			<input type="hidden" value="<?php echo esc_attr( $map_lng ); ?>" name="<?php echo esc_attr( $_prefix.'map_lng' ); ?>" id="map_lng" />
		</div>

		<div class="vendor_field">
			<label for="address"><?php esc_html_e( 'Adresse complète', 'eventlist' ); ?></label>
			<input type="text" id="address" name="<?php echo esc_attr( $_prefix.'address' ); ?>" value="<?php echo esc_attr( $address ); ?>" />
		</div>
		
		<!-- City/Country Hidden Fields (Populated by JS or backend logic) -->
		<div style="display:none;">
			<?php el_get_state( $el_country ); ?>
			<?php el_get_city( $el_city ); ?>
		</div>

		<!-- Venue Name -->
		<div class="vendor_field">
			<label for="add_venue"><?php esc_html_e( 'Nom du lieu (ex: Salle des fêtes)', 'eventlist' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $_prefix.'add_venue' ); ?>" id="add_venue" value="<?php echo esc_attr( $venue ? (is_array($venue) ? $venue[0] : $venue) : '' ); ?>" placeholder="<?php esc_attr_e( 'Nom du lieu', 'eventlist' ); ?>">
		</div>

	</div>

	<!-- Venue Additional Info -->
	<div class="event_basic_block">
		<h4 class="heading_section"><?php esc_html_e( 'Informations sur le lieu', 'eventlist' ); ?></h4>

		<!-- Type (Indoor/Outdoor) -->
		<div class="vendor_field">
			<label for="venue_event_type"><?php esc_html_e( 'Type de lieu', 'eventlist' ); ?></label>
			<select name="<?php echo $_prefix.'venue_event_type'; ?>" id="venue_event_type">
				<option value=""><?php esc_html_e( 'Sélectionnez...', 'eventlist' ); ?></option>
				<option value="indoor" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'indoor' ); ?>><?php esc_html_e( 'Intérieur', 'eventlist' ); ?></option>
				<option value="outdoor" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'outdoor' ); ?>><?php esc_html_e( 'Extérieur', 'eventlist' ); ?></option>
				<option value="both" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'both' ); ?>><?php esc_html_e( 'Intérieur & Extérieur', 'eventlist' ); ?></option>
			</select>
		</div>

		<!-- Parking -->
		<div class="vendor_field">
			<label for="venue_parking"><?php esc_html_e( 'Stationnement', 'eventlist' ); ?></label>
			<textarea name="<?php echo $_prefix.'venue_parking'; ?>" id="venue_parking" rows="3" placeholder="<?php esc_attr_e( 'Infos parking...', 'eventlist' ); ?>"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_parking', true) ); ?></textarea>
		</div>

		<!-- Access -->
		<div class="vendor_field">
			<label for="venue_access"><?php esc_html_e( 'Accès & Transports', 'eventlist' ); ?></label>
			<textarea name="<?php echo $_prefix.'venue_access'; ?>" id="venue_access" rows="3" placeholder="<?php esc_attr_e( 'Bus, Métro, Accès route...', 'eventlist' ); ?>"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_access', true) ); ?></textarea>
		</div>

		<!-- PMR -->
		<div class="vendor_field">
			<label class="el_input_checkbox" for="venue_pmr_accessible">
				<?php esc_html_e( 'Accessible PMR', 'eventlist' ); ?>
				<input type="checkbox" id="venue_pmr_accessible" name="<?php echo $_prefix.'venue_pmr_accessible'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_pmr_accessible', true), '1' ); ?> />
				<span class="checkmark"></span>
			</label>
			<textarea name="<?php echo $_prefix.'venue_pmr_info'; ?>" id="venue_pmr_info" rows="2" placeholder="<?php esc_attr_e( 'Détails accessibilité...', 'eventlist' ); ?>" style="margin-top: 10px;"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_pmr_info', true) ); ?></textarea>
		</div>

		<!-- Restaurant -->
		<div class="vendor_field">
			<label class="el_input_checkbox" for="venue_restaurant_available">
				<?php esc_html_e( 'Restauration sur place', 'eventlist' ); ?>
				<input type="checkbox" id="venue_restaurant_available" name="<?php echo $_prefix.'venue_restaurant_available'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_restaurant_available', true), '1' ); ?> />
				<span class="checkmark"></span>
			</label>
			<textarea name="<?php echo $_prefix.'venue_restaurant_info'; ?>" id="venue_restaurant_info" rows="2" placeholder="<?php esc_attr_e( 'Détails restauration...', 'eventlist' ); ?>" style="margin-top: 10px;"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_restaurant_info', true) ); ?></textarea>
		</div>

		<!-- Drinks -->
		<div class="vendor_field">
			<label class="el_input_checkbox" for="venue_drinks_available">
				<?php esc_html_e( 'Boissons sur place', 'eventlist' ); ?>
				<input type="checkbox" id="venue_drinks_available" name="<?php echo $_prefix.'venue_drinks_available'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_drinks_available', true), '1' ); ?> />
				<span class="checkmark"></span>
			</label>
			<textarea name="<?php echo $_prefix.'venue_drinks_info'; ?>" id="venue_drinks_info" rows="2" placeholder="<?php esc_attr_e( 'Détails boissons...', 'eventlist' ); ?>" style="margin-top: 10px;"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_drinks_info', true) ); ?></textarea>
		</div>

	</div>

</div>
