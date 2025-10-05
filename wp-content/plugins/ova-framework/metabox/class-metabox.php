<?php
/**
 * Include and setup custom metaboxes and fields. (make sure you copy this file to outside the CMB2 directory)
 *
 * Be sure to replace all instances of 'yourprefix_' with your project's prefix.
 * http://nacin.com/2010/05/11/in-wordpress-prefix-everything/
 *
 * @category YourThemeOrPlugin
 * @package  Demo_CMB2
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/WebDevStudios/CMB2
 */



add_action( 'cmb2_admin_init', 'ovaframework_metaboxes_default' );
function ovaframework_metaboxes_default() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = 'ova_met_';
    

    
    /* Page Settings ***************************************************************************/
    /* ************************************************************************************/
    $page_settings = new_cmb2_box( array(
        'id'            => 'page_heading_settings',
        'title'         => esc_html__( 'Show Page Heading', 'ova-framework' ),
        'object_types'  => array( 'page','post'), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true,
        
    ) );

        // Display title of page
        $page_settings->add_field( array(
            'name'       => esc_html__( 'Show title of page', 'ova-framework' ),
            'desc'       => esc_html__( 'Allow display title of page', 'ova-framework' ),
            'id'         => $prefix . 'page_heading',
            'type'             => 'select',
            // 'show_option_none' => false,
            'options'          => array(
                'yes' => esc_html__( 'Yes', 'ova-framework' ),
                'no'   => esc_html__('No', 'ova-framework' )
            ),
            'default' => 'yes',
            
        ) );


 
   

    
    /* Post Settings *********************************************************************************/
    /* *******************************************************************************/
    $post_settings = new_cmb2_box( array(
        'id'            => 'post_video',
        'title'         => esc_html__( 'Post Settings', 'ova-framework' ),
        'object_types'  => array( 'post'), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
    ) );

        // Video or Audio
        $post_settings->add_field( array(
            'name'       => esc_html__( 'Link audio or video', 'ova-framework' ),
            'desc'       => esc_html__( 'Insert link audio or video use for video/audio post-format', 'ova-framework' ),
            'id'         => $prefix . 'embed_media',
            'type'             => 'oembed',
        ) );


        // Gallery image
        $post_settings->add_field( array(
            'name'       => esc_html__( 'Gallery image', 'ova-framework' ),
            'desc'       => esc_html__( 'image in gallery post format', 'ova-framework' ),
            'id'         => $prefix . 'file_list',
            'type'             => 'file_list',
        ) );




    /* General Settings ***************************************************************/
    /* ********************************************************************************/
    $general_settings = new_cmb2_box( array(
        'id'            => 'layout_settings',
        'title'         => esc_html__( 'General Settings', 'ova-framework' ),
        'object_types'  => array( 'page', 'post', 'event'), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
    ));

        $general_settings->add_field( array(
            'name'       => esc_html__( 'Header Version', 'ova-framework' ),
            'id'         => $prefix . 'header_version',
            'description' => esc_html__( 'This value will override value in customizer without Global', 'ova-framework' ),
            'type'             => 'select',
            'show_option_none' => false,
            'options'          => array_merge( array( 'global' => 'Global' ),  apply_filters('meup_list_header', array() ) ),
            'default' => 'global'
            
        ));

        $general_settings->add_field( array(
            'name'       => esc_html__( 'Footer Version', 'ova-framework' ),
            'id'         => $prefix . 'footer_version',
            'description' => esc_html__( 'This value will override value in customizer without Global', 'ova-framework'  ),
            'type'             => 'select',
            'show_option_none' => false,
            'options'          => array_merge( array( 'global' => 'Global' ),  apply_filters('meup_list_footer', array() )),
            'default' => 'global'

        ) );

        $general_settings->add_field( array(
            'name'       => esc_html__( 'Main Layout', 'ova-framework' ),
            'desc'       => esc_html__( 'This value will override value in theme customizer without Global', 'ova-framework' ),
            'id'         => $prefix.'main_layout',
            'type'             => 'select',
            'show_option_none' => false,
            'options'          => array_merge( array( 'global' => 'Global' ),  apply_filters('meup_define_layout', array())),
            'default' => 'global',
            
        ) );


        $general_settings->add_field( array(
            'name'       => esc_html__( 'Width of site', 'ova-framework' ),
            'desc'       => esc_html__( 'This value will override value in theme customizer without Global', 'ova-framework' ),
            'id'         => $prefix . 'width_site',
            'type'             => 'select',
            'show_option_none' => false,
            'options'          => array_merge( array( 'global' => 'Global' ),  apply_filters('meup_define_wide_boxed', array())),
            'default' => 'global',
            
        ) );

   
}

