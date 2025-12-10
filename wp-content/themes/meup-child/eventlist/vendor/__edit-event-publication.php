<?php if ( ! defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
$_prefix = OVA_METABOX_EVENT;

// Récupération des données
$the_post = get_post( $post_id );
$event_password = $the_post->post_password ?? '';
$post_status = $the_post->post_status ?? 'draft';

// Déterminer la visibilité
// public = publish sans password
// public_protected = publish avec password
// private = private sans password
// private_protected = private avec password
$visibility = 'public';
if ( $post_status === 'private' && ! empty( $event_password ) ) {
    $visibility = 'private_protected';
} elseif ( $post_status === 'private' ) {
    $visibility = 'private';
} elseif ( ! empty( $event_password ) ) {
    $visibility = 'public_protected';
}

// Statut en ligne / hors ligne
// En ligne = publish ou private (visible)
// Hors ligne = draft, pending, etc.
$is_online = in_array( $post_status, array( 'publish', 'private' ) );

// Extra Services
$el_handicap = get_post_meta( $post_id, $_prefix.'el_handicap', true );
$el_animal   = get_post_meta( $post_id, $_prefix.'el_animal', true );
$el_baby     = get_post_meta( $post_id, $_prefix.'el_baby', true );
$el_wifi     = get_post_meta( $post_id, $_prefix.'el_wifi', true );
$el_parking  = get_post_meta( $post_id, $_prefix.'el_parking', true );
$el_restau   = get_post_meta( $post_id, $_prefix.'el_restau', true );
?>

<div class="publication_section">
    <!-- En-tête -->
    <div class="section_header">
        <h4 class="section_title"><?php esc_html_e( 'Publication', 'eventlist' ); ?></h4>
        <p class="section_subtitle"><?php esc_html_e( 'Choisissez la visibilité et le statut de votre événement', 'eventlist' ); ?></p>
    </div>

    <!-- Options de visibilité -->
    <div class="publication_field">
        <label class="field_label"><strong><?php esc_html_e( 'Cet événement est :', 'eventlist' ); ?></strong></label>

        <div class="visibility_options">
            <!-- Public -->
            <label class="visibility_option <?php echo $visibility === 'public' ? 'selected' : ''; ?>">
                <input type="radio"
                       name="event_visibility"
                       value="public"
                       class="visibility_radio"
                       <?php checked( $visibility, 'public' ); ?>>
                <span class="visibility_checkmark"></span>
                <div class="visibility_content">
                    <span class="visibility_title"><?php esc_html_e( 'Public', 'eventlist' ); ?></span>
                    <span class="visibility_desc"><?php esc_html_e( 'Référencé et accessible à tout le monde sur Le Hiboo', 'eventlist' ); ?></span>
                </div>
            </label>

            <!-- Public et protégé par mot de passe -->
            <label class="visibility_option <?php echo $visibility === 'public_protected' ? 'selected' : ''; ?>">
                <input type="radio"
                       name="event_visibility"
                       value="public_protected"
                       class="visibility_radio"
                       <?php checked( $visibility, 'public_protected' ); ?>>
                <span class="visibility_checkmark"></span>
                <div class="visibility_content">
                    <span class="visibility_title"><?php esc_html_e( 'Public et protégé par mot de passe', 'eventlist' ); ?></span>
                    <span class="visibility_desc"><?php esc_html_e( 'Référencé, mais accessible uniquement via un mot de passe sur Le Hiboo', 'eventlist' ); ?></span>
                </div>
            </label>

            <!-- Champ mot de passe pour Public protégé -->
            <div class="password_field_wrapper" id="public_password_wrapper" style="<?php echo $visibility === 'public_protected' ? '' : 'display: none;'; ?>">
                <label class="password_label"><?php esc_html_e( 'Mot de passe :', 'eventlist' ); ?></label>
                <div class="password_input_wrapper">
                    <input type="password"
                           name="event_password_public"
                           class="password_input"
                           value="<?php echo esc_attr( $visibility === 'public_protected' ? $event_password : '' ); ?>"
                           placeholder="<?php esc_attr_e( '....................', 'eventlist' ); ?>">
                    <button type="button" class="toggle_password" tabindex="-1">
                        <svg class="eye_icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Privé -->
            <label class="visibility_option <?php echo $visibility === 'private' ? 'selected' : ''; ?>">
                <input type="radio"
                       name="event_visibility"
                       value="private"
                       class="visibility_radio"
                       <?php checked( $visibility, 'private' ); ?>>
                <span class="visibility_checkmark"></span>
                <div class="visibility_content">
                    <span class="visibility_title"><?php esc_html_e( 'Privé', 'eventlist' ); ?></span>
                    <span class="visibility_desc"><?php esc_html_e( 'Non référencé sur Le Hiboo. Lien URL de la page à partager.', 'eventlist' ); ?></span>
                </div>
            </label>

            <!-- Privé et protégé par mot de passe -->
            <label class="visibility_option <?php echo $visibility === 'private_protected' ? 'selected' : ''; ?>">
                <input type="radio"
                       name="event_visibility"
                       value="private_protected"
                       class="visibility_radio"
                       <?php checked( $visibility, 'private_protected' ); ?>>
                <span class="visibility_checkmark"></span>
                <div class="visibility_content">
                    <span class="visibility_title"><?php esc_html_e( 'Privé et protégé par mot de passe', 'eventlist' ); ?></span>
                    <span class="visibility_desc"><?php esc_html_e( 'Non référencé sur Le Hiboo, mais accessible uniquement via un mot de passe.', 'eventlist' ); ?></span>
                </div>
            </label>

            <!-- Champ mot de passe pour Privé protégé -->
            <div class="password_field_wrapper" id="private_password_wrapper" style="<?php echo $visibility === 'private_protected' ? '' : 'display: none;'; ?>">
                <label class="password_label"><?php esc_html_e( 'Mot de passe :', 'eventlist' ); ?></label>
                <div class="password_input_wrapper">
                    <input type="password"
                           name="event_password_private"
                           class="password_input"
                           value="<?php echo esc_attr( $visibility === 'private_protected' ? $event_password : '' ); ?>"
                           placeholder="<?php esc_attr_e( '....................', 'eventlist' ); ?>">
                    <button type="button" class="toggle_password" tabindex="-1">
                        <svg class="eye_icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Séparateur -->
    <hr class="publication_separator">

    <!-- Statut En ligne / Hors ligne -->
    <div class="publication_field">
        <label class="field_label"><strong><?php esc_html_e( 'Cet événement est :', 'eventlist' ); ?></strong></label>

        <div class="status_toggle">
            <label class="status_option <?php echo ! $is_online ? 'selected' : ''; ?>">
                <input type="radio"
                       name="event_online_status"
                       value="offline"
                       class="status_radio"
                       <?php checked( $is_online, false ); ?>>
                <span class="status_checkmark"></span>
                <span class="status_label"><?php esc_html_e( 'Hors ligne', 'eventlist' ); ?></span>
            </label>

            <label class="status_option <?php echo $is_online ? 'selected' : ''; ?>">
                <input type="radio"
                       name="event_online_status"
                       value="online"
                       class="status_radio"
                       <?php checked( $is_online, true ); ?>>
                <span class="status_checkmark"></span>
                <span class="status_label"><?php esc_html_e( 'En ligne', 'eventlist' ); ?></span>
            </label>
        </div>
    </div>

    <!-- Champs cachés pour synchroniser avec le formulaire principal -->
    <input type="hidden" name="event_password" id="event_password_hidden" value="<?php echo esc_attr( $event_password ); ?>">
    <input type="hidden" name="event_status" id="event_status_hidden" value="<?php echo esc_attr( $is_online ? ($visibility === 'private' || $visibility === 'private_protected' ? 'private' : 'publish') : 'draft' ); ?>">
</div>

<!-- Services & Accessibilité -->
<div class="services_section">
    <div class="section_header">
        <h4 class="section_title"><?php esc_html_e( 'Services & Accessibilité', 'eventlist' ); ?></h4>
        <p class="section_subtitle"><?php esc_html_e( 'Informez vos participants sur les services disponibles', 'eventlist' ); ?></p>
    </div>

    <div class="services_grid">
        <label class="service_item">
            <input type="checkbox"
                   name="<?php echo esc_attr( $_prefix.'el_handicap' ); ?>"
                   value="yes"
                   class="service_checkbox"
                   <?php checked( $el_handicap, 'yes' ); ?>>
            <span class="service_checkmark"></span>
            <span class="service_icon">♿</span>
            <span class="service_label"><?php esc_html_e( 'Accessible Handicap', 'eventlist' ); ?></span>
        </label>

        <label class="service_item">
            <input type="checkbox"
                   name="<?php echo esc_attr( $_prefix.'el_animal' ); ?>"
                   value="yes"
                   class="service_checkbox"
                   <?php checked( $el_animal, 'yes' ); ?>>
            <span class="service_checkmark"></span>
            <span class="service_icon">🐾</span>
            <span class="service_label"><?php esc_html_e( 'Animaux acceptés', 'eventlist' ); ?></span>
        </label>

        <label class="service_item">
            <input type="checkbox"
                   name="<?php echo esc_attr( $_prefix.'el_baby' ); ?>"
                   value="yes"
                   class="service_checkbox"
                   <?php checked( $el_baby, 'yes' ); ?>>
            <span class="service_checkmark"></span>
            <span class="service_icon">👶</span>
            <span class="service_label"><?php esc_html_e( 'Adapté aux bébés', 'eventlist' ); ?></span>
        </label>

        <label class="service_item">
            <input type="checkbox"
                   name="<?php echo esc_attr( $_prefix.'el_wifi' ); ?>"
                   value="yes"
                   class="service_checkbox"
                   <?php checked( $el_wifi, 'yes' ); ?>>
            <span class="service_checkmark"></span>
            <span class="service_icon">📶</span>
            <span class="service_label"><?php esc_html_e( 'Wifi gratuit', 'eventlist' ); ?></span>
        </label>

        <label class="service_item">
            <input type="checkbox"
                   name="<?php echo esc_attr( $_prefix.'el_parking' ); ?>"
                   value="yes"
                   class="service_checkbox"
                   <?php checked( $el_parking, 'yes' ); ?>>
            <span class="service_checkmark"></span>
            <span class="service_icon">🅿️</span>
            <span class="service_label"><?php esc_html_e( 'Parking sur place', 'eventlist' ); ?></span>
        </label>

        <label class="service_item">
            <input type="checkbox"
                   name="<?php echo esc_attr( $_prefix.'el_restau' ); ?>"
                   value="yes"
                   class="service_checkbox"
                   <?php checked( $el_restau, 'yes' ); ?>>
            <span class="service_checkmark"></span>
            <span class="service_icon">🍽️</span>
            <span class="service_label"><?php esc_html_e( 'Restauration', 'eventlist' ); ?></span>
        </label>
    </div>
</div>

<style>
/* Publication Section */
.publication_section {
    padding: 0;
}

.publication_section .section_header {
    margin-bottom: 30px;
}

.publication_section .section_title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 8px;
}

.publication_section .section_subtitle {
    font-size: 15px;
    color: #64748b;
    margin: 0;
}

/* Field Label */
.publication_field {
    margin-bottom: 30px;
}

.field_label {
    display: block;
    font-size: 15px;
    color: #334155;
    margin-bottom: 16px;
}

/* Visibility Options */
.visibility_options {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.visibility_option {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px 0;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s;
}

.visibility_option:last-of-type {
    border-bottom: none;
}

.visibility_option:hover {
    background: transparent;
}

.visibility_radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.visibility_checkmark {
    width: 24px;
    height: 24px;
    min-width: 24px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    background: #fff;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 2px;
}

.visibility_option.selected .visibility_checkmark {
    background: #FF6600;
    border-color: #FF6600;
}

.visibility_option.selected .visibility_checkmark::after {
    content: '';
    display: block;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin-bottom: 2px;
}

.visibility_content {
    flex: 1;
}

.visibility_title {
    display: block;
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.visibility_desc {
    display: block;
    font-size: 14px;
    color: #64748b;
    line-height: 1.5;
}

/* Password Field */
.password_field_wrapper {
    margin-left: 40px;
    padding: 16px 0 20px;
}

.password_label {
    display: block;
    font-size: 14px;
    color: #64748b;
    margin-bottom: 10px;
}

.password_input_wrapper {
    position: relative;
    max-width: 400px;
}

.password_input {
    width: 100%;
    padding: 14px 50px 14px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    background: #fff;
    transition: all 0.2s;
    letter-spacing: 2px;
}

.password_input:focus {
    outline: none;
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

.toggle_password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    color: #94a3b8;
    transition: color 0.2s;
}

.toggle_password:hover {
    color: #64748b;
}

.eye_icon {
    display: block;
}

/* Separator */
.publication_separator {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 30px 0;
}

/* Status Toggle */
.status_toggle {
    display: flex;
    gap: 40px;
}

.status_option {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.status_radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.status_checkmark {
    width: 24px;
    height: 24px;
    min-width: 24px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    background: #fff;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status_option.selected .status_checkmark {
    background: #FF6600;
    border-color: #FF6600;
}

.status_option.selected .status_checkmark::after {
    content: '';
    display: block;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin-bottom: 2px;
}

.status_label {
    font-size: 15px;
    font-weight: 500;
    color: #334155;
}

/* Services Section */
.services_section {
    margin-top: 50px;
    padding-top: 40px;
    border-top: 1px solid #e2e8f0;
}

.services_section .section_header {
    margin-bottom: 24px;
}

.services_section .section_title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 8px;
}

.services_section .section_subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

/* Services Grid */
.services_grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.service_item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.service_item:hover {
    background: #f1f5f9;
}

.service_item.selected,
.service_item:has(.service_checkbox:checked) {
    background: #fff8f5;
    border-color: #FF6600;
}

.service_checkbox {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.service_checkmark {
    width: 20px;
    height: 20px;
    min-width: 20px;
    border: 2px solid #d1d5db;
    border-radius: 5px;
    background: #fff;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.service_item:has(.service_checkbox:checked) .service_checkmark {
    background: #FF6600;
    border-color: #FF6600;
}

.service_item:has(.service_checkbox:checked) .service_checkmark::after {
    content: '';
    display: block;
    width: 5px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin-bottom: 2px;
}

.service_icon {
    font-size: 20px;
}

.service_label {
    font-size: 14px;
    font-weight: 500;
    color: #334155;
}

/* Responsive */
@media (max-width: 768px) {
    .services_grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .status_toggle {
        flex-direction: column;
        gap: 16px;
    }

    .password_field_wrapper {
        margin-left: 0;
    }
}

@media (max-width: 480px) {
    .services_grid {
        grid-template-columns: 1fr;
    }
}

/* ================================================
   Header Status Button Styles (Sticky Bar)
   ================================================ */

/* Base styles - override plugin defaults */
.event_status_indicator {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    padding: 10px 20px !important;
    border-radius: 25px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer;
    transition: all 0.25s ease;
    border: 2px solid transparent;
}

/* Online state - Green */
.event_status_indicator.online {
    background: #3CB371 !important;
    color: #fff !important;
    border-color: #3CB371 !important;
}

.event_status_indicator.online i {
    color: #fff !important;
}

.event_status_indicator.online:hover {
    background: #2E8B57 !important;
    border-color: #2E8B57 !important;
}

/* Offline state - Gray outline */
.event_status_indicator.offline {
    background: #fff !important;
    color: #334155 !important;
    border-color: #d1d5db !important;
}

.event_status_indicator.offline i {
    color: #64748b !important;
}

.event_status_indicator.offline:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
}

/* Power icon style */
.event_status_indicator i.icon_off,
.event_status_indicator i.fa-power-off {
    font-size: 14px;
}
</style>

<script>
jQuery(document).ready(function($) {
    var PublicationManager = {
        init: function() {
            this.bindEvents();
            this.updateHeaderButton();
        },

        bindEvents: function() {
            var self = this;

            // Changement de visibilité
            $(document).on('change', '.visibility_radio', function() {
                self.handleVisibilityChange($(this));
            });

            // Changement de statut en ligne/hors ligne
            $(document).on('change', '.status_radio', function() {
                self.handleStatusChange($(this));
            });

            // Toggle password visibility
            $(document).on('click', '.toggle_password', function(e) {
                e.preventDefault();
                self.togglePasswordVisibility($(this));
            });

            // Synchroniser le mot de passe
            $(document).on('input', '.password_input', function() {
                self.syncPassword();
            });

            // Services checkbox
            $(document).on('change', '.service_checkbox', function() {
                var $item = $(this).closest('.service_item');
                if ($(this).is(':checked')) {
                    $item.addClass('selected');
                } else {
                    $item.removeClass('selected');
                }
            });

            // Clic sur le bouton de statut dans la barre flottante
            $(document).on('click', '.event_status_indicator', function(e) {
                e.preventDefault();
                self.toggleOnlineStatus();
            });
        },

        toggleOnlineStatus: function() {
            var isCurrentlyOnline = $('input[name="event_online_status"]:checked').val() === 'online';

            if (isCurrentlyOnline) {
                // Passer hors ligne
                $('input[name="event_online_status"][value="offline"]').prop('checked', true).trigger('change');
            } else {
                // Passer en ligne
                $('input[name="event_online_status"][value="online"]').prop('checked', true).trigger('change');
            }
        },

        handleVisibilityChange: function($radio) {
            var value = $radio.val();

            // Mettre à jour la sélection visuelle
            $('.visibility_option').removeClass('selected');
            $radio.closest('.visibility_option').addClass('selected');

            // Afficher/masquer les champs de mot de passe
            $('#public_password_wrapper').hide();
            $('#private_password_wrapper').hide();

            if (value === 'public_protected') {
                $('#public_password_wrapper').slideDown(200);
            } else if (value === 'private_protected') {
                $('#private_password_wrapper').slideDown(200);
            }

            // Mettre à jour le champ caché event_status du formulaire principal
            this.updateEventStatus();
            this.syncPassword();
        },

        handleStatusChange: function($radio) {
            var value = $radio.val();

            // Mettre à jour la sélection visuelle
            $('.status_option').removeClass('selected');
            $radio.closest('.status_option').addClass('selected');

            // Mettre à jour le bouton dans la barre flottante
            this.updateHeaderButton();

            // Mettre à jour le champ caché event_status
            this.updateEventStatus();
        },

        updateEventStatus: function() {
            var visibility = $('input[name="event_visibility"]:checked').val();
            var isOnline = $('input[name="event_online_status"]:checked').val() === 'online';

            var status = 'draft'; // Par défaut hors ligne

            if (isOnline) {
                if (visibility === 'private' || visibility === 'private_protected') {
                    status = 'private';
                } else {
                    status = 'publish';
                }
            }

            // Mettre à jour les champs cachés du formulaire principal
            $('#publish_event_input').val(status);
            $('#event_status_hidden').val(status);

            // Mettre à jour le badge de statut dans la sidebar
            $('.event-status-badge').text(status);
        },

        syncPassword: function() {
            var visibility = $('input[name="event_visibility"]:checked').val();
            var password = '';

            if (visibility === 'public_protected') {
                password = $('input[name="event_password_public"]').val();
            } else if (visibility === 'private_protected') {
                password = $('input[name="event_password_private"]').val();
            }

            $('#event_password_hidden').val(password);
        },

        togglePasswordVisibility: function($btn) {
            var $input = $btn.siblings('.password_input');
            var type = $input.attr('type');

            if (type === 'password') {
                $input.attr('type', 'text');
                $btn.find('.eye_icon').css('opacity', '0.5');
            } else {
                $input.attr('type', 'password');
                $btn.find('.eye_icon').css('opacity', '1');
            }
        },

        updateHeaderButton: function() {
            var isOnline = $('input[name="event_online_status"]:checked').val() === 'online';
            var $indicator = $('.event_status_indicator');

            if (isOnline) {
                $indicator.removeClass('offline').addClass('online');
                $indicator.find('span').text('<?php echo esc_js( __( 'En ligne', 'eventlist' ) ); ?>');
            } else {
                $indicator.removeClass('online').addClass('offline');
                $indicator.find('span').text('<?php echo esc_js( __( 'Hors ligne', 'eventlist' ) ); ?>');
            }
        }
    };

    PublicationManager.init();

    // Initialiser les checkboxes de services qui sont cochées
    $('.service_checkbox:checked').each(function() {
        $(this).closest('.service_item').addClass('selected');
    });

    // Synchroniser le mot de passe et le statut au chargement
    PublicationManager.syncPassword();
    PublicationManager.updateEventStatus();

    // Scroll vers la section publication si hash présent
    if (window.location.hash === '#section_publication') {
        setTimeout(function() {
            $('html, body').animate({
                scrollTop: $('#section_publication').offset().top - 100
            }, 300);
        }, 300);
    }
});
</script>
