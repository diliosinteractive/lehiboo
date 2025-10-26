<?php
if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Installation et gestion des tables pour le gestionnaire de médias des vendors
 */
class EL_Install_Media_Folders {

    /**
     * Version de la base de données
     */
    const DB_VERSION = '1.0.0';
    const DB_VERSION_OPTION = 'el_media_folders_db_version';

    /**
     * Installation des tables
     */
    public static function install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $current_version = get_option( self::DB_VERSION_OPTION );

        // Si déjà installé, ne rien faire
        if ( $current_version === self::DB_VERSION ) {
            return;
        }

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Table des dossiers
        $table_folders = $wpdb->prefix . 'vendor_gallery_folders';
        $sql_folders = "CREATE TABLE {$table_folders} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            parent_id bigint(20) DEFAULT 0,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            color varchar(7) DEFAULT '#FF6B35',
            icon varchar(50) DEFAULT 'folder',
            order_num int(11) DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY parent_id (parent_id),
            KEY slug (slug)
        ) $charset_collate;";

        // Table de relation images-dossiers
        $table_images = $wpdb->prefix . 'vendor_gallery_images';
        $sql_images = "CREATE TABLE {$table_images} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            attachment_id bigint(20) NOT NULL,
            folder_id bigint(20) DEFAULT 0,
            user_id bigint(20) NOT NULL,
            order_num int(11) DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY attachment_folder (attachment_id),
            KEY folder_id (folder_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta( $sql_folders );
        dbDelta( $sql_images );

        // Mettre à jour la version
        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );

        // Migrer les images existantes
        self::migrate_existing_images();
    }

    /**
     * Migrer les images existantes dans la nouvelle table
     */
    private static function migrate_existing_images() {
        global $wpdb;

        $table_images = $wpdb->prefix . 'vendor_gallery_images';

        // Récupérer toutes les images des vendors
        $attachments = get_posts( array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) );

        if ( ! empty( $attachments ) ) {
            foreach ( $attachments as $attachment_id ) {
                $author_id = get_post_field( 'post_author', $attachment_id );

                // Vérifier si l'auteur est un vendor
                $user = get_userdata( $author_id );
                if ( ! $user ) continue;

                // Vérifier si l'image n'existe pas déjà
                $exists = $wpdb->get_var( $wpdb->prepare(
                    "SELECT id FROM {$table_images} WHERE attachment_id = %d",
                    $attachment_id
                ) );

                if ( ! $exists ) {
                    $wpdb->insert(
                        $table_images,
                        array(
                            'attachment_id' => $attachment_id,
                            'folder_id'     => 0, // Racine par défaut
                            'user_id'       => $author_id,
                            'order_num'     => 0,
                            'created_at'    => current_time( 'mysql' ),
                        ),
                        array( '%d', '%d', '%d', '%d', '%s' )
                    );
                }
            }
        }
    }

    /**
     * Désinstallation complète (suppression des tables)
     */
    public static function uninstall() {
        global $wpdb;

        $table_folders = $wpdb->prefix . 'vendor_gallery_folders';
        $table_images = $wpdb->prefix . 'vendor_gallery_images';

        $wpdb->query( "DROP TABLE IF EXISTS {$table_images}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$table_folders}" );

        delete_option( self::DB_VERSION_OPTION );
    }

    /**
     * Vérifier et mettre à jour si nécessaire
     */
    public static function check_version() {
        $current_version = get_option( self::DB_VERSION_OPTION );

        if ( $current_version !== self::DB_VERSION ) {
            self::install();
        }
    }
}
