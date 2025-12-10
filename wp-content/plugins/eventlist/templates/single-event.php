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
						// Afficher le formulaire de mot de passe stylisé
						?>
						<div class="el-password-protected-wrapper">
							<div class="el-password-card">
								<div class="el-password-icon">
									<img src="<?php echo esc_url( EL_PLUGIN_URI . 'assets/img/unknow_user.png' ); ?>" alt="Le Hiboo" />
								</div>
								<h2 class="el-password-title"><?php esc_html_e( 'Événement privé', 'eventlist' ); ?></h2>
								<p class="el-password-desc"><?php esc_html_e( 'Cet événement est réservé à un groupe privé. Entrez le mot de passe fourni par l\'organisateur pour accéder au contenu.', 'eventlist' ); ?></p>

								<div class="el-password-form-wrapper">
									<?php echo get_the_password_form(); ?>
								</div>

								<div class="el-password-footer">
									<p><?php esc_html_e( 'Vous n\'avez pas le mot de passe ?', 'eventlist' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour à l\'accueil', 'eventlist' ); ?></a></p>
								</div>
							</div>
						</div>

						<style>
						.el-password-protected-wrapper {
							min-height: 70vh;
							display: flex;
							align-items: center;
							justify-content: center;
							padding: 40px 20px;
							background: linear-gradient(135deg, #faf5f0 0%, #f5ebe0 100%);
						}

						.el-password-card {
							background: #fff;
							border-radius: 24px;
							padding: 50px 40px;
							max-width: 480px;
							width: 100%;
							text-align: center;
							box-shadow: 0 10px 40px rgba(255, 96, 31, 0.08), 0 2px 10px rgba(0, 0, 0, 0.04);
						}

						.el-password-icon {
							width: 100px;
							height: 100px;
							margin: 0 auto 24px;
						}

						.el-password-icon img {
							width: 100%;
							height: 100%;
							object-fit: cover;
							border-radius: 50%;
						}

						.el-password-title {
							font-size: 28px;
							font-weight: 700;
							color: #1e293b;
							margin: 0 0 12px;
						}

						.el-password-desc {
							font-size: 15px;
							color: #64748b;
							line-height: 1.6;
							margin: 0 0 32px;
						}

						.el-password-form-wrapper {
							margin-bottom: 28px;
						}

						.el-password-form-wrapper .post-password-form {
							display: flex;
							flex-direction: column;
							gap: 16px;
						}

						.el-password-form-wrapper .post-password-form p {
							margin: 0;
							display: flex;
							flex-direction: column;
							gap: 16px;
						}

						.el-password-form-wrapper .post-password-form label {
							display: flex;
							flex-direction: column;
							gap: 8px;
							text-align: left;
							font-size: 14px;
							font-weight: 600;
							color: #334155;
						}

						.el-password-form-wrapper .post-password-form input[type="password"] {
							width: 100%;
							padding: 16px 20px;
							border: 2px solid #e2e8f0;
							border-radius: 12px;
							font-size: 16px;
							transition: all 0.2s;
							background: #fff;
						}

						.el-password-form-wrapper .post-password-form input[type="password"]:focus {
							outline: none;
							border-color: #FF601F;
							box-shadow: 0 0 0 4px rgba(255, 96, 31, 0.1);
						}

						.el-password-form-wrapper .post-password-form input[type="submit"] {
							width: 100%;
							background: #FF601F;
							color: #fff;
							border: none;
							border-radius: 12px;
							font-size: 16px;
							font-weight: 600;
							cursor: pointer;
							transition: all 0.3s;
						}

						.el-password-form-wrapper .post-password-form input[type="submit"]:hover {
							background: #e5561c;
							transform: translateY(-2px);
							box-shadow: 0 8px 20px rgba(255, 96, 31, 0.25);
						}

						.el-password-footer {
							padding-top: 24px;
							border-top: 1px solid #f1f5f9;
						}

						.el-password-footer p {
							margin: 0;
							font-size: 14px;
							color: #64748b;
						}

						.el-password-footer a {
							color: #FF601F;
							text-decoration: none;
							font-weight: 600;
						}

						.el-password-footer a:hover {
							text-decoration: underline;
						}

						@media (max-width: 520px) {
							.el-password-card {
								padding: 40px 25px;
							}

							.el-password-title {
								font-size: 24px;
							}

							.el-password-icon {
								width: 80px;
								height: 80px;
							}
						}
						</style>
						<?php
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
