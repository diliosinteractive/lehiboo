<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
$tags = get_the_terms( get_the_ID(), 'event_tag' );
?>
<?php if ( !empty ( $tags ) &&  is_array( $tags ) ) : ?>
<div class="event-tag event_section_white">
	<h3 class="tag-single-event second_font heading"><?php esc_html_e("Tags", "eventlist") ?></h3>

	<div class="wp-link-tag">
		<?php
		foreach ($tags as $tag) {
			?>
			<a href="<?php echo esc_url(get_term_link($tag->term_id)) ?>"><?php echo esc_html($tag->name) ?></a>
			<?php
		}
		?>
	</div>

</div>
<?php endif ?>