<?php get_header(); ?>
<?php $single_layout = apply_filters( 'meup_theme_sidebar','' ); ?>
	<div class="wrap_site <?php echo esc_attr($single_layout); ?>">
		<div id="main-content" class="main">

			<?php 
			if ( have_posts() ) : while ( have_posts() ) : the_post();

				get_template_part( 'content/post', 'default' );

			    if ( comments_open() || get_comments_number() ) {
			    	comments_template();
			    }
				
			endwhile; else :
			    get_template_part( 'content/content', 'none' );
			endif;

			 ?>
			
		</div> <!-- #main-content -->
		<?php get_sidebar(); ?>
	</div> <!-- .wrap_site -->

<?php get_footer(); ?>