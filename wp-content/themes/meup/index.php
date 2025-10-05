<?php get_header(); ?>
<?php $blog_layout = apply_filters( 'meup_theme_sidebar','' ); 
$blog_template = apply_filters( 'meup_blog_template', '' );
?>
<div class="wrap_site <?php echo esc_attr($blog_layout); ?>">
	<div id="main-content" class="main">
		<?php if ( have_posts() ) : ?>
			<div class="<?php echo esc_attr($blog_template); ?>">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php switch ($blog_template) {
						case 'blog_v2':
						get_template_part( 'content/blog', 'version-2' );
						break;

						default:
						get_template_part( 'content/blog', 'default' );
						break;
					}?>
				<?php endwhile; ?>
			</div>
			<?php else : ?>
				<?php get_template_part( 'content/content', 'none' ); ?>
			<?php endif;  wp_reset_postdata(); ?>

			<div class="pagination-wrapper">
				<?php meup_pagination_theme(); ?>
			</div>

		</div> <!-- #main-content -->
		<?php get_sidebar(); ?>
	</div> <!-- .wrap_site -->

	<?php get_footer(); ?>