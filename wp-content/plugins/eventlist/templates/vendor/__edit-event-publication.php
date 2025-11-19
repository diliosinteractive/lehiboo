<?php if ( !defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$the_post 		= get_post( $post_id );
$event_password = $the_post->post_password;
$event_status 	= $the_post->post_status ?? 'publish';

if ( ! empty( $event_password ) && $event_status === 'publish' ) {
	$event_status = 'protected';
}

// Extra Services (Meta keys from rules.md)
$el_handicap = get_post_meta( $post_id, $_prefix.'el_handicap', true );
$el_animal   = get_post_meta( $post_id, $_prefix.'el_animal', true );
$el_baby     = get_post_meta( $post_id, $_prefix.'el_baby', true );
$el_wifi     = get_post_meta( $post_id, $_prefix.'el_wifi', true );
$el_parking  = get_post_meta( $post_id, $_prefix.'el_parking', true );
$el_restau   = get_post_meta( $post_id, $_prefix.'el_restau', true );

?>

<!-- Extra Services Section -->
<div class="event_basic_block" id="section_services_extra">
	<h4 class="heading_section"><?php esc_html_e( 'Services & Accessibilité', 'eventlist' ); ?></h4>
	<p class="field_description"><?php esc_html_e( 'Informez vos participants sur les services disponibles.', 'eventlist' ); ?></p>

	<div class="services_grid vendor_field">
		
		<label class="el_input_checkbox service_item">
			<span class="service_icon">♿</span>
			<span class="service_label"><?php esc_html_e( 'Accessible Handicap', 'eventlist' ); ?></span>
			<input type="checkbox" name="<?php echo $_prefix.'el_handicap'; ?>" value="yes" <?php checked($el_handicap, 'yes'); ?> />
			<span class="checkmark"></span>
		</label>

		<label class="el_input_checkbox service_item">
			<span class="service_icon">🐾</span>
			<span class="service_label"><?php esc_html_e( 'Animaux acceptés', 'eventlist' ); ?></span>
			<input type="checkbox" name="<?php echo $_prefix.'el_animal'; ?>" value="yes" <?php checked($el_animal, 'yes'); ?> />
			<span class="checkmark"></span>
		</label>

		<label class="el_input_checkbox service_item">
			<span class="service_icon">👶</span>
			<span class="service_label"><?php esc_html_e( 'Adapté aux bébés', 'eventlist' ); ?></span>
			<input type="checkbox" name="<?php echo $_prefix.'el_baby'; ?>" value="yes" <?php checked($el_baby, 'yes'); ?> />
			<span class="checkmark"></span>
		</label>

		<label class="el_input_checkbox service_item">
			<span class="service_icon">📶</span>
			<span class="service_label"><?php esc_html_e( 'Wifi gratuit', 'eventlist' ); ?></span>
			<input type="checkbox" name="<?php echo $_prefix.'el_wifi'; ?>" value="yes" <?php checked($el_wifi, 'yes'); ?> />
			<span class="checkmark"></span>
		</label>

		<label class="el_input_checkbox service_item">
			<span class="service_icon">🅿️</span>
			<span class="service_label"><?php esc_html_e( 'Parking sur place', 'eventlist' ); ?></span>
			<input type="checkbox" name="<?php echo $_prefix.'el_parking'; ?>" value="yes" <?php checked($el_parking, 'yes'); ?> />
			<span class="checkmark"></span>
		</label>

		<label class="el_input_checkbox service_item">
			<span class="service_icon">🍽️</span>
			<span class="service_label"><?php esc_html_e( 'Restauration', 'eventlist' ); ?></span>
			<input type="checkbox" name="<?php echo $_prefix.'el_restau'; ?>" value="yes" <?php checked($el_restau, 'yes'); ?> />
			<span class="checkmark"></span>
		</label>

	</div>
</div>

<!-- Publication Settings -->
<div class="event_basic_block" id="section_publication">
	<h4 class="heading_section"><?php esc_html_e( 'Paramètres de publication', 'eventlist' ); ?></h4>

	<div class="vendor_field">
		<label class="label"><strong><?php esc_html_e( 'Visibilité de l\'événement', 'eventlist' ); ?></strong></label>

		<div class="input_radio_group">
			<label for="event_status_publish" class="el_input_radio">
				<?php esc_html_e( 'Public', 'eventlist' ); ?>
				<span class="el_icon_help dashicons dashicons-editor-help" data-tippy-content="<?php esc_attr_e( 'Tout le monde peut voir l\'événement.', 'eventlist' ); ?>"></span>
				<input type="radio" name="event_status" value="publish" <?php checked( $event_status, 'publish' ); ?> id="event_status_publish">
				<span class="checkmark"></span>
			</label>

			<label for="event_status_pending" class="el_input_radio">
				<?php esc_html_e( 'En attente de validation', 'eventlist' ); ?>
				<span class="el_icon_help dashicons dashicons-editor-help" data-tippy-content="<?php esc_attr_e( 'Seuls vous et l\'administrateur pouvez voir cet événement.', 'eventlist' ); ?>"></span>
				<input type="radio" name="event_status" value="pending" <?php checked( $event_status, 'pending' ); ?> id="event_status_pending">
				<span class="checkmark"></span>
			</label>

			<label for="event_status_protected" class="el_input_radio">
				<?php esc_html_e( 'Protégé par mot de passe', 'eventlist' ); ?>
				<span class="el_icon_help dashicons dashicons-editor-help" data-tippy-content="<?php esc_attr_e( 'L\'événement est public mais nécessite un mot de passe pour voir le contenu.', 'eventlist' ); ?>"></span>
				<input type="radio" name="event_status" value="protected" <?php checked( $event_status, 'protected' ); ?> id="event_status_protected">
				<span class="checkmark"></span>
			</label>

			<label for="event_status_private" class="el_input_radio">
				<?php esc_html_e( 'Privé (Lien caché)', 'eventlist' ); ?>
				<span class="el_icon_help dashicons dashicons-editor-help" data-tippy-content="<?php esc_attr_e( 'L\'événement est privé et accessible uniquement via lien direct. Il n\'apparaît pas dans les recherches.', 'eventlist' ); ?>"></span>
				<input type="radio" name="event_status" value="private" <?php checked( $event_status, 'private' ); ?> id="event_status_private">
				<span class="checkmark"></span>
			</label>
		</div>
	</div>

	<?php $is_password_active = $event_status === 'private' || $event_status === 'protected' ? 'is-active' : ''; ?>
	<div class="wrap_event_password vendor_field <?php echo esc_attr( $is_password_active ); ?>">
		<label for="event_password" ><?php esc_html_e( 'Définir un mot de passe', 'eventlist' ); ?></label>
		<div class="input_group">
			<input type="password" id="event_password" name="event_password" value="<?php echo esc_attr( $event_password ); ?>" placeholder="<?php esc_attr_e( 'Mot de passe...', 'eventlist' ); ?>" autocomplete="off" />
			<span class="show_hide_password"><i class="fa fa-eye"></i></span>
		</div>
	</div>

</div>
