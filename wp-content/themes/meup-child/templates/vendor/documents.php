<?php
/**
 * Template - Vendor Documents Page
 * Gestion des documents pour les partenaires
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = get_current_user_id();
$documents = LeHiboo_Vendor_Documents::get_vendor_documents( $user_id );
$required_docs = LeHiboo_Vendor_Documents::get_required_documents();
$all_doc_types = LeHiboo_Vendor_Documents::get_all_document_types();
$missing_docs = LeHiboo_Vendor_Documents::get_missing_required_documents( $user_id );
$all_approved = LeHiboo_Vendor_Documents::vendor_has_all_required_approved( $user_id );
?>

<div class="vendor_documents_page">

	<div class="page_header">
		<h1><i class="fas fa-folder-open"></i> Mes Documents</h1>
		<p>Gérez vos documents de vérification. Les documents marqués d'une étoile sont obligatoires.</p>
	</div>

	<!-- Statut global -->
	<div class="documents_status_banner <?php echo $all_approved ? 'status_approved' : ( empty( $missing_docs ) ? 'status_pending' : 'status_incomplete' ); ?>">
		<?php if ( $all_approved ) : ?>
			<div class="status_icon"><i class="fas fa-check-circle"></i></div>
			<div class="status_content">
				<h3>Tous vos documents sont validés</h3>
				<p>Vous pouvez publier vos activités sur Le Hiboo.</p>
			</div>
		<?php elseif ( empty( $missing_docs ) ) : ?>
			<div class="status_icon"><i class="fas fa-hourglass-half"></i></div>
			<div class="status_content">
				<h3>Documents en cours de vérification</h3>
				<p>Notre équipe vérifie vos documents. Vous serez notifié par email.</p>
			</div>
		<?php else : ?>
			<div class="status_icon"><i class="fas fa-exclamation-triangle"></i></div>
			<div class="status_content">
				<h3>Documents requis manquants</h3>
				<p>Veuillez uploader les documents obligatoires ci-dessous pour activer votre compte.</p>
			</div>
		<?php endif; ?>
	</div>

	<!-- Liste des documents -->
	<div class="documents_list">
		<?php foreach ( $all_doc_types as $type => $info ) :
			$doc = isset( $documents[ $type ] ) ? $documents[ $type ] : null;
			$is_required = in_array( $type, $required_docs );
			$status = $doc ? $doc['status'] : 'not_uploaded';
		?>
			<div class="document_card status_<?php echo esc_attr( $status ); ?> <?php echo $is_required ? 'required' : ''; ?>">
				<div class="document_icon">
					<i class="fas <?php echo esc_attr( $info['icon'] ); ?>"></i>
				</div>

				<div class="document_info">
					<h3 class="document_title">
						<?php echo esc_html( $info['label'] ); ?>
						<?php if ( $is_required ) : ?>
							<span class="required_badge" title="Document obligatoire">*</span>
						<?php endif; ?>
					</h3>
					<p class="document_description"><?php echo esc_html( $info['description'] ); ?></p>

					<?php if ( $doc ) : ?>
						<div class="document_meta">
							<span class="meta_date">
								<i class="fas fa-calendar"></i>
								Uploadé le <?php echo date_i18n( 'j M Y', strtotime( $doc['uploaded_at'] ) ); ?>
							</span>

							<?php if ( $status === 'approved' ) : ?>
								<span class="status_badge badge_approved">
									<i class="fas fa-check"></i> Validé
								</span>
							<?php elseif ( $status === 'pending' ) : ?>
								<span class="status_badge badge_pending">
									<i class="fas fa-clock"></i> En attente
								</span>
							<?php elseif ( $status === 'rejected' ) : ?>
								<span class="status_badge badge_rejected">
									<i class="fas fa-times"></i> Refusé
								</span>
								<?php if ( ! empty( $doc['rejection_reason'] ) ) : ?>
									<p class="rejection_reason">
										<i class="fas fa-info-circle"></i>
										<?php echo esc_html( $doc['rejection_reason'] ); ?>
									</p>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="document_actions">
					<?php if ( $doc && $doc['attachment_id'] ) : ?>
						<a href="<?php echo esc_url( wp_get_attachment_url( $doc['attachment_id'] ) ); ?>" target="_blank" class="btn_view_doc" title="Voir le document">
							<i class="fas fa-eye"></i>
						</a>
					<?php endif; ?>

					<?php if ( $status !== 'approved' ) : ?>
						<label class="btn_upload_doc" title="<?php echo $doc ? 'Remplacer' : 'Uploader'; ?>">
							<i class="fas fa-upload"></i>
							<input type="file"
								   class="document_upload_input"
								   data-type="<?php echo esc_attr( $type ); ?>"
								   accept="<?php echo esc_attr( $info['accept'] ); ?>"
								   style="display: none;">
						</label>
					<?php endif; ?>

					<?php if ( $doc && $status !== 'approved' ) : ?>
						<button type="button" class="btn_delete_doc" data-type="<?php echo esc_attr( $type ); ?>" title="Supprimer">
							<i class="fas fa-trash"></i>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Info section -->
	<div class="documents_info_section">
		<h3><i class="fas fa-info-circle"></i> Informations</h3>
		<ul>
			<li>Les documents marqués d'une <strong>étoile (*)</strong> sont obligatoires.</li>
			<li>Formats acceptés : PDF, JPG, JPEG, PNG</li>
			<li>Taille maximale : 5 Mo par fichier</li>
			<li>Les documents sont vérifiés sous 24-48h ouvrées.</li>
			<li>Vous recevrez un email dès qu'un document est validé ou refusé.</li>
		</ul>
	</div>

</div>

<style>
/* Documents Page Styles */
.vendor_documents_page {
	max-width: 900px;
}

.page_header {
	margin-bottom: 24px;
}

.page_header h1 {
	display: flex;
	align-items: center;
	gap: 12px;
	margin: 0 0 8px 0;
	font-size: 24px;
	font-weight: 700;
	color: #1F2937;
}

.page_header h1 i {
	color: #FF601F;
}

.page_header p {
	margin: 0;
	color: #6B7280;
	font-size: 15px;
}

/* Status Banner */
.documents_status_banner {
	display: flex;
	align-items: center;
	gap: 16px;
	padding: 20px;
	border-radius: 12px;
	margin-bottom: 24px;
}

.documents_status_banner.status_approved {
	background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
	border: 1px solid #10B981;
}

.documents_status_banner.status_pending {
	background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
	border: 1px solid #F59E0B;
}

.documents_status_banner.status_incomplete {
	background: linear-gradient(135deg, #FEF2F2 0%, #FECACA 100%);
	border: 1px solid #EF4444;
}

.status_icon {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24px;
	flex-shrink: 0;
}

.status_approved .status_icon {
	background: #10B981;
	color: #fff;
}

.status_pending .status_icon {
	background: #F59E0B;
	color: #fff;
}

.status_incomplete .status_icon {
	background: #EF4444;
	color: #fff;
}

.status_content h3 {
	margin: 0 0 4px 0;
	font-size: 16px;
	font-weight: 600;
	color: #1F2937;
}

.status_content p {
	margin: 0;
	font-size: 14px;
	color: #4B5563;
}

/* Documents List */
.documents_list {
	display: flex;
	flex-direction: column;
	gap: 12px;
	margin-bottom: 30px;
}

.document_card {
	display: flex;
	align-items: center;
	gap: 16px;
	padding: 20px;
	background: #fff;
	border-radius: 12px;
	border: 1px solid #E5E7EB;
	transition: all 0.2s ease;
}

.document_card:hover {
	border-color: #D1D5DB;
	box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.document_card.required {
	border-left: 4px solid #FF601F;
}

.document_card.status_approved {
	background: #F0FDF4;
	border-color: #86EFAC;
}

.document_card.status_rejected {
	background: #FEF2F2;
	border-color: #FECACA;
}

.document_icon {
	width: 50px;
	height: 50px;
	background: #F3F4F6;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 22px;
	color: #6B7280;
	flex-shrink: 0;
}

.status_approved .document_icon {
	background: #DCFCE7;
	color: #16A34A;
}

.status_pending .document_icon {
	background: #FEF3C7;
	color: #D97706;
}

.status_rejected .document_icon {
	background: #FEE2E2;
	color: #DC2626;
}

.document_info {
	flex: 1;
}

.document_title {
	margin: 0 0 4px 0;
	font-size: 16px;
	font-weight: 600;
	color: #1F2937;
}

.required_badge {
	color: #EF4444;
	font-size: 18px;
}

.document_description {
	margin: 0;
	font-size: 13px;
	color: #6B7280;
}

.document_meta {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 12px;
	margin-top: 10px;
}

.meta_date {
	font-size: 12px;
	color: #9CA3AF;
}

.meta_date i {
	margin-right: 4px;
}

.status_badge {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 4px 10px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 500;
}

.badge_approved {
	background: #DCFCE7;
	color: #15803D;
}

.badge_pending {
	background: #FEF3C7;
	color: #B45309;
}

.badge_rejected {
	background: #FEE2E2;
	color: #B91C1C;
}

.rejection_reason {
	margin: 8px 0 0 0;
	padding: 8px 12px;
	background: #FEE2E2;
	border-radius: 6px;
	font-size: 13px;
	color: #991B1B;
}

.rejection_reason i {
	margin-right: 6px;
}

/* Document Actions */
.document_actions {
	display: flex;
	gap: 8px;
	flex-shrink: 0;
}

.btn_view_doc,
.btn_upload_doc,
.btn_delete_doc {
	width: 40px;
	height: 40px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 16px;
	cursor: pointer;
	transition: all 0.2s ease;
	border: none;
}

.btn_view_doc {
	background: #EEF2FF;
	color: #4F46E5;
}

.btn_view_doc:hover {
	background: #C7D2FE;
}

.btn_upload_doc {
	background: #FFF4EF;
	color: #FF601F;
}

.btn_upload_doc:hover {
	background: #FFE4D6;
}

.btn_delete_doc {
	background: #FEF2F2;
	color: #EF4444;
}

.btn_delete_doc:hover {
	background: #FECACA;
}

/* Info Section */
.documents_info_section {
	padding: 20px;
	background: #F9FAFB;
	border-radius: 12px;
	border: 1px solid #E5E7EB;
}

.documents_info_section h3 {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0 0 12px 0;
	font-size: 15px;
	font-weight: 600;
	color: #374151;
}

.documents_info_section h3 i {
	color: #6366F1;
}

.documents_info_section ul {
	margin: 0;
	padding-left: 20px;
}

.documents_info_section li {
	margin-bottom: 6px;
	font-size: 14px;
	color: #4B5563;
}

/* Loading state */
.document_card.uploading {
	opacity: 0.7;
	pointer-events: none;
}

.document_card.uploading::after {
	content: '';
	position: absolute;
	top: 50%;
	left: 50%;
	width: 24px;
	height: 24px;
	margin: -12px 0 0 -12px;
	border: 3px solid #FF601F;
	border-top-color: transparent;
	border-radius: 50%;
	animation: spin 0.8s linear infinite;
}

@keyframes spin {
	to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 640px) {
	.document_card {
		flex-wrap: wrap;
	}

	.document_actions {
		width: 100%;
		justify-content: flex-end;
		margin-top: 12px;
		padding-top: 12px;
		border-top: 1px solid #E5E7EB;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	var nonce = '<?php echo wp_create_nonce( 'lehiboo_vendor_documents_nonce' ); ?>';

	// Upload document
	$('.document_upload_input').on('change', function() {
		var $input = $(this);
		var $card = $input.closest('.document_card');
		var type = $input.data('type');
		var file = this.files[0];

		if (!file) return;

		// Validation
		var maxSize = 5 * 1024 * 1024; // 5MB
		if (file.size > maxSize) {
			alert('Le fichier est trop volumineux. Taille max: 5 Mo');
			return;
		}

		// Upload
		var formData = new FormData();
		formData.append('action', 'lehiboo_upload_vendor_document');
		formData.append('nonce', nonce);
		formData.append('document_type', type);
		formData.append('document', file);

		$card.addClass('uploading');

		$.ajax({
			url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.data.message || 'Erreur lors de l\'upload');
					$card.removeClass('uploading');
				}
			},
			error: function() {
				alert('Erreur de connexion');
				$card.removeClass('uploading');
			}
		});
	});

	// Delete document
	$('.btn_delete_doc').on('click', function() {
		if (!confirm('Supprimer ce document ?')) return;

		var $btn = $(this);
		var $card = $btn.closest('.document_card');
		var type = $btn.data('type');

		$.ajax({
			url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
			type: 'POST',
			data: {
				action: 'lehiboo_delete_vendor_document',
				nonce: nonce,
				document_type: type
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.data.message || 'Erreur');
				}
			}
		});
	});
});
</script>
