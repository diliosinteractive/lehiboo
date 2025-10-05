<header class=" ovatheme_header_default" id="ovatheme_header_default">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<nav class="navbar navbar-expand-lg px-0 py-0">
					
					<a href="<?php echo esc_url(home_url('/')); ?>" class="navbar-brand">
						<?php if( get_theme_mod( 'logo', '' ) != '' ) { ?>
							<img src="<?php  echo esc_url( get_theme_mod('logo', '') ); ?>" alt="<?php bloginfo('name');  ?>">
						<?php }else { ?> <span class="blogname"><?php bloginfo('name');  ?></span><?php } ?>
					</a>

					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#header_menu" aria-controls="header_menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'meup' ); ?>">
						<i class="fas fa-bars"></i>
					</button>

					<div class="collapse navbar-collapse justify-content-end" id="header_menu">

						<?php
						wp_nav_menu( array(
							'menu'              => '',
							'theme_location'    => 'primary',
							'depth'             => 3,
							'container'         => '',
							'container_class'   => '',
							'container_id'      => '',
							'menu_class'        => 'nav navbar-nav navbar-right',
							'fallback_cb'       => 'Meup_WP_Bootstrap_NavWalker::fallback',
							'walker'            => new Meup_WP_Bootstrap_NavWalker()
						));
						?>

					</div>

				</nav>
			</div>
			
		</div>
	</div>
</header>

<div class="ovatheme_breadcrumbs ovatheme_breadcrumbs_default">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php meup_breadcrumbs_header(); ?>
			</div>
		</div>	
	</div>
</div>
