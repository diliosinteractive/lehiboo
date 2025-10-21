<?php
/**
 * Template Vendor: Messages
 *
 * Affiche la liste des messages reçus par l'organisateur
 *
 * @package LeHiboo
 * @since 3.4.0
 */

if ( !defined( 'ABSPATH' ) ) exit();

$current_user_id = get_current_id();
$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

// Récupérer tous les messages de l'organisateur connecté
$args = array(
    'post_type'      => 'organizer_message',
    'post_status'    => 'private',
    'author'         => $current_user_id,
    'posts_per_page' => 20,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
);

$messages_query = new WP_Query( $args );

// Compter les messages non lus
$unread_args = array(
    'post_type'      => 'organizer_message',
    'post_status'    => 'private',
    'author'         => $current_user_id,
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'     => '_is_read',
            'value'   => '0',
            'compare' => '='
        )
    ),
);
$unread_query = new WP_Query( $unread_args );
$unread_count = $unread_query->found_posts;

// Gérer l'action de marquage comme lu
if ( isset( $_POST['mark_as_read'] ) && isset( $_POST['message_id'] ) && wp_verify_nonce( $_POST['message_nonce'], 'mark_message_read' ) ) {
    $message_id = intval( $_POST['message_id'] );

    // Vérifier que le message appartient à l'utilisateur
    $message = get_post( $message_id );
    if ( $message && $message->post_author == $current_user_id ) {
        update_post_meta( $message_id, '_is_read', 1 );
        wp_safe_redirect( add_query_arg( array( 'vendor' => 'messages' ), get_myaccount_page() ) );
        exit;
    }
}
?>

<div class="wrap_content">
    <div class="el_vendor_messages_wrapper">

        <div class="messages_header">
            <h2 class="messages_title">
                <i class="icon_mail_alt"></i>
                <?php esc_html_e( 'Mes Messages', 'eventlist' ); ?>
                <?php if ( $unread_count > 0 ) : ?>
                    <span class="unread_badge"><?php echo esc_html( $unread_count ); ?></span>
                <?php endif; ?>
            </h2>
            <p class="messages_subtitle">
                <?php esc_html_e( 'Messages reçus concernant vos activités', 'eventlist' ); ?>
            </p>
        </div>

        <?php if ( $messages_query->have_posts() ) : ?>

            <div class="messages_list">
                <table class="vendor_messages_table">
                    <thead>
                        <tr>
                            <th class="col_status"><?php esc_html_e( 'Statut', 'eventlist' ); ?></th>
                            <th class="col_from"><?php esc_html_e( 'De', 'eventlist' ); ?></th>
                            <th class="col_event"><?php esc_html_e( 'Activité', 'eventlist' ); ?></th>
                            <th class="col_date"><?php esc_html_e( 'Date', 'eventlist' ); ?></th>
                            <th class="col_actions"><?php esc_html_e( 'Actions', 'eventlist' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ( $messages_query->have_posts() ) : $messages_query->the_post();
                            $message_id = get_the_ID();

                            // Métadonnées
                            $from_name = get_post_meta( $message_id, '_from_name', true );
                            $from_email = get_post_meta( $message_id, '_from_email', true );
                            $event_id = get_post_meta( $message_id, '_event_id', true );
                            $sent_date = get_post_meta( $message_id, '_sent_date', true );
                            $is_read = get_post_meta( $message_id, '_is_read', true );
                            $email_sent = get_post_meta( $message_id, '_email_sent', true );

                            $event_title = get_the_title( $event_id );
                            $event_link = get_edit_post_link( $event_id );

                            $message_content = get_the_content();
                            $message_excerpt = wp_trim_words( $message_content, 15, '...' );

                            $row_class = $is_read ? 'message_read' : 'message_unread';
                        ?>
                            <tr class="message_row <?php echo esc_attr( $row_class ); ?>" data-message-id="<?php echo esc_attr( $message_id ); ?>">

                                <!-- Statut -->
                                <td class="col_status">
                                    <?php if ( ! $is_read ) : ?>
                                        <span class="status_badge unread">
                                            <i class="icon_mail"></i>
                                            <?php esc_html_e( 'Non lu', 'eventlist' ); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="status_badge read">
                                            <i class="icon_mail_alt"></i>
                                            <?php esc_html_e( 'Lu', 'eventlist' ); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- De -->
                                <td class="col_from">
                                    <div class="sender_info">
                                        <strong class="sender_name"><?php echo esc_html( $from_name ); ?></strong>
                                        <span class="sender_email"><?php echo esc_html( $from_email ); ?></span>
                                    </div>
                                </td>

                                <!-- Activité -->
                                <td class="col_event">
                                    <?php if ( $event_id && get_post_status( $event_id ) ) : ?>
                                        <a href="<?php echo esc_url( get_permalink( $event_id ) ); ?>" target="_blank" class="event_link">
                                            <?php echo esc_html( $event_title ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="event_deleted"><?php esc_html_e( 'Activité supprimée', 'eventlist' ); ?></span>
                                    <?php endif; ?>
                                </td>

                                <!-- Date -->
                                <td class="col_date">
                                    <?php echo esc_html( date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime( $sent_date ) ) ); ?>
                                </td>

                                <!-- Actions -->
                                <td class="col_actions">
                                    <button type="button" class="btn_view_message" data-message-id="<?php echo esc_attr( $message_id ); ?>">
                                        <i class="icon_search"></i>
                                        <?php esc_html_e( 'Voir', 'eventlist' ); ?>
                                    </button>
                                </td>

                            </tr>

                            <!-- Ligne détails (cachée par défaut) -->
                            <tr class="message_details" id="message_details_<?php echo esc_attr( $message_id ); ?>" style="display:none;">
                                <td colspan="5">
                                    <div class="message_details_content">
                                        <div class="message_header_details">
                                            <h4><?php esc_html_e( 'Message complet', 'eventlist' ); ?></h4>
                                            <div class="message_meta">
                                                <span><strong><?php esc_html_e( 'De:', 'eventlist' ); ?></strong> <?php echo esc_html( $from_name ); ?> (<?php echo esc_html( $from_email ); ?>)</span>
                                                <span><strong><?php esc_html_e( 'Activité:', 'eventlist' ); ?></strong> <?php echo esc_html( $event_title ); ?></span>
                                                <span><strong><?php esc_html_e( 'Date:', 'eventlist' ); ?></strong> <?php echo esc_html( date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime( $sent_date ) ) ); ?></span>
                                            </div>
                                        </div>
                                        <div class="message_body">
                                            <?php echo nl2br( esc_html( $message_content ) ); ?>
                                        </div>
                                        <div class="message_actions_details">
                                            <?php if ( ! $is_read ) : ?>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="message_id" value="<?php echo esc_attr( $message_id ); ?>">
                                                    <?php wp_nonce_field( 'mark_message_read', 'message_nonce' ); ?>
                                                    <button type="submit" name="mark_as_read" class="btn_mark_read">
                                                        <i class="icon_check"></i>
                                                        <?php esc_html_e( 'Marquer comme lu', 'eventlist' ); ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <a href="mailto:<?php echo esc_attr( $from_email ); ?>" class="btn_reply">
                                                <i class="icon_mail_alt"></i>
                                                <?php esc_html_e( 'Répondre', 'eventlist' ); ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ( $messages_query->max_num_pages > 1 ) : ?>
                <div class="messages_pagination">
                    <?php
                    echo paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'current'   => $paged,
                        'total'     => $messages_query->max_num_pages,
                        'prev_text' => '<i class="icon_arrow-left_alt"></i> ' . esc_html__( 'Précédent', 'eventlist' ),
                        'next_text' => esc_html__( 'Suivant', 'eventlist' ) . ' <i class="icon_arrow-right_alt"></i>',
                    ) );
                    ?>
                </div>
            <?php endif; ?>

        <?php else : ?>

            <!-- Aucun message -->
            <div class="messages_empty">
                <div class="empty_icon">
                    <i class="icon_mail_alt"></i>
                </div>
                <h3><?php esc_html_e( 'Aucun message', 'eventlist' ); ?></h3>
                <p><?php esc_html_e( 'Vous n\'avez encore reçu aucun message concernant vos activités.', 'eventlist' ); ?></p>
            </div>

        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

    </div>
</div>

<style>
/* Messages Vendor Styles */
.el_vendor_messages_wrapper {
    background: #FFFFFF;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.messages_header {
    margin-bottom: 30px;
    border-bottom: 2px solid #F5F5F5;
    padding-bottom: 20px;
}

.messages_title {
    font-size: 28px;
    font-weight: 600;
    color: #222222;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.messages_title i {
    font-size: 32px;
    color: #FF5722;
}

.unread_badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    background: #FF5722;
    color: #FFFFFF;
    font-size: 14px;
    font-weight: 600;
    border-radius: 14px;
}

.messages_subtitle {
    margin: 0;
    font-size: 15px;
    color: #717171;
}

/* Table */
.vendor_messages_table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

.vendor_messages_table thead th {
    background: #F7F7F7;
    padding: 14px 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #484848;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #DDDDDD;
}

.vendor_messages_table tbody tr.message_row {
    border-bottom: 1px solid #EBEBEB;
    transition: background 0.2s ease;
}

.vendor_messages_table tbody tr.message_row:hover {
    background: #F9F9F9;
}

.vendor_messages_table tbody tr.message_unread {
    background: #FFF8F5;
}

.vendor_messages_table tbody tr.message_unread:hover {
    background: #FFF0E8;
}

.vendor_messages_table tbody td {
    padding: 16px;
    font-size: 14px;
    color: #484848;
}

.col_status {
    width: 120px;
}

.col_from {
    width: 25%;
}

.col_event {
    width: 30%;
}

.col_date {
    width: 180px;
}

.col_actions {
    width: 100px;
    text-align: center;
}

/* Status badges */
.status_badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status_badge.unread {
    background: #FFE5DD;
    color: #FF5722;
}

.status_badge.read {
    background: #E8F5E9;
    color: #4CAF50;
}

/* Sender info */
.sender_info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sender_name {
    font-weight: 600;
    color: #222222;
}

.sender_email {
    font-size: 13px;
    color: #717171;
}

/* Event link */
.event_link {
    color: #FF5722;
    text-decoration: none;
    font-weight: 500;
}

.event_link:hover {
    text-decoration: underline;
}

.event_deleted {
    color: #999999;
    font-style: italic;
}

/* Buttons */
.btn_view_message {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #FF5722;
    color: #FFFFFF;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn_view_message:hover {
    background: #E64A19;
}

/* Message details */
.message_details td {
    background: #FAFAFA;
    padding: 0 !important;
}

.message_details_content {
    padding: 24px;
}

.message_header_details h4 {
    margin: 0 0 12px 0;
    font-size: 18px;
    color: #222222;
}

.message_meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #717171;
}

.message_body {
    background: #FFFFFF;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #EBEBEB;
    margin-bottom: 20px;
    line-height: 1.6;
    color: #484848;
}

.message_actions_details {
    display: flex;
    gap: 12px;
}

.btn_mark_read,
.btn_reply {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn_mark_read {
    background: #4CAF50;
    color: #FFFFFF;
    border: none;
}

.btn_mark_read:hover {
    background: #45A049;
}

.btn_reply {
    background: transparent;
    color: #FF5722;
    border: 1px solid #FF5722;
}

.btn_reply:hover {
    background: #FF5722;
    color: #FFFFFF;
}

/* Empty state */
.messages_empty {
    text-align: center;
    padding: 60px 20px;
}

.empty_icon i {
    font-size: 80px;
    color: #DDDDDD;
    margin-bottom: 20px;
}

.messages_empty h3 {
    font-size: 22px;
    color: #484848;
    margin: 0 0 8px 0;
}

.messages_empty p {
    font-size: 15px;
    color: #717171;
    margin: 0;
}

/* Pagination */
.messages_pagination {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.messages_pagination .page-numbers {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    margin: 0 4px;
    background: #F7F7F7;
    color: #484848;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.messages_pagination .page-numbers:hover,
.messages_pagination .page-numbers.current {
    background: #FF5722;
    color: #FFFFFF;
}

/* Responsive */
@media (max-width: 768px) {
    .vendor_messages_table thead {
        display: none;
    }

    .vendor_messages_table tbody tr.message_row {
        display: flex;
        flex-direction: column;
        padding: 16px;
        gap: 12px;
    }

    .vendor_messages_table tbody td {
        padding: 0;
        width: 100% !important;
    }

    .col_actions {
        text-align: left;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle message details
    $('.btn_view_message').on('click', function() {
        var messageId = $(this).data('message-id');
        var $detailsRow = $('#message_details_' + messageId);
        var $currentRow = $(this).closest('tr');

        // Fermer tous les autres détails
        $('.message_details').not($detailsRow).slideUp(300);

        // Toggle le détail actuel
        $detailsRow.slideToggle(300);

        // Marquer comme lu visuellement si non lu
        if ($currentRow.hasClass('message_unread')) {
            $currentRow.removeClass('message_unread').addClass('message_read');
            $currentRow.find('.status_badge').removeClass('unread').addClass('read')
                .html('<i class="icon_mail_alt"></i> <?php esc_html_e( "Lu", "eventlist" ); ?>');
        }
    });
});
</script>
