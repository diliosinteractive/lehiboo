<?php
add_action('wp_enqueue_scripts', 'meup_theme_scripts_styles', 12);
add_action('wp_enqueue_scripts', 'meup_theme_script_default', 13);
add_action('wp_enqueue_scripts',  'meup_enqueue_customize', 13 );

// Fix select2 woocommerce
add_action( 'wp_enqueue_scripts', function(){
    global $post;
    if ( isset( $post ) && ! empty( $post ) ) {
        if ( function_exists('is_woocommerce') && ! is_woocommerce()
            && ! has_shortcode( $post->post_content, 'woocommerce_cart' )
            && ! has_shortcode( $post->post_content, 'woocommerce_checkout' )  ) {
            wp_dequeue_style( 'woocommerce-general' );
        }
    }
    
}, 100 );



function meup_theme_scripts_styles() {

    // enqueue the javascript that performs in-link comment reply fanciness
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' ); 
    }
    
    /* Add Javascript  */
    wp_enqueue_script( 'bootstrap', MEUP_URI.'/assets/libs/bootstrap/js/bootstrap.bundle.min.js' , array( 'jquery' ), null, true );
    wp_enqueue_script( 'popper', MEUP_URI.'/assets/libs/popper/popper.min.js' , array( 'jquery' ), null, true );
    wp_enqueue_script( 'bootstrap-js', MEUP_URI.'/assets/libs/bootstrap/js/bootstrap.min.js', array('jquery'), false, true );
    wp_enqueue_script( 'ova-select2', MEUP_URI.'/assets/libs/select2/select2.min.js' , array( 'jquery' ), null, true );

    wp_enqueue_script('meup-script', MEUP_URI.'/assets/js/script.js', array('jquery'),null,true);

    if( is_ssl() ){
        wp_enqueue_script('prettyphoto', MEUP_URI.'/assets/libs/prettyphoto/jquery.prettyPhoto_https.js', array( 'jquery' ), null, true );
    }else{
        wp_enqueue_script('prettyphoto', MEUP_URI.'/assets/libs/prettyphoto/jquery.prettyPhoto.js',array( 'jquery' ), null, true );
    }

    /* Add Css  */
    wp_enqueue_style('bootstrap', MEUP_URI.'/assets/libs/bootstrap/css/bootstrap.min.css', array(), null);
    wp_enqueue_style('prettyphoto', MEUP_URI.'/assets/libs/prettyphoto/css/prettyPhoto.css', array(), null);

    wp_enqueue_style( 'ova-select2', MEUP_URI. '/assets/libs/select2/select2.min.css', array(), null );

    wp_enqueue_style('v4-shims', MEUP_URI.'/assets/libs/fontawesome/css/v4-shims.min.css', array(), null);
    wp_enqueue_style('fontawesome', MEUP_URI.'/assets/libs/fontawesome/css/all.min.css', array(), null);
    wp_enqueue_style('elegant-font', MEUP_URI.'/assets/libs/elegant_font/ele_style.css', array(), null);
    wp_enqueue_style( 'flaticon', MEUP_URI.'/assets/libs/flaticon/font/flaticon.css', array(), null );
    
    wp_enqueue_style('meup-theme', MEUP_URI.'/assets/css/theme.css', array(), null);
}

function meup_theme_script_default(){

  if ( is_child_theme() ) {
      wp_enqueue_style( 'meup-parent-style', trailingslashit( get_template_directory_uri() ) . 'style.css', array(), null );
  }


  wp_enqueue_style( 'meup-style', get_stylesheet_uri(), array(), null );


}



function meup_enqueue_customize(){

    $css = '';
           
    $primary_color = get_theme_mod( 'primary_color', '#ff601f' );
    $link_color = get_theme_mod( 'link_color', '#3d64ff' );

    $color_my_account = get_theme_mod( 'color_my_account', '#3d64ff' );
    $button_color_add = get_theme_mod( 'button_color_add', '#82b440' );

    $button_color_remove = get_theme_mod( 'button_color_remove', '#ff601f' );

    $button_color_add_cart = get_theme_mod( 'button_color_add_cart', '#90ba3e' );

    $color_error_cart = get_theme_mod( 'color_error_cart', '#f16460' );

    $color_rating_color = get_theme_mod( 'color_rating_color', '#ffa800' );


    $vendor_sidebar_bgcolor = get_theme_mod( 'vendor_sidebar_bgcolor', '#343353' );


    $vendor_sidebar_color = get_theme_mod( 'vendor_sidebar_color', '#ffffff' );


    $chart_color = get_theme_mod( 'chart_color', '#ff601f' );

    $vendor_color_one = get_theme_mod( 'vendor_color_one', '#233D4C' );
    $vendor_color_two = get_theme_mod( 'vendor_color_two', '#666666' );
    $vendor_color_three = get_theme_mod( 'vendor_color_three', '#888888' );
    $vendor_color_four = get_theme_mod( 'vendor_color_four', '#222222' );
    $vendor_color_five = get_theme_mod( 'vendor_color_five', '#333333' );    
    $vendor_color_six = get_theme_mod( 'vendor_color_six', '#cccccc' );
    
    

    $css .= '--primary: '.$primary_color.';';
    $css .= '--link: '.$link_color.';';
    $css .= '--color-my-account: '.$color_my_account.';';
    $css .= '--button-color-add: '.$button_color_add.';';
    $css .= '--button-color-remove: '.$button_color_remove.';';
    $css .= '--button-color-add-cart: '.$button_color_add_cart.';';
    $css .= '--color-error-cart: '.$color_error_cart.';';
    $css .= '--color-rating-color: '.$color_rating_color.';';
    $css .= '--vendor-sidebar-bgcolor: '.$vendor_sidebar_bgcolor.';';
    $css .= '--vendor-sidebar-color: '.$vendor_sidebar_color.';';
    $css .= '--chart-color: '.$chart_color.';';
    $css .= '--vendor-color-one: '.$vendor_color_one.';';
    $css .= '--vendor-color-two: '.$vendor_color_two.';';
    $css .= '--vendor-color-three: '.$vendor_color_three.';';
    $css .= '--vendor-color-four: '.$vendor_color_four.';';
    $css .= '--vendor-color-five: '.$vendor_color_five.';';
    $css .= '--vendor-color-six: '.$vendor_color_six.';';
    

    $var = ":root{{$css}}";

   

    wp_add_inline_style( 'meup-style', $var );

}