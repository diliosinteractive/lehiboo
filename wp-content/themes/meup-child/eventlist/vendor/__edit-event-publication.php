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
        <button type="button" class="validation_modal_close">&times;</button>
        <div class="validation_modal_icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="validation_modal_header">
            <h3><?php esc_html_e( 'Informations manquantes', 'eventlist' ); ?></h3>
            <p class="validation_modal_intro"><?php esc_html_e( 'Pour mettre votre activité en ligne, veuillez compléter les éléments suivants :', 'eventlist' ); ?></p>
        </div>
        <div class="validation_modal_body">
            <div id="profile_errors_section" class="validation_section" style="display: none;">
                <div class="validation_section_header">
                    <i class="fas fa-user-circle"></i>
                    <span><?php esc_html_e( 'Profil partenaire', 'eventlist' ); ?></span>
                </div>
                <ul id="profile_errors_list"></ul>
                <a href="<?php echo esc_url( add_query_arg( 'vendor', 'profile', get_myaccount_page() ) ); ?>" class="validation_link">
                    <?php esc_html_e( 'Compléter mon profil', 'eventlist' ); ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div id="documents_errors_section" class="validation_section" style="display: none;">
                <div class="validation_section_header">
                    <i class="fas fa-folder-open"></i>
                    <span><?php esc_html_e( 'Documents', 'eventlist' ); ?></span>
                </div>
                <ul id="documents_errors_list"></ul>
                <a href="<?php echo esc_url( add_query_arg( 'vendor', 'documents', get_myaccount_page() ) ); ?>" class="validation_link">
                    <?php esc_html_e( 'Gérer mes documents', 'eventlist' ); ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div id="activity_errors_section" class="validation_section" style="display: none;">
                <div class="validation_section_header">
                    <i class="fas fa-calendar-check"></i>
                    <span><?php esc_html_e( 'Fiche activité', 'eventlist' ); ?></span>
                </div>
                <ul id="activity_errors_list"></ul>
            </div>
        </div>
        <div class="validation_modal_footer">
            <button type="button" class="validation_modal_btn_close"><?php esc_html_e( 'J\'ai compris', 'eventlist' ); ?></button>
        </div>
    </div>
</div>

<style>
/* Modal Validation - Design amélioré */
.validation_modal_overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.validation_modal {
    background: #fff;
    border-radius: 16px;
    max-width: 440px;
    width: 100%;
    max-height: 85vh;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: modalSlideIn 0.25s ease-out;
    position: relative;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.96) translateY(-8px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.validation_modal_close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: transparent;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 22px;
    color: #9ca3af;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    z-index: 10;
    line-height: 1;
}

.validation_modal_close:hover {
    background: #f3f4f6;
    color: #6b7280;
}

.validation_modal_icon {
    display: flex;
    justify-content: center;
    padding: 28px 24px 12px;
}

.validation_modal_icon svg {
    color: #FF601F;
    width: 56px;
    height: 56px;
    padding: 12px;
    background: #FFF5F0;
    border-radius: 50%;
}

.validation_modal_header {
    text-align: center;
    padding: 0 24px 16px;
}

.validation_modal_header h3 {
    margin: 0 0 6px;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

.validation_modal_intro {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}

.validation_modal_body {
    padding: 0 20px;
    overflow-y: auto;
    max-height: 45vh;
}

.validation_section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 10px;
}

.validation_section:last-child {
    margin-bottom: 0;
}

.validation_section_header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.validation_section_header i {
    color: #FF601F;
    font-size: 15px;
    width: 28px;
    height: 28px;
    background: #FFF5F0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.validation_modal_body ul {
    margin: 0 0 10px;
    padding: 0;
    list-style: none;
}

.validation_modal_body ul li {
    padding: 5px 0 5px 18px;
    position: relative;
    color: #ef4444;
    font-size: 13px;
    font-weight: 500;
}

.validation_modal_body ul li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 5px;
    height: 5px;
    background: #ef4444;
    border-radius: 50%;
}

.validation_link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #FF601F;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    padding: 4px 0;
    transition: gap 0.2s;
}

.validation_link:hover {
    gap: 8px;
    text-decoration: none;
}

.validation_link i {
    font-size: 10px;
}

.validation_modal_footer {
    padding: 16px 20px 20px;
    text-align: center;
}

.validation_modal_btn_close {
    background: linear-gradient(135deg, #FF601F 0%, #e5561c 100%);
    color: #fff;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
    box-shadow: 0 2px 8px rgba(255, 96, 31, 0.3);
}

.validation_modal_btn_close:hover {
    background: linear-gradient(135deg, #e5561c 0%, #d14a10 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 96, 31, 0.4);
}

/* Champs avec erreur de validation */
.validation_field_error {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}

.validation_field_error + .select2-container .select2-selection,
.select2-container--default .select2-selection.validation_field_error {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}

/* Label avec erreur */
.validation_label_error {
    color: #ef4444 !important;
}

/* Responsive */
@media (max-width: 480px) {
    .validation_modal {
        max-width: calc(100vw - 32px);
        border-radius: 12px;
    }
    .validation_modal_icon svg {
        width: 48px;
        height: 48px;
        padding: 10px;
    }
    .validation_modal_header h3 {
        font-size: 16px;
    }
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
            var self = this;
            var errors = [];

            // Réinitialiser les erreurs visuelles
            self.clearFieldErrors();

            // Helper pour obtenir la valeur Select2
            function getSelect2Value($select) {
                if ($select.length === 0) return null;
                // Essayer Select2 d'abord
                if ($select.hasClass('select2-hidden-accessible')) {
                    return $select.select2('data');
                }
                return $select.val();
            }

            // 1. Nom de l'activité
            var $nameField = $('input[name="name_event"]');
            var name = $nameField.val();
            if (!name || name.trim() === '') {
                errors.push({ field: "Nom de l'activité", selector: 'input[name="name_event"]' });
                self.markFieldError($nameField);
            }

            // 2. Catégorie
            var $catField = $('select[name="event_cat"]');
            var category = $catField.val();
            if (!category || category === '' || category === '0') {
                errors.push({ field: "Catégorie", selector: 'select[name="event_cat"]' });
                self.markFieldError($catField);
            }

            // 3. Type d'événement (Select2 multi)
            var $tagField = $('select[name="event_tag[]"]');
            var eventTagData = getSelect2Value($tagField);
            var hasEventTag = false;
            if (Array.isArray(eventTagData) && eventTagData.length > 0) {
                // Select2 data format
                hasEventTag = eventTagData.some(function(item) {
                    return item.id && item.id !== '';
                });
            } else if (eventTagData && eventTagData.length > 0) {
                // Regular val()
                hasEventTag = true;
            }
            if (!hasEventTag) {
                errors.push({ field: "Type d'événement", selector: 'select[name="event_tag[]"]' });
                self.markFieldError($tagField);
            }

            // 4. Public visé (Select2 multi)
            var $publicField = $('select[name="event_public[]"]');
            var eventPublicData = getSelect2Value($publicField);
            var hasEventPublic = false;
            if (Array.isArray(eventPublicData) && eventPublicData.length > 0) {
                hasEventPublic = eventPublicData.some(function(item) {
                    return item.id && item.id !== '';
                });
            } else if (eventPublicData && eventPublicData.length > 0) {
                hasEventPublic = true;
            }
            if (!hasEventPublic) {
                errors.push({ field: "Public visé", selector: 'select[name="event_public[]"]' });
                self.markFieldError($publicField);
            }

            // 5. Type de lieu et adresse
            var locationType = $('input[name="ova_mb_event_type_location"]:checked, input[name="type_location"]:checked').val();
            if (locationType === 'has_location' || locationType === '1') {
                var $addressField = $('input[name="ova_mb_event_map_address"], input[name="map_address"]').first();
                var address = $addressField.val();
                if (!address || address.trim() === '') {
                    errors.push({ field: "Adresse", selector: 'input[name="ova_mb_event_map_address"]' });
                    self.markFieldError($addressField);
                }
            }

            // 6. Au moins 1 créneau
            var hasValidSlot = false;
            $('.calendar_item, .ticket_item, .slot_item').each(function() {
                var dateVal = $(this).find('input[type="date"], input[name*="date"], input[name*="start"]').val();
                if (dateVal && dateVal.trim() !== '') {
                    hasValidSlot = true;
                    return false;
                }
            });
            // Vérifier aussi les inputs de date globaux
            if (!hasValidSlot) {
                $('input[name*="calendar"][name*="date"], input[name*="slot"][name*="date"]').each(function() {
                    if ($(this).val() && $(this).val().trim() !== '') {
                        hasValidSlot = true;
                        return false;
                    }
                });
            }
            if (!hasValidSlot) {
                errors.push({ field: "Créneau", selector: '.calendar_section, #section_calendar' });
            }

            // 7. Description >= 100 caractères
            var descContent = '';
            if (typeof tinymce !== 'undefined' && tinymce.get('content_event')) {
                descContent = tinymce.get('content_event').getContent({ format: 'text' });
            } else {
                descContent = $('#content_event').val() || '';
                descContent = descContent.replace(/<[^>]*>/g, '');
            }
            descContent = descContent.replace(/\s+/g, ' ').trim();
            if (descContent.length < 100) {
                errors.push({ field: "Description (min. 100 caractères)", selector: '#content_event' });
            }

            // 8. Image de présentation
            var $thumbField = $('input[name="img_thumbnail"]');
            var thumbnail = $thumbField.val();
            if (!thumbnail || thumbnail === '' || thumbnail === '0') {
                errors.push({ field: "Image de présentation", selector: 'input[name="img_thumbnail"]' });
            }

            return errors;
        },

        markFieldError: function($field) {
            if ($field.length === 0) return;

            // Pour Select2
            if ($field.hasClass('select2-hidden-accessible')) {
                $field.next('.select2-container').find('.select2-selection').addClass('validation_field_error');
            } else {
                $field.addClass('validation_field_error');
            }

            // Marquer le label aussi
            var $wrapper = $field.closest('.vendor_field, .el_form_group, .form-group');
            if ($wrapper.length) {
                $wrapper.find('label').first().addClass('validation_label_error');
            }
        },

        clearFieldErrors: function() {
            $('.validation_field_error').removeClass('validation_field_error');
            $('.validation_label_error').removeClass('validation_label_error');
            $('.select2-selection.validation_field_error').removeClass('validation_field_error');
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
            var self = this;
            $('#publication_validation_modal').fadeOut(200, function() {
                // Ne pas effacer les erreurs pour que l'utilisateur voit les champs à corriger
            });
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
