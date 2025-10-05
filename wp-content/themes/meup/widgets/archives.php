<?php

class Meup_WP_Widget_Archives extends WP_Widget_Archives {
    public function widget( $args, $instance ) {
        $default_title = esc_html__( 'Archives', 'ovadefault' );
        $title         = ! empty( $instance['title'] ) ? $instance['title'] : $default_title;

        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $count    = ! empty( $instance['count'] ) ? '1' : '0';
        $dropdown = ! empty( $instance['dropdown'] ) ? '1' : '0';

        echo ''.$args['before_widget'];
        echo '<div class="widget-custom">';
        if ( $title ) {
            echo ''.$args['before_title'] . $title . $args['after_title'];
        }

        if ( $dropdown ) {
            $dropdown_id = "{$this->id_base}-dropdown-{$this->number}";
            ?>
            <label class="screen-reader-text" for="<?php echo esc_attr( $dropdown_id ); ?>"><?php echo esc_html( $title ); ?></label>
            <select id="<?php echo esc_attr( $dropdown_id ); ?>" name="archive-dropdown">
                <?php
                /**
                 * Filters the arguments for the Archives widget drop-down.
                 *
                 * @since 2.8.0
                 * @since 4.9.0 Added the `$instance` parameter.
                 *
                 * @see wp_get_archives()
                 *
                 * @param array $args     An array of Archives widget drop-down arguments.
                 * @param array $instance Settings for the current Archives widget instance.
                 */
                $dropdown_args = apply_filters(
                    'widget_archives_dropdown_args',
                    array(
                        'type'            => 'monthly',
                        'format'          => 'option',
                        'show_post_count' => $count,
                    ),
                    $instance
                );

                switch ( $dropdown_args['type'] ) {
                    case 'yearly':
                        $label = esc_html__( 'Select Year', 'ovadefault' );
                        break;
                    case 'monthly':
                        $label = esc_html__( 'Select Month', 'ovadefault' );
                        break;
                    case 'daily':
                        $label = esc_html__( 'Select Day', 'ovadefault' );
                        break;
                    case 'weekly':
                        $label = esc_html__( 'Select Week', 'ovadefault' );
                        break;
                    default:
                        $label = esc_html__( 'Select Post', 'ovadefault' );
                        break;
                }

                $type_attr = current_theme_supports( 'html5', 'script' ) ? '' : ' type="text/javascript"';
                ?>

                <option value=""><?php echo esc_html( $label ); ?></option>
                <?php wp_get_archives( $dropdown_args ); ?>

            </select>

            <script<?php echo ''.$type_attr; ?>>
                /* <![CDATA[ */
                (function() {
                    var dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id ); ?>" );
                    function onSelectChange() {
                        if ( dropdown.options[ dropdown.selectedIndex ].value !== '' ) {
                            document.location.href = this.options[ this.selectedIndex ].value;
                        }
                    }
                    dropdown.onchange = onSelectChange;
                })();
                /* ]]> */
            </script>
            <?php
        } else {
            $pattern = '#<li([^>]*)><a([^>]*)>(.*?)<\/a>&nbsp;\s*\(([0-9]*)\)\s*<\/li>#i';  // removed ( and )
            $replacement = '<li$1><a$2><span class="archive-name">$3</span> <span class="number">$4</span></a>'; // give cat name and count a span, wrap it all in a link

            $format = current_theme_supports( 'html5', 'navigation-widgets' ) ? 'html5' : 'xhtml';

            /** This filter is documented in wp-includes/widgets/class-wp-nav-menu-widget.php */
            $format = apply_filters( 'navigation_widgets_format', $format );

            if ( 'html5' === $format ) {
                // The title may be filtered: Strip out HTML and make sure the aria-label is never empty.
                $title      = trim( strip_tags( $title ) );
                $aria_label = $title ? $title : $default_title;
                echo '<nav role="navigation" aria-label="' . esc_attr( $aria_label ) . '">';
            }
            ?>

            <ul>
                <?php
                ob_start();
                wp_get_archives(
                /**
                 * Filters the arguments for the Archives widget.
                 *
                 * @since 2.8.0
                 * @since 4.9.0 Added the `$instance` parameter.
                 *
                 * @see wp_get_archives()
                 *
                 * @param array $args     An array of Archives option arguments.
                 * @param array $instance Array of settings for the current widget.
                 */
                    apply_filters(
                        'widget_archives_args',
                        array(
                            'type'            => 'monthly',
                            'show_post_count' => $count,
                        ),
                        $instance
                    )
                );

                $content_subject = ob_get_clean();

                echo preg_replace( $pattern, $replacement, $content_subject );
                ?>
            </ul>

            <?php
            if ( 'html5' === $format ) {
                echo '</nav>';
            }
        }
        echo '</div>';
        echo ''.$args['after_widget'];
    }
}