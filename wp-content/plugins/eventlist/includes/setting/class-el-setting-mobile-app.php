<?php
if (!defined('ABSPATH')) {
    exit();
}

/**
 * Mobile App Settings
 * Configure homepage hero, ads, and text blocks for the mobile app
 */
class EL_Setting_Mobile_App extends EL_Abstract_Setting {
    /**
     * setting id
     * @var string
     */
    public $_id = 'mobile_app';

    /**
     * _title
     * @var null
     */
    public $_title = null;

    /**
     * $_position
     * @var integer
     */
    public $_position = 100;

    public $_tab = true;

    public function __construct() {
        $this->_title = __('Application Mobile', 'eventlist');
        add_filter('el_admin_setting_fields', array($this, 'el_generate_fields_mobile_app'), 10, 2);
        parent::__construct();
    }

    public function el_generate_fields_mobile_app($groups, $id = "mobile_app") {
        if ($id == 'mobile_app') {
            $groups[$id . '_hero'] = apply_filters(
                'el_admin_setting_fields_mobile_hero',
                $this->el_admin_setting_fields_hero(),
                $this->id
            );

            $groups[$id . '_ads'] = apply_filters(
                'el_admin_setting_fields_mobile_ads',
                $this->el_admin_setting_fields_ads(),
                $this->id
            );

            $groups[$id . '_texts'] = apply_filters(
                'el_admin_setting_fields_mobile_texts',
                $this->el_admin_setting_fields_texts(),
                $this->id
            );
        }

        return $groups;
    }

    /**
     * Hero Section Settings
     */
    public function el_admin_setting_fields_hero() {
        return array(
            'title' => __('Section Hero (Homepage)', 'eventlist'),
            array(
                'fields' => array(
                    array(
                        'type' => 'image',
                        'label' => __('Image Hero', 'eventlist'),
                        'desc' => __('Image affichée dans la section hero de la homepage mobile. Taille recommandée: 1200x600px', 'eventlist'),
                        'name' => 'hero_image',
                        'default' => '',
                    ),
                    array(
                        'type' => 'input',
                        'label' => __('Titre Hero', 'eventlist'),
                        'desc' => __('Titre principal affiché dans la section hero', 'eventlist'),
                        'name' => 'hero_title',
                        'default' => 'Trouvez votre prochaine aventure locale',
                    ),
                    array(
                        'type' => 'input',
                        'label' => __('Sous-titre Hero', 'eventlist'),
                        'desc' => __('Sous-titre affiché sous le titre principal', 'eventlist'),
                        'name' => 'hero_subtitle',
                        'default' => 'Découvrez les meilleurs événements près de chez vous',
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => __('Afficher la barre de recherche', 'eventlist'),
                        'desc' => __('Afficher ou masquer la barre de recherche dans le hero', 'eventlist'),
                        'name' => 'hero_show_search',
                        'default' => 'yes',
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => __('Afficher les filtres rapides', 'eventlist'),
                        'desc' => __('Afficher les chips de filtres rapides (Ce soir, Gratuit, etc.)', 'eventlist'),
                        'name' => 'hero_show_quick_filters',
                        'default' => 'yes',
                    ),
                )
            )
        );
    }

    /**
     * Ads Section Settings
     */
    public function el_admin_setting_fields_ads() {
        return array(
            'title' => __('Publicités Homepage', 'eventlist'),
            array(
                'fields' => array(
                    array(
                        'type' => 'checkbox',
                        'label' => __('Activer les publicités', 'eventlist'),
                        'desc' => __('Afficher les bannières publicitaires sur la homepage', 'eventlist'),
                        'name' => 'ads_enabled',
                        'default' => '',
                    ),
                    array(
                        'type' => 'image',
                        'label' => __('Bannière publicitaire 1', 'eventlist'),
                        'desc' => __('Première bannière publicitaire. Taille recommandée: 1080x200px', 'eventlist'),
                        'name' => 'ads_banner_1',
                        'default' => '',
                    ),
                    array(
                        'type' => 'input',
                        'label' => __('Lien bannière 1', 'eventlist'),
                        'desc' => __('URL de destination au clic sur la bannière 1', 'eventlist'),
                        'name' => 'ads_banner_1_url',
                        'default' => '',
                    ),
                    array(
                        'type' => 'image',
                        'label' => __('Bannière publicitaire 2', 'eventlist'),
                        'desc' => __('Deuxième bannière publicitaire. Taille recommandée: 1080x200px', 'eventlist'),
                        'name' => 'ads_banner_2',
                        'default' => '',
                    ),
                    array(
                        'type' => 'input',
                        'label' => __('Lien bannière 2', 'eventlist'),
                        'desc' => __('URL de destination au clic sur la bannière 2', 'eventlist'),
                        'name' => 'ads_banner_2_url',
                        'default' => '',
                    ),
                    array(
                        'type' => 'image',
                        'label' => __('Bannière publicitaire 3', 'eventlist'),
                        'desc' => __('Troisième bannière publicitaire. Taille recommandée: 1080x200px', 'eventlist'),
                        'name' => 'ads_banner_3',
                        'default' => '',
                    ),
                    array(
                        'type' => 'input',
                        'label' => __('Lien bannière 3', 'eventlist'),
                        'desc' => __('URL de destination au clic sur la bannière 3', 'eventlist'),
                        'name' => 'ads_banner_3_url',
                        'default' => '',
                    ),
                )
            )
        );
    }

    /**
     * Text Blocks Settings
     */
    public function el_admin_setting_fields_texts() {
        return array(
            'title' => __('Blocs de texte', 'eventlist'),
            array(
                'fields' => array(
                    array(
                        'type' => 'input',
                        'label' => __('Titre section événements', 'eventlist'),
                        'desc' => __('Titre de la section "Retrouvez tous vos événements"', 'eventlist'),
                        'name' => 'events_section_title',
                        'default' => 'Retrouvez tous vos événements',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Description section événements', 'eventlist'),
                        'desc' => __('Description sous le titre de la section événements', 'eventlist'),
                        'name' => 'events_section_description',
                        'default' => 'Explorez notre sélection d\'événements locaux',
                    ),
                    array(
                        'type' => 'input',
                        'label' => __('Titre section thématiques', 'eventlist'),
                        'desc' => __('Titre de la section des thématiques', 'eventlist'),
                        'name' => 'thematiques_section_title',
                        'default' => 'Explorez par thématique',
                    ),
                    array(
                        'type' => 'input',
                        'label' => __('Titre section villes', 'eventlist'),
                        'desc' => __('Titre de la section des villes', 'eventlist'),
                        'name' => 'cities_section_title',
                        'default' => 'Événements par ville',
                    ),
                    array(
                        'type' => 'input',
                        'label' => __('Texte bouton Explorer', 'eventlist'),
                        'desc' => __('Texte du bouton principal "Explorer"', 'eventlist'),
                        'name' => 'explore_button_text',
                        'default' => 'Explorer les activités',
                    ),
                )
            )
        );
    }

    /**
     * Get all mobile app settings as array for API
     */
    public static function get_mobile_app_config() {
        $settings = EL_Setting::instance('ova_eventlist', 'mobile_app');

        // Get hero image URL
        $hero_image_id = $settings->get('hero_image', '');
        $hero_image_url = $hero_image_id ? wp_get_attachment_url($hero_image_id) : '';

        // Get ads images URLs
        $ads_banner_1_id = $settings->get('ads_banner_1', '');
        $ads_banner_2_id = $settings->get('ads_banner_2', '');
        $ads_banner_3_id = $settings->get('ads_banner_3', '');

        return array(
            'hero' => array(
                'image' => $hero_image_url,
                'title' => $settings->get('hero_title', 'Trouvez votre prochaine aventure locale'),
                'subtitle' => $settings->get('hero_subtitle', 'Découvrez les meilleurs événements près de chez vous'),
                'show_search' => !empty($settings->get('hero_show_search', '')),
                'show_quick_filters' => !empty($settings->get('hero_show_quick_filters', '')),
            ),
            'ads' => array(
                'enabled' => !empty($settings->get('ads_enabled', '')),
                'banners' => array_filter(array(
                    array(
                        'image' => $ads_banner_1_id ? wp_get_attachment_url($ads_banner_1_id) : '',
                        'url' => $settings->get('ads_banner_1_url', ''),
                    ),
                    array(
                        'image' => $ads_banner_2_id ? wp_get_attachment_url($ads_banner_2_id) : '',
                        'url' => $settings->get('ads_banner_2_url', ''),
                    ),
                    array(
                        'image' => $ads_banner_3_id ? wp_get_attachment_url($ads_banner_3_id) : '',
                        'url' => $settings->get('ads_banner_3_url', ''),
                    ),
                ), function($banner) {
                    return !empty($banner['image']);
                }),
            ),
            'texts' => array(
                'events_section_title' => $settings->get('events_section_title', 'Retrouvez tous vos événements'),
                'events_section_description' => $settings->get('events_section_description', 'Explorez notre sélection d\'événements locaux'),
                'thematiques_section_title' => $settings->get('thematiques_section_title', 'Explorez par thématique'),
                'cities_section_title' => $settings->get('cities_section_title', 'Événements par ville'),
                'explore_button_text' => $settings->get('explore_button_text', 'Explorer les activités'),
            ),
        );
    }
}

$GLOBALS['mobile_app_settings'] = new EL_Setting_Mobile_App();
