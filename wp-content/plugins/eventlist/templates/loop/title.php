<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php 
$archive_column = EL_Setting::instance()->event->get( 'archive_column', 'two-column' );
$layout_column = isset ( $_GET['layout_event'] ) ? sanitize_text_field( $_GET['layout_event'] ) : $archive_column;
 ?>
<h2 class="loop_title">
	<a class="second_font" href="<?php the_permalink(); ?> ">
		<?php 
		if( $layout_column == 'two-column' ){
			echo esc_html( sub_string_word( get_the_title(), apply_filters( 'el_title_count_two', 120 ) ) );
		}elseif( $layout_column == 'three-column' ){
			echo esc_html( sub_string_word( get_the_title(), apply_filters( 'el_title_count_three', 120 ) ) );
		}else{
			echo esc_html( get_the_title() );
		}
		?>
	</a>
</h2>

