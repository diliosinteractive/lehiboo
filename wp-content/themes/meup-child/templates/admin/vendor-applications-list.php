<?php
/**
 * Template Admin - Liste des demandes partenaires
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap lehiboo_vendor_admin">
	<h1 class="admin_page_title">
		<i class="fas fa-businessman"></i>
		Demandes Partenaires
		<span class="title_badge"><?php echo $stats['pending']; ?> en attente</span>
	</h1>

	<!-- Stats Cards -->
	<div class="stats_cards">
		<div class="stat_card stat_total">
			<div class="stat_icon">
				<i class="fas fa-users"></i>
			</div>
			<div class="stat_content">
				<div class="stat_value"><?php echo $stats['total']; ?></div>
				<div class="stat_label">Total</div>
			</div>
		</div>

		<div class="stat_card stat_pending">
			<div class="stat_icon">
				<i class="fas fa-clock"></i>
			</div>
			<div class="stat_content">
				<div class="stat_value"><?php echo $stats['pending']; ?></div>
				<div class="stat_label">En attente</div>
			</div>
		</div>

		<div class="stat_card stat_approved">
			<div class="stat_icon">
				<i class="fas fa-check-circle"></i>
			</div>
			<div class="stat_content">
				<div class="stat_value"><?php echo $stats['approved']; ?></div>
				<div class="stat_label">Approuvés</div>
			</div>
		</div>

		<div class="stat_card stat_rejected">
			<div class="stat_icon">
				<i class="fas fa-times-circle"></i>
			</div>
			<div class="stat_content">
				<div class="stat_value"><?php echo $stats['rejected']; ?></div>
				<div class="stat_label">Rejetés</div>
			</div>
		</div>
	</div>

	<!-- Filters -->
	<div class="tablenav top">
		<div class="alignleft actions">
			<form method="get">
				<input type="hidden" name="page" value="lehiboo-vendor-applications">

				<select name="status" id="filter_status">
					<option value="all" <?php selected( $status_filter, 'all' ); ?>>Tous les statuts</option>
					<option value="pending_approval" <?php selected( $status_filter, 'pending_approval' ); ?>>En attente</option>
					<option value="approved" <?php selected( $status_filter, 'approved' ); ?>>Approuvés</option>
					<option value="rejected" <?php selected( $status_filter, 'rejected' ); ?>>Rejetés</option>
				</select>

				<button type="submit" class="button">Filtrer</button>
			</form>
		</div>
	</div>

	<!-- Applications Table -->
	<table class="wp-list-table widefat fixed striped vendors_table">
		<thead>
			<tr>
				<th class="column_name">Organisation</th>
				<th class="column_contact">Contact</th>
				<th class="column_type">Type</th>
				<th class="column_date">Date demande</th>
				<th class="column_status">Statut</th>
				<th class="column_actions">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $vendors ) ) : ?>
				<?php foreach ( $vendors as $vendor ) :
					$vendor_status = get_user_meta( $vendor->ID, 'vendor_status', true );
					$org_name = get_user_meta( $vendor->ID, 'org_display_name', true );
					$org_type = get_user_meta( $vendor->ID, 'org_type', true );
					$org_phone = get_user_meta( $vendor->ID, 'org_phone', true );
					$app_date = get_user_meta( $vendor->ID, 'vendor_application_date', true );
					$logo_id = get_user_meta( $vendor->ID, 'org_logo_id', true );
					$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
				?>
				<tr data-user-id="<?php echo esc_attr( $vendor->ID ); ?>">
					<td class="column_name">
						<div class="org_info">
							<?php if ( $logo_url ) : ?>
								<img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="org_logo">
							<?php else : ?>
								<div class="org_logo_placeholder">
									<i class="fas fa-building"></i>
								</div>
							<?php endif; ?>
							<div class="org_details">
								<strong><?php echo esc_html( $org_name ?: 'Non renseigné' ); ?></strong>
								<span class="org_id">#<?php echo $vendor->ID; ?></span>
							</div>
						</div>
					</td>

					<td class="column_contact">
						<div class="contact_info">
							<div><i class="fas fa-user"></i> <?php echo esc_html( $vendor->first_name . ' ' . $vendor->last_name ); ?></div>
							<div><i class="fas fa-envelope"></i> <a href="mailto:<?php echo esc_attr( $vendor->user_email ); ?>"><?php echo esc_html( $vendor->user_email ); ?></a></div>
							<?php if ( $org_phone ) : ?>
								<div><i class="fas fa-phone"></i> <?php echo esc_html( $org_phone ); ?></div>
							<?php endif; ?>
						</div>
					</td>

					<td class="column_type">
						<?php
						$types = array(
							'association' => 'Association',
							'entreprise' => 'Entreprise',
							'autoentrepreneur' => 'Auto-entrepreneur',
							'collectivite' => 'Collectivité',
							'autre' => 'Autre'
						);
						echo isset( $types[$org_type] ) ? esc_html( $types[$org_type] ) : esc_html( $org_type );
						?>
					</td>

					<td class="column_date">
						<?php echo $app_date ? date_i18n( 'j M Y', strtotime( $app_date ) ) : '-'; ?>
					</td>

					<td class="column_status">
						<?php
						$status_classes = array(
							'pending_approval' => 'status_pending',
							'approved' => 'status_approved',
							'rejected' => 'status_rejected'
						);
						$status_labels = array(
							'pending_approval' => 'En attente',
							'approved' => 'Approuvé',
							'rejected' => 'Rejeté'
						);
						$status_class = $status_classes[$vendor_status] ?? 'status_pending';
						$status_label = $status_labels[$vendor_status] ?? $vendor_status;
						?>
						<span class="status_badge <?php echo esc_attr( $status_class ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</span>
					</td>

					<td class="column_actions">
						<button class="button button_view" onclick="toggleVendorDetails(<?php echo $vendor->ID; ?>)">
							<i class="fas fa-eye"></i> Voir détails
						</button>

						<?php if ( $vendor_status === 'pending_approval' ) : ?>
							<button class="button button_approve" data-user-id="<?php echo $vendor->ID; ?>">
								<i class="fas fa-check"></i> Approuver
							</button>
							<button class="button button_reject" data-user-id="<?php echo $vendor->ID; ?>">
								<i class="fas fa-times"></i> Rejeter
							</button>
						<?php endif; ?>
					</td>
				</tr>

				<!-- Row Details (Hidden by default) -->
				<tr class="vendor_details_row" id="vendor_details_<?php echo $vendor->ID; ?>" style="display: none;">
					<td colspan="6">
						<div class="vendor_details_content">
							<div class="details_grid">
								<!-- Organisation Info -->
								<div class="details_section">
									<h3><i class="fas fa-building"></i> Informations Organisation</h3>
									<div class="details_items">
										<div class="detail_item">
											<strong>Nom:</strong>
											<span><?php echo esc_html( $org_name ); ?></span>
										</div>
										<div class="detail_item">
											<strong>Type:</strong>
											<span><?php echo isset( $types[$org_type] ) ? esc_html( $types[$org_type] ) : esc_html( $org_type ); ?></span>
										</div>
										<div class="detail_item">
											<strong>SIRET:</strong>
											<span><?php echo esc_html( get_user_meta( $vendor->ID, 'org_siret', true ) ?: '-' ); ?></span>
										</div>
										<div class="detail_item">
											<strong>Site web:</strong>
											<?php
											$website = get_user_meta( $vendor->ID, 'org_website', true );
											echo $website ? '<a href="' . esc_url( $website ) . '" target="_blank">' . esc_html( $website ) . '</a>' : '-';
											?>
										</div>
										<div class="detail_item">
											<strong>Adresse:</strong>
											<span>
												<?php
												$address = get_user_meta( $vendor->ID, 'org_address', true );
												$city = get_user_meta( $vendor->ID, 'org_city', true );
												$zipcode = get_user_meta( $vendor->ID, 'org_zipcode', true );
												echo esc_html( $address . ', ' . $zipcode . ' ' . $city );
												?>
											</span>
										</div>
										<div class="detail_item detail_item_full">
											<strong>Description:</strong>
											<p><?php echo esc_html( get_user_meta( $vendor->ID, 'org_description', true ) ); ?></p>
										</div>
									</div>
								</div>

								<!-- Categories -->
								<div class="details_section">
									<h3><i class="fas fa-tags"></i> Catégories d'activités</h3>
									<div class="categories_list">
										<?php
										$categories = get_user_meta( $vendor->ID, 'org_categories', true );
										if ( is_array( $categories ) && ! empty( $categories ) ) {
											foreach ( $categories as $cat ) {
												echo '<span class="category_tag">' . esc_html( $cat ) . '</span>';
											}
										} else {
											echo '<span class="no_data">Aucune catégorie sélectionnée</span>';
										}
										?>
									</div>
								</div>

								<!-- Documents -->
								<div class="details_section">
									<h3><i class="fas fa-file-alt"></i> Documents</h3>
									<div class="documents_list">
										<?php
										$documents = array(
											'org_logo_id' => 'Logo',
											'org_cover_id' => 'Image de couverture',
											'org_kbis_id' => 'Kbis',
											'org_insurance_id' => 'Assurance',
											'org_certifications_id' => 'Certifications'
										);
										foreach ( $documents as $meta_key => $label ) {
											$doc_id = get_user_meta( $vendor->ID, $meta_key, true );
											if ( $doc_id ) {
												$doc_url = wp_get_attachment_url( $doc_id );
												echo '<div class="document_item">';
												echo '<i class="fas fa-file"></i>';
												echo '<a href="' . esc_url( $doc_url ) . '" target="_blank">' . esc_html( $label ) . '</a>';
												echo '</div>';
											}
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="6" class="no_vendors">
						<i class="fas fa-inbox"></i>
						<p>Aucune demande trouvée.</p>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<!-- Pagination -->
	<?php if ( $total > $per_page ) : ?>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<?php
			echo paginate_links( array(
				'base' => add_query_arg( 'paged', '%#%' ),
				'format' => '',
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'total' => ceil( $total / $per_page ),
				'current' => $paged
			) );
			?>
		</div>
	</div>
	<?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="reject_modal" class="vendor_modal" style="display: none;">
	<div class="modal_content">
		<div class="modal_header">
			<h2>Rejeter la demande</h2>
			<button class="modal_close">&times;</button>
		</div>
		<div class="modal_body">
			<p>Indiquez la raison du rejet (optionnel):</p>
			<textarea id="reject_reason" rows="5" placeholder="Exemple: Le dossier ne correspond pas à nos critères..."></textarea>
		</div>
		<div class="modal_footer">
			<button class="button button_cancel">Annuler</button>
			<button class="button button_confirm_reject">Confirmer le rejet</button>
		</div>
	</div>
</div>

<script>
function toggleVendorDetails(userId) {
	jQuery('#vendor_details_' + userId).slideToggle();
}
</script>
