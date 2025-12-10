<?php if( ! defined( 'ABSPATH' ) ) exit();
	$id_cal = isset($_GET['idcal']) ? $_GET['idcal'] : '';
	get_header();
?>

	<?php

		/**
		 * Hook: el_before_main_content
		 * @hooked: el_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked el_breadcrumb - 20
		 */
		remove_action( 'el_before_main_content','el_output_content_wrapper' );
		do_action( 'el_before_main_content' );
	?>

			<?php if( have_posts() ): ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php
					// V1 Le Hiboo - Vérifier si l'événement est protégé par mot de passe
					if ( post_password_required() ) {
						// Afficher le formulaire de mot de passe WordPress
						echo '<div class="el-password-protected-wrapper" style="max-width: 600px; margin: 60px auto; padding: 40px; text-align: center;">';
						echo '<h2>' . esc_html__( 'Événement protégé', 'eventlist' ) . '</h2>';
						echo '<p>' . esc_html__( 'Cet événement est protégé par un mot de passe. Veuillez entrer le mot de passe pour y accéder.', 'eventlist' ) . '</p>';
						echo get_the_password_form();
						echo '</div>';
					} elseif ( $id_cal !== '' ) {
						el_get_template_part( 'content', 'single-event-ticket' );
					} else {
						el_get_template_part( 'content', 'single-event' );
					}

					?>

				<?php endwhile; // end of the loop. ?>

			<?php endif; ?>

<?php
	/**
	 * Hook: el_after_main_content.
	 *
	 * @hooked el_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	remove_action( 'el_before_main_content','el_output_content_wrapper' );
	do_action( 'el_after_main_content' );

?>

<?php

get_footer();
