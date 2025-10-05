<?php if( ! defined( 'ABSPATH' ) ) exit(); 

$show_more_desc = EL()->options->event->get('show_more_desc', 'yes');

if ( $show_more_desc == 'yes' ) {
	$height_desc = EL()->options->event->get('height_description_show', '580');
}

$content = get_the_content();

if ( ! empty( $content ) ) : ?>
	<div class="event_desc event_section_white">
		<h2 class="heading desc-event second_font"><?php esc_html_e("Description", "eventlist") ?></h2>

		<div class="wrap_content" data-height="<?php echo esc_attr($height_desc); ?>">
		<?php endif; ?>
		
		<?php the_content(); ?>

		<?php if ( ! empty( $content ) ) : ?>
			<?php if ( $show_more_desc == 'yes' ) { ?>
				<div class="el_show_more_desc">
					<a href="#" class="btn_showmore"><i class="fas fa-angle-down"></i></a>
				</div>
			<?php } ?>
			
		</div>
		
	</div>
<?php endif; ?>
