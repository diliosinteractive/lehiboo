<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Publication de l'événement
 * Contient: Statut de visibilité (Publish, Pending, Protected, Private), Mot de passe
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';

$the_post 		= get_post( $post_id );

$event_password = $the_post->post_password;
$event_status 	= $the_post->post_status ?? 'publish';

if ( ! empty( $event_password ) && $event_status === 'publish' ) {
	$event_status = 'protected';
}

?>

<!-- Publication Settings -->
<div class="event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Paramètres de publication', 'eventlist' ); ?></h4>

	<div class="vendor_field">

		<label><?php esc_html_e( 'Visibilité', 'eventlist' ); ?></label>

		<div class="input_radio_group">
			<label for="event_status_publish" class="el_input_radio">
				<?php esc_html_e( 'Public', 'eventlist' ); ?>
				<span class="el_icon_help dashicons dashicons-editor-help"
				data-tippy-content="<?php esc_attr_e( 'Tout le monde peut voir l\'événement.', 'eventlist' ); ?>"></span>
				<input type="radio" name="event_status" value="publish" <?php checked( $event_status, 'publish' ); ?> id="event_status_publish">
				<span class="checkmark"></span>
			</label>
			<label for="event_status_pending" class="el_input_radio">
				<?php esc_html_e( 'En attente', 'eventlist' ); ?>
				<span class="el_icon_help dashicons dashicons-editor-help"
				data-tippy-content="<?php esc_attr_e( 'Seuls vous et l\'administrateur pouvez voir cet événement.', 'eventlist' ); ?>"></span>
				<input type="radio" name="event_status" value="pending" <?php checked( $event_status, 'pending' ); ?> id="event_status_pending">
				<span class="checkmark"></span>
			</label>
			<label for="event_status_protected" class="el_input_radio">
				<?php esc_html_e( 'Protégé par mot de passe', 'eventlist' ); ?>
				<span class="el_icon_help dashicons dashicons-editor-help"
				data-tippy-content="<?php esc_attr_e( 'L\'événement est public mais nécessite un mot de passe pour voir le contenu.', 'eventlist' ); ?>"></span>
				<input type="radio" name="event_status" value="protected" <?php checked( $event_status, 'protected' ); ?> id="event_status_protected">
				<span class="checkmark"></span>
			</label>
			<label for="event_status_private" class="el_input_radio">
				<?php esc_html_e( 'Privé', 'eventlist' ); ?>
				<span class="el_icon_help dashicons dashicons-editor-help"
				data-tippy-content="<?php esc_attr_e( 'L\'événement est privé et accessible uniquement via lien direct. Il n\'apparaît pas dans les recherches.', 'eventlist' ); ?>"></span>
				<input type="radio" name="event_status" value="private" <?php checked( $event_status, 'private' ); ?> id="event_status_private">
				<span class="checkmark"></span>
			</label>
		</div>
	</div>

	<?php $is_password_active = $event_status === 'private' || $event_status === 'protected' ? 'is-active' : ''; ?>
	<div class="wrap_event_password vendor_field <?php echo esc_attr( $is_password_active ); ?>">
		<label for="event_password" ><?php esc_html_e( 'Mot de passe', 'eventlist' ); ?></label>
		<div class="input_group">
			<a href="#" class="show_hide_password" aria-role="button" aria-label="<?php esc_attr_e( 'Afficher/masquer mot de passe', 'eventlist' ); ?>">
				<i class="fa fa-eye" aria-hidden="true"></i>
			</a>
			<input type="password" id="event_password"
			name="event_password" value="<?php echo esc_attr( $event_password ); ?>"
			data-mess="<?php esc_attr_e( 'Le mot de passe est requis', 'eventlist' ); ?>"
			autocomplete="off" autocorrect="off" autocapitalize="none">
		</div>

	</div>

</div>
