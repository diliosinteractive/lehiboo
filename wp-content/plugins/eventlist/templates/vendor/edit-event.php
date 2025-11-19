<?php if ( !defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';


?>

<!-- Custom Assets for Event Form -->
<link rel="stylesheet" href="<?php echo EL_PLUGIN_URI . 'assets/css/vendor-event-form.css'; ?>">
<script src="<?php echo EL_PLUGIN_URI . 'assets/js/vendor-event-form.js'; ?>" defer></script>

<div class="el-vendor-event-form-wrapper">
    
    <form action="" method="post" id="el-vendor-event-form" enctype="multipart/form-data">

        <div class="el-vendor-layout">
            
            <!-- Sidebar Navigation -->
            <aside class="el-vendor-sidebar">
                <nav class="el-anchor-nav">
                    <ul>
                        <li><a href="#section-general" class="active"><?php esc_html_e( 'Informations générales', 'eventlist' ); ?></a></li>
                        <li><a href="#section-presentation"><?php esc_html_e( 'Présentation', 'eventlist' ); ?></a></li>
                        <li><a href="#section-localisation"><?php esc_html_e( 'Localisation', 'eventlist' ); ?></a></li>
                        <li><a href="#section-calendar"><?php esc_html_e( 'Créneaux', 'eventlist' ); ?></a></li>
                        <li><a href="#section-ticket"><?php esc_html_e( 'Billetterie', 'eventlist' ); ?></a></li>
                        <li><a href="#section-publication"><?php esc_html_e( 'Publication', 'eventlist' ); ?></a></li>
                        <!-- Future: Co-organizers -->
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="el-vendor-content">
                
                <!-- Section: General Info -->
                <section id="section-general" class="el-form-section">
                    <?php echo el_get_template( 'vendor/__edit-event-general.php', array( 'post_id' => $post_id, 'event_req_field' => $event_req_field ) ); ?>
                </section>

                <!-- Section: Presentation -->
                <section id="section-presentation" class="el-form-section">
                    <?php echo el_get_template( 'vendor/__edit-event-presentation.php', array( 'post_id' => $post_id, 'event_req_field' => $event_req_field ) ); ?>
                </section>

                <!-- Section: Localisation -->
                <section id="section-localisation" class="el-form-section">
                    <?php echo el_get_template( 'vendor/__edit-event-localisation.php', array( 'post_id' => $post_id, 'event_req_field' => $event_req_field ) ); ?>
                </section>

                <!-- Section: Calendar -->
                <section id="section-calendar" class="el-form-section">
                    <?php echo el_get_template( 'vendor/__edit-event-calendar.php', array( 'post_id' => $post_id, 'event_req_field' => $event_req_field ) ); ?>
                </section>

                <!-- Section: Ticket -->
                <section id="section-ticket" class="el-form-section">
                    <?php echo el_get_template( 'vendor/__edit-event-ticket.php', array( 'post_id' => $post_id, 'event_req_field' => $event_req_field ) ); ?>
                </section>

                <!-- Section: Publication -->
                <section id="section-publication" class="el-form-section">
                    <?php echo el_get_template( 'vendor/__edit-event-publication.php', array( 'post_id' => $post_id, 'event_req_field' => $event_req_field ) ); ?>
                </section>

                <?php wp_nonce_field( 'el_event_action', 'el_event_nonce' ); ?>
                <input type="hidden" name="el_event_action" value="edit_event" />
                <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />

            </main>

        </div>

        <!-- Sticky Footer Actions -->
        <div class="el-vendor-sticky-footer">
            <div class="el-completion-gauge-wrapper">
                <div class="el-gauge-label"><?php esc_html_e( 'Complétion de la fiche', 'eventlist' ); ?></div>
                <div class="el-gauge-bar">
                    <div class="el-gauge-fill" style="width: 0%;" id="el-completion-fill"></div>
                </div>
                <div class="el-gauge-percent" id="el-completion-percent">0%</div>
            </div>

            <div class="el-form-actions">
                <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" target="_blank" class="button el-btn-preview">
                    <?php esc_html_e( 'Prévisualiser', 'eventlist' ); ?>
                </a>
                
                <button type="submit" name="save_draft" class="button el-btn-save">
                    <?php esc_html_e( 'Enregistrer', 'eventlist' ); ?>
                </button>

                <button type="button" id="el-btn-go-live" class="button el-btn-publish" disabled>
                    <?php esc_html_e( 'Mettre en ligne', 'eventlist' ); ?>
                </button>
                <input type="hidden" name="publish_event" id="publish_event_input" value="0">
            </div>
        </div>

    </form>
</div>

<script type="text/javascript">
    // Simple inline script to handle Go Live button click for now
    document.getElementById('el-btn-go-live').addEventListener('click', function() {
        document.getElementById('publish_event_input').value = '1';
        document.getElementById('el-vendor-event-form').submit();
    });
</script>