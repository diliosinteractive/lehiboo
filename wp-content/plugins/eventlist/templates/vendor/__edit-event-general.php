<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Informations générales de l'événement
 * Contient: Nom, Catégorie, Taxonomies personnalisées, Tags
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

// Get selected cat
$get_cat_selected = get_the_terms( $post_id, 'event_cat' ) ? get_the_terms( $post_id, 'event_cat' ) : '';
$cats_selected = array();
if ($get_cat_selected != '') {
	foreach ($get_cat_selected as $key => $value) {
		$cats_selected[] = $value->term_id;
	}
}

// Get selected tags
$get_tag_selected = get_the_terms( $post_id, 'event_tag' ) ? get_the_terms( $post_id, 'event_tag' ) : '';
$tags_name_selected = array();
if ($get_tag_selected != '') {
	$i = 0;
	foreach ($get_tag_selected as $key => $value) {
		$tags_name_selected[] = $value->name;
		if ($i++ == 5) break;
	}
}

$the_post = get_post( $post_id );
$post_title = empty( $post_id ) ? '' : $the_post->post_title;

$list_taxonomy = EL_Post_Types::register_taxonomies_customize();

?>

<input type="hidden" value="<?php echo esc_attr( $post_id ); ?>" id="post_id" name="post_id"/>
<input type="hidden" class="prefix" value="<?php echo esc_attr(OVA_METABOX_EVENT); ?>">

<!-- Basic -->
<div class="basic_info event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Informations de base', 'eventlist' ); ?></h4>
	<!-- alert -->
	<div class="event_basic_block_alert"></div>

	<div class="wrap_name_event vendor_field">
		<label for="name_event" ><?php esc_html_e( 'Nom de l\'événement', 'eventlist' ); ?> <span class="el_req">*</span></label>
		<input type="text" id="name_event" name="name_event" value="<?php echo esc_attr( $post_title ); ?>" placeholder="<?php esc_html_e( 'Saisir le titre', 'eventlist' ); ?>" autocomplete="one-time-code" required>
	</div>

	<div class="wrap_cat vendor_field">
		<label for="event_cat"><?php esc_html_e( 'Catégorie', 'eventlist' ); ?> <span class="el_req">*</span></label>

		<?php
		$selected_opt = ! empty( $cats_selected ) ? $cats_selected[0] : '';
		$required = true;
		el_get_taxonomy3('event_cat', 'event_cat', $selected_opt, $required ); ?>
	</div>

	<?php
	$arr_list_slug_taxonomy = [];
	$el_custom_taxonomy_required = apply_filters( 'el_custom_taxonomy_required', array() );
	if( $list_taxonomy ) {
		foreach( $list_taxonomy as $taxonomy ) {

			$exclude_tax = apply_filters( 'el_exclude_custom_taxonomy', array() );

			if ( ! current_user_can('administrator') && in_array( $taxonomy['slug'], $exclude_tax ) ) {
				continue;
			}

			$arr_list_slug_taxonomy[] = $taxonomy['slug'];
			$taxonomys = el_get_taxonomy( $taxonomy['slug'] );

			$get_taxonomy_select = get_the_terms( $post_id, $taxonomy['slug'] ) ? get_the_terms( $post_id, $taxonomy['slug'] ) : '';

			$tax_selected = [];
			if ( $get_taxonomy_select != '' ) {
				foreach ($get_taxonomy_select as $key => $value) {
					$tax_selected[] = $value->term_id;
				}
			}
			?>
			<div class="wrap_<?php echo esc_attr( $taxonomy['slug'] ); ?> el_custom_taxonomy vendor_field ">
				<label for="<?php echo esc_attr( $taxonomy['slug'] ); ?>"><?php echo esc_attr( $taxonomy['name'] ); ?>
					<?php if ( in_array( $taxonomy['slug'], $el_custom_taxonomy_required ) ): ?>
						<span class="el_req">*</span>
					<?php endif; ?>
				</label>
				<?php
				// V1 Le Hiboo - Sélection unique pour thématique et saison, multiple pour événements spéciaux
				$single_select_taxonomies = array( 'event_thematique', 'event_saison' );
				$is_single = in_array( $taxonomy['slug'], $single_select_taxonomies );
				$multiple_attr = $is_single ? '' : 'multiple="multiple"';
				?>
				<select name="<?php echo esc_attr( $taxonomy['slug'] ) ?>" id="<?php echo esc_attr( $taxonomy['slug'] ); ?>" class="selectpicker" <?php echo $multiple_attr; ?> >
					<option value="" ><?php esc_html_e( '--- Sélectionner ---', 'eventlist' ); ?></option>
				<?php foreach ( $taxonomys as $tax ) {

					if ( $get_taxonomy_select != '' ) { ?>
						<option value="<?php echo esc_attr( $tax->term_id ); ?>" <?php echo in_array($tax->term_id, $tax_selected) ? esc_attr( 'selected' ) : ''; ?> ><?php echo esc_html( $tax->name ); ?></option>
					<?php } else { ?>
						<option value="<?php echo esc_attr( $tax->term_id ); ?>" ><?php echo esc_html( $tax->name ); ?></option>
					<?php }

				} ?>
				</select>
			</div>
			<?php
		}
	} ?>
	<input type="hidden" id="el_list_slug_taxonomy" value="<?php echo esc_attr( json_encode( $arr_list_slug_taxonomy ) ); ?>">
	<input type="hidden" id="el_custom_taxonomy_required" value="<?php echo esc_attr( json_encode( $el_custom_taxonomy_required ) ); ?>">
	<input type="hidden" id="el_list_taxonomy" value="<?php echo esc_attr( json_encode( $list_taxonomy ) ); ?>" data-mess="<?php esc_attr_e( '[taxonomy_name] est requis.', 'eventlist' ); ?>">

	<div class="wrap_tag vendor_field">
		<label for="event_tag">
			<?php esc_html_e( 'Tags', 'eventlist' ); ?>
			<?php if ( apply_filters( 'el_event_tag_req', false, $args ) == true ): ?>
				<span class="el_req">*</span>
			<?php endif; ?>
		</label>
		<?php if ( $post_id != '' ) { ?>
			<input type="text" class="event_tag" id="event_tag" value="<?php echo esc_attr( implode(", ", $tags_name_selected) ); ?>" placeholder="<?php esc_html_e( 'Musique, Festival, Culture', 'eventlist' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
			<span><?php esc_html_e( '(max: 6 tags)', 'eventlist' ); ?></span>
		<?php } else { ?>
			<input type="text" class="event_tag" id="event_tag" value="" placeholder="<?php esc_html_e( 'Musique, Festival, Culture', 'eventlist' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
			<span><?php esc_html_e( '(max: 6 tags)', 'eventlist' ); ?></span>
		<?php } ?>
	</div>

</div>
