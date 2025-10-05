<?php
function meup_customize_css() {

    $layout_woo = include MEUP_URL . '/customize/settings/layout-woo.php';
    $layout_global = include MEUP_URL . '/customize/settings/layout-global.php';
    $general = include MEUP_URL . '/customize/settings/general.php';

    $allcss = <<<CSS
{$layout_woo}{$layout_global}{$general}
CSS;
    return $allcss;
    
}



add_action('wp_enqueue_scripts', 'meup_render_style', 14);

function meup_render_style(){
    wp_enqueue_style( 'ova-google-fonts', meup_customize_google_fonts(), array(), null );
    wp_add_inline_style( 'meup-style', meup_customize_css() );
}

function meup_customize_google_fonts(){

    $fonts_url = '';

    $default_primary_font = json_decode( meup_default_primary_font() );
    $default_second_font = json_decode( meup_default_second_font() );

    $custom_fonts = get_theme_mod('ova_custom_font','');

    // Primary Font 
    $primary_font = json_decode( get_theme_mod( 'primary_font' ) ) ? json_decode( get_theme_mod( 'primary_font' ) ) : $default_primary_font;
    $primary_font_family = $primary_font->font;
    $primary_font_weights_string = $primary_font->regularweight ? $primary_font->regularweight : '100,200,300,400,500,600,700,800,900';
    $is_custom_primary_font = $custom_fonts != '' ? !strpos($primary_font_family, $custom_fonts) : true;


    // Second Font
    $second_font = json_decode( get_theme_mod( 'second_font' ) ) ? json_decode( get_theme_mod( 'second_font' ) ) : $default_second_font;
    $second_font_family = $second_font->font;
    $second_font_weights_string = $second_font->regularweight ? $second_font->regularweight : '100,200,300,400,500,600,700,800,900';
    $is_custom_second_font = $custom_fonts != '' ? !strpos($second_font_family, $custom_fonts) : true;
    
    
    $general_flag = _x( 'on', $primary_font_family.': on or off', 'meup');
    $second_flag = _x( 'on', $second_font_family.': on or off', 'meup');
    

 
    if ( 'off' !== $general_flag || 'off' !== $second_flag || 'off' !== $three_flag  ) {
        $font_families = array();
 
        if ( 'off' !== $general_flag && $is_custom_primary_font ) {
            $font_families[] = $primary_font_family.':'.$primary_font_weights_string;
        }
 
        if ( 'off' !== $second_flag && $is_custom_second_font ) {
            $font_families[] = $second_font_family.':'.$second_font_weights_string;
        }


        if($font_families != null){
          $query_args = array(
              'family' => urlencode( implode( '|', $font_families ) )
          );  
          $fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );
        }
        
    }
 
    return esc_url_raw( $fonts_url );
}