<?php if ( ! defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
$_prefix = OVA_METABOX_EVENT;

// Vérifier si le vendor peut publier (documents validés)
$current_vendor_id = get_current_user_id();
$can_vendor_publish = true;
if ( class_exists( 'LeHiboo_Vendor_Onboarding' ) ) {
    $can_vendor_publish = LeHiboo_Vendor_Onboarding::can_vendor_publish( $current_vendor_id );
}

// IMPORTANT: Pour un nouvel événement (pas de post_id), forcer les valeurs par défaut
if ( empty( $post_id ) || $post_id === 0 ) {
    $event_password = '';
    $post_status = 'draft';
    $is_online = false;
    $visibility = 'public';
} else {
    // Récupération des données pour un événement existant
    $the_post = get_post( $post_id );
    $event_password = $the_post->post_password ?? '';
    $post_status = $the_post->post_status ?? 'draft';

    // Statut en ligne / hors ligne
    // En ligne = publish ou private (visible)
    // Hors ligne = draft, pending, etc.
    $is_online = in_array( $post_status, array( 'publish', 'private' ) );

    // Déterminer la visibilité
    $visibility = 'public';
    if ( $post_status === 'publish' && ! empty( $event_password ) ) {
        $visibility = 'public_protected';
    } elseif ( $post_status === 'private' && ! empty( $event_password ) ) {
        $visibility = 'private_protected';
    } elseif ( $post_status === 'private' ) {
        $visibility = 'private';
    }
}

// Si le vendor ne peut pas publier, forcer hors ligne
if ( ! $can_vendor_publish ) {
    $is_online = false;
}
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

        <?php if ( ! $can_vendor_publish ) : ?>
        <div class="publication_warning">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span><?php esc_html_e( 'Vos documents doivent être validés avant de pouvoir mettre une activité en ligne.', 'eventlist' ); ?></span>
            <a href="<?php echo esc_url( add_query_arg( 'vendor', 'documents', wc_get_account_endpoint_url( 'vendor' ) ) ); ?>"><?php esc_html_e( 'Voir mes documents', 'eventlist' ); ?></a>
        </div>
        <?php endif; ?>

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

            <label class="status_option <?php echo $is_online ? 'selected' : ''; ?> <?php echo ! $can_vendor_publish ? 'disabled' : ''; ?>">
                <input type="radio"
                       name="event_online_status"
                       value="online"
                       class="status_radio"
                       <?php checked( $is_online, true ); ?>
                       <?php echo ! $can_vendor_publish ? 'disabled' : ''; ?>>
                <span class="status_checkmark"></span>
                <span class="status_label"><?php esc_html_e( 'En ligne', 'eventlist' ); ?></span>
            </label>
        </div>
    </div>

    <!-- Champs cachés pour synchroniser avec le formulaire principal -->
    <input type="hidden" name="event_password" id="event_password_hidden" value="<?php echo esc_attr( $event_password ); ?>">
    <input type="hidden" name="event_status" id="event_status_hidden" value="<?php echo esc_attr( $is_online ? ($visibility === 'private' || $visibility === 'private_protected' ? 'private' : 'publish') : 'draft' ); ?>">
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

/* Publication Warning */
.publication_warning {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #92400e;
}

.publication_warning svg {
    flex-shrink: 0;
    color: #f59e0b;
}

.publication_warning span {
    flex: 1;
}

.publication_warning a {
    color: #d97706;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}

.publication_warning a:hover {
    text-decoration: underline;
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

/* Disabled state for status option */
.status_option.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.status_option.disabled .status_checkmark {
    background: #f1f5f9;
    border-color: #e2e8f0;
}

.status_option.disabled .status_label {
    color: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
    .status_toggle {
        flex-direction: column;
        gap: 16px;
    }

    .password_field_wrapper {
        margin-left: 0;
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

<!-- Modal de validation -->
<div id="publication_validation_modal" class="validation_modal_overlay" style="display: none;">
    <div class="validation_modal">
        <div class="validation_modal_header">
            <h3><?php esc_html_e( 'Informations manquantes', 'eventlist' ); ?></h3>
            <button type="button" class="validation_modal_close">&times;</button>
        </div>
        <div class="validation_modal_body">
            <p class="validation_modal_intro"><?php esc_html_e( 'Pour mettre votre activité en ligne, veuillez compléter les informations suivantes :', 'eventlist' ); ?></p>

            <div id="profile_errors_section" style="display: none;">
                <h4><i class="fas fa-user"></i> <?php esc_html_e( 'Profil partenaire', 'eventlist' ); ?></h4>
                <ul id="profile_errors_list"></ul>
                <a href="<?php echo esc_url( add_query_arg( 'vendor', 'profile', get_myaccount_page() ) ); ?>" class="validation_link">
                    <?php esc_html_e( 'Compléter mon profil', 'eventlist' ); ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div id="documents_errors_section" style="display: none;">
                <h4><i class="fas fa-folder"></i> <?php esc_html_e( 'Documents', 'eventlist' ); ?></h4>
                <ul id="documents_errors_list"></ul>
                <a href="<?php echo esc_url( add_query_arg( 'vendor', 'documents', get_myaccount_page() ) ); ?>" class="validation_link">
                    <?php esc_html_e( 'Gérer mes documents', 'eventlist' ); ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div id="activity_errors_section" style="display: none;">
                <h4><i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Fiche activité', 'eventlist' ); ?></h4>
                <ul id="activity_errors_list"></ul>
            </div>
        </div>
        <div class="validation_modal_footer">
            <button type="button" class="validation_modal_btn_close"><?php esc_html_e( 'Fermer', 'eventlist' ); ?></button>
        </div>
    </div>
</div>

<style>
/* Modal Validation */
.validation_modal_overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.validation_modal {
    background: #fff;
    border-radius: 16px;
    max-width: 550px;
    width: 100%;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.validation_modal_header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    background: #fef3c7;
}

.validation_modal_header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #92400e;
    display: flex;
    align-items: center;
    gap: 10px;
}

.validation_modal_header h3::before {
    content: '⚠️';
}

.validation_modal_close {
    background: none;
    border: none;
    font-size: 28px;
    color: #92400e;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.validation_modal_close:hover {
    opacity: 1;
}

.validation_modal_body {
    padding: 24px;
    overflow-y: auto;
    max-height: 50vh;
}

.validation_modal_intro {
    margin: 0 0 20px;
    color: #4b5563;
    font-size: 14px;
}

.validation_modal_body h4 {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin: 20px 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
}

.validation_modal_body h4:first-of-type {
    margin-top: 0;
}

.validation_modal_body h4 i {
    color: #FF601F;
    font-size: 14px;
}

.validation_modal_body ul {
    margin: 0 0 12px;
    padding: 0;
    list-style: none;
}

.validation_modal_body ul li {
    padding: 8px 0 8px 24px;
    position: relative;
    color: #dc2626;
    font-size: 14px;
}

.validation_modal_body ul li::before {
    content: '✕';
    position: absolute;
    left: 0;
    color: #dc2626;
    font-weight: bold;
    font-size: 12px;
}

.validation_link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #FF601F;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    margin-top: 4px;
}

.validation_link:hover {
    text-decoration: underline;
}

.validation_modal_footer {
    padding: 16px 24px;
    border-top: 1px solid #e5e7eb;
    text-align: right;
}

.validation_modal_btn_close {
    background: #FF601F;
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.validation_modal_btn_close:hover {
    background: #e5561c;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Variable globale pour savoir si le vendor peut publier
    var canVendorPublish = <?php echo $can_vendor_publish ? 'true' : 'false'; ?>;

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
                var value = $(this).val();
                if (value === 'online') {
                    // Valider avant de passer en ligne
                    self.validateBeforeOnline($(this));
                } else {
                    self.handleStatusChange($(this));
                }
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

            // Clic sur le bouton de statut dans la barre flottante
            $(document).on('click', '.event_status_indicator', function(e) {
                e.preventDefault();
                self.toggleOnlineStatus();
            });

            // Fermer le modal
            $(document).on('click', '.validation_modal_close, .validation_modal_btn_close, .validation_modal_overlay', function(e) {
                if (e.target === this) {
                    self.closeModal();
                }
            });

            // Empêcher la fermeture en cliquant sur le modal
            $(document).on('click', '.validation_modal', function(e) {
                e.stopPropagation();
            });
        },

        validateBeforeOnline: function($radio) {
            var self = this;
            var activityErrors = this.validateActivityFields();

            // Appel AJAX pour valider le profil
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'lehiboo_validate_publication',
                    nonce: $('input[name="el_edit_event_nonce"]').val()
                },
                success: function(response) {
                    var profileErrors = [];
                    var documentErrors = [];

                    if (response.success && response.data.profile_errors) {
                        response.data.profile_errors.forEach(function(err) {
                            if (err.section === 'documents') {
                                documentErrors.push(err);
                            } else {
                                profileErrors.push(err);
                            }
                        });
                    }

                    var hasErrors = profileErrors.length > 0 || documentErrors.length > 0 || activityErrors.length > 0;

                    if (hasErrors) {
                        self.showValidationModal(profileErrors, documentErrors, activityErrors);
                        // Remettre sur "Hors ligne"
                        $('input[name="event_online_status"][value="offline"]').prop('checked', true);
                        $('.status_option').removeClass('selected');
                        $('input[name="event_online_status"][value="offline"]').closest('.status_option').addClass('selected');
                    } else {
                        // Tout est OK, passer en ligne
                        self.handleStatusChange($radio);
                    }
                },
                error: function() {
                    // En cas d'erreur AJAX, vérifier au moins les champs activité
                    if (activityErrors.length > 0) {
                        self.showValidationModal([], [], activityErrors);
                        $('input[name="event_online_status"][value="offline"]').prop('checked', true);
                        $('.status_option').removeClass('selected');
                        $('input[name="event_online_status"][value="offline"]').closest('.status_option').addClass('selected');
                    } else {
                        self.handleStatusChange($radio);
                    }
                }
            });
        },

        validateActivityFields: function() {
            var errors = [];

            // 1. Nom de l'activité
            var name = $('input[name="name_event"]').val();
            if (!name || name.trim() === '') {
                errors.push({ field: "Nom de l'activité", message: "Le nom de l'activité est obligatoire" });
            }

            // 2. Catégorie
            var category = $('select[name="event_cat"]').val();
            if (!category || category === '' || category === '0') {
                errors.push({ field: "Catégorie", message: "La catégorie est obligatoire" });
            }

            // 3. Type d'événement
            var eventTag = $('select[name="event_tag[]"]').val();
            if (!eventTag || eventTag.length === 0) {
                errors.push({ field: "Type d'événement", message: "Le type d'événement est obligatoire" });
            }

            // 4. Public visé
            var eventPublic = $('select[name="event_public[]"]').val();
            if (!eventPublic || eventPublic.length === 0) {
                errors.push({ field: "Public visé", message: "Le public visé est obligatoire" });
            }

            // 5. Type de lieu et adresse
            var locationType = $('input[name="ova_mb_event_type_location"]:checked, input[name="type_location"]:checked').val();
            if (locationType === 'has_location' || locationType === '1') {
                var address = $('input[name="ova_mb_event_map_address"]').val() || $('input[name="map_address"]').val();
                if (!address || address.trim() === '') {
                    errors.push({ field: "Adresse", message: "L'adresse est obligatoire pour un lieu physique" });
                }
            }

            // 6. Au moins 1 créneau
            var hasSlot = $('.calendar_item, .schedule-item, .ticket_item').length > 0;
            var hasValidSlot = false;
            $('.calendar_item, .ticket_item').each(function() {
                var dateVal = $(this).find('input[type="date"], input[name*="date"]').val();
                if (dateVal && dateVal.trim() !== '') {
                    hasValidSlot = true;
                    return false;
                }
            });
            if (!hasValidSlot) {
                errors.push({ field: "Créneau", message: "Au moins un créneau est obligatoire" });
            }

            // 7. Gratuit ou Payant - vérifier si un tarif existe
            // On suppose que si des tickets existent, c'est OK

            // 8. Description >= 100 caractères
            var descContent = '';
            if (typeof tinymce !== 'undefined' && tinymce.get('content_event')) {
                descContent = tinymce.get('content_event').getContent({ format: 'text' });
            } else {
                descContent = $('#content_event').val() || '';
                descContent = descContent.replace(/<[^>]*>/g, '');
            }
            descContent = descContent.replace(/\s+/g, ' ').trim();
            if (descContent.length < 100) {
                errors.push({ field: "Description", message: "La description doit contenir au moins 100 caractères (actuellement : " + descContent.length + ")" });
            }

            // 9. Image de présentation
            var thumbnail = $('input[name="img_thumbnail"]').val();
            if (!thumbnail || thumbnail === '' || thumbnail === '0') {
                errors.push({ field: "Image de présentation", message: "L'image de présentation est obligatoire" });
            }

            return errors;
        },

        showValidationModal: function(profileErrors, documentErrors, activityErrors) {
            // Réinitialiser les listes
            $('#profile_errors_list, #documents_errors_list, #activity_errors_list').empty();
            $('#profile_errors_section, #documents_errors_section, #activity_errors_section').hide();

            // Afficher les erreurs profil
            if (profileErrors.length > 0) {
                profileErrors.forEach(function(err) {
                    $('#profile_errors_list').append('<li>' + err.field + '</li>');
                });
                $('#profile_errors_section').show();
            }

            // Afficher les erreurs documents
            if (documentErrors.length > 0) {
                documentErrors.forEach(function(err) {
                    $('#documents_errors_list').append('<li>' + err.message + '</li>');
                });
                $('#documents_errors_section').show();
            }

            // Afficher les erreurs activité
            if (activityErrors.length > 0) {
                activityErrors.forEach(function(err) {
                    $('#activity_errors_list').append('<li>' + err.field + '</li>');
                });
                $('#activity_errors_section').show();
            }

            // Afficher le modal
            $('#publication_validation_modal').fadeIn(200);
            $('body').css('overflow', 'hidden');
        },

        closeModal: function() {
            $('#publication_validation_modal').fadeOut(200);
            $('body').css('overflow', '');
        },

        toggleOnlineStatus: function() {
            var self = this;
            var isCurrentlyOnline = $('input[name="event_online_status"]:checked').val() === 'online';

            if (isCurrentlyOnline) {
                // Passer hors ligne - pas besoin de validation
                $('input[name="event_online_status"][value="offline"]').prop('checked', true).trigger('change');
            } else {
                // Passer en ligne - déclencher la validation
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

    // Synchroniser le mot de passe, le statut et le bouton au chargement
    PublicationManager.syncPassword();
    PublicationManager.updateEventStatus();
    PublicationManager.updateHeaderButton();

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
