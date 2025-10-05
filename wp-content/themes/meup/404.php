<?php get_header();  ?>

<div class="meup_404_page">
	<div class="container">
		<div class="pnf-content">
			<h2 class="second_font"><?php esc_html_e( '404', 'meup' ); ?></h2>
			<p><?php esc_html_e( 'Oops, Sorry We Can\'t Find That Page', 'meup' ); ?></p>
			<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search here', 'meup' ); ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php esc_attr_e( 'Search for:', 'meup' ); ?>" />
				<button type="submit" class="search-submit" value="<?php esc_attr_e( 'Search', 'meup' ); ?>"><i class="fa fa-search"></i></button>
			</form>									
			<a href="<?php echo esc_url( home_url('/') ); ?>" class="second_font btn-meup-default go_back"><?php esc_html_e( 'GO BACK HOME', 'meup' ); ?></a>
		</div>		
	</div>

</div>

<?php get_footer(); ?>