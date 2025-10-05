<?php get_header(); ?>
<?php $global_layout = apply_filters( 'meup_theme_sidebar','' ); ?>
	<div class="wrap_site <?php echo esc_attr($global_layout); ?>">
		<div id="main-content" class="main">
			<?php					
			if ( have_posts() ) : while ( have_posts() ) : the_post();
			    get_template_part( 'content/content', 'page' );
			    if ( comments_open() ) comments_template( '', true );
				endwhile; else : ?>
			        <p><?php esc_html_e('Sorry, no pages matched your criteria.', 'meup'); ?></p>
			<?php endif; ?>	
		</div> <!-- #main-content -->
		<?php get_sidebar(); ?>
	</div> <!-- .wrap_site -->

<?php get_footer(); ?>