<?php
/**
 * Setup meup Child Theme's textdomain.
 *
 * Declare textdomain for this child theme.
 * Translations can be filed in the /languages/ directory.
 */
function meup_child_theme_setup() {
	load_child_theme_textdomain( 'meup-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'meup_child_theme_setup' );


// Add Code is here.

// Add Parent Style
add_action( 'wp_enqueue_scripts', 'meup_child_scripts', 100 );
function meup_child_scripts() {
    wp_enqueue_style( 'meup-parent-style', get_template_directory_uri(). '/style.css' );
    wp_enqueue_style( 'meup-airbnb-style', get_stylesheet_directory_uri() . '/airbnb-style.css', array('meup-parent-style'), '1.0.2' );

    // Toast Notification System - Chargé globalement
    wp_enqueue_style( 'toast-notification', get_stylesheet_directory_uri() . '/assets/css/toast-notification.css', array(), '1.0.0' );
    wp_enqueue_script( 'toast-notification', get_stylesheet_directory_uri() . '/assets/js/toast-notification.js', array('jquery'), '1.0.0', true );

    // Styles Airbnb pour Single Event
    if( is_singular('event') ) {
        wp_enqueue_style( 'single-event-airbnb', get_stylesheet_directory_uri() . '/single-event-airbnb.css', array('meup-parent-style'), '3.3.1' );
        wp_enqueue_script( 'single-event-airbnb', get_stylesheet_directory_uri() . '/assets/js/single-event-airbnb.js', array('jquery', 'toast-notification'), '3.3.1', true );

        // Cloudflare Turnstile CAPTCHA
        wp_enqueue_script( 'cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );

        // Localiser le script pour AJAX
        wp_localize_script( 'single-event-airbnb', 'el_ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'el_ajax_nonce' ),
            'turnstile_sitekey' => '0x4AAAAAAB75T9T-6xfs5mqd' // À remplacer par votre clé
        ));
    }

    // Styles pour la page Messages Vendor
    if( is_page() ) {
        global $post;
        if( $post && has_shortcode( $post->post_content, 'el_member_account' ) ) {
            wp_enqueue_style( 'vendor-messages', get_stylesheet_directory_uri() . '/vendor-messages.css', array('meup-parent-style'), '3.4.0' );
        }
    }

    // V1 Le Hiboo - Modern Author Profile JS (page auteur)
    if( is_author() ) {
        // Enregistrer le script d'abord
        wp_register_script( 'author-profile-modern', plugins_url( 'eventlist/assets/js/frontend/author-profile-modern.js' ), array('jquery'), '1.0.1', true );

        // Localiser APRÈS l'enregistrement
        wp_localize_script( 'author-profile-modern', 'el_ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'el_ajax_nonce' )
        ));

        // Enqueue le script
        wp_enqueue_script( 'author-profile-modern' );

        // Cloudflare Turnstile CAPTCHA pour formulaire de contact
        wp_enqueue_script( 'cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );
    }

    // V1 Le Hiboo - Page de choix d'inscription (Utilisateur/Partenaire)
    if ( is_page_template( 'page-templates/template-register-choice.php' ) ) {
        wp_enqueue_style( 'lehiboo-register-choice', get_stylesheet_directory_uri() . '/assets/css/register-choice.css', array('meup-parent-style'), '1.0.0' );

        // Styles et scripts pour le formulaire utilisateur
        wp_enqueue_style( 'lehiboo-register-customer', get_stylesheet_directory_uri() . '/assets/css/register-customer.css', array('meup-parent-style'), '1.0.0' );
        wp_enqueue_script( 'lehiboo-register-customer', get_stylesheet_directory_uri() . '/assets/js/register-customer.js', array('jquery'), '1.0.0', true );

        // Styles et scripts pour le formulaire partenaire
        wp_enqueue_style( 'lehiboo-register-vendor', get_stylesheet_directory_uri() . '/assets/css/register-vendor.css', array('lehiboo-register-customer'), '2.1.0' );
        wp_enqueue_script( 'lehiboo-register-vendor', get_stylesheet_directory_uri() . '/assets/js/register-vendor.js', array('jquery'), '2.1.0', true );

        // Cloudflare Turnstile CAPTCHA pour formulaire partenaire
        wp_enqueue_script( 'cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );

        // Enregistrer le script OTP (sera chargé dynamiquement si besoin)
        wp_register_script( 'lehiboo-otp-verification', get_stylesheet_directory_uri() . '/assets/js/otp-verification.js', array('jquery'), '1.0.1', true );

        // Localiser les scripts (même objet pour customer et vendor)
        wp_localize_script( 'lehiboo-register-customer', 'lehiboo_register_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'customer_register_nonce' ),
            'otp_script_url' => get_stylesheet_directory_uri() . '/assets/js/otp-verification.js',
            'otp_ajax_url' => admin_url( 'admin-ajax.php' ),
            'otp_nonce' => wp_create_nonce( 'lehiboo_otp_nonce' )
        ));

        wp_localize_script( 'lehiboo-register-vendor', 'lehiboo_register_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'vendor_register_nonce' )
        ));

        // Localiser le script OTP
        wp_localize_script( 'lehiboo-otp-verification', 'lehiboo_otp_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'lehiboo_otp_nonce' )
        ));
    }

    // V1 Le Hiboo - Popup Authentification (Connexion/Inscription) + OTP
    if ( ! is_user_logged_in() && ( is_singular('event') || is_author() ) ) {
        wp_enqueue_style( 'lehiboo-auth-popup', get_stylesheet_directory_uri() . '/assets/css/auth-popup.css', array(), '1.0.1' );
        wp_enqueue_script( 'lehiboo-auth-popup', get_stylesheet_directory_uri() . '/assets/js/auth-popup.js', array('jquery'), '1.0.1', true );

        // Enregistrer (mais ne pas charger) le script OTP - sera chargé dynamiquement si besoin
        wp_register_script( 'lehiboo-otp-verification', get_stylesheet_directory_uri() . '/assets/js/otp-verification.js', array('jquery'), '1.0.0', true );

        wp_localize_script( 'lehiboo-auth-popup', 'lehiboo_auth_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'lehiboo_auth_nonce' ),
            'otp_script_url' => get_stylesheet_directory_uri() . '/assets/js/otp-verification.js',
            // Données OTP incluses pour le script dynamique
            'otp_ajax_url' => admin_url( 'admin-ajax.php' ),
            'otp_nonce' => wp_create_nonce( 'lehiboo_otp_nonce' )
        ));

        // Également localiser pour le script OTP (au cas où il serait chargé directement)
        wp_localize_script( 'lehiboo-otp-verification', 'lehiboo_otp_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'lehiboo_otp_nonce' )
        ));
    }
}

/**
 * V1 Le Hiboo - Inclure le template du popup d'authentification dans le footer
 */
add_action( 'wp_footer', 'lehiboo_include_auth_popup_template' );
function lehiboo_include_auth_popup_template() {
    // Uniquement si non connecté et sur les pages event/author
    if ( ! is_user_logged_in() && ( is_singular('event') || is_author() ) ) {
        $template_path = get_stylesheet_directory() . '/templates/auth-popup.php';
        if ( file_exists( $template_path ) ) {
            include $template_path;
        }
    }
}

// ========================================
// SYSTÈME OTP PERSONNALISÉ (GRATUIT)
// ========================================
if( file_exists( get_stylesheet_directory() . '/includes/class-lehiboo-otp.php' ) ) {
	require_once get_stylesheet_directory() . '/includes/class-lehiboo-otp.php';
}

// ========================================
// EXTENSIONS METABOX EVENT (FAQ, Inclus, etc.)
// ========================================
if( file_exists( get_stylesheet_directory() . '/includes/event-metabox-extensions.php' ) ) {
	require_once get_stylesheet_directory() . '/includes/event-metabox-extensions.php';
}

// ========================================
// INTERFACE ADMIN GESTION PARTENAIRES
// ========================================
if( file_exists( get_stylesheet_directory() . '/includes/class-lehiboo-vendor-admin.php' ) ) {
	require_once get_stylesheet_directory() . '/includes/class-lehiboo-vendor-admin.php';
}

// ========================================
// RESTRICTIONS PARTENAIRES
// ========================================
if( file_exists( get_stylesheet_directory() . '/includes/class-lehiboo-vendor-restrictions.php' ) ) {
	require_once get_stylesheet_directory() . '/includes/class-lehiboo-vendor-restrictions.php';
}

// ========================================
// TAXONOMIES PERSONNALISÉES
// ========================================
// Pour ajouter une nouvelle taxonomie, ajoutez simplement une ligne add_filter ci-dessous
// Le nombre sera automatiquement détecté et synchronisé

add_filter( 'register_taxonomy_el_1', function ($params){ return array( 'slug' => 'eljob', 'name' => esc_html__( 'Job', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_2', function ($params){ return array( 'slug' => 'eltime', 'name' => esc_html__( 'Time', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_3', function ($params){ return array( 'slug' => 'elpublic', 'name' => esc_html__( 'Public', 'meup-child' ) ); } );
// Ajoutez ici d'autres taxonomies si nécessaire :
// add_filter( 'register_taxonomy_el_4', function ($params){ return array( 'slug' => 'elniveau', 'name' => esc_html__( 'Niveau', 'meup-child' ) ); } );

// Synchronisation automatique du nombre de taxonomies
add_action( 'admin_init', 'meup_child_sync_taxonomy_count' );

function meup_child_sync_taxonomy_count() {
    global $wp_filter;

    // Compter les filtres register_taxonomy_el_* déclarés
    $max_taxonomy = 0;
    if ( isset( $wp_filter ) && is_array( $wp_filter ) ) {
        foreach ( array_keys( $wp_filter ) as $tag ) {
            if ( preg_match( '/^register_taxonomy_el_(\d+)$/', $tag, $matches ) ) {
                $num = (int) $matches[1];
                if ( $num > $max_taxonomy ) {
                    $max_taxonomy = $num;
                }
            }
        }
    }

    // Mettre à jour l'option en base de données si nécessaire
    if ( $max_taxonomy > 0 ) {
        $options = get_option( 'ova_eventlist_general', array() );

        // Mettre à jour uniquement si la valeur a changé
        if ( ! isset( $options['el_total_taxonomy'] ) || $options['el_total_taxonomy'] != $max_taxonomy ) {
            $options['el_total_taxonomy'] = $max_taxonomy;
            update_option( 'ova_eventlist_general', $options );
        }
    }
}
// ========================================
// AJAX HANDLER - CONTACT ORGANISATEUR
// ========================================
add_action( 'wp_ajax_send_organizer_message', 'handle_send_organizer_message' );
add_action( 'wp_ajax_nopriv_send_organizer_message', 'handle_send_organizer_message' );

function handle_send_organizer_message() {
	// Vérifier que l'utilisateur est connecté
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Vous devez être connecté pour envoyer un message.' ) );
	}

	// Vérifier le nonce
	if ( ! isset( $_POST['contact_nonce'] ) || ! wp_verify_nonce( $_POST['contact_nonce'], 'contact_organizer_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Erreur de sécurité.' ) );
	}

	// Récupérer les données
	$name = isset( $_POST['contact_name'] ) ? sanitize_text_field( $_POST['contact_name'] ) : '';
	$email = isset( $_POST['contact_email'] ) ? sanitize_email( $_POST['contact_email'] ) : '';
	$subject = isset( $_POST['contact_subject'] ) ? sanitize_text_field( $_POST['contact_subject'] ) : '';
	$message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( $_POST['contact_message'] ) : '';
	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;

	// Debug: Log des données reçues
	error_log( 'CONTACT FORM - Event ID: ' . $event_id );

	// Vérifier que l'événement existe
	$event_post = get_post( $event_id );
	if ( ! $event_post ) {
		wp_send_json_error( array( 'message' => 'Événement introuvable (ID: ' . $event_id . ')' ) );
	}

	// Récupérer l'email de l'organisateur depuis l'événement
	$author_id = $event_post->post_author;
	error_log( 'CONTACT FORM - Author ID: ' . $author_id );
	error_log( 'CONTACT FORM - Event post_author type: ' . gettype($author_id) );
	error_log( 'CONTACT FORM - Event post_author value: ' . var_export($author_id, true) );

	// Vérifier si author_id est valide
	if ( empty( $author_id ) || $author_id == 0 ) {
		error_log( 'CONTACT FORM - ERROR: Author ID is empty or 0' );
		wp_send_json_error( array( 'message' => 'ID auteur invalide (Event ID: ' . $event_id . ', Author ID: ' . $author_id . ')' ) );
	}

	// Récupérer l'email professionnel du partenaire (priorité sur email WordPress)
	$organizer_email = get_user_meta( $author_id, 'user_professional_email', true );
	error_log( 'CONTACT FORM - Professional email from meta: ' . var_export($organizer_email, true) );

	// Fallback sur l'email WordPress si pas d'email professionnel
	if ( empty( $organizer_email ) ) {
		$organizer_email = get_the_author_meta( 'user_email', $author_id );
		error_log( 'CONTACT FORM - WordPress email fallback: ' . var_export($organizer_email, true) );
	}

	$organizer_name = get_the_author_meta( 'display_name', $author_id );

	error_log( 'CONTACT FORM - Final organizer email: ' . $organizer_email );
	error_log( 'CONTACT FORM - Final organizer name: ' . $organizer_name );

	// Validation détaillée
	$errors = array();

	if ( empty( $name ) ) {
		$errors[] = 'Nom manquant';
	}
	if ( empty( $email ) ) {
		$errors[] = 'Email manquant';
	}
	if ( empty( $subject ) ) {
		$errors[] = 'Objet manquant';
	}
	if ( empty( $message ) ) {
		$errors[] = 'Message manquant';
	}
	if ( empty( $event_id ) ) {
		$errors[] = 'Event ID manquant';
	}
	if ( empty( $organizer_email ) ) {
		$errors[] = 'Email organisateur introuvable';
	}

	if ( ! empty( $errors ) ) {
		wp_send_json_error( array(
			'message' => 'Veuillez remplir tous les champs.',
			'errors' => $errors,
			'debug' => array(
				'name' => $name,
				'email' => $email,
				'message_length' => strlen($message),
				'organizer_email' => $organizer_email
			)
		) );
	}

	if ( ! is_email( $email ) || ! is_email( $organizer_email ) ) {
		wp_send_json_error( array( 'message' => 'Adresse email invalide.' ) );
	}

	// Vérifier le CAPTCHA Turnstile
	$turnstile_response = isset( $_POST['cf-turnstile-response'] ) ? $_POST['cf-turnstile-response'] : '';

	if ( empty( $turnstile_response ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez valider le CAPTCHA.' ) );
	}

	// Valider le token Turnstile auprès de Cloudflare
	$secret_key = '0x4AAAAAAB75T-X7AoX9nIt-M-0G2ndG4zU'; // À remplacer par votre clé secrète
	$verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	$response_verify = wp_remote_post( $verify_url, array(
		'body' => array(
			'secret' => $secret_key,
			'response' => $turnstile_response,
			'remoteip' => $_SERVER['REMOTE_ADDR']
		)
	));

	if ( is_wp_error( $response_verify ) ) {
		wp_send_json_error( array( 'message' => 'Erreur de validation du CAPTCHA.' ) );
	}

	$verify_data = json_decode( wp_remote_retrieve_body( $response_verify ), true );

	if ( ! $verify_data['success'] ) {
		wp_send_json_error( array( 'message' => 'CAPTCHA invalide. Veuillez réessayer.' ) );
	}

	// Préparer l'email
	$event_title = get_the_title( $event_id );
	$event_link = get_permalink( $event_id );

	$subject = sprintf( '[%s] Message concernant: %s', get_bloginfo('name'), $event_title );

	$body = "Vous avez reçu un message concernant votre événement:\n\n";
	$body .= "Événement: {$event_title}\n";
	$body .= "Lien: {$event_link}\n\n";
	$body .= "De: {$name} ({$email})\n\n";
	$body .= "Message:\n{$message}\n\n";
	$body .= "---\n";
	$body .= "Cet email a été envoyé via le formulaire de contact de " . get_bloginfo('name');

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>',
		'Reply-To: ' . $name . ' <' . $email . '>'
	);

	// Sauvegarder le message dans la base de données
	$message_id = save_organizer_message( $event_id, $name, $email, $subject, $message, $author_id );

	// Envoyer l'email
	$sent = wp_mail( $organizer_email, $subject, $body, $headers );

	if ( $sent && $message_id ) {
		// Marquer le message comme envoyé
		update_post_meta( $message_id, '_email_sent', 1 );

		wp_send_json_success( array(
			'message' => 'Votre message a été envoyé avec succès!',
			'message_id' => $message_id
		) );
	} elseif ( $message_id && ! $sent ) {
		// Message sauvegardé mais email non envoyé
		update_post_meta( $message_id, '_email_sent', 0 );
		update_post_meta( $message_id, '_email_error', 'Erreur lors de l\'envoi de l\'email' );

		wp_send_json_error( array( 'message' => 'Message sauvegardé mais l\'email n\'a pas pu être envoyé.' ) );
	} else {
		wp_send_json_error( array( 'message' => 'Erreur lors de l\'envoi du message. Veuillez réessayer.' ) );
	}
}

// ========================================
// CUSTOM POST TYPE - MESSAGES ORGANISATEURS
// ========================================
add_action( 'init', 'register_organizer_messages_cpt' );

function register_organizer_messages_cpt() {
	$labels = array(
		'name'                  => 'Messages',
		'singular_name'         => 'Message',
		'menu_name'             => 'Messages',
		'name_admin_bar'        => 'Message',
		'add_new'               => 'Ajouter',
		'add_new_item'          => 'Ajouter un message',
		'new_item'              => 'Nouveau message',
		'edit_item'             => 'Modifier le message',
		'view_item'             => 'Voir le message',
		'all_items'             => 'Tous les messages',
		'search_items'          => 'Rechercher des messages',
		'not_found'             => 'Aucun message trouvé',
		'not_found_in_trash'    => 'Aucun message trouvé dans la corbeille'
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false, // On l'affichera dans le menu EventList
		'capability_type'     => 'post',
		'capabilities'        => array(
			'create_posts' => 'do_not_allow', // Empêche la création manuelle
		),
		'map_meta_cap'        => true,
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => null,
		'supports'            => array( 'title', 'editor', 'author' ),
	);

	register_post_type( 'organizer_message', $args );
}

// Sauvegarder le message dans la base de données
function save_organizer_message( $event_id, $from_name, $from_email, $subject, $message_content, $organizer_id ) {
	// Si event_id = 0, c'est un message depuis le profil organisateur
	if ( $event_id > 0 ) {
		$event_title = get_the_title( $event_id );
		$post_title = sprintf( '%s - Message de %s', $event_title, $from_name );
	} else {
		// Message depuis le profil organisateur
		$org_name = get_user_meta( $organizer_id, 'org_display_name', true );
		if ( empty( $org_name ) ) {
			$user_data = get_userdata( $organizer_id );
			$org_name = $user_data ? $user_data->display_name : 'Organisateur';
		}
		$post_title = sprintf( 'Profil %s - Message de %s', $org_name, $from_name );
	}

	// Créer le post
	$message_id = wp_insert_post( array(
		'post_title'    => $post_title,
		'post_content'  => $message_content,
		'post_status'   => 'private',
		'post_type'     => 'organizer_message',
		'post_author'   => $organizer_id,
	));

	if ( ! is_wp_error( $message_id ) ) {
		// Ajouter les métadonnées
		update_post_meta( $message_id, '_from_name', sanitize_text_field( $from_name ) );
		update_post_meta( $message_id, '_from_email', sanitize_email( $from_email ) );
		update_post_meta( $message_id, '_subject', sanitize_text_field( $subject ) );
		update_post_meta( $message_id, '_event_id', intval( $event_id ) ); // 0 si message profil
		update_post_meta( $message_id, '_sent_date', current_time( 'mysql' ) );
		update_post_meta( $message_id, '_is_read', 0 );

		return $message_id;
	}

	return false;
}

// ========================================
// FILTER - EVENTLIST TEMPLATE OVERRIDE POUR CHILD THEME
// ========================================
add_filter( 'el_locate_template', 'meup_child_locate_vendor_template', 10, 4 );
function meup_child_locate_vendor_template( $template, $template_name, $template_path, $default_path ) {
	// Chemin dans le child theme
	$child_theme_template = get_stylesheet_directory() . '/' . trailingslashit( $template_path ) . $template_name;

	// Si le template existe dans le child theme, l'utiliser en priorité
	if ( file_exists( $child_theme_template ) ) {
		error_log( 'EVENTLIST TEMPLATE OVERRIDE: Using child theme template - ' . $child_theme_template );
		return $child_theme_template;
	}

	// Sinon retourner le template par défaut
	return $template;
}

// ========================================
// AJAX - MARQUER MESSAGE COMME LU
// ========================================
add_action( 'wp_ajax_mark_message_read', 'meup_ajax_mark_message_read' );
function meup_ajax_mark_message_read() {
	// Vérifier le nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mark_message_read_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Erreur de sécurité.' ) );
	}

	$current_user_id = get_current_user_id();
	$message_id = isset( $_POST['message_id'] ) ? intval( $_POST['message_id'] ) : 0;

	if ( ! $message_id ) {
		wp_send_json_error( array( 'message' => 'ID message manquant.' ) );
	}

	$message = get_post( $message_id );

	// Vérifier que le message appartient à l'utilisateur
	if ( ! $message || $message->post_author != $current_user_id ) {
		wp_send_json_error( array( 'message' => 'Accès refusé.' ) );
	}

	// Marquer comme lu
	update_post_meta( $message_id, '_is_read', 1 );

	wp_send_json_success( array(
		'message' => 'Message marqué comme lu.',
		'message_id' => $message_id
	) );
}

// ========================================
// AJAX - RÉPONDRE À UN MESSAGE
// ========================================
add_action( 'wp_ajax_reply_to_message', 'meup_ajax_reply_to_message' );
function meup_ajax_reply_to_message() {
	// Vérifier le nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'reply_message_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Erreur de sécurité.' ) );
	}

	$current_user_id = get_current_user_id();

	// Récupérer les données
	$to_email = isset( $_POST['to_email'] ) ? sanitize_email( $_POST['to_email'] ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
	$message_id = isset( $_POST['message_id'] ) ? intval( $_POST['message_id'] ) : 0;

	// Validation
	if ( empty( $to_email ) || ! is_email( $to_email ) ) {
		wp_send_json_error( array( 'message' => 'Email destinataire invalide.' ) );
	}

	if ( empty( $subject ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez saisir un objet.' ) );
	}

	if ( empty( $message ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez saisir un message.' ) );
	}

	// Vérifier que le message original appartient à l'utilisateur
	if ( $message_id ) {
		$original_message = get_post( $message_id );
		if ( ! $original_message || $original_message->post_author != $current_user_id ) {
			wp_send_json_error( array( 'message' => 'Accès refusé.' ) );
		}
	}

	// Récupérer les infos de l'utilisateur connecté (le partenaire)
	$current_user = wp_get_current_user();
	$from_name = $current_user->display_name;
	$from_email = $current_user->user_email;

	// Vérifier si l'utilisateur a un email professionnel
	$professional_email = get_user_meta( $current_user_id, 'user_professional_email', true );
	if ( ! empty( $professional_email ) && is_email( $professional_email ) ) {
		$from_email = $professional_email;
	}

	// Préparer l'email
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: ' . $from_name . ' <' . $from_email . '>',
		'Reply-To: ' . $from_name . ' <' . $from_email . '>'
	);

	$email_body = $message . "\n\n";
	$email_body .= "---\n";
	$email_body .= "Cet email a été envoyé depuis " . get_bloginfo('name') . " par " . $from_name;

	// Envoyer l'email
	$sent = wp_mail( $to_email, $subject, $email_body, $headers );

	if ( $sent ) {
		// Enregistrer la réponse dans l'historique
		if ( $message_id ) {
			// Marquer le message original comme lu
			update_post_meta( $message_id, '_is_read', 1 );

			// Récupérer l'historique des réponses
			$replies = get_post_meta( $message_id, '_replies', true );
			if ( ! is_array( $replies ) ) {
				$replies = array();
			}

			// Ajouter la nouvelle réponse
			$replies[] = array(
				'date' => current_time( 'mysql' ),
				'from_name' => $from_name,
				'from_email' => $from_email,
				'subject' => $subject,
				'message' => $message,
				'to_email' => $to_email
			);

			// Sauvegarder l'historique
			update_post_meta( $message_id, '_replies', $replies );
		}

		wp_send_json_success( array(
			'message' => 'Votre réponse a été envoyée avec succès!'
		) );
	} else {
		wp_send_json_error( array(
			'message' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.'
		) );
	}
}

// ========================================
// V1 LE HIBOO - MISE À JOUR SLUG PARTENAIRES
// ========================================

/**
 * Fonction utilitaire pour mettre à jour le user_nicename (slug URL)
 * de tous les partenaires existants basé sur leur org_display_name
 *
 * À exécuter une seule fois via wp-admin/admin.php?action=update_vendor_slugs
 */
function lehiboo_update_vendor_slugs() {
	// Vérifier les permissions admin
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès non autorisé' );
	}

	global $wpdb;

	// Récupérer tous les utilisateurs avec le rôle el_event_vendor
	$vendors = get_users( array(
		'role' => 'el_event_vendor',
		'fields' => 'ID'
	) );

	$updated = 0;
	$skipped = 0;
	$errors = array();

	foreach ( $vendors as $user_id ) {
		$org_display_name = get_user_meta( $user_id, 'org_display_name', true );

		if ( empty( $org_display_name ) ) {
			// Fallback sur org_name si org_display_name est vide
			$org_display_name = get_user_meta( $user_id, 'org_name', true );
		}

		if ( empty( $org_display_name ) ) {
			$skipped++;
			continue;
		}

		// Créer le slug depuis org_display_name
		$new_nicename = sanitize_title( $org_display_name );

		// Vérifier que le slug est unique
		$existing_user = get_user_by( 'slug', $new_nicename );
		if ( $existing_user && $existing_user->ID !== $user_id ) {
			// Si le slug existe déjà, ajouter un suffixe numérique
			$i = 1;
			$base_nicename = $new_nicename;
			while ( get_user_by( 'slug', $new_nicename ) && get_user_by( 'slug', $new_nicename )->ID !== $user_id ) {
				$new_nicename = $base_nicename . '-' . $i;
				$i++;
			}
		}

		// Mettre à jour le user_nicename dans wp_users
		$result = $wpdb->update(
			$wpdb->users,
			array( 'user_nicename' => $new_nicename ),
			array( 'ID' => $user_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( $result === false ) {
			$errors[] = "Erreur pour l'utilisateur ID $user_id";
		} else {
			// Nettoyer le cache utilisateur
			clean_user_cache( $user_id );
			$updated++;
		}
	}

	// Afficher le résultat
	echo '<div style="padding: 20px; font-family: sans-serif;">';
	echo '<h2>✅ Mise à jour des slugs partenaires terminée</h2>';
	echo '<p><strong>Mis à jour :</strong> ' . $updated . ' partenaires</p>';
	echo '<p><strong>Ignorés :</strong> ' . $skipped . ' partenaires (pas de nom d\'organisation)</p>';

	if ( ! empty( $errors ) ) {
		echo '<p><strong>Erreurs :</strong></p>';
		echo '<ul>';
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul>';
	}

	echo '<p><a href="' . admin_url() . '">← Retour au tableau de bord</a></p>';
	echo '</div>';
}

// Ajouter une action admin pour exécuter la fonction
add_action( 'admin_action_update_vendor_slugs', 'lehiboo_update_vendor_slugs' );

// ========================================
// AJAX HANDLER - Contact Author Form
// ========================================
add_action( 'wp_ajax_send_author_message', 'lehiboo_send_author_message' );
add_action( 'wp_ajax_nopriv_send_author_message', 'lehiboo_send_author_message' );

function lehiboo_send_author_message() {
	// Vérifier que l'utilisateur est connecté
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Vous devez être connecté pour envoyer un message.' ) );
	}

	// Vérifier le nonce
	check_ajax_referer( 'contact_author_nonce', 'contact_nonce' );

	// Récupérer les données du formulaire
	$author_id = isset( $_POST['author_id'] ) ? intval( $_POST['author_id'] ) : 0;
	$name = isset( $_POST['name_customer'] ) ? sanitize_text_field( $_POST['name_customer'] ) : '';
	$email = isset( $_POST['email_customer'] ) ? sanitize_email( $_POST['email_customer'] ) : '';
	$phone = isset( $_POST['phone_customer'] ) ? sanitize_text_field( $_POST['phone_customer'] ) : '';
	$subject = isset( $_POST['subject_customer'] ) ? sanitize_text_field( $_POST['subject_customer'] ) : '';
	$message = isset( $_POST['content'] ) ? sanitize_textarea_field( $_POST['content'] ) : '';

	// Validation
	if ( empty( $name ) || empty( $email ) || empty( $subject ) || empty( $message ) || empty( $author_id ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez remplir tous les champs requis.' ) );
	}

	// Récupérer l'email de l'auteur
	$author = get_userdata( $author_id );
	if ( ! $author ) {
		wp_send_json_error( array( 'message' => 'Organisateur introuvable.' ) );
	}

	$author_email = $author->user_email;

	if ( ! is_email( $email ) || ! is_email( $author_email ) ) {
		wp_send_json_error( array( 'message' => 'Adresse email invalide.' ) );
	}

	// Vérifier le CAPTCHA Turnstile
	$turnstile_response = isset( $_POST['cf-turnstile-response'] ) ? $_POST['cf-turnstile-response'] : '';

	if ( empty( $turnstile_response ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez valider le CAPTCHA.' ) );
	}

	// Valider le token Turnstile auprès de Cloudflare
	$secret_key = '0x4AAAAAAB75T-X7AoX9nIt-M-0G2ndG4zU';
	$verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	$response_verify = wp_remote_post( $verify_url, array(
		'body' => array(
			'secret' => $secret_key,
			'response' => $turnstile_response,
			'remoteip' => $_SERVER['REMOTE_ADDR']
		)
	));

	if ( is_wp_error( $response_verify ) ) {
		wp_send_json_error( array( 'message' => 'Erreur de validation du CAPTCHA.' ) );
	}

	$verify_data = json_decode( wp_remote_retrieve_body( $response_verify ), true );

	if ( ! $verify_data['success'] ) {
		wp_send_json_error( array( 'message' => 'CAPTCHA invalide. Veuillez réessayer.' ) );
	}

	// Préparer l'email
	$author_name = get_user_meta( $author_id, 'org_display_name', true ) ?: $author->display_name;

	$email_subject = sprintf( '[%s] Nouveau message de contact: %s', get_bloginfo('name'), $subject );

	$email_body = "Vous avez reçu un nouveau message de contact:\n\n";
	$email_body .= "De: {$name}\n";
	$email_body .= "Email: {$email}\n";
	$email_body .= "Téléphone: {$phone}\n";
	$email_body .= "Objet: {$subject}\n\n";
	$email_body .= "Message:\n{$message}\n\n";
	$email_body .= "---\n";
	$email_body .= "Ce message a été envoyé depuis votre profil organisateur sur " . get_bloginfo('name') . "\n";
	$email_body .= "Profil: " . get_author_posts_url( $author_id );

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>'
	);

	// Sauvegarder le message dans la base de données
	// Note: Pour un message depuis le profil (pas lié à un événement), on utilise 0 comme event_id
	$message_id = save_organizer_message( 0, $name, $email, $subject, $message, $author_id );

	if ( ! $message_id ) {
		wp_send_json_error( array( 'message' => 'Erreur lors de la sauvegarde du message.' ) );
	}

	// Ajouter le téléphone dans les métadonnées
	if ( ! empty( $phone ) ) {
		update_post_meta( $message_id, '_phone', sanitize_text_field( $phone ) );
	}

	// Envoyer l'email
	$sent = wp_mail( $author_email, $email_subject, $email_body, $headers );

	if ( $sent ) {
		// Marquer l'email comme envoyé
		update_post_meta( $message_id, '_email_sent', 1 );
		wp_send_json_success( array( 'message' => 'Message envoyé avec succès !' ) );
	} else {
		// Même si l'email échoue, le message est sauvegardé dans la base
		update_post_meta( $message_id, '_email_sent', 0 );
		wp_send_json_error( array( 'message' => 'Message sauvegardé mais erreur lors de l\'envoi de l\'email.' ) );
	}
}

// ========================================
// POPUP AUTHENTIFICATION - AJAX HANDLERS
// ========================================

/**
 * Charger le template du popup authentification
 */
add_action( 'wp_ajax_load_auth_popup_template', 'lehiboo_load_auth_popup_template' );
add_action( 'wp_ajax_nopriv_load_auth_popup_template', 'lehiboo_load_auth_popup_template' );

function lehiboo_load_auth_popup_template() {
	ob_start();
	include get_stylesheet_directory() . '/templates/auth-popup.php';
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}

/**
 * AJAX - Connexion utilisateur
 */
add_action( 'wp_ajax_nopriv_lehiboo_ajax_login', 'lehiboo_handle_ajax_login' );

function lehiboo_handle_ajax_login() {
	// Vérifier le nonce
	check_ajax_referer( 'auth_login_nonce', 'login_nonce' );

	// Récupérer les données
	$email = isset( $_POST['login_email'] ) ? sanitize_email( $_POST['login_email'] ) : '';
	$password = isset( $_POST['login_password'] ) ? $_POST['login_password'] : '';
	$remember = isset( $_POST['login_remember'] ) && $_POST['login_remember'] == '1';

	// Validation
	if ( empty( $email ) || empty( $password ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez remplir tous les champs.' ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'Adresse email invalide.' ) );
	}

	// Tentative de connexion
	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		wp_send_json_error( array( 'message' => 'Email ou mot de passe incorrect.' ) );
	}

	$creds = array(
		'user_login'    => $user->user_login,
		'user_password' => $password,
		'remember'      => $remember,
	);

	$user_signon = wp_signon( $creds, is_ssl() );

	if ( is_wp_error( $user_signon ) ) {
		wp_send_json_error( array( 'message' => 'Email ou mot de passe incorrect.' ) );
	}

	wp_send_json_success( array(
		'message' => 'Connexion réussie ! Redirection en cours...',
		'user_id' => $user_signon->ID
	) );
}

/**
 * AJAX - Inscription utilisateur
 */
add_action( 'wp_ajax_nopriv_lehiboo_ajax_register', 'lehiboo_handle_ajax_register' );

function lehiboo_handle_ajax_register() {
	// Vérifier le nonce
	check_ajax_referer( 'auth_register_nonce', 'register_nonce' );

	// Récupérer les données
	$firstname = isset( $_POST['register_firstname'] ) ? sanitize_text_field( $_POST['register_firstname'] ) : '';
	$lastname = isset( $_POST['register_lastname'] ) ? sanitize_text_field( $_POST['register_lastname'] ) : '';
	$email = isset( $_POST['register_email'] ) ? sanitize_email( $_POST['register_email'] ) : '';
	$terms_accepted = isset( $_POST['register_terms'] );

	// Validation
	if ( empty( $firstname ) || empty( $lastname ) || empty( $email ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez remplir tous les champs.' ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'Adresse email invalide.' ) );
	}

	if ( ! $terms_accepted ) {
		wp_send_json_error( array( 'message' => 'Vous devez accepter les conditions d\'utilisation.' ) );
	}

	// Vérifier si l'email existe déjà
	if ( email_exists( $email ) ) {
		wp_send_json_error( array( 'message' => 'Cette adresse email est déjà utilisée.' ) );
	}

	// Générer un nom d'utilisateur unique
	$username = strtolower( $firstname . '.' . $lastname );
	$username_base = $username;
	$counter = 1;

	while ( username_exists( $username ) ) {
		$username = $username_base . $counter;
		$counter++;
	}

	// Générer un mot de passe fort aléatoire
	$password = wp_generate_password( 12, true, true );

	// Créer l'utilisateur
	$user_id = wp_create_user( $username, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => 'Erreur lors de la création du compte : ' . $user_id->get_error_message() ) );
	}

	// Mettre à jour les métadonnées utilisateur
	wp_update_user( array(
		'ID'           => $user_id,
		'first_name'   => $firstname,
		'last_name'    => $lastname,
		'display_name' => $firstname . ' ' . $lastname,
	) );

	// Assigner le rôle subscriber par défaut
	$user = new WP_User( $user_id );
	$user->set_role( 'subscriber' );

	// ========================================
	// SYSTÈME OTP GRATUIT - LE HIBOO
	// ========================================

	// Créer le code OTP
	$otp_code = LeHiboo_OTP::create_otp( $user_id, $email );

	if ( ! $otp_code ) {
		error_log( 'LeHiboo Registration: Échec création OTP pour user_id=' . $user_id . ', email=' . $email );
		// Supprimer l'utilisateur créé car l'OTP a échoué
		wp_delete_user( $user_id );
		wp_send_json_error( array(
			'message' => 'Erreur lors de la génération du code de vérification. Veuillez réessayer.',
			'debug' => array(
				'step' => 'create_otp_failed',
				'user_id' => $user_id
			)
		) );
	}

	// Envoyer l'email avec le code OTP
	$otp_sent = LeHiboo_OTP::send_otp_email( $user_id, $email, $otp_code, $firstname );

	if ( ! $otp_sent ) {
		error_log( 'LeHiboo Registration: Échec envoi email OTP pour user_id=' . $user_id );
		wp_send_json_error( array(
			'message' => 'Erreur lors de l\'envoi de l\'email de vérification. Veuillez vérifier votre adresse email.',
			'debug' => array(
				'step' => 'send_otp_email_failed',
				'user_id' => $user_id
			)
		) );
	}

	// Envoyer également l'email de bienvenue avec le mot de passe
	lehiboo_send_welcome_email( $user_id, $email, $password, $firstname );

	// Retourner succès avec OTP requis
	wp_send_json_success( array(
		'message' => 'Votre compte a été créé ! Un code de vérification a été envoyé à votre email.',
		'otp_required' => true,
		'user_id' => $user_id,
		'show_otp_form' => true
	) );
}

/**
 * Envoyer l'email de bienvenue avec le mot de passe
 */
function lehiboo_send_welcome_email( $user_id, $email, $password, $firstname ) {
	$site_name = get_bloginfo( 'name' );
	$login_url = home_url( '/member-account/' );

	$subject = sprintf( '[%s] Bienvenue ! Votre compte a été créé', $site_name );

	$message = "Bonjour {$firstname},\n\n";
	$message .= "Bienvenue sur {$site_name} !\n\n";
	$message .= "Votre compte a été créé avec succès. Voici vos identifiants de connexion :\n\n";
	$message .= "Email : {$email}\n";
	$message .= "Mot de passe : {$password}\n\n";
	$message .= "Pour vous connecter, cliquez sur le lien suivant :\n";
	$message .= "{$login_url}\n\n";
	$message .= "Nous vous recommandons de changer votre mot de passe après votre première connexion.\n\n";
	$message .= "Cordialement,\n";
	$message .= "L'équipe {$site_name}";

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	wp_mail( $email, $subject, $message, $headers );
}

/**
 * Injecter le popup dans le footer pour les utilisateurs non connectés
 * SUPPRIMÉ: Fonction dupliquée avec lehiboo_include_auth_popup_template() (ligne 92)
 * Cette fonction causait un double chargement du template popup
 */
// add_action( 'wp_footer', 'lehiboo_inject_auth_popup' );
// function lehiboo_inject_auth_popup() {
// 	if ( ! is_user_logged_in() && ( is_singular('event') || is_author() ) ) {
// 		include get_stylesheet_directory() . '/templates/auth-popup.php';
// 	}
// }

// ========================================
// AJAX HANDLER - INSCRIPTION UTILISATEUR (PAGE DÉDIÉE)
// ========================================

/**
 * AJAX - Inscription utilisateur depuis la page d'inscription
 */
add_action( 'wp_ajax_nopriv_lehiboo_customer_register', 'lehiboo_handle_customer_register' );

function lehiboo_handle_customer_register() {
	// Vérifier le nonce
	check_ajax_referer( 'customer_register_nonce', 'customer_register_nonce' );

	// Récupérer les données
	$firstname = isset( $_POST['customer_firstname'] ) ? sanitize_text_field( $_POST['customer_firstname'] ) : '';
	$lastname = isset( $_POST['customer_lastname'] ) ? sanitize_text_field( $_POST['customer_lastname'] ) : '';
	$email = isset( $_POST['customer_email'] ) ? sanitize_email( $_POST['customer_email'] ) : '';
	$password = isset( $_POST['customer_password'] ) ? $_POST['customer_password'] : '';
	$password_confirm = isset( $_POST['customer_password_confirm'] ) ? $_POST['customer_password_confirm'] : '';
	$terms_accepted = isset( $_POST['customer_terms'] );
	$newsletter = isset( $_POST['customer_newsletter'] );

	// Validation
	if ( empty( $firstname ) || empty( $lastname ) || empty( $email ) || empty( $password ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez remplir tous les champs obligatoires.' ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'Adresse email invalide.' ) );
	}

	if ( strlen( $password ) < 8 ) {
		wp_send_json_error( array( 'message' => 'Le mot de passe doit contenir au moins 8 caractères.' ) );
	}

	if ( $password !== $password_confirm ) {
		wp_send_json_error( array( 'message' => 'Les mots de passe ne correspondent pas.' ) );
	}

	if ( ! $terms_accepted ) {
		wp_send_json_error( array( 'message' => 'Vous devez accepter les Conditions Générales d\'Utilisation.' ) );
	}

	// Vérifier si l'email existe déjà
	if ( email_exists( $email ) ) {
		wp_send_json_error( array( 'message' => 'Cette adresse email est déjà utilisée.' ) );
	}

	// Générer un nom d'utilisateur unique
	$username = strtolower( $firstname . '.' . $lastname );
	$username_base = $username;
	$counter = 1;

	while ( username_exists( $username ) ) {
		$username = $username_base . $counter;
		$counter++;
	}

	// Créer l'utilisateur avec le mot de passe fourni
	$user_id = wp_create_user( $username, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => 'Erreur lors de la création du compte : ' . $user_id->get_error_message() ) );
	}

	// Mettre à jour les métadonnées utilisateur
	wp_update_user( array(
		'ID'           => $user_id,
		'first_name'   => $firstname,
		'last_name'    => $lastname,
		'display_name' => $firstname . ' ' . $lastname,
	) );

	// Assigner le rôle subscriber par défaut
	$user = new WP_User( $user_id );
	$user->set_role( 'subscriber' );

	// Sauvegarder la préférence newsletter
	if ( $newsletter ) {
		update_user_meta( $user_id, 'newsletter_optin', 1 );
	}

	// ========================================
	// SYSTÈME OTP GRATUIT - LE HIBOO
	// ========================================

	// Créer le code OTP
	$otp_code = LeHiboo_OTP::create_otp( $user_id, $email );

	if ( ! $otp_code ) {
		error_log( 'LeHiboo Customer Registration: Échec création OTP pour user_id=' . $user_id . ', email=' . $email );
		// Supprimer l'utilisateur créé car l'OTP a échoué
		wp_delete_user( $user_id );
		wp_send_json_error( array(
			'message' => 'Erreur lors de la génération du code de vérification. Veuillez réessayer.',
			'debug' => array(
				'step' => 'create_otp_failed',
				'user_id' => $user_id
			)
		) );
	}

	// Envoyer l'email avec le code OTP
	$otp_sent = LeHiboo_OTP::send_otp_email( $user_id, $email, $otp_code, $firstname );

	if ( ! $otp_sent ) {
		error_log( 'LeHiboo Customer Registration: Échec envoi email OTP pour user_id=' . $user_id );
		wp_send_json_error( array(
			'message' => 'Erreur lors de l\'envoi de l\'email de vérification. Veuillez vérifier votre adresse email.',
			'debug' => array(
				'step' => 'send_otp_email_failed',
				'user_id' => $user_id
			)
		) );
	}

	// Retourner succès avec OTP requis
	wp_send_json_success( array(
		'message' => 'Votre compte a été créé ! Un code de vérification a été envoyé à votre email.',
		'otp_required' => true,
		'user_id' => $user_id,
		'show_otp_form' => true
	) );
}

// ========================================
// AJAX HANDLER - INSCRIPTION PARTENAIRE
// ========================================

/**
 * AJAX - Inscription partenaire depuis la page d'inscription
 */
add_action( 'wp_ajax_nopriv_lehiboo_vendor_register', 'lehiboo_handle_vendor_register' );

function lehiboo_handle_vendor_register() {
	// Vérifier le nonce
	check_ajax_referer( 'vendor_register_nonce', 'vendor_register_nonce' );

	// ÉTAPE 1 : Informations personnelles
	$firstname = isset( $_POST['vendor_firstname'] ) ? sanitize_text_field( $_POST['vendor_firstname'] ) : '';
	$lastname = isset( $_POST['vendor_lastname'] ) ? sanitize_text_field( $_POST['vendor_lastname'] ) : '';
	$email = isset( $_POST['vendor_email'] ) ? sanitize_email( $_POST['vendor_email'] ) : '';
	$phone = isset( $_POST['vendor_phone'] ) ? sanitize_text_field( $_POST['vendor_phone'] ) : '';
	$password = isset( $_POST['vendor_password'] ) ? $_POST['vendor_password'] : '';

	// ÉTAPE 2 : Organisation
	$org_name = isset( $_POST['vendor_org_name'] ) ? sanitize_text_field( $_POST['vendor_org_name'] ) : '';
	$org_type = isset( $_POST['vendor_org_type'] ) ? sanitize_text_field( $_POST['vendor_org_type'] ) : '';
	$org_siret = isset( $_POST['vendor_org_siret'] ) ? sanitize_text_field( $_POST['vendor_org_siret'] ) : '';
	$org_address = isset( $_POST['vendor_org_address'] ) ? sanitize_text_field( $_POST['vendor_org_address'] ) : '';
	$org_city = isset( $_POST['vendor_org_city'] ) ? sanitize_text_field( $_POST['vendor_org_city'] ) : '';
	$org_zipcode = isset( $_POST['vendor_org_zipcode'] ) ? sanitize_text_field( $_POST['vendor_org_zipcode'] ) : '';
	$org_website = isset( $_POST['vendor_org_website'] ) ? esc_url_raw( $_POST['vendor_org_website'] ) : '';
	$org_description = isset( $_POST['vendor_org_description'] ) ? sanitize_textarea_field( $_POST['vendor_org_description'] ) : '';
	$org_roles = isset( $_POST['vendor_org_roles'] ) ? array_map( 'sanitize_text_field', $_POST['vendor_org_roles'] ) : array();
	$categories = isset( $_POST['vendor_categories'] ) ? array_map( 'sanitize_text_field', $_POST['vendor_categories'] ) : array();

	// Validation de base
	if ( empty( $firstname ) || empty( $lastname ) || empty( $email ) || empty( $password ) ||
	     empty( $org_name ) || empty( $org_type ) || empty( $org_siret ) ||
	     empty( $org_address ) || empty( $org_city ) || empty( $org_zipcode ) ||
	     empty( $org_description ) || empty( $categories ) ) {
		wp_send_json_error( array( 'message' => 'Veuillez remplir tous les champs obligatoires.' ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'Adresse email invalide.' ) );
	}

	if ( email_exists( $email ) ) {
		wp_send_json_error( array( 'message' => 'Cette adresse email est déjà utilisée.' ) );
	}

	if ( strlen( $password ) < 8 ) {
		wp_send_json_error( array( 'message' => 'Le mot de passe doit contenir au moins 8 caractères.' ) );
	}

	// Générer username unique
	$username = strtolower( sanitize_title( $org_name ) );
	$username_base = $username;
	$counter = 1;
	while ( username_exists( $username ) ) {
		$username = $username_base . '-' . $counter;
		$counter++;
	}

	// Créer l'utilisateur
	$user_id = wp_create_user( $username, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => 'Erreur : ' . $user_id->get_error_message() ) );
	}

	// Mettre à jour les infos
	wp_update_user( array(
		'ID' => $user_id,
		'first_name' => $firstname,
		'last_name' => $lastname,
		'display_name' => $org_name,
	) );

	// Assigner le rôle el_event_vendor
	$user = new WP_User( $user_id );
	$user->set_role( 'el_event_vendor' );

	// Sauvegarder les métadonnées organisation
	update_user_meta( $user_id, 'org_display_name', $org_name );
	update_user_meta( $user_id, 'org_name', $org_name );
	update_user_meta( $user_id, 'org_type', $org_type );
	update_user_meta( $user_id, 'org_siret', $org_siret );
	update_user_meta( $user_id, 'org_address', $org_address );
	update_user_meta( $user_id, 'org_city', $org_city );
	update_user_meta( $user_id, 'org_zipcode', $org_zipcode );
	update_user_meta( $user_id, 'org_website', $org_website );
	update_user_meta( $user_id, 'org_description', $org_description );
	update_user_meta( $user_id, 'user_professional_email', $email );
	update_user_meta( $user_id, 'user_phone', $phone );
	update_user_meta( $user_id, 'org_phone', $phone );
	update_user_meta( $user_id, 'org_roles', $org_roles );
	update_user_meta( $user_id, 'org_categories', $categories );

	// STATUT : En attente d'approbation
	update_user_meta( $user_id, 'vendor_status', 'pending_approval' );
	update_user_meta( $user_id, 'vendor_application_date', current_time( 'mysql' ) );

	// Gestion des uploads de fichiers
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

	// Upload logo
	if ( ! empty( $_FILES['vendor_logo']['name'] ) ) {
		$logo_id = media_handle_upload( 'vendor_logo', 0 );
		if ( ! is_wp_error( $logo_id ) ) {
			update_user_meta( $user_id, 'org_logo_id', $logo_id );
		}
	}

	// Upload cover
	if ( ! empty( $_FILES['vendor_cover']['name'] ) ) {
		$cover_id = media_handle_upload( 'vendor_cover', 0 );
		if ( ! is_wp_error( $cover_id ) ) {
			update_user_meta( $user_id, 'org_cover_id', $cover_id );
		}
	}

	// Upload Kbis
	if ( ! empty( $_FILES['vendor_kbis']['name'] ) ) {
		$kbis_id = media_handle_upload( 'vendor_kbis', 0 );
		if ( ! is_wp_error( $kbis_id ) ) {
			update_user_meta( $user_id, 'org_kbis_id', $kbis_id );
		}
	}

	// Upload Assurance
	if ( ! empty( $_FILES['vendor_insurance']['name'] ) ) {
		$insurance_id = media_handle_upload( 'vendor_insurance', 0 );
		if ( ! is_wp_error( $insurance_id ) ) {
			update_user_meta( $user_id, 'org_insurance_id', $insurance_id );
		}
	}

	// Email de notification à l'admin
	$admin_email = get_option( 'admin_email' );
	$subject = '[Le Hiboo] Nouvelle demande partenaire : ' . $org_name;
	$message = "Une nouvelle demande de partenariat a été reçue.\n\n";
	$message .= "Organisation : {$org_name}\n";
	$message .= "Contact : {$firstname} {$lastname}\n";
	$message .= "Email : {$email}\n";
	$message .= "Type : {$org_type}\n\n";
	$message .= "Accédez à l'administration pour valider cette demande.";

	wp_mail( $admin_email, $subject, $message );

	// Email de confirmation au partenaire
	$subject_vendor = '[Le Hiboo] Votre demande de partenariat a été reçue';
	$message_vendor = "Bonjour {$firstname},\n\n";
	$message_vendor .= "Nous avons bien reçu votre demande de partenariat pour {$org_name}.\n\n";
	$message_vendor .= "Votre dossier est en cours d'examen. Notre équipe reviendra vers vous sous 48h ouvrées.\n\n";
	$message_vendor .= "Cordialement,\nL'équipe Le Hiboo";

	wp_mail( $email, $subject_vendor, $message_vendor );

	// Succès
	wp_send_json_success( array(
		'message' => 'Votre demande a été envoyée avec succès ! Vous recevrez une réponse sous 48h.',
		'redirect_url' => home_url( '/demande-recue' )
	) );
}

// ========================================
// AJAX HANDLERS - VÉRIFICATION OTP
// ========================================

/**
 * AJAX - Vérifier le code OTP
 */
add_action( 'wp_ajax_nopriv_lehiboo_verify_otp', 'lehiboo_ajax_verify_otp' );

function lehiboo_ajax_verify_otp() {
	// Vérifier le nonce
	check_ajax_referer( 'otp_verification_nonce', 'otp_nonce' );

	// Récupérer les données
	$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
	$otp_code = isset( $_POST['otp_code'] ) ? sanitize_text_field( $_POST['otp_code'] ) : '';

	// Validation
	if ( ! $user_id || ! $otp_code ) {
		wp_send_json_error( array( 'message' => 'Données manquantes.' ) );
	}

	if ( strlen( $otp_code ) !== 6 || ! ctype_digit( $otp_code ) ) {
		wp_send_json_error( array( 'message' => 'Code invalide. Le code doit contenir 6 chiffres.' ) );
	}

	// Vérifier le code OTP
	$result = LeHiboo_OTP::verify_otp( $user_id, $otp_code );

	if ( ! $result['success'] ) {
		wp_send_json_error( array( 'message' => $result['message'] ) );
	}

	// Connexion automatique après vérification réussie
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		wp_send_json_error( array( 'message' => 'Utilisateur introuvable.' ) );
	}

	wp_set_auth_cookie( $user_id, true, is_ssl() );
	wp_set_current_user( $user_id );
	do_action( 'wp_login', $user->user_login, $user );

	wp_send_json_success( array(
		'message' => 'Email vérifié ! Connexion en cours...',
		'user_id' => $user_id
	) );
}

/**
 * AJAX - Renvoyer le code OTP
 */
add_action( 'wp_ajax_nopriv_lehiboo_resend_otp', 'lehiboo_ajax_resend_otp' );

function lehiboo_ajax_resend_otp() {
	// Récupérer l'ID utilisateur
	$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

	if ( ! $user_id ) {
		wp_send_json_error( array( 'message' => 'ID utilisateur manquant.' ) );
	}

	// Renvoyer le code
	$result = LeHiboo_OTP::resend_otp( $user_id );

	if ( ! $result['success'] ) {
		wp_send_json_error( array( 'message' => $result['message'] ) );
	}

	wp_send_json_success( array( 'message' => $result['message'] ) );
}

/**
 * AJAX - Charger le template OTP
 */
add_action( 'wp_ajax_load_otp_template', 'lehiboo_load_otp_template' );
add_action( 'wp_ajax_nopriv_load_otp_template', 'lehiboo_load_otp_template' );

function lehiboo_load_otp_template() {
	$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

	if ( ! $user_id ) {
		wp_send_json_error( array( 'message' => 'ID utilisateur manquant.' ) );
	}

	// Simuler le query param pour le template
	$_GET['user_id'] = $user_id;

	ob_start();
	include get_stylesheet_directory() . '/templates/otp-verification.php';
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}

/**
 * Créer automatiquement la page vendor-pending si elle n'existe pas
 * @version 1.0.0
 */
function lehiboo_create_vendor_pending_page() {
	// Vérifier si la page existe déjà
	$page = get_page_by_path( 'vendor-pending' );

	if ( ! $page ) {
		// Créer la page
		$page_id = wp_insert_post( array(
			'post_title'     => 'Demande Partenaire en Attente',
			'post_name'      => 'vendor-pending',
			'post_content'   => '',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'page_template'  => 'templates/vendor-pending.php'
		) );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			// Mettre à jour le template
			update_post_meta( $page_id, '_wp_page_template', 'templates/vendor-pending.php' );
		}
	}
}
add_action( 'after_switch_theme', 'lehiboo_create_vendor_pending_page' );
add_action( 'admin_init', 'lehiboo_create_vendor_pending_page' );
