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
	$EL_Setting_Event->get('latitude_map_default') == '' ? $map_lat = '39.177972' : $map_lat = $EL_Setting_Event->get('latitude_map_default');
	$EL_Setting_Event->get('longitude_map_default') == '' ? $map_lng = '-100.363750' : $map_lng = $EL_Setting_Event->get('longitude_map_default');
}

$event_type = get_post_meta( $post_id, $_prefix.'event_type', true) ? get_post_meta( $post_id, $_prefix.'event_type', true) : apply_filters( 'el_event_type_default', 'classic' );

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

<!-- Event Type -->
<div class="event_basic_block event_type_section">
	<h4 class="heading_section">
		<?php esc_html_e( 'L\'événement se déroule', 'eventlist' ); ?>
	</h4>
	<div class="event_type">

		<?php if( apply_filters( 'el_show_event_type_physical', true ) ): ?>
			<label class="el_input_radio" for="classsic_event_type">
				<?php esc_html_e( 'Dans un lieu physique', 'eventlist' ); ?>
				<input type="radio" value="classic" name="<?php echo $_prefix.'event_type'; ?>" <?php echo $event_type == 'classic' ? 'checked' : ''; ?> class="classsic_event_type event_type_radio" id="classsic_event_type" />
				<span class="checkmark"></span>
			</label>
		<?php endif; ?>

		<?php if( apply_filters( 'el_show_event_type_online', true ) ): ?>
			<label class="el_input_radio el_ml_10px" for="online_event_type">
				<?php esc_html_e( 'En ligne', 'eventlist' ); ?>
				<input type="radio" value="online" name="<?php echo $_prefix.'event_type'; ?>" <?php echo $event_type == 'online' ? 'checked' : ''; ?> class="online_event_type event_type_radio" id="online_event_type" />
				<span class="checkmark"></span>
			</label>
		<?php endif; ?>

		<?php if( apply_filters( 'el_show_event_type_home', true ) ): ?>
			<label class="el_input_radio el_ml_10px" for="home_event_type">
				<?php esc_html_e( 'À la maison', 'eventlist' ); ?>
				<input type="radio" value="home" name="<?php echo $_prefix.'event_type'; ?>" <?php echo $event_type == 'home' ? 'checked' : ''; ?> class="home_event_type event_type_radio" id="home_event_type" />
				<span class="checkmark"></span>
			</label>
			<br>
		<?php endif; ?>

	</div>

</div>

<!-- Section pour "À la maison" -->
<div class="home_location_section" style="<?php echo $event_type == 'home' ? 'display: block;' : 'display: none;'; ?>">
	<div class="event_basic_block">
		<h4 class="heading_section">
			<?php esc_html_e( 'Sélection des villes', 'eventlist' ); ?>
		</h4>
		<p class="field_description">
			<?php esc_html_e( 'L\'activité peut se faire à la maison, sélectionnez dans quelles villes l\'événement doit être publié', 'eventlist' ); ?>
		</p>

		<div class="home_cities_selection vendor_field">
			<label class="el_input_checkbox" for="show_all_cities">
				<?php esc_html_e( 'Afficher dans toutes les villes', 'eventlist' ); ?>
				<input type="checkbox" id="show_all_cities" name="<?php echo $_prefix.'show_all_cities'; ?>" value="1" <?php echo get_post_meta( $post_id, $_prefix.'show_all_cities', true) == '1' ? 'checked' : ''; ?> />
				<span class="checkmark"></span>
			</label>

			<div class="city_selector" style="<?php echo get_post_meta( $post_id, $_prefix.'show_all_cities', true) == '1' ? 'display: none;' : 'display: block;'; ?>">
				<div class="get_city vendor_field">
					<?php el_get_city( $el_city ); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Location -->
<div class="location event_basic_block physical_location_section" style="<?php echo $event_type == 'classic' ? 'display: block;' : 'display: none;'; ?>">

	<h4 class="heading_section">
		<?php esc_html_e( 'Sélectionnez le lieu où se déroule l\'activité', 'eventlist' ); ?>
	</h4>

	<!-- Choix de la source d'adresse -->
	<div class="address_source_choice vendor_field">
		<label class="label">
			<strong><?php esc_html_e( 'Veuillez choisir la source de l\'adresse pour cette localisation :', 'eventlist' ); ?></strong>
		</label>
		<div class="address_source_options">
			<label class="el_input_radio" for="address_source_entity">
				<?php esc_html_e( 'Mon adresse d\'entité', 'eventlist' ); ?>
				<input type="radio" value="entity" name="<?php echo $_prefix.'address_source'; ?>" id="address_source_entity" class="address_source_radio" <?php echo get_post_meta( $post_id, $_prefix.'address_source', true) == 'entity' || get_post_meta( $post_id, $_prefix.'address_source', true) == '' ? 'checked' : ''; ?> />
				<span class="checkmark"></span>
			</label>

			<label class="el_input_radio el_ml_10px" for="address_source_new">
				<?php esc_html_e( 'Nouvelle adresse', 'eventlist' ); ?>
				<input type="radio" value="new" name="<?php echo $_prefix.'address_source'; ?>" id="address_source_new" class="address_source_radio" <?php echo get_post_meta( $post_id, $_prefix.'address_source', true) == 'new' ? 'checked' : ''; ?> />
				<span class="checkmark"></span>
			</label>
		</div>
	</div>

	<div class="country_city ">
		<div class="get_country vendor_field">
			<?php el_get_state( $el_country ); ?>
		</div>
		<div class="get_city vendor_field">
			<?php el_get_city( $el_city ); ?>
		</div>
	</div>


	<div id="mb_venue">
		<div class="ova_row vendor_field">
			<label class="label" for="add_venue">
				<?php esc_html_e( 'Nom du lieu', 'eventlist' ); ?>
				<?php if ( apply_filters( 'el_venue_req', false, $args ) == true ): ?>
					<span class="el_req">*</span>
				<?php endif; ?>
				<span class="help-text" style="display: block; font-weight: normal; font-size: 13px; color: #6b7280; margin-top: 5px;">
					<?php esc_html_e( 'Exemple : Maison des Associations, Salle Polyvalente, etc.', 'eventlist' ); ?>
				</span>
			</label>

			<div style="display: flex; gap: 10px; align-items: flex-start;">
				<input
					type="text"
					name="<?php echo esc_attr( $_prefix.'add_venue' ); ?>"
					id="add_venue"
					value=''
					autocomplete="off"
					autocorrect="off"
					autocapitalize="none"
					placeholder="<?php esc_attr_e( 'Nom du lieu', 'eventlist' ); ?>"
					style="flex: 1;" />
				<button class="button check_venue el_btn_add" style="white-space: nowrap;">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:5px;">
						<line x1="12" y1="5" x2="12" y2="19"></line>
						<line x1="5" y1="12" x2="19" y2="12"></line>
					</svg>
					<?php esc_html_e( 'Enregistrer le lieu', 'eventlist' ); ?>
				</button>
			</div>
			<p class="help-text" style="margin-top: 8px; font-size: 12px; color: #6b7280; font-style: italic;">
				<?php esc_html_e( 'Ce lieu sera enregistré pour être réutilisé dans vos prochains événements', 'eventlist' ); ?>
			</p>

		</div>

		<!-- List Venue -->
		<ul id="data_venue">
			<?php if ( $venue ) {

				foreach ( $venue as $key => $value) {

					$post_venue = el_get_page_by_title( $value, OBJECT, 'venue' );
					if ($post_venue) { ?>
						<li>
							<input type="hidden" name="<?php echo esc_attr( $_prefix.'venue'.'['.$key.']' ); ?>" value="<?php echo esc_attr($value); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
							<i class="icon_close_alt remove_venue"></i>&nbsp;
							<span>
								<?php echo esc_html( stripslashes_deep( $post_venue->post_title ) ); ?>
							</span>
						</li>

					<?php }
				}
			} ?>
		</ul>
	</div>

	<div class="el_map">

		<input id="pac-input" name="<?php echo esc_attr( $_prefix.'map_address' ); ?>" value="<?php echo $post_id != '' ? $map_address : __('Paris, France', 'eventlist'); ?>" class="controls" type="text" placeholder="<?php esc_html_e( 'Saisir une adresse sur la carte', 'eventlist' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">

		<div class="place-autocomplete-card" id="place-autocomplete-card">
	      <p><?php esc_html_e( 'Rechercher un lieu:', 'eventlist' ); ?></p>
	    </div>
		<div class="vendor_field" id="admin_show_map" style="height: 300px;"></div>

		<input type="hidden" value="<?php echo esc_attr( $map_name ); ?>" name="<?php echo esc_attr( $_prefix.'map_name' ); ?>" id="map_name"  autocomplete="off" autocorrect="off" autocapitalize="none"/>
	</div>

	<div class="edit_latlng vendor_field">

		<label class="el_input_checkbox" for="editor_latlng" style="height:20px;">
			<?php esc_html_e( 'Modifier la position', 'eventlist' ); ?>
			<input type="checkbox" value="" name="editor_latlng" id="editor_latlng" />
			<span class="checkmark"></span>
		</label>

		<div class="wrap_lnglat">
			<div class="lng vendor_field">
				<label><?php esc_html_e( 'Longitude', 'eventlist' ); ?>: </label>
				<input type="text" class="readonly" value="<?php echo esc_attr( $map_lat ); ?>" name="<?php echo esc_attr( $_prefix.'map_lat' ); ?>" id="map_lat" autocomplete="off" autocorrect="off" autocapitalize="none" readonly />
			</div>

			<div class="lat vendor_field">
				<label><?php esc_html_e( 'Latitude', 'eventlist' ); ?>: </label>
				<input type="text" class="readonly" value="<?php echo esc_attr( $map_lng ); ?>" name="<?php echo esc_attr( $_prefix.'map_lng' ); ?>" id="map_lng" autocomplete="off" autocorrect="off" autocapitalize="none" readonly/>
			</div>
		</div>
	</div>

	<div class="el_address vendor_field">
		<span class="edit_address">
			<label for="edit_full_address" class="el_input_checkbox">

				<?php esc_html_e( 'Modifier l\'adresse complète', 'eventlist' ); ?>

				<input type="checkbox" id='edit_full_address' class="edit_full_address" name="<?php echo esc_attr( $_prefix.'edit_full_address' ); ?>" value="<?php echo esc_attr( $edit_full_address ); ?>" <?php echo esc_attr( $edit_full_address ); ?> >
				<span class="checkmark"></span>
			</label>

			<input type="text" id="address" class="address <?php echo esc_attr($edit_full_address != 'checked' ? 'readonly' : ''); ?>" name="<?php echo esc_attr( $_prefix.'address' ); ?>" value="<?php echo esc_attr( $address ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" <?php echo esc_attr($edit_full_address != 'checked' ? 'readonly' : ''); ?> >
		</span>
	</div>

	<!-- Informations complémentaires du lieu -->
	<div class="venue_additional_info">

		<!-- Type d'événement (Intérieur/Extérieur) -->
		<div class="venue_event_type vendor_field">
			<label class="label">
				<strong><?php esc_html_e( 'Type d\'événements organisés', 'eventlist' ); ?></strong>
				<span class="optional"><?php esc_html_e( '(facultatif)', 'eventlist' ); ?></span>
			</label>
			<select name="<?php echo $_prefix.'venue_event_type'; ?>" id="venue_event_type">
				<option value=""><?php esc_html_e( 'Sélectionnez...', 'eventlist' ); ?></option>
				<option value="indoor" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'indoor' ); ?>><?php esc_html_e( 'Intérieur', 'eventlist' ); ?></option>
				<option value="outdoor" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'outdoor' ); ?>><?php esc_html_e( 'Extérieur', 'eventlist' ); ?></option>
				<option value="both" <?php selected( get_post_meta( $post_id, $_prefix.'venue_event_type', true), 'both' ); ?>><?php esc_html_e( 'Intérieur & Extérieur', 'eventlist' ); ?></option>
			</select>
		</div>

		<!-- Stationnement -->
		<div class="venue_parking vendor_field">
			<label class="label">
				<strong><?php esc_html_e( 'Stationnement', 'eventlist' ); ?></strong>
				<span class="optional"><?php esc_html_e( '(facultatif)', 'eventlist' ); ?></span>
			</label>
			<textarea name="<?php echo $_prefix.'venue_parking'; ?>" id="venue_parking" rows="3" placeholder="<?php esc_attr_e( 'Informations pour stationner...', 'eventlist' ); ?>"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_parking', true) ); ?></textarea>

			<div class="parking_image_upload">
				<label><?php esc_html_e( 'Plan de stationnement', 'eventlist' ); ?></label>
				<?php
				$parking_image = get_post_meta( $post_id, $_prefix.'venue_parking_image', true);
				?>
				<div class="image-wrap-parking">
					<?php if ( $parking_image ): ?>
						<div class="item">
							<img src="<?php echo esc_url( wp_get_attachment_url( $parking_image ) ); ?>" class="image" />
							<input type="hidden" name="<?php echo $_prefix.'venue_parking_image'; ?>" value="<?php echo esc_attr( $parking_image ); ?>"/>
						</div>
						<a href="#" class="el_remove_parking_image">
							<span class="dashicons dashicons-no"></span>
						</a>
					<?php endif; ?>
				</div>
				<a href="#" class="button button-secondary el_add_parking_image"><?php esc_html_e( 'Choisir une image', 'eventlist' ); ?></a>
			</div>
		</div>

		<!-- Accès & Transports -->
		<div class="venue_access vendor_field">
			<label class="label">
				<strong><?php esc_html_e( 'Accès & Transports', 'eventlist' ); ?></strong>
				<span class="optional"><?php esc_html_e( '(facultatif)', 'eventlist' ); ?></span>
			</label>
			<textarea name="<?php echo $_prefix.'venue_access'; ?>" id="venue_access" rows="3" placeholder="<?php esc_attr_e( 'Informations sur l\'accès et les transports...', 'eventlist' ); ?>"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_access', true) ); ?></textarea>

			<div class="access_image_upload">
				<label><?php esc_html_e( 'Plan d\'accès', 'eventlist' ); ?></label>
				<?php
				$access_image = get_post_meta( $post_id, $_prefix.'venue_access_image', true);
				?>
				<div class="image-wrap-access">
					<?php if ( $access_image ): ?>
						<div class="item">
							<img src="<?php echo esc_url( wp_get_attachment_url( $access_image ) ); ?>" class="image" />
							<input type="hidden" name="<?php echo $_prefix.'venue_access_image'; ?>" value="<?php echo esc_attr( $access_image ); ?>"/>
						</div>
						<a href="#" class="el_remove_access_image">
							<span class="dashicons dashicons-no"></span>
						</a>
					<?php endif; ?>
				</div>
				<a href="#" class="button button-secondary el_add_access_image"><?php esc_html_e( 'Choisir une image', 'eventlist' ); ?></a>
			</div>
		</div>

		<!-- Accessibilité PMR -->
		<div class="venue_pmr vendor_field">
			<label class="label">
				<strong><?php esc_html_e( 'Accessibilité PMR', 'eventlist' ); ?></strong>
				<span class="optional"><?php esc_html_e( '(facultatif)', 'eventlist' ); ?></span>
			</label>
			<div class="pmr_checkbox">
				<label class="el_input_checkbox" for="venue_pmr_accessible">
					<?php esc_html_e( 'Oui', 'eventlist' ); ?>
					<input type="checkbox" id="venue_pmr_accessible" name="<?php echo $_prefix.'venue_pmr_accessible'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_pmr_accessible', true), '1' ); ?> />
					<span class="checkmark"></span>
				</label>
			</div>
			<textarea name="<?php echo $_prefix.'venue_pmr_info'; ?>" id="venue_pmr_info" rows="2" placeholder="<?php esc_attr_e( 'Informations sur l\'accessibilité...', 'eventlist' ); ?>"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_pmr_info', true) ); ?></textarea>
		</div>

		<!-- Restauration sur place -->
		<div class="venue_restaurant vendor_field">
			<label class="label">
				<strong><?php esc_html_e( 'Restauration sur place', 'eventlist' ); ?></strong>
				<span class="optional"><?php esc_html_e( '(facultatif)', 'eventlist' ); ?></span>
			</label>
			<div class="restaurant_checkbox">
				<label class="el_input_checkbox" for="venue_restaurant_available">
					<?php esc_html_e( 'Oui', 'eventlist' ); ?>
					<input type="checkbox" id="venue_restaurant_available" name="<?php echo $_prefix.'venue_restaurant_available'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_restaurant_available', true), '1' ); ?> />
					<span class="checkmark"></span>
				</label>
			</div>
			<textarea name="<?php echo $_prefix.'venue_restaurant_info'; ?>" id="venue_restaurant_info" rows="2" placeholder="<?php esc_attr_e( 'Informations sur la restauration...', 'eventlist' ); ?>"><?php echo esc_textarea( get_post_meta( $post_id, $_prefix.'venue_restaurant_info', true) ); ?></textarea>
		</div>

		<!-- Boisson sur place -->
		<div class="venue_drinks vendor_field">
			<label class="label">
				<strong><?php esc_html_e( 'Boisson sur place', 'eventlist' ); ?></strong>
				<span class="optional"><?php esc_html_e( '(facultatif)', 'eventlist' ); ?></span>
			</label>
			<div class="drinks_checkbox">
				<label class="el_input_checkbox" for="venue_drinks_available">
					<?php esc_html_e( 'Oui', 'eventlist' ); ?>
					<input type="checkbox" id="venue_drinks_available" name="<?php echo $_prefix.'venue_drinks_available'; ?>" value="1" <?php checked( get_post_meta( $post_id, $_prefix.'venue_drinks_available', true), '1' ); ?> />
					<span class="checkmark"></span>
				</label>
			</div>
			<?php
			$venue_drinks_info = get_post_meta( $post_id, $_prefix.'venue_drinks_info', true);
			wp_editor( $venue_drinks_info, 'venue_drinks_info', array(
				'textarea_name' => $_prefix.'venue_drinks_info',
				'textarea_rows' => 5,
				'media_buttons' => false,
				'teeny' => true,
				'quicktags' => true
			) );
			?>
		</div>

	</div>

</div>
