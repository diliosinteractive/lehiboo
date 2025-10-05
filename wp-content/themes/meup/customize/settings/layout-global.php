<?php
$global_cssCode = "";
$global_width_sidebar   = '';
$global_layout  = 'layout_1c';


$global_layout_sidebar = apply_filters( 'meup_get_layout', '' );
$global_layout = ( is_array( $global_layout_sidebar ) && is_active_sidebar('main-sidebar') ) ? $global_layout_sidebar[0] : 'layout_1c';
$global_width_sidebar = is_array( $global_layout_sidebar ) ? $global_layout_sidebar[1] : '';



$global_width_site = apply_filters( 'meup_width_site', '' );
$global_boxed_container_width = get_theme_mod( 'global_boxed_container_width', '1170' );
$global_global_boxed_offset = get_theme_mod( 'global_boxed_offset', '20' );


if ($global_width_sidebar && 'layout_1c' != $global_layout){
    $global_cssCode .= <<<CSS
@media (min-width: 992px){
    #sidebar{
        flex: 0 0 {$global_width_sidebar}px;
        max-width: {$global_width_sidebar}px;
        padding-left: 45px;
        padding-right: 0;
    }
    
    #main-content{
        flex: 0 0 calc(100% - {$global_width_sidebar}px);
        max-width: calc(100% - {$global_width_sidebar}px);
    }
}

@media(max-width: 991px){
    #sidebar, #main-content{
        flex: 0 0 100%;
        max-width: 100%;
    }
    
}

CSS;
}

if( $global_layout == 'layout_2l' ){
    $global_cssCode .= <<<CSS

    @media (min-width: 992px){
        .wrap_site{
            flex-direction: row-reverse;
        }
        #sidebar{
            padding-right: 45px;
            padding-left: 0;
            border-left: none;
            border-right: 1px solid;
        }
        #main-content {
            padding-right: 0px !important;
            padding-left: 45px;
        }
    }

CSS;
}

if( $global_width_site == 'boxed' ){
    $global_cssCode .= <<<CSS
.ova-wrapp{
    max-width: {$global_boxed_container_width}px;
    margin: 0 auto;
    background-color: #fff;
    padding: {$global_global_boxed_offset}px;
}
.wrap_site{
    padding: 0;
}

CSS;

}
return $global_cssCode;