<?php if( ! defined( 'ABSPATH' ) ) exit();

global $event;

$id = get_the_ID();

$taxonomies 	= array();
$list_taxonomy 	= EL_Post_Types::register_taxonomies_customize();

if ( !empty( $list_taxonomy ) && is_array( $list_taxonomy ) ) {
	foreach( $list_taxonomy as $taxonomy ) {
		$slug 	= $taxonomy['slug'];
		$terms 	= get_the_terms( $id, $slug );
		
		if ( !empty( $terms ) && is_array( $terms ) ) {
			foreach( $terms as $term ) {
				$arr_term = array(
					'term_id' 	=> $term->term_id,
					'name' 		=> $term->name,
					'slug' 		=> $term->slug,
					'taxonomy' 	=> $slug
				);
				array_push( $taxonomies, $arr_term );
			}
		}
	}
}

?>

<?php if ( !empty ( $taxonomies ) &&  is_array( $taxonomies ) ) : ?>
	<div class="event-taxonomy event_section_white">
		<h3 class="taxonomy-single-event second_font heading"><?php esc_html_e("Taxonomies", "eventlist") ?></h3>
		<div class="wp-link-taxonomy">
			<?php foreach ( $taxonomies as $tax ):
				$link = get_term_link( $tax['term_id'], $tax['taxonomy'] );
				if ( is_object ( $link ) ) {
					continue;
				}

			?>
				<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $tax['name'] ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>