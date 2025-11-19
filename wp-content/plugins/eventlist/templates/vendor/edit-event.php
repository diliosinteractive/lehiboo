<?php if ( !defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';

// Get Event Data for Sidebar Header
$event_title = get_the_title($post_id);
if(empty($event_title)) $event_title = __('Nouvel événement', 'eventlist');
$event_img_id = get_post_thumbnail_id($post_id);
$event_img_url = $event_img_id ? wp_get_attachment_image_url($event_img_id, 'thumbnail') : EL_PLUGIN_URI . 'assets/img/placeholder.png';

?>

<!-- Custom Assets for Event Form -->
<link rel="stylesheet" href="<?php echo EL_PLUGIN_URI . 'assets/css/vendor-event-form.css'; ?>">
<script src="<?php echo EL_PLUGIN_URI . 'assets/js/vendor-event-form.js'; ?>" defer></script>

<div class="vendor_wrap el-vendor-event-form-wrapper">
    
    <!-- 1. Main Dashboard Sidebar -->
    <?php echo el_get_template( 'vendor/sidebar.php' ); ?>

    <!-- 2. Main Content Wrapper -->
    <div class="contents">
        
        <!-- Sticky Header/Bar (Placed here to span full width of content area) -->
        <div class="profile_sticky_bar">
            <div class="sticky_bar_inner">
                <div class="sticky_bar_left">
                    <h3><?php esc_html_e('Éditer l\'activité', 'eventlist'); ?></h3>
                </div>
                <div class="sticky_bar_right">
                    <a href="<?php echo get_permalink($post_id); ?>" target="_blank" class="btn_preview_profile">
                        <i class="icon_search"></i>
                        <span><?php esc_html_e('Prévisualiser', 'eventlist'); ?></span>
                    </a>
                    <button type="button" class="btn_save_profile" id="el-btn-save">
                        <i class="icon_check"></i>
                        <span><?php esc_html_e('Enregistrer', 'eventlist'); ?></span>
                    </button>
                    <button type="button" class="btn_save_profile btn_publish" id="el-btn-go-live" disabled>
                        <i class="icon_cloud-upload_alt"></i>
                        <span><?php esc_html_e('Mettre en ligne', 'eventlist'); ?></span>
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
                    <input type="hidden" name="el_update_event_nonce" value="<?php echo wp_create_nonce('el_update_event_nonce'); ?>">
                    <input type="hidden" name="post_status" id="publish_event_input" value="<?php echo get_post_status($post_id); ?>">

                </form>
            </div>

        </div> <!-- End .vendor_profile -->
    </div> <!-- End .contents -->

</div> <!-- End .vendor_wrap -->

<script type="text/javascript">
    // Handle Go Live
    document.getElementById('el-btn-go-live').addEventListener('click', function() {
        document.getElementById('publish_event_input').value = 'publish';
        document.getElementById('el-vendor-event-form').submit();
    });
    // Handle Save
    document.getElementById('el-btn-save').addEventListener('click', function() {
        document.getElementById('el-vendor-event-form').submit();
    });
</script>