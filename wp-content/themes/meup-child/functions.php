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
        wp_enqueue_script( 'author-profile-modern', plugins_url( 'eventlist/assets/js/frontend/author-profile-modern.js' ), array('jquery'), '1.0.0', true );
    }
}

// ========================================
// EXTENSIONS METABOX EVENT (FAQ, Inclus, etc.)
// ========================================
if( file_exists( get_stylesheet_directory() . '/includes/event-metabox-extensions.php' ) ) {
	require_once get_stylesheet_directory() . '/includes/event-metabox-extensions.php';
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
	$event_title = get_the_title( $event_id );

	// Créer le post
	$message_id = wp_insert_post( array(
		'post_title'    => sprintf( '%s - Message de %s', $event_title, $from_name ),
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
		update_post_meta( $message_id, '_event_id', intval( $event_id ) );
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
