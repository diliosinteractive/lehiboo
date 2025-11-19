<?php if ( ! defined( 'ABSPATH' ) ) exit();

$post_id 	= isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
$_prefix 	= OVA_METABOX_EVENT;

$ticket_link = get_post_meta( $post_id, $_prefix.'ticket_link', true) ? get_post_meta( $post_id, $_prefix.'ticket_link', true) : '';
$ticket_external_link = get_post_meta( $post_id, $_prefix.'ticket_external_link', true) ? get_post_meta( $post_id, $_prefix.'ticket_external_link', true) : '';
$ticket_external_prices = get_post_meta( $post_id, $_prefix.'ticket_external_prices', true) ? get_post_meta( $post_id, $_prefix.'ticket_external_prices', true) : array();

// Organizer Info
$name_organizer   = get_post_meta( $post_id, $_prefix.'name_organizer', true) ? get_post_meta( $post_id, $_prefix.'name_organizer', true) : '';
$phone_organizer  = get_post_meta( $post_id, $_prefix.'phone_organizer', true) ? get_post_meta( $post_id, $_prefix.'phone_organizer', true) : '';
$mail_organizer   = get_post_meta( $post_id, $_prefix.'mail_organizer', true) ? get_post_meta( $post_id, $_prefix.'mail_organizer', true) : '';
$info_organizer   = get_post_meta( $post_id, $_prefix.'info_organizer', true) ? get_post_meta( $post_id, $_prefix.'info_organizer', true) : '';

?>

<div class="edit_ticket_info event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Billetterie', 'eventlist' ); ?></h4>
	<p class="field_description">
		<?php esc_html_e( 'Gérez les inscriptions ou redirigez vos visiteurs.', 'eventlist' ); ?>
	</p>

	<div class="ticket_link_options vendor_field">
		<label class="label"><strong><?php esc_html_e( 'Mode de billetterie', 'eventlist' ); ?></strong></label>
		<div class="el_row">
			<!-- Internal Registration -->
			<div class="el_col_6">
				<label class="el_card_radio <?php echo ($ticket_link == 'ticket_internal_link') ? 'active' : ''; ?>" for="ticket_internal_link">
					<input type="radio" value="ticket_internal_link" name="<?php echo esc_attr( $_prefix.'ticket_link' ); ?>" id="ticket_internal_link" <?php checked( $ticket_link, 'ticket_internal_link' ); ?> />
					<span class="radio_content">
						<i class="icon_pencil-edit"></i>
						<span class="radio_title"><?php esc_html_e( 'Inscription sur le site', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Gérez les inscrits directement ici', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>

			<!-- External Link -->
			<div class="el_col_6">
				<label class="el_card_radio <?php echo ($ticket_link == 'ticket_external_link') ? 'active' : ''; ?>" for="ticket_external_link">
					<input type="radio" value="ticket_external_link" id="ticket_external_link" name="<?php echo esc_attr( $_prefix.'ticket_link' ); ?>" <?php checked( $ticket_link, 'ticket_external_link' ); ?> />
					<span class="radio_content">
						<i class="icon_link"></i>
						<span class="radio_title"><?php esc_html_e( 'Lien externe', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Redirigez vers une autre billetterie', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>
		</div>
		
		<!-- Coming Soon Badge -->
		<div style="margin-top: 12px; text-align: center;">
			<span class="el_badge_coming_soon" style="background: #F7F7F7; color: #717171; padding: 4px 12px; border-radius: 100px; font-size: 12px;">
				<?php esc_html_e( 'La billetterie payante arrive bientôt !', 'eventlist' ); ?>
			</span>
		</div>
	</div>

	<!-- External Link Configuration -->
	<div class="ticket_external_link_section" style="<?php echo $ticket_link == 'ticket_external_link' ? 'display: block;' : 'display: none;'; ?>">
		<hr class="el_separator">
		<h5 class="heading_section"><?php esc_html_e( 'Configuration du lien externe', 'eventlist' ); ?></h5>

		<div class="vendor_field">
			<label for="ticket_external_link"><?php esc_html_e( 'Lien URL de la billetterie', 'eventlist' ); ?> <span class="el_req">*</span></label>
			<div class="el_input_icon_wrapper">
				<i class="icon_link"></i>
				<input type="url" id="ticket_external_link" name="<?php echo esc_attr( $_prefix.'ticket_external_link' ); ?>" value="<?php echo esc_url( $ticket_external_link ); ?>" placeholder="<?php esc_attr_e( 'https://...', 'eventlist' ); ?>" />
			</div>
		</div>

		<div class="vendor_field">
			<label><?php esc_html_e( 'Tarifs (Informatif)', 'eventlist' ); ?></label>
			<p class="field_description"><?php esc_html_e( 'Ajoutez un ou plusieurs tarifs pour informer vos visiteurs.', 'eventlist' ); ?></p>

			<div class="external_prices_list">
				<?php if ( !empty($ticket_external_prices) && is_array($ticket_external_prices) ): ?>
					<?php foreach ($ticket_external_prices as $key => $price_item): ?>
						<div class="external_price_item el_row" style="align-items: center; margin-bottom: 10px;">
							<div class="el_col_6">
								<input type="text" name="<?php echo esc_attr( $_prefix.'ticket_external_prices['.$key.'][name]' ); ?>" value="<?php echo esc_attr($price_item['name']); ?>" placeholder="<?php esc_attr_e( 'Nom du tarif', 'eventlist' ); ?>" />
							</div>
							<div class="el_col_4">
								<div class="el_input_icon_wrapper">
									<i class="icon_currency">€</i>
									<input type="text" name="<?php echo esc_attr( $_prefix.'ticket_external_prices['.$key.'][price]' ); ?>" value="<?php echo esc_attr($price_item['price']); ?>" placeholder="<?php esc_attr_e( 'Prix', 'eventlist' ); ?>" />
								</div>
							</div>
							<div class="el_col_2">
								<button type="button" class="button remove_external_price" style="color: red; background: none; border: none;">x</button>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<button type="button" class="button add_external_price el_btn_add">
				<i class="icon_plus"></i> <?php esc_html_e( 'Ajouter un tarif', 'eventlist' ); ?>
			</button>
		</div>
	</div>

	<!-- Internal Registration Configuration -->
	<div class="ticket_internal_link_section" style="<?php echo $ticket_link == 'ticket_internal_link' ? 'display: block;' : 'display: none;'; ?>">
		<hr class="el_separator">
		<h5 class="heading_section"><?php esc_html_e( 'Configuration des inscriptions', 'eventlist' ); ?></h5>
		
		<p class="field_description">
			<?php esc_html_e( 'Configurez les types de billets disponibles pour l\'inscription sur le site.', 'eventlist' ); ?>
		</p>

		<div class="ticket_list_wrapper">
			<?php 
			$tickets = get_post_meta( $post_id, $_prefix.'ticket', true);
			if ( !empty($tickets) && is_array($tickets) ):
				foreach ( $tickets as $key => $value ): ?>
					<div class="ticket_item vendor_field" style="background: #F7F7F7; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
						<div class="ticket_header" style="display: flex; justify-content: space-between; margin-bottom: 12px;">
							<span class="ticket_title" style="font-weight: 600;"><?php echo isset($value['name_ticket']) ? esc_html($value['name_ticket']) : 'Nouveau Billet'; ?></span>
							<button type="button" class="button remove_ticket" style="color: red; background: none; border: none;">x</button>
						</div>
						
						<div class="el_row">
							<div class="el_col_6">
								<label><?php esc_html_e( 'Nom du billet', 'eventlist' ); ?></label>
								<input type="text" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][name_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['name_ticket']) ? $value['name_ticket'] : '' ); ?>" placeholder="Ex: Place Adulte" />
							</div>
							<div class="el_col_6">
								<label><?php esc_html_e( 'Prix (€)', 'eventlist' ); ?></label>
								<input type="text" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][price_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['price_ticket']) ? $value['price_ticket'] : '' ); ?>" placeholder="0" />
							</div>
						</div>
						
						<div class="el_row" style="margin-top: 12px;">
							<div class="el_col_4">
								<label><?php esc_html_e( 'Quantité totale', 'eventlist' ); ?></label>
								<input type="number" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_total_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['number_total_ticket']) ? $value['number_total_ticket'] : '' ); ?>" />
							</div>
							<div class="el_col_4">
								<label><?php esc_html_e( 'Min / commande', 'eventlist' ); ?></label>
								<input type="number" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_min_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['number_min_ticket']) ? $value['number_min_ticket'] : '' ); ?>" />
							</div>
							<div class="el_col_4">
								<label><?php esc_html_e( 'Max / commande', 'eventlist' ); ?></label>
								<input type="number" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_max_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['number_max_ticket']) ? $value['number_max_ticket'] : '' ); ?>" />
							</div>
						</div>
					</div>
				<?php endforeach;
			endif; ?>
		</div>

		<button type="button" class="button add_ticket el_btn_add">
			<i class="icon_plus"></i> <?php esc_html_e( 'Ajouter un type de billet', 'eventlist' ); ?>
		</button>
	</div>

</div>

<!-- Organizer Contact Info -->
<div class="organizer_contact_section event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Contact Organisateur', 'eventlist' ); ?></h4>
	<p class="field_description"><?php esc_html_e( 'Ces informations seront affichées pour que les participants puissent vous contacter.', 'eventlist' ); ?></p>

	<div class="vendor_field">
		<label class="el_checkbox_wrapper">
			<input type="checkbox" id="info_organizer" name="<?php echo esc_attr( $_prefix.'info_organizer' ); ?>" value="yes" <?php checked($info_organizer, 'yes'); ?> />
			<span class="el_checkbox_label"><?php esc_html_e( 'Utiliser les infos de mon profil', 'eventlist' ); ?></span>
		</label>
	</div>

	<div class="organizer_custom_info" style="<?php echo $info_organizer == 'yes' ? 'display: none;' : 'display: block;'; ?> margin-top: 16px;">
		<div class="el_row">
			<div class="el_col_4">
				<div class="vendor_field">
					<label for="name_organizer"><?php esc_html_e( 'Nom', 'eventlist' ); ?></label>
					<input type="text" id="name_organizer" name="<?php echo esc_attr( $_prefix.'name_organizer' ); ?>" value="<?php echo esc_attr( $name_organizer ); ?>" />
				</div>
			</div>
			<div class="el_col_4">
				<div class="vendor_field">
					<label for="phone_organizer"><?php esc_html_e( 'Téléphone', 'eventlist' ); ?></label>
					<input type="text" id="phone_organizer" name="<?php echo esc_attr( $_prefix.'phone_organizer' ); ?>" value="<?php echo esc_attr( $phone_organizer ); ?>" />
				</div>
			</div>
			<div class="el_col_4">
				<div class="vendor_field">
					<label for="mail_organizer"><?php esc_html_e( 'Email', 'eventlist' ); ?></label>
					<input type="email" id="mail_organizer" name="<?php echo esc_attr( $_prefix.'mail_organizer' ); ?>" value="<?php echo esc_attr( $mail_organizer ); ?>" />
				</div>
			</div>
		</div>
	</div>
</div>