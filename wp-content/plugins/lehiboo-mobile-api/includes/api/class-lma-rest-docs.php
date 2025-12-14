<?php
/**
 * REST API Documentation Controller
 * Sert l'interface Swagger UI et le fichier OpenAPI
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Docs {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // OpenAPI JSON spec
        register_rest_route($this->namespace, '/docs/openapi.json', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_openapi_spec'),
            'permission_callback' => '__return_true',
        ));

        // Swagger UI HTML
        register_rest_route($this->namespace, '/docs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_swagger_ui'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * GET /docs/openapi.json
     * Retourne la spécification OpenAPI
     */
    public function get_openapi_spec() {
        $spec_file = LMA_PLUGIN_DIR . 'docs/openapi.json';

        if (!file_exists($spec_file)) {
            return new WP_Error('spec_not_found', 'OpenAPI specification not found', array('status' => 404));
        }

        $spec = json_decode(file_get_contents($spec_file), true);

        // Update server URL dynamically
        $spec['servers'] = array(
            array(
                'url' => rest_url('lehiboo/v2'),
                'description' => 'Current server',
            ),
        );

        return new WP_REST_Response($spec, 200);
    }

    /**
     * GET /docs
     * Retourne l'interface Swagger UI
     */
    public function get_swagger_ui() {
        $spec_url = rest_url('lehiboo/v2/docs/openapi.json');

        // Serve HTML directly
        header('Content-Type: text/html; charset=UTF-8');

        echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeHiboo Mobile API - Documentation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; padding: 0; }
        .swagger-ui .topbar { display: none; }
        .swagger-ui .info { margin: 20px 0; }
        .swagger-ui .info .title { color: #1a1a2e; }
        .swagger-ui .scheme-container { background: #f8f9fa; padding: 15px; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function() {
            SwaggerUIBundle({
                url: "' . esc_url($spec_url) . '",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                docExpansion: "list",
                filter: true,
                showExtensions: true,
                showCommonExtensions: true,
                tryItOutEnabled: true
            });
        };
    </script>
</body>
</html>';

        exit;
    }
}
