<?php

if (!defined( 'ABSPATH' )) {
    exit;
}
if (!class_exists( 'Meup_Customize' )){

class Meup_Customize {
	
	public function __construct() {
        add_action( 'customize_register', array( $this, 'meup_customize_register' ) );
    }

    public function meup_customize_register($wp_customize) {
        
        $this->meup_init_remove_setting( $wp_customize );
        $this->meup_init_ova_typography( $wp_customize );
        $this->meup_init_ova_layout( $wp_customize );
        $this->meup_init_ova_header( $wp_customize );
        $this->meup_init_ova_footer( $wp_customize );
        $this->meup_init_ova_blog( $wp_customize );
        $this->meup_init_ova_event( $wp_customize );
        $this->meup_init_ova_venue( $wp_customize );
        $this->meup_init_ova_author( $wp_customize );

        
   
        do_action( 'meup_customize_register', $wp_customize );
    }

    public function meup_init_remove_setting( $wp_customize ){
    	/* Remove Colors &  Header Image Customize */
		$wp_customize->remove_section('colors');
		$wp_customize->remove_section('header_image');
    }

    
    
    /* Typo */
    public function meup_init_ova_typography($wp_customize){
    	
    	


    		/* Body Pane ******************************/
			$wp_customize->add_section( 'typo_general' , array(
			    'title'      => esc_html__( 'Typography', 'meup' ),
			    'priority'   => 1,
			    // 'panel' => 'typo_panel',
			) );


				/* General Typo */
				$wp_customize->add_setting( 'general_heading', array(
				  'default' => '',
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );

				$wp_customize->add_control(
					new Meup_Customize_Control_Heading( 
					$wp_customize, 
					'general_heading', 
					array(
						'label'          => esc_html__('General Typo','meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'general_heading',
					) )
				);


				/* General Font */
				$wp_customize->add_setting( 'primary_font',
					array(
						'default' => meup_default_primary_font(),
						'sanitize_callback' => 'meup_google_font_sanitization'
					)
				);
				$wp_customize->add_control( new Meup_Google_Font_Select_Custom_Control( $wp_customize, 'primary_font',
					array(
						'label' => esc_html__( 'Primary Font', 'meup' ),
						'section' => 'typo_general',
						'input_attrs' => array(
							'font_count' => 'all',
							'orderby' => 'popular',
						),
					)
				) );
				

				/* Font Size */
				$wp_customize->add_setting( 'general_font_size', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '16px',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				
				$wp_customize->add_control('general_font_size', array(
					'label' => esc_html__('Font Size','meup'),
					'description' => esc_html__('Example: 16px, 1.2em','meup'),
					'section' => 'typo_general',
					'settings' => 'general_font_size',
					'type' 		=>'text'
				));

				/* Line Height */
				$wp_customize->add_setting( 'general_line_height', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '23px',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				
				$wp_customize->add_control('general_line_height', array(
					'label' => esc_html__('Line height','meup'),
					'description' => esc_html__('Example: 23px, 1.6em','meup'),
					'section' => 'typo_general',
					'settings' => 'general_line_height',
					'type' 		=>'text'
				));


				/* Letter Space */
				$wp_customize->add_setting( 'general_letter_space', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '0px',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				
				$wp_customize->add_control('general_letter_space', array(
					'label' => esc_html__('Letter Spacing','meup'),
					'description' => esc_html__('Example: 0px, 0.5em','meup'),
					'section' => 'typo_general',
					'settings' => 'general_letter_space',
					'type' 		=>'text'
				));


				$wp_customize->add_setting( 'general_color', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#233D4C',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'general_color', 
					array(
						'label'          => esc_html__("Content Color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'general_color',
					) ) 
				);
						

				/* Message */
				$wp_customize->add_setting( 'second_font_message', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new Meup_Customize_Control_Heading( 
					$wp_customize, 
					'second_font_message', 
					array(
						'label'          => esc_html__('Second Font','meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'second_font_message',
					) )
				);

				/* Heading Font */
				$wp_customize->add_setting( 'second_font',
					array(
						'default' => meup_default_second_font(),
						'sanitize_callback' => 'meup_google_font_sanitization'
					)
				);
				$wp_customize->add_control( new Meup_Google_Font_Select_Custom_Control( $wp_customize, 'second_font',
					array(
						'label' => esc_html__( 'Font', 'meup' ),
						'section' => 'typo_general',
						'input_attrs' => array(
							'font_count' => 'all',
							'orderby' => 'popular',
						),
					)
				) );



				$wp_customize->add_setting( 'color_message', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );

				$wp_customize->add_control(
					new Meup_Customize_Control_Heading( 
					$wp_customize, 
					'color_message', 
					array(
						'label'          => esc_html__('General Color','meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'color_message',
					) )
				);


				$wp_customize->add_setting( 'primary_color', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#ff601f',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'primary_color', 
					array(
						'label'          => esc_html__("Primary color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'primary_color',
					) ) 
				);

				$wp_customize->add_setting( 'link_color', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#3d64ff',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'link_color', 
					array(
						'label'          => esc_html__("Link color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'link_color',
					) ) 
				);


				$wp_customize->add_setting( 'color_my_account', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );

				$wp_customize->add_control(
					new Meup_Customize_Control_Heading( 
					$wp_customize, 
					'color_my_account', 
					array(
						'label'          => esc_html__('My Account Color','meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'color_my_account',
					) )
				);


				$wp_customize->add_setting( 'button_color_add', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#82b440',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'button_color_add', 
					array(
						'label'          => esc_html__("Add Button color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'button_color_add',
					) ) 
				);

				$wp_customize->add_setting( 'button_color_remove', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#ff601f',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'button_color_remove', 
					array(
						'label'          => esc_html__("Remove/Active Button color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'button_color_remove',
					) ) 
				);

				$wp_customize->add_setting( 'button_color_add_cart', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#90ba3e',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'button_color_add_cart', 
					array(
						'label'          => esc_html__("Add to Cart Button color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'button_color_add_cart',
					) ) 
				);
				
				$wp_customize->add_setting( 'color_error_cart', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#f16460',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'color_error_cart', 
					array(
						'label'          => esc_html__("Color error cart",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'color_error_cart',
					) ) 
				);

				$wp_customize->add_setting( 'color_rating_color', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#ffa800',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'color_rating_color', 
					array(
						'label'          => esc_html__("Color error cart",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'color_rating_color',
					) ) 
				);


				$wp_customize->add_setting( 'vendor_sidebar_bgcolor', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#343353',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'vendor_sidebar_bgcolor', 
					array(
						'label'          => esc_html__("Vendor Sidebar Background Color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'vendor_sidebar_bgcolor',
					) ) 
				);

				$wp_customize->add_setting( 'vendor_sidebar_color', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#ffffff',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'vendor_sidebar_color', 
					array(
						'label'          => esc_html__("Vendor Sidebar Color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'vendor_sidebar_color',
					) ) 
				);


				$wp_customize->add_setting( 'chart_color', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#ff601f',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'chart_color', 
					array(
						'label'          => esc_html__("Chart Color",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'chart_color',
					) ) 
				);


				$wp_customize->add_setting( 'vendor_color_one', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#233D4C',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'vendor_color_one', 
					array(
						'label'          => esc_html__("Vendor Color 1",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'vendor_color_one',
					) ) 
				);

				$wp_customize->add_setting( 'vendor_color_two', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#666666',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'vendor_color_two', 
					array(
						'label'          => esc_html__("Vendor Color 2",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'vendor_color_two',
					) ) 
				);

				$wp_customize->add_setting( 'vendor_color_three', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#888888',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'vendor_color_three', 
					array(
						'label'          => esc_html__("Vendor Color 3",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'vendor_color_three',
					) ) 
				);

				$wp_customize->add_setting( 'vendor_color_four', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#222222',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'vendor_color_four', 
					array(
						'label'          => esc_html__("Vendor Color 4",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'vendor_color_four',
					) ) 
				);

				$wp_customize->add_setting( 'vendor_color_five', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#333333',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'vendor_color_five', 
					array(
						'label'          => esc_html__("Vendor Color 5",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'vendor_color_five',
					) ) 
				);

				$wp_customize->add_setting( 'vendor_color_six', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '#cccccc',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
					$wp_customize, 
					'vendor_color_six', 
					array(
						'label'          => esc_html__("Vendor Color 6",'meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'vendor_color_six',
					) ) 
				);
				
				
				



				/* Custom Font */
				/* Message */
				$wp_customize->add_setting( 'custom_font_message', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control(
					new Meup_Customize_Control_Heading( 
					$wp_customize, 
					'custom_font_message', 
					array(
						'label'          => esc_html__('Custom Font','meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'custom_font_message',
					) )
				);


				$wp_customize->add_control(
					new Meup_Customize_Control_Heading( 
					$wp_customize, 
					'custom_font_message', 
					array(
						'label'          => esc_html__('Custom Font','meup'),
			            'section'        => 'typo_general',
			            'settings'       => 'custom_font_message',
					) )
				);

				$wp_customize->add_setting( 'ova_custom_font', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );

				$wp_customize->add_control('ova_custom_font', array(
					'label' => esc_html__('Custom Font','meup'),
					'description' => esc_html__('Step 1: Insert font-face in style.css file: Refer https://www.w3schools.com/cssref/css3_pr_font-face_rule.asp. Step 2: Insert font-family and font-weight like format: 
						["Perpetua", "Regular:Bold:Italic:Light"]. Step 3: Refresh customize page to display new font in dropdown font field.','meup'),
					'section' => 'typo_general',
					'settings' => 'ova_custom_font',
					'type' =>'textarea'
				));

		
			

    }


    /* Layout */
    public function meup_init_ova_layout( $wp_customize ){

    	$wp_customize->add_section( 'layout_section' , array(
		    'title'      => esc_html__( 'Layout', 'meup' ),
		    'priority'   => 2,
		) );


    		$wp_customize->add_setting( 'global_preload', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => 'yes',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_preload', array(
				'label' => esc_html__('Preload','meup'),
				'section' => 'layout_section',
				'settings' => 'global_preload',
				'type' =>'select',
				'choices' => array(
					'yes' => esc_html__('Yes', 'meup'),
					'no' => esc_html__('No', 'meup')
				)
			));

			$wp_customize->add_setting( 'global_layout', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => 'layout_2r',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_layout', array(
				'label' => esc_html__('Layout','meup'),
				'section' => 'layout_section',
				'settings' => 'global_layout',
				'type' =>'select',
				'choices' => apply_filters( 'meup_define_layout', array() )
			));

			$wp_customize->add_setting( 'global_sidebar_width', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => '320',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_sidebar_width', array(
				'label' => esc_html__('Sidebar Width (px)','meup'),
				'section' => 'layout_section',
				'settings' => 'global_sidebar_width',
				'type' =>'number'
			));
			

			$wp_customize->add_setting( 'global_width_content', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => '1170',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_width_content', array(
				'label' => esc_html__('Width Content (px)','meup'),
				'section' => 'layout_section',
				'settings' => 'global_width_content',
				'type' =>'number',
				'default' => '1170'
			));

			$wp_customize->add_setting( 'global_width_site', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => 'wide',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_width_site', array(
				'label' => esc_html__('Width Site','meup'),
				'section' => 'layout_section',
				'settings' => 'global_width_site',
				'type' =>'select',
				'choices' => apply_filters('meup_define_wide_boxed', array())
			));

			$wp_customize->add_setting( 'global_boxed_container_width', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => '1170',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_boxed_container_width', array(
				'label' => esc_html__('Boxed Container Width (px)','meup'),
				'section' => 'layout_section',
				'settings' => 'global_boxed_container_width',
				'type' =>'number',
				'default' => '1170'
			));
			$wp_customize->add_setting( 'global_boxed_offset', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => '20',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_boxed_offset', array(
				'label' => esc_html__('Boxed Offset (px)','meup'),
				'section' => 'layout_section',
				'settings' => 'global_boxed_offset',
				'type' =>'number',
				'default' => '20'
			));

    }

    /* Header */
    public function meup_init_ova_header( $wp_customize ){

    	$wp_customize->add_section( 'header_section' , array(
		    'title'      => esc_html__( 'Header', 'meup' ),
		    'priority'   => 3,
		) );

			$wp_customize->add_setting( 'global_header', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => 'default',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_header', array(
				'label' => esc_html__('Header Default','meup'),
				'description' => esc_html__('This isn\'t effect in Blog' ,'meup'),
				'section' => 'header_section',
				'settings' => 'global_header',
				'type' =>'select',
				'choices' => apply_filters('meup_list_header', array())
			));

    }

    /* Footer */
    public function meup_init_ova_footer( $wp_customize ){

    	$wp_customize->add_section( 'footer_section' , array(
		    'title'      => esc_html__( 'Footer', 'meup' ),
		    'priority'   => 4,
		) );

			$wp_customize->add_setting( 'global_footer', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => 'default',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('global_footer', array(
				'label' => esc_html__('Footer Default','meup'),
				'description' => esc_html__('This isn\'t effect in Blog' ,'meup'),
				'section' => 'footer_section',
				'settings' => 'global_footer',
				'type' =>'select',
				'choices' => apply_filters('meup_list_footer', array())
			));

    }

    /* Event */
    public function meup_init_ova_event( $wp_customize ){

    	$wp_customize->add_panel( 'event_panel', array(
		    'title'      => esc_html__( 'Event', 'meup' ),
		    'priority' => 5,
		) );

			$wp_customize->add_section( 'archive_event_section' , array(
			    'title'      => esc_html__( 'Archive', 'meup' ),
			    'priority'   => 30,
			    'panel' => 'event_panel',
			) );

				$wp_customize->add_setting( 'archive_event_header', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				) );
				$wp_customize->add_control('archive_event_header', array(
					'label' => esc_html__('Header','meup'),
					'section' => 'archive_event_section',
					'settings' => 'archive_event_header',
					'type' =>'select',
					'choices' => apply_filters('meup_list_header', array())
				));

				$wp_customize->add_setting( 'archive_event_background', array(
					'type' => 'theme_mod', // or 'option'
					'capability' => 'edit_theme_options',
					'theme_supports' => '', // Rarely needed.
					'transport' => 'refresh', // or postMessage
					'sanitize_callback' => 'sanitize_text_field' // Get function name 
				) );
				$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'archive_event_background', array(
					'label'             => esc_html__('Background Archive Event', 'meup'),
					'section'           => 'archive_event_section',
					'settings'          => 'archive_event_background',    
				)));

				$wp_customize->add_setting( 'archive_event_footer', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('archive_event_footer', array(
					'label' => esc_html__('Footer','meup'),
					'section' => 'archive_event_section',
					'settings' => 'archive_event_footer',
					'type' =>'select',
					'choices' => apply_filters('meup_list_footer', array())
				));
				


			$wp_customize->add_section( 'single_event_section' , array(
			    'title'      => esc_html__( 'Single', 'meup' ),
			    'priority'   => 30,
			    'panel' => 'event_panel',
			) );


				$wp_customize->add_setting( 'single_event_header', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('single_event_header', array(
					'label' => esc_html__('Header','meup'),
					'section' => 'single_event_section',
					'settings' => 'single_event_header',
					'type' =>'select',
					'choices' => apply_filters('meup_list_header', array())
				));

				$wp_customize->add_setting( 'single_event_footer', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('single_event_footer', array(
					'label' => esc_html__('Footer','meup'),
					'section' => 'single_event_section',
					'settings' => 'single_event_footer',
					'type' =>'select',
					'choices' => apply_filters('meup_list_footer', array())
				));

    }

    /* Venue */
    public function meup_init_ova_venue( $wp_customize ){

    	$wp_customize->add_panel( 'venue_panel', array(
		    'title'      => esc_html__( 'Venue', 'meup' ),
		    'priority' => 5,
		) );

			$wp_customize->add_section( 'archive_venue_section' , array(
			    'title'      => esc_html__( 'Archive', 'meup' ),
			    'priority'   => 30,
			    'panel' => 'venue_panel',
			) );

				$wp_customize->add_setting( 'archive_venue_header', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('archive_venue_header', array(
					'label' => esc_html__('Header','meup'),
					'section' => 'archive_venue_section',
					'settings' => 'archive_venue_header',
					'type' =>'select',
					'choices' => apply_filters('meup_list_header', array())
				));

				$wp_customize->add_setting( 'archive_venue_footer', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('archive_venue_footer', array(
					'label' => esc_html__('Footer','meup'),
					'section' => 'archive_venue_section',
					'settings' => 'archive_venue_footer',
					'type' =>'select',
					'choices' => apply_filters('meup_list_footer', array())
				));
				


			$wp_customize->add_section( 'single_venue_section' , array(
			    'title'      => esc_html__( 'Single', 'meup' ),
			    'priority'   => 30,
			    'panel' => 'venue_panel',
			) );


				$wp_customize->add_setting( 'single_venue_header', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('single_venue_header', array(
					'label' => esc_html__('Header','meup'),
					'section' => 'single_venue_section',
					'settings' => 'single_venue_header',
					'type' =>'select',
					'choices' => apply_filters('meup_list_header', array())
				));

				$wp_customize->add_setting( 'single_venue_footer', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('single_venue_footer', array(
					'label' => esc_html__('Footer','meup'),
					'section' => 'single_venue_section',
					'settings' => 'single_venue_footer',
					'type' =>'select',
					'choices' => apply_filters('meup_list_footer', array())
				));

    }

    /* Author */
    public function meup_init_ova_author( $wp_customize ){

    		$wp_customize->add_section( 'author_general' , array(
			    'title'      => esc_html__( 'Author', 'meup' ),
			    'priority'   => 6,
			) );
			

			$wp_customize->add_setting( 'author_header', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => 'default',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('author_header', array(
				'label' => esc_html__('Header','meup'),
				'section' => 'author_general',
				'settings' => 'author_header',
				'type' =>'select',
				'choices' => apply_filters('meup_list_header', array())
			));

			$wp_customize->add_setting( 'author_footer', array(
			  'type' => 'theme_mod', // or 'option'
			  'capability' => 'edit_theme_options',
			  'theme_supports' => '', // Rarely needed.
			  'default' => 'default',
			  'transport' => 'refresh', // or postMessage
			  'sanitize_callback' => 'sanitize_text_field' // Get function name 
			  
			) );
			$wp_customize->add_control('single_venue_footer', array(
				'label' => esc_html__('Footer','meup'),
				'section' => 'author_general',
				'settings' => 'author_footer',
				'type' =>'select',
				'choices' => apply_filters('meup_list_footer', array())
			));


    }

    /* Blog */
    public function meup_init_ova_blog( $wp_customize ){

    	$wp_customize->add_panel( 'blog_panel', array(
		    'title'      => esc_html__( 'Blog', 'meup' ),
		    'priority' => 5,
		) );

			$wp_customize->add_section( 'blog_section' , array(
			    'title'      => esc_html__( 'Archive', 'meup' ),
			    'priority'   => 30,
			    'panel' => 'blog_panel',
			) );

				$wp_customize->add_setting( 'blog_template', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('blog_template', array(
					'label' => esc_html__('Type','meup'),
					'section' => 'blog_section',
					'settings' => 'blog_template',
					'type' =>'select',
					'choices' => array(
						'default' => esc_html__('Default', 'meup'),
						'blog_v2' => esc_html__('Blog Version 2', 'meup'),
					)
				));

				$wp_customize->add_setting( 'blog_layout', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'layout_2r',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('blog_layout', array(
					'label' => esc_html__('Layout','meup'),
					'section' => 'blog_section',
					'settings' => 'blog_layout',
					'type' =>'select',
					'choices' => apply_filters( 'meup_define_layout', array() )
				));

				$wp_customize->add_setting( 'blog_sidebar_width', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '320',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('blog_sidebar_width', array(
					'label' => esc_html__('Sidebar Width (px)','meup'),
					'section' => 'blog_section',
					'settings' => 'blog_sidebar_width',
					'type' =>'number'
				));


				



				

				$wp_customize->add_setting( 'blog_header', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('blog_header', array(
					'label' => esc_html__('Header','meup'),
					'section' => 'blog_section',
					'settings' => 'blog_header',
					'type' =>'select',
					'choices' => apply_filters('meup_list_header', array())
				));

				$wp_customize->add_setting( 'blog_footer', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('blog_footer', array(
					'label' => esc_html__('Footer','meup'),
					'section' => 'blog_section',
					'settings' => 'blog_footer',
					'type' =>'select',
					'choices' => apply_filters('meup_list_footer', array())
				));


				$wp_customize->add_setting( 'number_desc_blog_v2', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 10,
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('number_desc_blog_v2', array(
					'label' => esc_html__('Number tex desciption blog version 2','meup'),
					'section' => 'blog_section',
					'settings' => 'number_desc_blog_v2',
					'type' =>'number',
					
				));


			$wp_customize->add_section( 'single_section' , array(
			    'title'      => esc_html__( 'Single', 'meup' ),
			    'priority'   => 30,
			    'panel' => 'blog_panel',
			) );	

				$wp_customize->add_setting( 'single_layout', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'layout_2r',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('single_layout', array(
					'label' => esc_html__('Layout','meup'),
					'section' => 'single_section',
					'settings' => 'single_layout',
					'type' =>'select',
					'choices' => apply_filters( 'meup_define_layout', array() )
				));

				$wp_customize->add_setting( 'single_sidebar_width', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => '320',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('single_sidebar_width', array(
					'label' => esc_html__('Sidebar Width (px)','meup'),
					'section' => 'single_section',
					'settings' => 'single_sidebar_width',
					'type' =>'number'
				));


				

				$wp_customize->add_setting( 'single_header', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('single_header', array(
					'label' => esc_html__('Header','meup'),
					'section' => 'single_section',
					'settings' => 'single_header',
					'type' =>'select',
					'choices' => apply_filters('meup_list_header', array())
				));

				$wp_customize->add_setting( 'single_footer', array(
				  'type' => 'theme_mod', // or 'option'
				  'capability' => 'edit_theme_options',
				  'theme_supports' => '', // Rarely needed.
				  'default' => 'default',
				  'transport' => 'refresh', // or postMessage
				  'sanitize_callback' => 'sanitize_text_field' // Get function name 
				  
				) );
				$wp_customize->add_control('single_footer', array(
					'label' => esc_html__('Footer','meup'),
					'section' => 'single_section',
					'settings' => 'single_footer',
					'type' =>'select',
					'choices' => apply_filters('meup_list_footer', array())
				));

    }

 
	
}

}

new Meup_Customize();






