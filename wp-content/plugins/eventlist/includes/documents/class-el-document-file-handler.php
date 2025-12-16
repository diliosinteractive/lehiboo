<?php
/**
 * Class EL_Document_File_Handler
 *
 * Gestion securisee des fichiers documents
 * V1 Le Hiboo - Gestion des documents securises
 *
 * SECURITE:
 * - Stockage hors du dossier public uploads
 * - Noms de fichiers hashes (SHA256)
 * - Validation MIME reelle avec finfo
 * - Acces via PHP uniquement (pas d'URL directe)
 * - Rate limiting par utilisateur
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Document_File_Handler {

    /**
     * Nom du dossier de stockage securise
     */
    const STORAGE_DIR = 'el-secure-documents';

    /**
     * Limite d'uploads par jour par utilisateur
     */
    const DAILY_UPLOAD_LIMIT = 20;

    /**
     * Mapping des extensions vers types MIME
     */
    private static $mime_map = array(
        'pdf' => array( 'application/pdf' ),
        'jpg' => array( 'image/jpeg' ),
        'jpeg' => array( 'image/jpeg', 'image/pjpeg' ),
        'png' => array( 'image/png' ),
        'doc' => array( 'application/msword' ),
        'docx' => array( 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ),
    );

    /**
     * Recupere le chemin de base du stockage securise
     *
     * @return string Chemin absolu du dossier de stockage
     */
    public static function get_storage_path() {
        $path = WP_CONTENT_DIR . '/' . self::STORAGE_DIR . '/';

        if ( ! file_exists( $path ) ) {
            self::create_storage_directory( $path );
        }

        return $path;
    }

    /**
     * Cree le dossier de stockage avec protections
     *
     * @param string $path Chemin du dossier
     * @return bool
     */
    private static function create_storage_directory( $path ) {
        // Creer le dossier
        if ( ! wp_mkdir_p( $path ) ) {
            return false;
        }

        // Creer le .htaccess
        self::create_htaccess_protection( $path );

        // Creer le index.php vide
        self::create_index_protection( $path );

        return true;
    }

    /**
     * Cree le fichier .htaccess pour bloquer l'acces direct
     *
     * @param string $path Chemin du dossier
     */
    public static function create_htaccess_protection( $path ) {
        $htaccess_content = "# Protection des documents securises - Le Hiboo
# Bloquer tout acces direct aux fichiers

Order deny,allow
Deny from all

<Files ~ \"^\\.ht\">
    Order allow,deny
    Deny from all
</Files>

# Bloquer aussi via Apache 2.4+
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
";

        file_put_contents( $path . '.htaccess', $htaccess_content );
    }

    /**
     * Cree un fichier index.php vide
     *
     * @param string $path Chemin du dossier
     */
    private static function create_index_protection( $path ) {
        $index_content = "<?php\n// Silence is golden.\n";
        file_put_contents( $path . 'index.php', $index_content );
    }

    /**
     * Genere un nom de fichier securise (hash SHA256)
     *
     * @param string $original_name Nom original du fichier
     * @param int $user_id ID de l'utilisateur
     * @return string Nom de fichier hashe avec extension
     */
    public static function generate_secure_filename( $original_name, $user_id ) {
        $timestamp = microtime( true );
        $random = wp_generate_password( 32, false );

        $hash_input = $original_name . $user_id . $timestamp . $random . wp_salt( 'auth' );
        $hash = hash( 'sha256', $hash_input );

        $extension = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );

        return $hash . '.' . $extension;
    }

    /**
     * Recupere le chemin de stockage avec sous-dossiers annee/mois
     *
     * @return string Chemin avec sous-dossiers
     */
    public static function get_dated_storage_path() {
        $base_path = self::get_storage_path();
        $year = date( 'Y' );
        $month = date( 'm' );

        $dated_path = $base_path . $year . '/' . $month . '/';

        if ( ! file_exists( $dated_path ) ) {
            wp_mkdir_p( $dated_path );
            // Ajouter index.php dans les sous-dossiers aussi
            if ( ! file_exists( $base_path . $year . '/index.php' ) ) {
                self::create_index_protection( $base_path . $year . '/' );
            }
            self::create_index_protection( $dated_path );
        }

        return $dated_path;
    }

    /**
     * Valide le type MIME reel d'un fichier
     *
     * @param string $file_path Chemin du fichier temporaire
     * @param array $allowed_extensions Extensions autorisees
     * @return true|WP_Error True si valide ou erreur
     */
    public static function validate_mime_type( $file_path, $allowed_extensions ) {
        // Verifier que le fichier existe
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', __( 'Fichier non trouve', 'eventlist' ) );
        }

        // Obtenir le type MIME reel avec finfo
        if ( function_exists( 'finfo_open' ) ) {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
            $real_mime = finfo_file( $finfo, $file_path );
            finfo_close( $finfo );

            if ( ! $real_mime ) {
                return new WP_Error( 'mime_detection_failed', __( 'Impossible de detecter le type du fichier', 'eventlist' ) );
            }

            // Construire la liste des types MIME autorises
            $allowed_mimes = array();
            foreach ( $allowed_extensions as $ext ) {
                $ext = strtolower( trim( $ext ) );
                if ( isset( self::$mime_map[ $ext ] ) ) {
                    $allowed_mimes = array_merge( $allowed_mimes, self::$mime_map[ $ext ] );
                }
            }

            if ( empty( $allowed_mimes ) ) {
                return new WP_Error( 'no_allowed_mimes', __( 'Aucun type de fichier autorise', 'eventlist' ) );
            }

            if ( ! in_array( $real_mime, $allowed_mimes ) ) {
                return new WP_Error(
                    'invalid_mime',
                    sprintf(
                        __( 'Type de fichier non autorise. Type detecte: %s. Types autorises: %s', 'eventlist' ),
                        $real_mime,
                        implode( ', ', $allowed_mimes )
                    )
                );
            }

            return true;
        }

        // Fallback si finfo n'est pas disponible (moins securise)
        $wp_filetype = wp_check_filetype( $file_path );
        if ( empty( $wp_filetype['type'] ) ) {
            return new WP_Error( 'invalid_file_type', __( 'Type de fichier invalide', 'eventlist' ) );
        }

        return true;
    }

    /**
     * Verifie le rate limiting pour les uploads
     *
     * @param int $vendor_id ID du vendor
     * @return true|WP_Error True si OK ou erreur
     */
    public static function check_upload_rate_limit( $vendor_id ) {
        $daily_limit = apply_filters( 'el_document_daily_upload_limit', self::DAILY_UPLOAD_LIMIT );
        $transient_key = 'el_doc_upload_' . absint( $vendor_id ) . '_' . date( 'Y-m-d' );

        $count = get_transient( $transient_key );
        if ( $count === false ) {
            $count = 0;
        }

        if ( $count >= $daily_limit ) {
            return new WP_Error(
                'rate_limit_exceeded',
                sprintf(
                    __( 'Limite d\'uploads atteinte (%d documents/jour). Reessayez demain.', 'eventlist' ),
                    $daily_limit
                )
            );
        }

        return true;
    }

    /**
     * Incremente le compteur de rate limiting
     *
     * @param int $vendor_id ID du vendor
     */
    private static function increment_upload_count( $vendor_id ) {
        $transient_key = 'el_doc_upload_' . absint( $vendor_id ) . '_' . date( 'Y-m-d' );
        $count = get_transient( $transient_key );

        if ( $count === false ) {
            $count = 0;
        }

        $seconds_until_midnight = strtotime( 'tomorrow' ) - time();
        set_transient( $transient_key, $count + 1, $seconds_until_midnight );
    }

    /**
     * Stocke un fichier de maniere securisee
     *
     * @param array $file Donnees du fichier ($_FILES)
     * @param int $vendor_id ID du vendor
     * @param int $document_type_id ID du type de document
     * @return array|WP_Error Donnees du fichier stocke ou erreur
     */
    public static function store_file( $file, $vendor_id, $document_type_id ) {
        // Verifier les erreurs d'upload
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            return self::get_upload_error( $file['error'] );
        }

        // Verifier le rate limiting
        $rate_check = self::check_upload_rate_limit( $vendor_id );
        if ( is_wp_error( $rate_check ) ) {
            return $rate_check;
        }

        // Recuperer les parametres du type de document
        $allowed_extensions = EL_Document_Types::get_allowed_extensions( $document_type_id );
        $max_size = EL_Document_Types::get_max_file_size( $document_type_id );

        // Verifier l'extension
        $extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if ( ! in_array( $extension, $allowed_extensions ) ) {
            return new WP_Error(
                'invalid_extension',
                sprintf(
                    __( 'Extension non autorisee. Extensions autorisees: %s', 'eventlist' ),
                    implode( ', ', $allowed_extensions )
                )
            );
        }

        // Verifier la taille
        if ( $file['size'] > $max_size ) {
            return new WP_Error(
                'file_too_large',
                sprintf(
                    __( 'Fichier trop volumineux. Taille maximum: %s', 'eventlist' ),
                    size_format( $max_size )
                )
            );
        }

        // Valider le type MIME reel
        $mime_check = self::validate_mime_type( $file['tmp_name'], $allowed_extensions );
        if ( is_wp_error( $mime_check ) ) {
            return $mime_check;
        }

        // Generer le nom de fichier securise
        $secure_filename = self::generate_secure_filename( $file['name'], $vendor_id );

        // Obtenir le chemin de destination
        $storage_path = self::get_dated_storage_path();
        $destination = $storage_path . $secure_filename;

        // Deplacer le fichier
        if ( ! move_uploaded_file( $file['tmp_name'], $destination ) ) {
            return new WP_Error( 'move_failed', __( 'Erreur lors du deplacement du fichier', 'eventlist' ) );
        }

        // Definir les permissions du fichier (lecture seule)
        chmod( $destination, 0644 );

        // Incrementer le compteur de rate limiting
        self::increment_upload_count( $vendor_id );

        // Obtenir le type MIME pour le stockage
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mime_type = finfo_file( $finfo, $destination );
        finfo_close( $finfo );

        // Retourner les donnees du fichier
        return array(
            'original_filename' => sanitize_file_name( $file['name'] ),
            'stored_filename' => $secure_filename,
            'file_path' => str_replace( WP_CONTENT_DIR, '', $destination ),
            'mime_type' => $mime_type,
            'file_size' => $file['size'],
        );
    }

    /**
     * Sert un fichier de maniere securisee
     *
     * @param int $document_id ID du document
     * @param int $requesting_user_id ID de l'utilisateur demandeur
     * @param bool $force_download Force le telechargement (vs affichage inline)
     */
    public static function serve_file( $document_id, $requesting_user_id, $force_download = true ) {
        $document = EL_Vendor_Documents::get( $document_id );

        if ( ! $document ) {
            wp_die( __( 'Document introuvable', 'eventlist' ), __( 'Erreur', 'eventlist' ), array( 'response' => 404 ) );
        }

        // Verifier les permissions
        $can_access = false;

        // Le proprietaire peut acceder
        if ( $document->vendor_id == $requesting_user_id ) {
            $can_access = true;
        }

        // Les admins peuvent acceder
        if ( current_user_can( 'manage_options' ) ) {
            $can_access = true;
        }

        if ( ! $can_access ) {
            wp_die( __( 'Acces refuse', 'eventlist' ), __( 'Erreur', 'eventlist' ), array( 'response' => 403 ) );
        }

        // Construire le chemin complet
        $file_path = WP_CONTENT_DIR . $document->file_path;

        if ( ! file_exists( $file_path ) ) {
            wp_die( __( 'Fichier introuvable', 'eventlist' ), __( 'Erreur', 'eventlist' ), array( 'response' => 404 ) );
        }

        // Logger l'acces
        EL_Document_Audit::log( $document_id, EL_Document_Audit::ACTION_DOWNLOADED );

        // Determiner le Content-Disposition
        $disposition = $force_download ? 'attachment' : 'inline';

        // Nettoyer les buffers de sortie
        while ( ob_get_level() ) {
            ob_end_clean();
        }

        // Envoyer les headers
        header( 'Content-Type: ' . $document->mime_type );
        header( 'Content-Disposition: ' . $disposition . '; filename="' . $document->original_filename . '"' );
        header( 'Content-Length: ' . $document->file_size );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Cache-Control: private, no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        header( 'X-Content-Type-Options: nosniff' );

        // Lire et envoyer le fichier
        readfile( $file_path );
        exit;
    }

    /**
     * Supprime un fichier du stockage
     *
     * @param string $file_path Chemin relatif du fichier (depuis WP_CONTENT_DIR)
     * @return bool
     */
    public static function delete_file( $file_path ) {
        $full_path = WP_CONTENT_DIR . $file_path;

        if ( file_exists( $full_path ) ) {
            return unlink( $full_path );
        }

        return true; // Fichier deja supprime
    }

    /**
     * Traduit les codes d'erreur d'upload PHP
     *
     * @param int $error_code Code d'erreur
     * @return WP_Error
     */
    private static function get_upload_error( $error_code ) {
        $messages = array(
            UPLOAD_ERR_INI_SIZE => __( 'Le fichier depasse la taille maximum autorisee par le serveur', 'eventlist' ),
            UPLOAD_ERR_FORM_SIZE => __( 'Le fichier depasse la taille maximum autorisee', 'eventlist' ),
            UPLOAD_ERR_PARTIAL => __( 'Le fichier n\'a ete que partiellement telecharge', 'eventlist' ),
            UPLOAD_ERR_NO_FILE => __( 'Aucun fichier n\'a ete telecharge', 'eventlist' ),
            UPLOAD_ERR_NO_TMP_DIR => __( 'Dossier temporaire manquant', 'eventlist' ),
            UPLOAD_ERR_CANT_WRITE => __( 'Echec de l\'ecriture du fichier sur le disque', 'eventlist' ),
            UPLOAD_ERR_EXTENSION => __( 'Une extension PHP a arrete l\'upload', 'eventlist' ),
        );

        $message = isset( $messages[ $error_code ] )
            ? $messages[ $error_code ]
            : __( 'Erreur inconnue lors de l\'upload', 'eventlist' );

        return new WP_Error( 'upload_error', $message );
    }

    /**
     * Verifie si le stockage est correctement configure
     *
     * @return array Rapport de verification
     */
    public static function check_storage_health() {
        $storage_path = self::get_storage_path();

        return array(
            'storage_exists' => file_exists( $storage_path ),
            'storage_writable' => is_writable( $storage_path ),
            'htaccess_exists' => file_exists( $storage_path . '.htaccess' ),
            'index_exists' => file_exists( $storage_path . 'index.php' ),
            'storage_path' => $storage_path,
        );
    }

    /**
     * Nettoie les fichiers orphelins (sans reference en BDD)
     *
     * @return int Nombre de fichiers supprimes
     */
    public static function cleanup_orphaned_files() {
        global $wpdb;

        $storage_path = self::get_storage_path();
        $deleted_count = 0;

        // Recuperer tous les fichiers stockes
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $storage_path, RecursiveDirectoryIterator::SKIP_DOTS )
        );

        foreach ( $iterator as $file ) {
            if ( $file->isFile() && ! in_array( $file->getFilename(), array( '.htaccess', 'index.php' ) ) ) {
                $filename = $file->getFilename();

                // Verifier si le fichier existe en BDD
                $table = $wpdb->prefix . 'el_vendor_documents';
                $exists = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE stored_filename = %s",
                    $filename
                ) );

                if ( ! $exists ) {
                    if ( unlink( $file->getPathname() ) ) {
                        $deleted_count++;
                    }
                }
            }
        }

        return $deleted_count;
    }

    /**
     * Calcule l'espace disque utilise
     *
     * @return int Taille en octets
     */
    public static function get_storage_size() {
        $storage_path = self::get_storage_path();
        $total_size = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $storage_path, RecursiveDirectoryIterator::SKIP_DOTS )
        );

        foreach ( $iterator as $file ) {
            if ( $file->isFile() && ! in_array( $file->getFilename(), array( '.htaccess', 'index.php' ) ) ) {
                $total_size += $file->getSize();
            }
        }

        return $total_size;
    }
}
