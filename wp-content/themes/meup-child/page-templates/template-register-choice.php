<?php
/**
 * Template Name: Choix Type d'Inscription
 * Description: Page permettant de choisir entre inscription Utilisateur ou Partenaire
 * @version 1.1.0
 */

// Rediriger si déjà connecté
if ( is_user_logged_in() ) {
	wp_redirect( home_url() );
	exit;
}

// Détecter le type d'inscription demandé
$registration_type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';

get_header();

// Si un type est sélectionné, afficher le formulaire correspondant
if ( $registration_type === 'customer' ) {
	// Formulaire Utilisateur
	get_template_part( 'templates/register', 'customer' );
	get_footer();
	return;
} elseif ( $registration_type === 'vendor' ) {
	// Formulaire Partenaire
	get_template_part( 'templates/register', 'vendor' );
	get_footer();
	return;
}

// Sinon, afficher la page de choix
?>

<div class="lehiboo_register_choice_wrapper">
	<div class="container">

		<!-- En-tête -->
		<div class="register_choice_header">
			<h1 class="register_choice_title">Quel type de compte souhaitez-vous créer ?</h1>
			<p class="register_choice_subtitle">Choisissez le type de compte qui correspond à vos besoins</p>
		</div>

		<!-- Cartes de sélection -->
		<div class="register_choice_cards">

			<!-- Carte Organisateur -->
			<div class="register_choice_card vendor_card" data-type="vendor">
				<div class="card_image_wrapper">
					<img
						src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/register-vendor.jpg"
						alt="Je suis un Organisateur d'activités"
						class="card_image"
					>
					<div class="card_overlay"></div>
				</div>

				<div class="card_content">
					<div class="card_icon">
						<i class="fas fa-calendar-alt"></i>
					</div>
					<h2 class="card_title">Je suis un Organisateur d'activités</h2>
					<p class="card_description">
						Créez et gérez vos événements, ateliers et activités.
						Touchez une large audience et développez votre activité.
					</p>

					<ul class="card_features">
						<li><i class="fas fa-check"></i> Publiez vos événements</li>
						<li><i class="fas fa-check"></i> Gérez vos réservations</li>
						<li><i class="fas fa-check"></i> Statistiques détaillées</li>
						<li><i class="fas fa-check"></i> Messagerie intégrée</li>
					</ul>

					<a href="<?php echo esc_url( add_query_arg( 'type', 'vendor', get_permalink() ) ); ?>" class="card_button vendor_button">
						<span class="button_text">S'inscrire en tant qu'Organisateur</span>
						<i class="fas fa-arrow-right"></i>
					</a>

					<p class="card_note">
						<i class="fas fa-info-circle"></i>
						Votre compte sera vérifié avant activation
					</p>
				</div>
			</div>

			<!-- Carte Utilisateur -->
			<div class="register_choice_card customer_card" data-type="customer">
				<div class="card_image_wrapper">
					<img
						src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/register-customer.jpg"
						alt="Je suis un Utilisateur qui recherche des activités"
						class="card_image"
					>
					<div class="card_overlay"></div>
				</div>

				<div class="card_content">
					<div class="card_icon">
						<i class="fas fa-search"></i>
					</div>
					<h2 class="card_title">Je suis un Utilisateur qui recherche des activités</h2>
					<p class="card_description">
						Découvrez et réservez des activités passionnantes près de chez vous.
						Profitez d'expériences uniques en famille ou entre amis.
					</p>

					<ul class="card_features">
						<li><i class="fas fa-check"></i> Réservez facilement</li>
						<li><i class="fas fa-check"></i> Sauvegardez vos favoris</li>
						<li><i class="fas fa-check"></i> Suivez vos réservations</li>
						<li><i class="fas fa-check"></i> Laissez des avis</li>
					</ul>

					<a href="<?php echo esc_url( add_query_arg( 'type', 'customer', get_permalink() ) ); ?>" class="card_button customer_button">
						<span class="button_text">S'inscrire en tant qu'Utilisateur</span>
						<i class="fas fa-arrow-right"></i>
					</a>

					<p class="card_note">
						<i class="fas fa-bolt"></i>
						Accès immédiat après vérification email
					</p>
				</div>
			</div>

		</div>

		<!-- Footer de la page -->
		<div class="register_choice_footer">
			<p>
				Vous avez déjà un compte ?
				<a href="<?php echo esc_url( home_url( '/member-account/' ) ); ?>" class="login_link">
					Se connecter
				</a>
			</p>
		</div>

	</div>
</div>

<?php
get_footer();
