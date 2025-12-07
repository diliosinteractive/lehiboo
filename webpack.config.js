const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    mode: 'production',
    entry: {
        // Plugin EventList - Main Style
        'wp-content/plugins/eventlist/assets/css/frontend/style': './wp-content/plugins/eventlist/assets/css/frontend/style.scss',

        // Theme Enfant - Vendor Pages
        'wp-content/themes/meup-child/assets/css/vendor-pages': './wp-content/themes/meup-child/assets/scss/vendor-pages.scss',
    },
    output: {
        path: path.resolve(__dirname),
        clean: false, // Ne pas nettoyer le dossier de sortie car on écrit dans des dossiers existants
    },
    module: {
        rules: [
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [
                                    ['autoprefixer', {}],
                                ],
                            },
                        },
                    },
                    'sass-loader',
                ],
            },
        ],
    },
    optimization: {
        minimizer: [
            // Minification CSS
            new CssMinimizerPlugin(),
            // Minification JS (si on ajoute des entrées JS plus tard)
            new TerserPlugin({
                extractComments: false,
            }),
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css', // Conserve le nom et chemin défini dans entry
        }),
    ],
    // Supprime la génération de fichiers JS inutiles pour les entrées CSS-only
    // Note: Webpack 5 génère quand même un JS par entrée, mais on peut vivre avec ou utiliser un plugin pour nettoyer.
    // Pour l'instant, on laisse comme ça, ce n'est pas gênant.
};
