/**
 * API data.gouv.fr Integration
 * V1 Le Hiboo - Intégration des API publiques françaises
 */

(function($) {
    'use strict';

    /**
     * API Recherche d'Entreprises (INSEE Sirene)
     * https://recherche-entreprises.api.gouv.fr
     */
    window.EL_API_Entreprise = {

        /**
         * Rechercher une entreprise par nom ou SIREN
         * @param {string} query - Nom de l'entreprise ou SIREN
         * @param {function} callback - Fonction de callback avec les résultats
         */
        search: function(query, callback) {
            if (!query || query.length < 2) {
                callback([]);
                return;
            }

            $.ajax({
                url: 'https://recherche-entreprises.api.gouv.fr/search',
                method: 'GET',
                data: {
                    q: query,
                    page: 1,
                    per_page: 10
                },
                success: function(response) {
                    if (response && response.results) {
                        callback(response.results);
                    } else {
                        callback([]);
                    }
                },
                error: function() {
                    callback([]);
                }
            });
        },

        /**
         * Obtenir les détails d'une entreprise par SIREN
         * @param {string} siren - Numéro SIREN (9 chiffres)
         * @param {function} callback - Fonction de callback avec les détails
         */
        getDetails: function(siren, callback) {
            if (!siren || siren.length !== 9) {
                callback(null);
                return;
            }

            $.ajax({
                url: 'https://recherche-entreprises.api.gouv.fr/search',
                method: 'GET',
                data: {
                    q: siren
                },
                success: function(response) {
                    if (response && response.results && response.results.length > 0) {
                        callback(response.results[0]);
                    } else {
                        callback(null);
                    }
                },
                error: function() {
                    callback(null);
                }
            });
        },

        /**
         * Parser les données de l'entreprise pour le formulaire
         * @param {object} entreprise - Objet entreprise de l'API
         * @return {object} - Données formatées
         */
        parseEntrepriseData: function(entreprise) {
            const siege = entreprise.siege || {};
            const adresse = siege.adresse || '';
            const complement_adresse = siege.complement_adresse || '';

            return {
                nom: entreprise.nom_complet || entreprise.nom_raison_sociale || '',
                siren: entreprise.siren || '',
                forme_juridique: entreprise.nature_juridique || '',
                date_creation: entreprise.date_creation || '',
                nombre_effectifs: entreprise.tranche_effectif_salarie || '',
                adresse_ligne1: adresse,
                adresse_ligne2: complement_adresse,
                ville: siege.libelle_commune || '',
                code_postal: siege.code_postal || '',
                pays: 'FR',
                latitude: siege.latitude || '',
                longitude: siege.longitude || ''
            };
        }
    };

    /**
     * API Adresse (Base Adresse Nationale)
     * https://api-adresse.data.gouv.fr
     */
    window.EL_API_Adresse = {

        /**
         * Rechercher une adresse avec autocomplétion
         * @param {string} query - Adresse à rechercher
         * @param {function} callback - Fonction de callback avec les résultats
         */
        search: function(query, callback) {
            if (!query || query.length < 3) {
                callback([]);
                return;
            }

            $.ajax({
                url: 'https://api-adresse.data.gouv.fr/search/',
                method: 'GET',
                data: {
                    q: query,
                    limit: 5,
                    autocomplete: 1
                },
                success: function(response) {
                    if (response && response.features) {
                        callback(response.features);
                    } else {
                        callback([]);
                    }
                },
                error: function() {
                    callback([]);
                }
            });
        },

        /**
         * Parser les données d'adresse pour le formulaire
         * @param {object} feature - Feature GeoJSON de l'API
         * @return {object} - Données formatées
         */
        parseAdresseData: function(feature) {
            const properties = feature.properties || {};
            const coordinates = feature.geometry ? feature.geometry.coordinates : [null, null];

            return {
                adresse_complete: properties.label || '',
                adresse_ligne1: properties.name || '',
                ville: properties.city || '',
                code_postal: properties.postcode || '',
                pays: 'FR',
                latitude: coordinates[1],
                longitude: coordinates[0]
            };
        }
    };

    /**
     * Initialisation de l'autocomplétion pour la recherche d'entreprise
     */
    $(document).ready(function() {

        // Autocomplétion recherche d'entreprise
        const $orgSearchInput = $('#org_name_search');
        if ($orgSearchInput.length > 0) {

            let searchTimeout;
            let $resultsList = $('<ul class="api-autocomplete-results org-results"></ul>');
            $orgSearchInput.after($resultsList);

            $orgSearchInput.on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val();

                if (query.length < 2) {
                    $resultsList.hide().empty();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    EL_API_Entreprise.search(query, function(results) {
                        $resultsList.empty();

                        if (results.length === 0) {
                            $resultsList.hide();
                            return;
                        }

                        results.forEach(function(entreprise) {
                            const ville = entreprise.siege ? entreprise.siege.libelle_commune : '';
                            const codePostal = entreprise.siege ? entreprise.siege.code_postal : '';
                            const localisation = [codePostal, ville].filter(Boolean).join(' ');

                            const $item = $('<li></li>')
                                .html(
                                    '<div class="autocomplete-item-content">' +
                                        '<div class="autocomplete-item-icon"><i class="fa fa-building"></i></div>' +
                                        '<div class="autocomplete-item-info">' +
                                            '<span class="autocomplete-item-name">' + (entreprise.nom_complet || entreprise.nom_raison_sociale) + '</span>' +
                                            '<span class="autocomplete-item-details">' +
                                                '<span class="autocomplete-siren">SIREN ' + entreprise.siren + '</span>' +
                                                (localisation ? '<span class="autocomplete-location"><i class="fa fa-map-marker-alt"></i> ' + localisation + '</span>' : '') +
                                            '</span>' +
                                        '</div>' +
                                    '</div>'
                                )
                                .data('entreprise', entreprise)
                                .on('click', function() {
                                    selectEntreprise($(this).data('entreprise'));
                                    $resultsList.hide();
                                });
                            $resultsList.append($item);
                        });

                        $resultsList.show();
                    });
                }, 300); // Debounce 300ms
            });

            // Fermer la liste au clic extérieur
            $(document).on('click', function(e) {
                if (!$(e.target).closest($orgSearchInput).length && !$(e.target).closest($resultsList).length) {
                    $resultsList.hide();
                }
            });
        }

        // Autocomplétion adresse
        const $adresseSearchInput = $('#org_address_search');
        if ($adresseSearchInput.length > 0) {

            let searchTimeout;
            let $resultsList = $('<ul class="api-autocomplete-results address-results"></ul>');
            $adresseSearchInput.after($resultsList);

            $adresseSearchInput.on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val();

                if (query.length < 3) {
                    $resultsList.hide().empty();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    EL_API_Adresse.search(query, function(results) {
                        $resultsList.empty();

                        if (results.length === 0) {
                            $resultsList.hide();
                            return;
                        }

                        results.forEach(function(feature) {
                            const props = feature.properties;
                            const $item = $('<li></li>')
                                .html(
                                    '<div class="autocomplete-item-content">' +
                                        '<div class="autocomplete-item-icon"><i class="fa fa-map-marker-alt"></i></div>' +
                                        '<div class="autocomplete-item-info">' +
                                            '<span class="autocomplete-item-name">' + props.name + '</span>' +
                                            '<span class="autocomplete-item-details">' +
                                                '<span class="autocomplete-location">' + props.postcode + ' ' + props.city + '</span>' +
                                            '</span>' +
                                        '</div>' +
                                    '</div>'
                                )
                                .data('feature', feature)
                                .on('click', function() {
                                    selectAdresse($(this).data('feature'));
                                    $resultsList.hide();
                                });
                            $resultsList.append($item);
                        });

                        $resultsList.show();
                    });
                }, 300); // Debounce 300ms
            });

            // Fermer la liste au clic extérieur
            $(document).on('click', function(e) {
                if (!$(e.target).closest($adresseSearchInput).length && !$(e.target).closest($resultsList).length) {
                    $resultsList.hide();
                }
            });
        }

    });

    /**
     * Sélectionner une entreprise et remplir le formulaire
     */
    function selectEntreprise(entreprise) {
        const data = EL_API_Entreprise.parseEntrepriseData(entreprise);

        // Remplir les champs du formulaire
        $('#org_name').val(data.nom).trigger('change');
        $('#org_display_name').val(data.nom).trigger('change');
        $('#org_siren').val(data.siren).trigger('change');
        $('#org_date_creation').val(data.date_creation).trigger('change');

        // Forme juridique - chercher l'option correspondante
        const $formeJuridique = $('#org_forme_juridique');
        if ($formeJuridique.length > 0 && data.forme_juridique) {
            $formeJuridique.val(mapFormeJuridique(data.forme_juridique)).trigger('change');
        }

        // Nombre d'effectifs
        $('#org_nombre_effectifs').val(data.nombre_effectifs).trigger('change');

        // Adresse
        $('#user_address_line1').val(data.adresse_ligne1).trigger('change');
        $('#user_address_line2').val(data.adresse_ligne2).trigger('change');
        $('#user_city').val(data.ville).trigger('change');
        $('#user_postcode').val(data.code_postal).trigger('change');
        $('#user_country').val(data.pays).trigger('change');

        // GPS - Remplir les champs cachés
        $('#org_latitude').val(data.latitude).trigger('change');
        $('#org_longitude').val(data.longitude).trigger('change');

        // GPS - Afficher les coordonnées dans le champ visible
        if (data.latitude && data.longitude) {
            $('#org_gps_display').val(data.latitude + ', ' + data.longitude);
        }

        // Mettre à jour la carte Leaflet
        if (typeof window.updateProfileMap === 'function' && data.latitude && data.longitude) {
            window.updateProfileMap(data.latitude, data.longitude);
        }

        // Mettre à jour le champ de recherche d'adresse
        if (data.adresse_ligne1) {
            const adresseComplete = [data.adresse_ligne1, data.code_postal, data.ville].filter(Boolean).join(' ');
            $('#org_address_search').val(adresseComplete);
        }

        // Cacher le champ de recherche et afficher le nom
        $('#org_name_search').hide();
        $('#org_name').show().prop('readonly', false);

        // Valider les onglets
        if (typeof validateAllTabs === 'function') {
            validateAllTabs();
        }
    }

    /**
     * Sélectionner une adresse et remplir le formulaire
     */
    function selectAdresse(feature) {
        const data = EL_API_Adresse.parseAdresseData(feature);

        // Remplir les champs
        $('#user_address_line1').val(data.adresse_ligne1).trigger('change');
        $('#user_city').val(data.ville).trigger('change');
        $('#user_postcode').val(data.code_postal).trigger('change');
        $('#user_country').val(data.pays).trigger('change');

        // GPS - Remplir les champs cachés
        $('#org_latitude').val(data.latitude).trigger('change');
        $('#org_longitude').val(data.longitude).trigger('change');

        // GPS - Afficher les coordonnées dans le champ visible
        if (data.latitude && data.longitude) {
            $('#org_gps_display').val(data.latitude + ', ' + data.longitude);
        }

        // Mettre à jour la carte Leaflet
        if (typeof window.updateProfileMap === 'function' && data.latitude && data.longitude) {
            window.updateProfileMap(data.latitude, data.longitude);
        }

        // Mettre à jour le champ de recherche avec l'adresse complète
        $('#org_address_search').val(data.adresse_complete || data.adresse_ligne1);

        // Valider les onglets
        if (typeof validateAllTabs === 'function') {
            validateAllTabs();
        }
    }

    /**
     * Mapper les formes juridiques de l'API vers les valeurs du select
     */
    function mapFormeJuridique(formeApi) {
        // Mapping simplifié - à compléter selon vos besoins
        const mapping = {
            '5499': 'association', // Association déclarée
            '5710': 'association', // Association loi 1901
            '5203': 'sarl', // SARL
            '5202': 'sarl', // SARL unipersonnelle
            '5710': 'sas', // SAS
            '5720': 'sas', // SAS unipersonnelle
            '1000': 'ei', // Entrepreneur individuel
            '5498': 'auto_entrepreneur', // Auto-entrepreneur
            '5599': 'autre'
        };

        return mapping[formeApi] || 'autre';
    }

})(jQuery);
