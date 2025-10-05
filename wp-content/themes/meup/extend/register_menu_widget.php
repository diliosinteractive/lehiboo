<?php
/* Register Menu */
add_action( 'init', 'meup_register_menus' );
function meup_register_menus() {
  register_nav_menus( array(
    'primary'   => esc_html__( 'Primary Menu', 'meup' )

  ) );
}

/* Register Widget */
add_action( 'widgets_init', 'ovaframework_second_widgets_init' );
function ovaframework_second_widgets_init() {
  
  $args_blog = array(
    'name' => esc_html__( 'Main Sidebar', 'meup'),
    'id' => "main-sidebar",
    'description' => esc_html__( 'Main Sidebar', 'meup' ),
    'class' => '',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => "</div>",
    'before_title' => '<h2 class="widget-title">',
    'after_title' => "</h2>",
  );
  register_sidebar( $args_blog );

require_once (MEUP_URL.'/widgets/archives.php');  
require_once (MEUP_URL.'/widgets/categories.php');  

register_widget('Meup_WP_Widget_Categories');
register_widget('Meup_WP_Widget_Archives');

}