<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
$cate = get_the_terms( get_the_ID(), 'event_cat' );
if ( !empty ( $cate ) && is_array( $cate ) ) {
	?>
	<span class="event_meta_cat">
		<!-- category -->
		<?php
		foreach ($cate as $cat) {
			$color_cate = get_term_meta($cat->term_id, '_category_color', true);
			$style = "";
			if ( $color_cate !== "" ) {
				$style = "style= 'background-color: #" . $color_cate . "'" ;
			}
			?>
			<a <?php echo esc_attr( $style ); ?> href="<?php echo esc_url(get_term_link($cat->term_id)) ?>"><?php echo esc_html($cat->name) ?></a>
			<?php
		}
		?>
	</span>
	<?php
}
?>