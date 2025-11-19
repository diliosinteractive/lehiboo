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
		<div class="el_row">
			<div class="el_col_4">
				<label class="el_checkbox_wrapper">
					<input type="checkbox" name="<?php echo $_prefix.'el_handicap'; ?>" value="yes" <?php checked($el_handicap, 'yes'); ?> />
					<span class="el_checkbox_label">♿ <?php esc_html_e( 'Accessible Handicap', 'eventlist' ); ?></span>
				</label>
			</div>
			<div class="el_col_4">
				<label class="el_checkbox_wrapper">
					<input type="checkbox" name="<?php echo $_prefix.'el_animal'; ?>" value="yes" <?php checked($el_animal, 'yes'); ?> />
					<span class="el_checkbox_label">🐾 <?php esc_html_e( 'Animaux acceptés', 'eventlist' ); ?></span>
				</label>
			</div>
			<div class="el_col_4">
				<label class="el_checkbox_wrapper">
					<input type="checkbox" name="<?php echo $_prefix.'el_baby'; ?>" value="yes" <?php checked($el_baby, 'yes'); ?> />
					<span class="el_checkbox_label">👶 <?php esc_html_e( 'Adapté aux bébés', 'eventlist' ); ?></span>
				</label>
			</div>
		</div>
		<div class="el_row" style="margin-top: 12px;">
			<div class="el_col_4">
				<label class="el_checkbox_wrapper">
					<input type="checkbox" name="<?php echo $_prefix.'el_wifi'; ?>" value="yes" <?php checked($el_wifi, 'yes'); ?> />
					<span class="el_checkbox_label">📶 <?php esc_html_e( 'Wifi gratuit', 'eventlist' ); ?></span>
				</label>
			</div>
			<div class="el_col_4">
				<label class="el_checkbox_wrapper">
					<input type="checkbox" name="<?php echo $_prefix.'el_parking'; ?>" value="yes" <?php checked($el_parking, 'yes'); ?> />
					<span class="el_checkbox_label">🅿️ <?php esc_html_e( 'Parking sur place', 'eventlist' ); ?></span>
				</label>
			</div>
			<div class="el_col_4">
				<label class="el_checkbox_wrapper">
					<input type="checkbox" name="<?php echo $_prefix.'el_restau'; ?>" value="yes" <?php checked($el_restau, 'yes'); ?> />
					<span class="el_checkbox_label">🍽️ <?php esc_html_e( 'Restauration', 'eventlist' ); ?></span>
				</label>
			</div>
		</div>
	</div>
</div>

<!-- Publication Settings -->
<div class="event_basic_block" id="section_publication">
	<h4 class="heading_section"><?php esc_html_e( 'Paramètres de publication', 'eventlist' ); ?></h4>
	<p class="field_description"><?php esc_html_e( 'Contrôlez qui peut voir votre événement.', 'eventlist' ); ?></p>

	<div class="vendor_field">
		<div class="el_row">
			<!-- Public -->
			<div class="el_col_6" style="margin-bottom: 12px;">
				<label class="el_card_radio <?php echo ($event_status == 'publish') ? 'active' : ''; ?>" for="event_status_publish">
					<input type="radio" name="event_status" value="publish" <?php checked( $event_status, 'publish' ); ?> id="event_status_publish">
					<span class="radio_content">
						<i class="icon_globe"></i>
						<span class="radio_title"><?php esc_html_e( 'Public', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Visible par tout le monde', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>

			<!-- Pending -->
			<div class="el_col_6" style="margin-bottom: 12px;">
				<label class="el_card_radio <?php echo ($event_status == 'pending') ? 'active' : ''; ?>" for="event_status_pending">
					<input type="radio" name="event_status" value="pending" <?php checked( $event_status, 'pending' ); ?> id="event_status_pending">
					<span class="radio_content">
						<i class="icon_hourglass"></i>
						<span class="radio_title"><?php esc_html_e( 'En attente', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'En attente de validation', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>

			<!-- Protected -->
			<div class="el_col_6">
				<label class="el_card_radio <?php echo ($event_status == 'protected') ? 'active' : ''; ?>" for="event_status_protected">
					<input type="radio" name="event_status" value="protected" <?php checked( $event_status, 'protected' ); ?> id="event_status_protected">
					<span class="radio_content">
						<i class="icon_lock"></i>
						<span class="radio_title"><?php esc_html_e( 'Protégé', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Nécessite un mot de passe', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>

			<!-- Private -->
			<div class="el_col_6">
				<label class="el_card_radio <?php echo ($event_status == 'private') ? 'active' : ''; ?>" for="event_status_private">
					<input type="radio" name="event_status" value="private" <?php checked( $event_status, 'private' ); ?> id="event_status_private">
					<span class="radio_content">
						<i class="icon_group"></i>
						<span class="radio_title"><?php esc_html_e( 'Privé', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Accessible via lien caché', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>
		</div>
	</div>

	<?php $is_password_active = $event_status === 'private' || $event_status === 'protected' ? 'is-active' : ''; ?>
	<div class="wrap_event_password vendor_field <?php echo esc_attr( $is_password_active ); ?>" style="margin-top: 16px;">
		<label for="event_password" ><?php esc_html_e( 'Définir un mot de passe', 'eventlist' ); ?></label>
		<div class="el_input_icon_wrapper">
			<i class="icon_key_alt"></i>
			<input type="password" id="event_password" name="event_password" value="<?php echo esc_attr( $event_password ); ?>" placeholder="<?php esc_attr_e( 'Mot de passe...', 'eventlist' ); ?>" autocomplete="off" />
			<span class="show_hide_password" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #717171;"><i class="fa fa-eye"></i></span>
		</div>
	</div>

</div>
