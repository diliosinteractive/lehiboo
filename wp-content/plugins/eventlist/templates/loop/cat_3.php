<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>

<div class="categories">
	<?php 
	$get_cat = get_the_terms( get_the_ID(), 'event_cat' );
	if ( !empty( $get_cat ) ) {
		foreach ( $get_cat as $v_cat ) { 
			$color_cat = get_term_meta($v_cat->term_id, '_category_color', true);
			$style = "";
			if ( $color_cat !== "" ) {
				$style = "style= 'background-color: #" . $color_cat . "'" ;
			}
			?>
			<a <?php echo esc_attr( $style ); ?> href="<?php echo esc_url(get_term_link($v_cat->term_id)) ?>"><?php echo esc_html($v_cat->name) ?></a>
			<?php
		}
	} ?>
</div>