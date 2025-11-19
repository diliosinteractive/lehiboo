<?php if ( ! defined( 'ABSPATH' ) ) exit();

$post_id 	= isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
$_prefix 	= OVA_METABOX_EVENT;

$ticket_link = get_post_meta( $post_id, $_prefix.'ticket_link', true) ? get_post_meta( $post_id, $_prefix.'ticket_link', true) : '';
$ticket_external_link = get_post_meta( $post_id, $_prefix.'ticket_external_link', true) ? get_post_meta( $post_id, $_prefix.'ticket_external_link', true) : '';
$ticket_external_prices = get_post_meta( $post_id, $_prefix.'ticket_external_prices', true) ? get_post_meta( $post_id, $_prefix.'ticket_external_prices', true) : array();

// Organizer Info (Moved from Presentation)
$name_organizer   = get_post_meta( $post_id, $_prefix.'name_organizer', true) ? get_post_meta( $post_id, $_prefix.'name_organizer', true) : '';
$phone_organizer  = get_post_meta( $post_id, $_prefix.'phone_organizer', true) ? get_post_meta( $post_id, $_prefix.'phone_organizer', true) : '';
$mail_organizer   = get_post_meta( $post_id, $_prefix.'mail_organizer', true) ? get_post_meta( $post_id, $_prefix.'mail_organizer', true) : '';
$info_organizer   = get_post_meta( $post_id, $_prefix.'info_organizer', true) ? get_post_meta( $post_id, $_prefix.'info_organizer', true) : '';

?>

<div class="edit_ticket_info event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Billetterie', 'eventlist' ); ?></h4>
	<p class="ticket_description">
		<?php esc_html_e( 'Gérez la billetterie (prochainement) ou les inscriptions directement sur LeHiboo, ou redirigez vos utilisateurs vers une plateforme externe si vous utilisez un outil tiers pour la billetterie.', 'eventlist' ); ?>
	</p>
</div>

<div class="event_basic_block">
	<h5 class="heading_section"><?php esc_html_e( 'Mode de billetterie', 'eventlist' ); ?></h5>
	
	<div class="ticket_link_options vendor_field">
		
		<!-- Paid Ticketing (Coming Soon) -->
		<label class="el_input_radio el_btn_ticket_choice disabled" style="opacity: 0.6; cursor: not-allowed;">
			<span class="choice_icon">🎫</span>
			<span class="choice_label"><?php esc_html_e( 'Créer une billetterie', 'eventlist' ); ?></span>
			<span class="choice_badge"><?php esc_html_e( '(Bientôt)', 'eventlist' ); ?></span>
			<input type="radio" name="dummy_ticket" disabled />
			<span class="checkmark"></span>
		</label>

		<!-- Internal Registration -->
		<label for="ticket_internal_link" class="el_input_radio el_btn_ticket_choice">
			<span class="choice_icon">📝</span>
			<span class="choice_label"><?php esc_html_e( 'Créer une liste d\'inscription', 'eventlist' ); ?></span>
			<input
				type="radio"
				value="ticket_internal_link"
				name="<?php echo esc_attr( $_prefix.'ticket_link' ); ?>"
				id="ticket_internal_link"
				<?php checked( $ticket_link, 'ticket_internal_link' ); ?>
			/>
			<span class="checkmark"></span>
		</label>

		<!-- External Link -->
		<label for="ticket_external_link" class="el_input_radio el_btn_ticket_choice">
			<span class="choice_icon">🔗</span>
			<span class="choice_label"><?php esc_html_e( 'Utiliser un lien externe', 'eventlist' ); ?></span>
			<input
				type="radio"
				value="ticket_external_link"
				id="ticket_external_link"
				name="<?php echo esc_attr( $_prefix.'ticket_link' ); ?>"
				<?php checked( $ticket_link, 'ticket_external_link' ); ?>
			/>
			<span class="checkmark"></span>
		</label>

	</div>
</div>

<!-- External Link Configuration -->
<div class="ticket_external_link_section event_basic_block" style="<?php echo $ticket_link == 'ticket_external_link' ? 'display: block;' : 'display: none;'; ?>">
	<h5 class="heading_section"><?php esc_html_e( 'Configuration du lien externe', 'eventlist' ); ?></h5>

	<div class="vendor_field">
		<label class="label">
			<strong><?php esc_html_e( 'Lien URL de la billetterie', 'eventlist' ); ?></strong>
			<span class="el_req">*</span>
		</label>
		<input
			type="url"
			name="<?php echo esc_attr( $_prefix.'ticket_external_link' ); ?>"
			value="<?php echo esc_url( $ticket_external_link ); ?>"
			placeholder="<?php esc_attr_e( 'https://...', 'eventlist' ); ?>"
		/>
	</div>

	<div class="vendor_field">
		<label class="label">
			<strong><?php esc_html_e( 'Tarifs (Informatif)', 'eventlist' ); ?></strong>
		</label>
		<p class="field_description"><?php esc_html_e( 'Ajoutez un ou plusieurs tarifs pour informer vos visiteurs.', 'eventlist' ); ?></p>

		<div class="external_prices_list">
			<?php if ( !empty($ticket_external_prices) && is_array($ticket_external_prices) ): ?>
				<?php foreach ($ticket_external_prices as $key => $price_item): ?>
					<div class="external_price_item">
						<input type="text" name="<?php echo esc_attr( $_prefix.'ticket_external_prices['.$key.'][name]' ); ?>" value="<?php echo esc_attr($price_item['name']); ?>" placeholder="<?php esc_attr_e( 'Nom du tarif', 'eventlist' ); ?>" class="price_name_input" />
						<input type="text" name="<?php echo esc_attr( $_prefix.'ticket_external_prices['.$key.'][price]' ); ?>" value="<?php echo esc_attr($price_item['price']); ?>" placeholder="<?php esc_attr_e( 'Prix', 'eventlist' ); ?>" class="price_amount_input" />
						<span class="currency_symbol">€</span>
						<button type="button" class="button remove_external_price">x</button>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<button type="button" class="button button-secondary add_external_price el_btn_add">
			<i class="icon_plus"></i> <?php esc_html_e( 'Ajouter un tarif', 'eventlist' ); ?>
		</button>
	</div>
</div>

<!-- Internal Registration Configuration -->
<div class="ticket_internal_link_section event_basic_block" style="<?php echo $ticket_link == 'ticket_internal_link' ? 'display: block;' : 'display: none;'; ?>">
	<h5 class="heading_section"><?php esc_html_e( 'Configuration des inscriptions', 'eventlist' ); ?></h5>
	
	<p class="field_description">
		<?php esc_html_e( 'Configurez les types de billets disponibles pour l\'inscription sur le site.', 'eventlist' ); ?>
	</p>

	<!-- Reuse existing ticket loop logic but simplified -->
	<div class="ticket_list_wrapper">
		<?php 
		$tickets = get_post_meta( $post_id, $_prefix.'ticket', true);
		if ( !empty($tickets) && is_array($tickets) ):
			foreach ( $tickets as $key => $value ): ?>
				<div class="ticket_item vendor_field">
					<div class="ticket_header">
						<span class="ticket_title"><?php echo isset($value['name_ticket']) ? esc_html($value['name_ticket']) : 'Nouveau Billet'; ?></span>
						<div class="ticket_actions">
							<button type="button" class="button remove_ticket">x</button>
						</div>
					</div>
					<div class="ticket_body">
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e( 'Nom du billet', 'eventlist' ); ?></label>
								<input type="text" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][name_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['name_ticket']) ? $value['name_ticket'] : '' ); ?>" placeholder="Ex: Place Adulte" />
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e( 'Prix (€)', 'eventlist' ); ?></label>
								<input type="text" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][price_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['price_ticket']) ? $value['price_ticket'] : '' ); ?>" placeholder="0" />
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label><?php esc_html_e( 'Quantité totale', 'eventlist' ); ?></label>
								<input type="number" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_total_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['number_total_ticket']) ? $value['number_total_ticket'] : '' ); ?>" />
							</div>
							<div class="col-md-4">
								<label><?php esc_html_e( 'Min par commande', 'eventlist' ); ?></label>
								<input type="number" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_min_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['number_min_ticket']) ? $value['number_min_ticket'] : '' ); ?>" />
							</div>
							<div class="col-md-4">
								<label><?php esc_html_e( 'Max par commande', 'eventlist' ); ?></label>
								<input type="number" name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_max_ticket]' ); ?>" value="<?php echo esc_attr( isset($value['number_max_ticket']) ? $value['number_max_ticket'] : '' ); ?>" />
							</div>
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

<!-- Organizer Contact Info (Moved here) -->
<div class="organizer_contact_section event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Contact Organisateur', 'eventlist' ); ?></h4>
	<p class="field_description"><?php esc_html_e( 'Ces informations seront affichées pour que les participants puissent vous contacter.', 'eventlist' ); ?></p>

	<div class="vendor_field">
		<label for="info_organizer" class="el_input_checkbox">
			<?php esc_html_e( 'Utiliser les infos de mon profil', 'eventlist' ); ?>
			<input type="checkbox" id="info_organizer" name="<?php echo esc_attr( $_prefix.'info_organizer' ); ?>" value="yes" <?php checked($info_organizer, 'yes'); ?> />
			<span class="checkmark"></span>
		</label>
	</div>

	<div class="organizer_custom_info" style="<?php echo $info_organizer == 'yes' ? 'display: none;' : 'display: block;'; ?>">
		<div class="vendor_field">
			<label for="name_organizer"><?php esc_html_e( 'Nom de l\'organisateur', 'eventlist' ); ?></label>
			<input type="text" id="name_organizer" name="<?php echo esc_attr( $_prefix.'name_organizer' ); ?>" value="<?php echo esc_attr( $name_organizer ); ?>" />
		</div>
		<div class="vendor_field">
			<label for="phone_organizer"><?php esc_html_e( 'Téléphone', 'eventlist' ); ?></label>
			<input type="text" id="phone_organizer" name="<?php echo esc_attr( $_prefix.'phone_organizer' ); ?>" value="<?php echo esc_attr( $phone_organizer ); ?>" />
		</div>
		<div class="vendor_field">
			<label for="mail_organizer"><?php esc_html_e( 'Email', 'eventlist' ); ?></label>
			<input type="email" id="mail_organizer" name="<?php echo esc_attr( $_prefix.'mail_organizer' ); ?>" value="<?php echo esc_attr( $mail_organizer ); ?>" />
		</div>
	</div>
</div>