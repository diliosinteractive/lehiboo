<?php if ( !defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';

// Get Event Data for Sidebar Header
$event_title = get_the_title($post_id);
if(empty($event_title)) $event_title = __('Nouvel événement', 'eventlist');
$event_img_id = get_post_thumbnail_id($post_id);
$event_img_url = $event_img_id ? wp_get_attachment_image_url($event_img_id, 'thumbnail') : EL_PLUGIN_URI . 'assets/img/placeholder.png';

$is_published = (get_post_status($post_id) == 'publish');

?>

<!-- Custom Assets for Event Form -->
<link rel="stylesheet" href="<?php echo EL_PLUGIN_URI . 'assets/css/vendor-event-form.css?v=' . time(); ?>">
<script src="<?php echo EL_PLUGIN_URI . 'assets/js/vendor-event-form.js?v=' . time(); ?>" defer></script>
<script src="<?php echo EL_PLUGIN_URI . 'assets/js/frontend/vendor-gallery.js'; ?>" defer></script>

<style>
/* V1 Le Hiboo - Amélioration des boutons de la barre d'outils */
.profile_sticky_bar .sticky_bar_right {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Status Indicator */
.event_status_indicator {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer;
    transition: all 0.25s ease;
    border: 2px solid transparent;
}

.event_status_indicator.online {
    background: #10b981 !important;
    color: #fff !important;
    border-color: #10b981 !important;
}

.event_status_indicator.online:hover {
    background: #059669 !important;
    border-color: #059669 !important;
}

.event_status_indicator.offline {
    background: #fff !important;
    color: #64748b !important;
    border-color: #e2e8f0 !important;
}

.event_status_indicator.offline:hover {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
}

.event_status_indicator svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* Preview Button */
.btn_preview_profile {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    padding: 10px 18px !important;
    background: #fff !important;
    color: #334155 !important;
    border: 2px solid #e2e8f0 !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    transition: all 0.25s ease;
}

.btn_preview_profile:hover {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
    color: #1e293b !important;
}

.btn_preview_profile svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

/* Duplicate Button */
.btn_duplicate_event {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    padding: 10px 18px !important;
    background: #fff !important;
    color: #334155 !important;
    border: 2px solid #e2e8f0 !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn_duplicate_event:hover {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
    color: #1e293b !important;
}

.btn_duplicate_event svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.btn_duplicate_event i {
    display: none;
}

/* Save Button */
.btn_save_profile {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    padding: 10px 20px !important;
    background: #FF601F !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn_save_profile:hover {
    background: #e5561c !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 96, 31, 0.3);
}

.btn_save_profile .icon-save {
    display: block;
}

.btn_save_profile .icon-loader {
    display: none;
    animation: spin 1s linear infinite;
}

.btn_save_profile.loading {
    opacity: 0.85;
    pointer-events: none;
}

.btn_save_profile.loading .icon-save {
    display: none;
}

.btn_save_profile.loading .icon-loader {
    display: block;
}

.btn_save_profile.loading span {
    opacity: 0.8;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.btn_save_profile svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .profile_sticky_bar .sticky_bar_right {
        gap: 8px;
    }

    .event_status_indicator span,
    .btn_preview_profile span,
    .btn_duplicate_event span {
        display: none;
    }

    .event_status_indicator,
    .btn_preview_profile,
    .btn_duplicate_event {
        padding: 10px !important;
    }

    .btn_save_profile {
        padding: 10px 16px !important;
    }
}
</style>

<div class="vendor_wrap el-vendor-event-form-wrapper">

    <!-- 1. Main Dashboard Sidebar -->
    <?php echo el_get_template( 'vendor/sidebar.php' ); ?>

    <!-- 2. Main Content Wrapper -->
    <div class="contents">

        <!-- Sticky Header/Bar (Placed here to span full width of content area) -->
        <div class="profile_sticky_bar">
            <div class="sticky_bar_inner">
                <div class="sticky_bar_left">
                    <h3><?php esc_html_e('Créer une activité', 'eventlist'); ?></h3>
                </div>
                <div class="sticky_bar_right">
                    <!-- Status Indicator -->
                    <div class="event_status_indicator <?php echo $is_published ? 'online' : 'offline'; ?>">
                        <?php if ($is_published) : ?>
                        <!-- Wifi/Online Icon -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
                            <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
                            <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                            <circle cx="12" cy="20" r="1" fill="currentColor"></circle>
                        </svg>
                        <?php else : ?>
                        <!-- Wifi Off Icon -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                            <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
                            <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
                            <path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path>
                            <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
                            <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                            <circle cx="12" cy="20" r="1" fill="currentColor"></circle>
                        </svg>
                        <?php endif; ?>
                        <span><?php echo $is_published ? __('En ligne', 'eventlist') : __('Hors ligne', 'eventlist'); ?></span>
                    </div>

                    <!-- Preview Button -->
                    <a href="<?php echo get_permalink($post_id); ?>" target="_blank" class="btn_preview_profile">
                        <!-- Eye Icon -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span><?php esc_html_e('Prévisualiser', 'eventlist'); ?></span>
                    </a>

                    <?php if ( !empty($post_id) && get_post_status($post_id) ) : ?>
                    <!-- Duplicate Button -->
                    <button type="button" class="btn_duplicate_event" id="el-btn-duplicate" data-post-id="<?php echo esc_attr($post_id); ?>">
                        <!-- Copy Icon -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span><?php esc_html_e('Dupliquer', 'eventlist'); ?></span>
                    </button>
                    <?php wp_nonce_field( 'el_duplicate_post_nonce', 'el_duplicate_post_nonce' ); ?>
                    <?php endif; ?>

                    <!-- Save Button -->
                    <button type="button" class="btn_save_profile" id="el-btn-save">
                        <!-- Save/Check Icon -->
                        <svg class="icon-save" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <!-- Loader Spinner -->
                        <svg class="icon-loader" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20"></circle>
                        </svg>
                        <span><?php esc_html_e('Enregistrer', 'eventlist'); ?></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Inner Flex Container (Sidebar + Content) -->
        <div class="vendor_profile">

            <!-- Inner Sidebar (Navigation) -->
            <div class="profile_navigation_sidebar">

                <!-- Event Header in Sidebar -->
                <div class="profile_user_header">
                    <div class="profile_avatar">
                        <img src="<?php echo esc_url($event_img_url); ?>" alt="<?php echo esc_attr($event_title); ?>">
                    </div>
                    <div class="profile_user_info">
                        <h3><?php echo esc_html($event_title); ?></h3>
                        <span class="event-status-badge"><?php echo get_post_status($post_id); ?></span>
                    </div>
                </div>

                <nav class="profile_tabs_nav el-anchor-nav">
                    <ul>
                        <li class="profile_tab_item active">
                            <a href="#section_general">
                                <i class="icon_document_alt"></i>
                                <span><?php esc_html_e('Informations générales', 'eventlist'); ?></span>
                                <i class="icon_check_alt2 status-icon"></i>
                            </a>
                        </li>
                        <li class="profile_tab_item">
                            <a href="#section_presentation">
                                <i class="icon_image"></i>
                                <span><?php esc_html_e('Présentation', 'eventlist'); ?></span>
                                <i class="icon_check_alt2 status-icon"></i>
                            </a>
                        </li>
                        <li class="profile_tab_item">
                            <a href="#section_localisation">
                                <i class="icon_pin_alt"></i>
                                <span><?php esc_html_e('Localisation', 'eventlist'); ?></span>
                                <i class="icon_check_alt2 status-icon"></i>
                            </a>
                        </li>
                        <li class="profile_tab_item">
                            <a href="#section_calendar">
                                <i class="icon_calendar"></i>
                                <span><?php esc_html_e('Créneaux', 'eventlist'); ?></span>
                                <i class="icon_check_alt2 status-icon"></i>
                            </a>
                        </li>
                        <li class="profile_tab_item">
                            <a href="#section_ticket">
                                <i class="icon_ticket"></i>
                                <span><?php esc_html_e('Billetterie', 'eventlist'); ?></span>
                                <i class="icon_check_alt2 status-icon"></i>
                            </a>
                        </li>
                        <li class="profile_tab_item">
                            <a href="#section_publication">
                                <i class="icon_globe"></i>
                                <span><?php esc_html_e('Publication', 'eventlist'); ?></span>
                                <i class="icon_check_alt2 status-icon"></i>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Sidebar Completion Widget -->
                <div class="sidebar-completion-widget">
                    <h4><?php esc_html_e('Complétion de la fiche', 'eventlist'); ?></h4>
                    <div class="el-completion-bar">
                        <div class="el-completion-fill" id="el-completion-fill-sidebar" style="width: 0%;"></div>
                    </div>
                    <span class="el-completion-percent" id="el-completion-percent-sidebar">0%</span>
                </div>

            </div>

            <!-- Inner Content Area -->
            <div class="profile_content_area">

                <form action="" method="post" id="el-vendor-event-form" enctype="multipart/form-data">

                    <!-- Sections -->
                    <div class="event-form-sections-wrapper">

                        <div id="section_general" class="event_section tab-contents active-section">
                            <?php echo el_get_template( 'vendor/__edit-event-general.php' ); ?>
                        </div>

                        <div id="section_presentation" class="event_section tab-contents active-section">
                            <?php echo el_get_template( 'vendor/__edit-event-presentation.php' ); ?>
                        </div>

                        <div id="section_localisation" class="event_section tab-contents active-section">
                            <?php echo el_get_template( 'vendor/__edit-event-localisation.php' ); ?>
                        </div>

                        <div id="section_calendar" class="event_section tab-contents active-section">
                            <?php echo el_get_template( 'vendor/__edit-event-calendar.php' ); ?>
                        </div>

                        <div id="section_ticket" class="event_section tab-contents active-section">
                            <?php echo el_get_template( 'vendor/__edit-event-ticket.php' ); ?>
                        </div>

                        <div id="section_publication" class="event_section tab-contents active-section">
                            <?php echo el_get_template( 'vendor/__edit-event-publication.php' ); ?>
                        </div>

                    </div>

                    <input type="hidden" name="event_id" value="<?php echo esc_attr($post_id); ?>">
                    <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                    <input type="hidden" name="el_edit_event_nonce" value="<?php echo wp_create_nonce('el_edit_event_nonce'); ?>">
                    <input type="hidden" name="event_status" id="publish_event_input" value="<?php echo get_post_status($post_id); ?>">

                </form>
            </div>

        </div> <!-- End .vendor_profile -->
    </div> <!-- End .contents -->

</div> <!-- End .vendor_wrap -->
