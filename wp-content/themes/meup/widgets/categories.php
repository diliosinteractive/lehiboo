<?php

class Meup_WP_Widget_Categories extends WP_Widget_Categories {
    public function widget($args, $instance) {
        static $first_dropdown = true;

        $default_title = esc_html__('Categories', 'ovadefault');
        $title         = !empty($instance['title']) ? $instance['title'] : $default_title;

        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        $count        = !empty($instance['count']) ? '1' : '0';
        $hierarchical = !empty($instance['hierarchical']) ? '1' : '0';
        $dropdown     = !empty($instance['dropdown']) ? '1' : '0';

        echo ''.$args['before_widget'];
        echo '<div class="widget-custom">';
        if ($title) {
            echo ''.$args['before_title'] . $title . $args['after_title'];
        }

        $cat_args = array(
            'orderby'      => 'name',
            'show_count'   => $count,
            'hierarchical' => $hierarchical,
        );



        if ($dropdown) {
            printf('<form action="%s" method="get">', esc_url(home_url()));
            $dropdown_id    = ($first_dropdown) ? 'cat' : "{$this->id_base}-dropdown-{$this->number}";
            $first_dropdown = false;

            echo '<label class="screen-reader-text" for="' . esc_attr($dropdown_id) . '">' . $title . '</label>';

            $cat_args['show_option_none'] = esc_html__('Select Category', 'ovadefault');
            $cat_args['id']               = $dropdown_id;

            /**
             * Filters the arguments for the Categories widget drop-down.
             *
             * @since 2.8.0
             * @since 4.9.0 Added the `$instance` parameter.
             *
             * @see wp_dropdown_categories()
             *
             * @param array $cat_args An array of Categories widget drop-down arguments.
             * @param array $instance Array of settings for the current widget.
             */
            wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args, $instance));

            echo '</form>';

            $type_attr = current_theme_supports('html5', 'script') ? '' : ' type="text/javascript"';
            ?>

            <script<?php echo ''.$type_attr; ?>>
                /* <![CDATA[ */
                (function () {
                    var dropdown = document.getElementById("<?php echo esc_js($dropdown_id); ?>");

                    function onCatChange() {
                        if (dropdown.options[dropdown.selectedIndex].value > 0) {
                            dropdown.parentNode.submit();
                        }
                    }

                    dropdown.onchange = onCatChange;
                })();
                /* ]]> */
            </script>

            <?php
        } else {
            $pattern = '#<li([^>]*)><a([^>]*)>(.*?)<\/a>\s*\(([0-9]*)\)\s*<\/li>#i';  // removed ( and )
            $replacement = '<li$1><a$2><span class="cat-name">$3</span> <span class="number">$4</span></a>'; // give cat name and count a span, wrap it all in a link


            $format = current_theme_supports('html5', 'navigation-widgets') ? 'html5' : 'xhtml';

            /** This filter is documented in wp-includes/widgets/class-wp-nav-menu-widget.php */
            $format = apply_filters('navigation_widgets_format', $format);

            if ('html5' === $format) {
                // The title may be filtered: Strip out HTML and make sure the aria-label is never empty.
                $title      = trim(strip_tags($title));
                $aria_label = $title ? $title : $default_title;
                echo '<nav role="navigation" aria-label="' . esc_attr($aria_label) . '">';
            }
            ?>

            <ul>
                <?php
                $cat_args['title_li'] = '';

                /**
                 * Filters the arguments for the Categories widget.
                 *
                 * @since 2.8.0
                 * @since 4.9.0 Added the `$instance` parameter.
                 *
                 * @param array $cat_args An array of Categories widget options.
                 * @param array $instance Array of settings for the current widget.
                 */
                ob_start();
                wp_list_categories(apply_filters('widget_categories_args', $cat_args, $instance));
                $content_subject = ob_get_clean();

                echo preg_replace( $pattern, $replacement, $content_subject );

                ?>
            </ul>

            <?php
            if ('html5' === $format) {
                echo '</nav>';
            }
        }
        echo '</div>';
        echo ''.$args['after_widget'];
    }
}


