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
		<?php esc_html_e( 'Type d\'événement', 'eventlist' ); ?>
	</h4>
	<div class="event_type">

		<?php if( apply_filters( 'el_show_event_type_physical', true ) ): ?>
			<label class="el_input_radio" for="classsic_event_type">
				<?php esc_html_e( 'Lieu physique', 'eventlist' ); ?>
				<input type="radio" value="classic" name="<?php echo $_prefix.'event_type'; ?>" <?php echo $event_type == 'classic' ? 'checked' : ''; ?> class="classsic_event_type" id="classsic_event_type" />
				<span class="checkmark"></span>
			</label>
		<?php endif; ?>

		<?php if( apply_filters( 'el_show_event_type_online', true ) ): ?>
			<label class="el_input_radio el_ml_10px" for="online_event_type">
				<?php esc_html_e( 'En ligne', 'eventlist' ); ?>
				<input type="radio" value="online" name="<?php echo $_prefix.'event_type'; ?>" <?php echo $event_type == 'online' ? 'checked' : ''; ?> class="online_event_type" id="online_event_type" />
				<span class="checkmark"></span>
			</label>
			<br>
		<?php endif; ?>

	</div>

</div>

<!-- Location -->
<div class="location event_basic_block">

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
				<?php endif; ?>:
			</label>

			<input type="text" name="<?php echo esc_attr( $_prefix.'add_venue' ); ?>" id="add_venue" value='' autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="<?php esc_attr_e( 'Palais des Congrès', 'eventlist' ); ?>"></input>
			<button class="button check_venue el_btn_add">
				<?php esc_html_e( 'Ajouter', 'eventlist' ); ?>
			</button>

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

</div>
