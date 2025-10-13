<?php
/**
 * V1 Le Hiboo - Options Espace Partenaire
 * Interface admin pour gérer les options des formulaires partenaires
 */

defined( 'ABSPATH' ) || exit;

// Traiter la soumission du formulaire
if ( isset( $_POST['el_save_partner_options'] ) && check_admin_referer( 'el_partner_options_nonce', 'el_partner_options_nonce' ) ) {

	// Sauvegarder les postes
	if ( isset( $_POST['el_postes'] ) ) {
		$postes = array_map( 'sanitize_text_field', $_POST['el_postes'] );
		$postes = array_filter( $postes ); // Retirer les valeurs vides
		update_option( 'el_postes_list', $postes );
	}

	// Sauvegarder les rôles d'organisation
	if ( isset( $_POST['el_org_roles'] ) ) {
		$org_roles = array();
		foreach ( $_POST['el_org_roles'] as $key => $label ) {
			$key = sanitize_key( $key );
			$label = sanitize_text_field( $label );
			if ( !empty( $label ) ) {
				$org_roles[$key] = $label;
			}
		}
		update_option( 'el_org_roles_list', $org_roles );
	}

	// Sauvegarder les statuts juridiques
	if ( isset( $_POST['el_statuts_juridiques'] ) ) {
		$statuts = array();
		foreach ( $_POST['el_statuts_juridiques'] as $key => $label ) {
			$key = sanitize_key( $key );
			$label = sanitize_text_field( $label );
			if ( !empty( $label ) ) {
				$statuts[$key] = $label;
			}
		}
		update_option( 'el_statuts_juridiques_list', $statuts );
	}

	// Sauvegarder les types de structure
	if ( isset( $_POST['el_types_structure'] ) ) {
		$types = array();
		foreach ( $_POST['el_types_structure'] as $key => $label ) {
			$key = sanitize_key( $key );
			$label = sanitize_text_field( $label );
			if ( !empty( $label ) ) {
				$types[$key] = $label;
			}
		}
		update_option( 'el_types_structure_list', $types );
	}

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Options enregistrées avec succès !', 'eventlist' ) . '</p></div>';
}

// Récupérer les valeurs actuelles
$postes = get_option( 'el_postes_list', array(
	'Directeur / Directrice',
	'Responsable événementiel',
	'Chargé(e) de communication',
	'Président(e)',
	'Gérant(e)',
) );

$org_roles = get_option( 'el_org_roles_list', array(
	'organisateur' => 'Organisateur d\'événements',
	'lieu' => 'Lieu / Salle',
	'prestataire' => 'Prestataire de services',
	'association' => 'Association culturelle',
) );

$statuts_juridiques = get_option( 'el_statuts_juridiques_list', array(
	'association' => 'Association loi 1901',
	'sarl' => 'SARL',
	'sas' => 'SAS',
	'auto_entrepreneur' => 'Auto-entrepreneur / Micro-entreprise',
	'eirl' => 'EIRL',
	'sa' => 'SA',
	'ei' => 'Entreprise Individuelle',
	'autre' => 'Autre',
) );

$types_structure = get_option( 'el_types_structure_list', array(
	'culturel' => 'Culturel',
	'sportif' => 'Sportif',
	'educatif' => 'Éducatif',
	'loisirs' => 'Loisirs',
	'artistique' => 'Artistique',
	'social' => 'Social / Solidaire',
) );

?>

<div class="wrap">
	<h1><?php esc_html_e( 'Options Espace Partenaire', 'eventlist' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Configurez les options disponibles dans les formulaires de l\'espace partenaire (profil, organisation, etc.).', 'eventlist' ); ?>
	</p>

	<form method="post" action="">
		<?php wp_nonce_field( 'el_partner_options_nonce', 'el_partner_options_nonce' ); ?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<!-- Colonne principale -->
				<div id="post-body-content">

					<!-- Section : Postes -->
					<div class="postbox">
						<div class="postbox-header">
							<h2><?php esc_html_e( 'Postes (Profil Gestionnaire)', 'eventlist' ); ?></h2>
						</div>
						<div class="inside">
							<p class="description">
								<?php esc_html_e( 'Liste des postes disponibles dans le champ "Poste" de l\'onglet Informations Personnelles.', 'eventlist' ); ?>
							</p>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Libellé', 'eventlist' ); ?></th>
										<th width="80"><?php esc_html_e( 'Action', 'eventlist' ); ?></th>
									</tr>
								</thead>
								<tbody id="postes_list">
									<?php foreach ( $postes as $index => $poste ): ?>
										<tr>
											<td>
												<input type="text" name="el_postes[]" value="<?php echo esc_attr( $poste ); ?>" class="regular-text" />
											</td>
											<td>
												<button type="button" class="button remove-row"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="2">
											<button type="button" class="button add-poste-row"><?php esc_html_e( '+ Ajouter un poste', 'eventlist' ); ?></button>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

					<!-- Section : Rôles d'organisation -->
					<div class="postbox">
						<div class="postbox-header">
							<h2><?php esc_html_e( 'Rôles de l\'organisation', 'eventlist' ); ?></h2>
						</div>
						<div class="inside">
							<p class="description">
								<?php esc_html_e( 'Rôles disponibles dans l\'onglet "Mon Organisation" (checkboxes multiples).', 'eventlist' ); ?>
							</p>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Clé (slug)', 'eventlist' ); ?></th>
										<th><?php esc_html_e( 'Libellé', 'eventlist' ); ?></th>
										<th width="80"><?php esc_html_e( 'Action', 'eventlist' ); ?></th>
									</tr>
								</thead>
								<tbody id="org_roles_list">
									<?php foreach ( $org_roles as $key => $label ): ?>
										<tr>
											<td>
												<input type="text" name="el_org_roles_keys[]" value="<?php echo esc_attr( $key ); ?>" class="regular-text" readonly />
											</td>
											<td>
												<input type="text" name="el_org_roles[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" />
											</td>
											<td>
												<button type="button" class="button remove-row"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="3">
											<button type="button" class="button add-org-role-row"><?php esc_html_e( '+ Ajouter un rôle', 'eventlist' ); ?></button>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

					<!-- Section : Statuts juridiques -->
					<div class="postbox">
						<div class="postbox-header">
							<h2><?php esc_html_e( 'Statuts juridiques', 'eventlist' ); ?></h2>
						</div>
						<div class="inside">
							<p class="description">
								<?php esc_html_e( 'Statuts disponibles dans le menu déroulant "Statut juridique".', 'eventlist' ); ?>
							</p>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Clé (slug)', 'eventlist' ); ?></th>
										<th><?php esc_html_e( 'Libellé', 'eventlist' ); ?></th>
										<th width="80"><?php esc_html_e( 'Action', 'eventlist' ); ?></th>
									</tr>
								</thead>
								<tbody id="statuts_juridiques_list">
									<?php foreach ( $statuts_juridiques as $key => $label ): ?>
										<tr>
											<td>
												<input type="text" name="el_statuts_juridiques_keys[]" value="<?php echo esc_attr( $key ); ?>" class="regular-text" readonly />
											</td>
											<td>
												<input type="text" name="el_statuts_juridiques[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" />
											</td>
											<td>
												<button type="button" class="button remove-row"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="3">
											<button type="button" class="button add-statut-row"><?php esc_html_e( '+ Ajouter un statut', 'eventlist' ); ?></button>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

					<!-- Section : Types de structure -->
					<div class="postbox">
						<div class="postbox-header">
							<h2><?php esc_html_e( 'Types de structure', 'eventlist' ); ?></h2>
						</div>
						<div class="inside">
							<p class="description">
								<?php esc_html_e( 'Types disponibles dans l\'onglet "Mon Organisation" (checkboxes multiples).', 'eventlist' ); ?>
							</p>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Clé (slug)', 'eventlist' ); ?></th>
										<th><?php esc_html_e( 'Libellé', 'eventlist' ); ?></th>
										<th width="80"><?php esc_html_e( 'Action', 'eventlist' ); ?></th>
									</tr>
								</thead>
								<tbody id="types_structure_list">
									<?php foreach ( $types_structure as $key => $label ): ?>
										<tr>
											<td>
												<input type="text" name="el_types_structure_keys[]" value="<?php echo esc_attr( $key ); ?>" class="regular-text" readonly />
											</td>
											<td>
												<input type="text" name="el_types_structure[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" />
											</td>
											<td>
												<button type="button" class="button remove-row"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="3">
											<button type="button" class="button add-type-row"><?php esc_html_e( '+ Ajouter un type', 'eventlist' ); ?></button>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

				</div>

				<!-- Sidebar -->
				<div id="postbox-container-1" class="postbox-container">
					<div class="postbox">
						<div class="postbox-header">
							<h2><?php esc_html_e( 'Enregistrer', 'eventlist' ); ?></h2>
						</div>
						<div class="inside">
							<div class="submitbox">
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<input type="submit" name="el_save_partner_options" class="button button-primary button-large" value="<?php esc_attr_e( 'Enregistrer les modifications', 'eventlist' ); ?>" />
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>

					<div class="postbox">
						<div class="postbox-header">
							<h2><?php esc_html_e( 'Aide', 'eventlist' ); ?></h2>
						</div>
						<div class="inside">
							<p><strong><?php esc_html_e( 'Clé (slug)', 'eventlist' ); ?> :</strong></p>
							<p><?php esc_html_e( 'Identifiant unique utilisé en interne. Ne pas modifier après création.', 'eventlist' ); ?></p>

							<p><strong><?php esc_html_e( 'Libellé', 'eventlist' ); ?> :</strong></p>
							<p><?php esc_html_e( 'Texte visible par les utilisateurs dans les formulaires.', 'eventlist' ); ?></p>
						</div>
					</div>
				</div>

			</div>
		</div>
	</form>
</div>

<script>
jQuery(document).ready(function($) {

	// Supprimer une ligne
	$('.remove-row').on('click', function() {
		if (confirm('<?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer cette ligne ?', 'eventlist' ); ?>')) {
			$(this).closest('tr').remove();
		}
	});

	// Ajouter un poste
	$('.add-poste-row').on('click', function() {
		var newRow = '<tr>' +
			'<td><input type="text" name="el_postes[]" value="" class="regular-text" /></td>' +
			'<td><button type="button" class="button remove-row"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button></td>' +
			'</tr>';
		$('#postes_list').append(newRow);

		// Réattacher l'événement
		$('.remove-row').off('click').on('click', function() {
			if (confirm('<?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer cette ligne ?', 'eventlist' ); ?>')) {
				$(this).closest('tr').remove();
			}
		});
	});

	// Ajouter un rôle d'organisation
	$('.add-org-role-row').on('click', function() {
		var timestamp = Date.now();
		var newKey = 'role_' + timestamp;
		var newRow = '<tr>' +
			'<td><input type="text" name="el_org_roles_keys[]" value="' + newKey + '" class="regular-text" readonly /></td>' +
			'<td><input type="text" name="el_org_roles[' + newKey + ']" value="" class="regular-text" /></td>' +
			'<td><button type="button" class="button remove-row"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button></td>' +
			'</tr>';
		$('#org_roles_list').append(newRow);

		$('.remove-row').off('click').on('click', function() {
			if (confirm('<?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer cette ligne ?', 'eventlist' ); ?>')) {
				$(this).closest('tr').remove();
			}
		});
	});

	// Ajouter un statut juridique
	$('.add-statut-row').on('click', function() {
		var timestamp = Date.now();
		var newKey = 'statut_' + timestamp;
		var newRow = '<tr>' +
			'<td><input type="text" name="el_statuts_juridiques_keys[]" value="' + newKey + '" class="regular-text" readonly /></td>' +
			'<td><input type="text" name="el_statuts_juridiques[' + newKey + ']" value="" class="regular-text" /></td>' +
			'<td><button type="button" class="button remove-row"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button></td>' +
			'</tr>';
		$('#statuts_juridiques_list').append(newRow);

		$('.remove-row').off('click').on('click', function() {
			if (confirm('<?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer cette ligne ?', 'eventlist' ); ?>')) {
				$(this).closest('tr').remove();
			}
		});
	});

	// Ajouter un type de structure
	$('.add-type-row').on('click', function() {
		var timestamp = Date.now();
		var newKey = 'type_' + timestamp;
		var newRow = '<tr>' +
			'<td><input type="text" name="el_types_structure_keys[]" value="' + newKey + '" class="regular-text" readonly /></td>' +
			'<td><input type="text" name="el_types_structure[' + newKey + ']" value="" class="regular-text" /></td>' +
			'<td><button type="button" class="button remove-row"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button></td>' +
			'</tr>';
		$('#types_structure_list').append(newRow);

		$('.remove-row').off('click').on('click', function() {
			if (confirm('<?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer cette ligne ?', 'eventlist' ); ?>')) {
				$(this).closest('tr').remove();
			}
		});
	});

});
</script>

<style>
.widefat td input[type="text"] {
	width: 100%;
}
.postbox {
	margin-bottom: 20px;
}
</style>
