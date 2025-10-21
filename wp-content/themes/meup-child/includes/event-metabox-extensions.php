<?php
/**
 * Extensions des Metabox Event
 *
 * Ajoute des champs supplémentaires aux événements:
 * - FAQ (repeater)
 * - Inclus / Non inclus
 * - Exigences
 * - Instructions point de RDV
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Ajouter les nouveaux champs au metabox Event
 */
add_action( 'add_meta_boxes', 'lehiboo_add_event_extended_metaboxes' );

function lehiboo_add_event_extended_metaboxes() {

	// FAQ Metabox
	add_meta_box(
		'lehiboo_event_faq',
		__( 'FAQ - Questions Fréquentes', 'eventlist' ),
		'lehiboo_render_faq_metabox',
		'event',
		'normal',
		'default'
	);

	// Inclus/Non inclus Metabox
	add_meta_box(
		'lehiboo_event_includes',
		__( 'Ce qui est inclus / Non inclus', 'eventlist' ),
		'lehiboo_render_includes_metabox',
		'event',
		'normal',
		'default'
	);

	// Exigences Metabox
	add_meta_box(
		'lehiboo_event_requirements',
		__( 'Conditions requises', 'eventlist' ),
		'lehiboo_render_requirements_metabox',
		'event',
		'normal',
		'default'
	);

	// Instructions point de RDV
	add_meta_box(
		'lehiboo_event_meeting_instructions',
		__( 'Instructions point de rendez-vous', 'eventlist' ),
		'lehiboo_render_meeting_instructions_metabox',
		'event',
		'normal',
		'default'
	);
}

/**
 * Render FAQ Metabox
 */
function lehiboo_render_faq_metabox( $post ) {
	wp_nonce_field( 'lehiboo_event_faq_nonce', 'lehiboo_event_faq_nonce' );

	$faq_items = get_post_meta( $post->ID, OVA_METABOX_EVENT . 'faq', true );

	if( empty($faq_items) || !is_array($faq_items) ) {
		$faq_items = array();
	}
	?>

	<div class="lehiboo_faq_repeater">
		<div id="faq_items_container">
			<?php
			if( !empty($faq_items) ) {
				foreach( $faq_items as $index => $faq ) {
					lehiboo_render_faq_item( $index, $faq );
				}
			}
			?>
		</div>

		<button type="button" class="button button-primary" id="add_faq_item">
			<i class="dashicons dashicons-plus"></i>
			<?php _e( 'Ajouter une question', 'eventlist' ); ?>
		</button>
	</div>

	<style>
		.lehiboo_faq_repeater { padding: 15px; }
		.faq_item_wrapper { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; position: relative; }
		.faq_item_wrapper .remove_faq_item { position: absolute; top: 10px; right: 10px; color: #a00; cursor: pointer; }
		.faq_item_wrapper input[type="text"] { width: 100%; margin-bottom: 10px; }
		.faq_item_wrapper textarea { width: 100%; min-height: 100px; }
		.faq_item_header { font-weight: bold; margin-bottom: 10px; }
	</style>

	<script>
	jQuery(document).ready(function($) {
		var faqIndex = <?php echo count($faq_items); ?>;

		// Ajouter une FAQ
		$('#add_faq_item').on('click', function() {
			var html = `
				<div class="faq_item_wrapper" data-index="${faqIndex}">
					<span class="remove_faq_item dashicons dashicons-no-alt"></span>
					<div class="faq_item_header">Question ${faqIndex + 1}</div>
					<label>
						<strong><?php _e( 'Question:', 'eventlist' ); ?></strong><br>
						<input type="text" name="<?php echo OVA_METABOX_EVENT; ?>faq[${faqIndex}][question]" value="" placeholder="<?php _e( 'Entrez la question...', 'eventlist' ); ?>" />
					</label>
					<label>
						<strong><?php _e( 'Réponse:', 'eventlist' ); ?></strong><br>
						<textarea name="<?php echo OVA_METABOX_EVENT; ?>faq[${faqIndex}][answer]" placeholder="<?php _e( 'Entrez la réponse...', 'eventlist' ); ?>"></textarea>
					</label>
				</div>
			`;

			$('#faq_items_container').append(html);
			faqIndex++;
		});

		// Supprimer une FAQ
		$(document).on('click', '.remove_faq_item', function() {
			if( confirm('<?php _e( 'Supprimer cette question ?', 'eventlist' ); ?>') ) {
				$(this).closest('.faq_item_wrapper').remove();
			}
		});
	});
	</script>

	<?php
}

/**
 * Render un item FAQ
 */
function lehiboo_render_faq_item( $index, $faq ) {
	$question = isset($faq['question']) ? $faq['question'] : '';
	$answer = isset($faq['answer']) ? $faq['answer'] : '';
	?>
	<div class="faq_item_wrapper" data-index="<?php echo esc_attr($index); ?>">
		<span class="remove_faq_item dashicons dashicons-no-alt"></span>
		<div class="faq_item_header"><?php echo sprintf( __( 'Question %d', 'eventlist' ), $index + 1 ); ?></div>

		<label>
			<strong><?php _e( 'Question:', 'eventlist' ); ?></strong><br>
			<input type="text"
			       name="<?php echo OVA_METABOX_EVENT; ?>faq[<?php echo $index; ?>][question]"
			       value="<?php echo esc_attr($question); ?>"
			       placeholder="<?php _e( 'Entrez la question...', 'eventlist' ); ?>" />
		</label>

		<label>
			<strong><?php _e( 'Réponse:', 'eventlist' ); ?></strong><br>
			<textarea name="<?php echo OVA_METABOX_EVENT; ?>faq[<?php echo $index; ?>][answer]"
			          placeholder="<?php _e( 'Entrez la réponse...', 'eventlist' ); ?>"><?php echo esc_textarea($answer); ?></textarea>
		</label>
	</div>
	<?php
}

/**
 * Render Inclus/Non inclus Metabox
 */
function lehiboo_render_includes_metabox( $post ) {
	wp_nonce_field( 'lehiboo_event_includes_nonce', 'lehiboo_event_includes_nonce' );

	$includes = get_post_meta( $post->ID, OVA_METABOX_EVENT . 'includes', true );
	$excludes = get_post_meta( $post->ID, OVA_METABOX_EVENT . 'excludes', true );
	?>

	<div style="padding: 15px;">
		<label style="display: block; margin-bottom: 15px;">
			<strong><?php _e( 'Ce qui est inclus', 'eventlist' ); ?>:</strong><br>
			<small><?php _e( 'Entrez chaque élément sur une nouvelle ligne', 'eventlist' ); ?></small>
			<textarea name="<?php echo OVA_METABOX_EVENT; ?>includes"
			          rows="6"
			          style="width: 100%; margin-top: 5px;"
			          placeholder="<?php _e( 'Ex: Matériel fourni&#10;Guide professionnel&#10;Repas et boissons', 'eventlist' ); ?>"><?php echo esc_textarea($includes); ?></textarea>
		</label>

		<label style="display: block;">
			<strong><?php _e( 'Ce qui n\'est pas inclus', 'eventlist' ); ?>:</strong><br>
			<small><?php _e( 'Entrez chaque élément sur une nouvelle ligne', 'eventlist' ); ?></small>
			<textarea name="<?php echo OVA_METABOX_EVENT; ?>excludes"
			          rows="6"
			          style="width: 100%; margin-top: 5px;"
			          placeholder="<?php _e( 'Ex: Transport jusqu\'au lieu&#10;Assurance personnelle&#10;Pourboires', 'eventlist' ); ?>"><?php echo esc_textarea($excludes); ?></textarea>
		</label>
	</div>

	<?php
}

/**
 * Render Exigences Metabox
 */
function lehiboo_render_requirements_metabox( $post ) {
	wp_nonce_field( 'lehiboo_event_requirements_nonce', 'lehiboo_event_requirements_nonce' );

	$requirements = get_post_meta( $post->ID, OVA_METABOX_EVENT . 'requirements', true );
	?>

	<div style="padding: 15px;">
		<label>
			<strong><?php _e( 'Conditions requises', 'eventlist' ); ?>:</strong><br>
			<small><?php _e( 'Entrez chaque exigence sur une nouvelle ligne', 'eventlist' ); ?></small>
			<textarea name="<?php echo OVA_METABOX_EVENT; ?>requirements"
			          rows="6"
			          style="width: 100%; margin-top: 5px;"
			          placeholder="<?php _e( 'Ex: Âge minimum: 18 ans&#10;Tenue confortable recommandée&#10;Pièce d\'identité requise', 'eventlist' ); ?>"><?php echo esc_textarea($requirements); ?></textarea>
		</label>
	</div>

	<?php
}

/**
 * Render Instructions Meeting Point Metabox
 */
function lehiboo_render_meeting_instructions_metabox( $post ) {
	wp_nonce_field( 'lehiboo_event_meeting_instructions_nonce', 'lehiboo_event_meeting_instructions_nonce' );

	$meeting_instructions = get_post_meta( $post->ID, OVA_METABOX_EVENT . 'meeting_instructions', true );
	?>

	<div style="padding: 15px;">
		<label>
			<strong><?php _e( 'Instructions supplémentaires', 'eventlist' ); ?>:</strong><br>
			<small><?php _e( 'Décrivez comment arriver au point de rendez-vous, où se garer, etc.', 'eventlist' ); ?></small>
			<textarea name="<?php echo OVA_METABOX_EVENT; ?>meeting_instructions"
			          rows="4"
			          style="width: 100%; margin-top: 5px;"
			          placeholder="<?php _e( 'Ex: Le point de rendez-vous se trouve à l\'entrée principale. Un parking gratuit est disponible à 200m.', 'eventlist' ); ?>"><?php echo esc_textarea($meeting_instructions); ?></textarea>
		</label>
	</div>

	<?php
}

/**
 * Sauvegarder les métadonnées
 */
add_action( 'save_post_event', 'lehiboo_save_event_extended_metaboxes' );

function lehiboo_save_event_extended_metaboxes( $post_id ) {

	// Vérifier les nonces
	$nonces = array(
		'lehiboo_event_faq_nonce',
		'lehiboo_event_includes_nonce',
		'lehiboo_event_requirements_nonce',
		'lehiboo_event_meeting_instructions_nonce'
	);

	// Vérifier autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Vérifier permissions
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Sauvegarder FAQ
	if ( isset($_POST['lehiboo_event_faq_nonce']) && wp_verify_nonce($_POST['lehiboo_event_faq_nonce'], 'lehiboo_event_faq_nonce') ) {
		$faq = isset($_POST[OVA_METABOX_EVENT . 'faq']) ? $_POST[OVA_METABOX_EVENT . 'faq'] : array();
		update_post_meta( $post_id, OVA_METABOX_EVENT . 'faq', $faq );
	}

	// Sauvegarder Inclus/Excludes
	if ( isset($_POST['lehiboo_event_includes_nonce']) && wp_verify_nonce($_POST['lehiboo_event_includes_nonce'], 'lehiboo_event_includes_nonce') ) {
		$includes = isset($_POST[OVA_METABOX_EVENT . 'includes']) ? sanitize_textarea_field($_POST[OVA_METABOX_EVENT . 'includes']) : '';
		$excludes = isset($_POST[OVA_METABOX_EVENT . 'excludes']) ? sanitize_textarea_field($_POST[OVA_METABOX_EVENT . 'excludes']) : '';

		update_post_meta( $post_id, OVA_METABOX_EVENT . 'includes', $includes );
		update_post_meta( $post_id, OVA_METABOX_EVENT . 'excludes', $excludes );
	}

	// Sauvegarder Requirements
	if ( isset($_POST['lehiboo_event_requirements_nonce']) && wp_verify_nonce($_POST['lehiboo_event_requirements_nonce'], 'lehiboo_event_requirements_nonce') ) {
		$requirements = isset($_POST[OVA_METABOX_EVENT . 'requirements']) ? sanitize_textarea_field($_POST[OVA_METABOX_EVENT . 'requirements']) : '';
		update_post_meta( $post_id, OVA_METABOX_EVENT . 'requirements', $requirements );
	}

	// Sauvegarder Meeting Instructions
	if ( isset($_POST['lehiboo_event_meeting_instructions_nonce']) && wp_verify_nonce($_POST['lehiboo_event_meeting_instructions_nonce'], 'lehiboo_event_meeting_instructions_nonce') ) {
		$meeting_instructions = isset($_POST[OVA_METABOX_EVENT . 'meeting_instructions']) ? sanitize_textarea_field($_POST[OVA_METABOX_EVENT . 'meeting_instructions']) : '';
		update_post_meta( $post_id, OVA_METABOX_EVENT . 'meeting_instructions', $meeting_instructions );
	}
}
