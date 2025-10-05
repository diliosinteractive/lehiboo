<?php get_header(); ?>
<?php $global_layout = apply_filters( 'meup_theme_sidebar','' ); ?>
	<div class="wrap_site <?php echo esc_attr($global_layout ); ?>">
		<div id="main-content" class="main">

			<header class="page-header">
				<h2 class="page-title">
					<?php esc_html_e('Search Results for: ','meup'); printf( '<span>%s</span>', get_search_query() ); ?>
				</h2>
			</header>

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			        <?php get_template_part( 'content/blog', 'default' ); ?>
			<?php endwhile; ?>
			    <div class="pagination-wrapper">
			        <?php meup_pagination_theme(); ?>
				</div>
			<?php else : ?>
			        <?php get_template_part( 'content/content', 'none' ); ?>
			<?php endif; ?>

			
		</div> <!-- #main-content -->
		<?php get_sidebar(); ?>
	</div> <!-- .wrap_site -->

<?php get_footer(); ?>