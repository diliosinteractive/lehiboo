<?php defined( 'ABSPATH' ) || exit;
/**
 * List All Hooks used in Template of plugin
 */

/**
 * @see el_output_content_wrapper()
 * @see el_breadcrumb()
 */
add_action( 'el_before_main_content', 'el_output_content_wrapper', 10 );
add_action( 'el_before_main_content', 'el_breadcrumb', 20 );

remove_action( 'el_before_main_content', 'el_breadcrumb', 20 );

/**
 * @see el_output_content_wrapper_end();
 */
add_action( 'el_after_main_content', 'el_output_content_wrapper_end', 10 );


/**
 * @see  el_pagination()
 */
add_action( 'el_after_archive_loop', 'el_pagination', 10);


/**
 * @see el_taxonomy_archive_description()
 * @see el_event_archive_description()
 */
add_action( 'el_archive_description', 'el_event_archive_description', 10 );
add_action( 'el_archive_description', 'el_taxonomy_archive_description', 10 );

/**
 * @see  el_loop_event_remove()
 */
add_action( 'el_loop_event_remove', 'el_loop_event_remove', 10 );


/**
 * @see  el_loop_event_thumbnail()
 */
add_action( 'el_loop_event_thumbnail', 'el_loop_event_thumbnail', 10 );

add_action( 'el_loop_event_thumbnail', 'el_loop_event_feature', 20 );

add_action( 'el_loop_event_cat_3', 'el_loop_event_feature', 20 );

/**
 * @see  el_loop_event_author()
 */
add_action( 'el_loop_event_author', 'el_loop_event_author', 10 );


/**
 * @see  el_loop_event_excerpt()
 */
// add_action( 'el_loop_event_excerpt', 'el_loop_event_excerpt' );


/**
 * * @see  el_loop_event_title()
 */
add_action( 'el_loop_event_title', 'el_loop_event_title', 10 );


/**
 * @see  el_loop_event_meta_cat()
 */
add_action( 'el_loop_event_cat', 'el_loop_event_cat', 10 );


add_action( 'el_loop_event_cat_3', 'el_loop_event_cat_3', 10 );


/**
 * @see  el_loop_event_price()
 */
add_action( 'el_loop_event_price', 'el_loop_event_price', 10 );


/**
 * @see  el_loop_event_location()
 */
add_action( 'el_loop_event_location', 'el_loop_event_location', 10 );


/**
 * @see el_loop_event_time()
 */
add_action( 'el_loop_event_time', 'el_loop_event_time', 10 );


/**
 * @see el_loop_event_status()
 */
add_action( 'el_loop_event_status', 'el_loop_event_status', 10 );


/**
 * @see el_loop_event_button()
 */
add_action( 'el_loop_event_button', 'el_loop_event_button', 10 );


/**
 * @see el_loop_event_favourite()
 */
add_action( 'el_loop_event_favourite', 'el_loop_event_favourite', 10 );


/**
 * @see el_loop_event_share()
 */
add_action( 'el_loop_event_share', 'el_loop_event_share', 10 );



/**
 * @see el_loop_event_date()
 */
add_action( 'el_loop_event_date', 'el_loop_event_date', 10 );


/**
 * @see el_loop_event_date_4()
 */
add_action( 'el_loop_event_date_4', 'el_loop_event_date_4', 10 );


/**
 * @see el_loop_event_ratting()
 */
add_action( 'el_loop_event_ratting', 'el_loop_event_ratting', 10 );


/**
 * @see  el_single_event_thumbnail()
 */
add_action( 'el_single_event_thumbnail', 'el_single_event_thumbnail', 10 );
remove_action( 'el_single_event_thumbnail', 'el_single_event_thumbnail', 10 );


/**
 * @see  el_single_event_cat()
 */
add_action( 'el_single_event_cat', 'el_loop_event_cat', 10 );


/**
 * @see  el_single_event_number_view()
 */
add_action( 'el_single_event_number_view', 'el_single_event_number_view', 10 );


/**
 * @see  el_single_event_favourite()
 */
add_action( 'el_single_event_favourite', 'el_single_event_favourite', 10 );


/**
 * @see  el_single_event_bookmark()
 */
add_action( 'el_single_event_bookmark', 'el_single_event_bookmark', 10 );


/**
 * @see  el_single_event_share()
 */
add_action( 'el_single_event_share', 'el_single_event_share', 10 );


/**
 * @see  el_single_event_author()
 */
add_action( 'el_single_event_author', 'el_single_event_author', 10 );


/**
 * @see  el_single_event_price()
 */
add_action( 'el_single_event_price', 'el_loop_event_price', 10 );

/**
 * @see el_single_event_status()
 */
add_action( 'el_single_event_status', 'el_loop_event_status', 10 );


/**
 * @see el_single_event_title()
 */
add_action( 'el_single_event_title', 'el_single_event_title', 10 );


/**
 * @see el_single_act_booking()
 */
add_action( 'el_single_act_booking', 'el_single_act_booking', 10 );


/**
 * @see el_single_share_social()
 */
add_action( 'el_single_share_social', 'el_single_share_social', 10 );

/**
 * @see el_single_report()
 */
add_action( 'el_single_report', 'el_single_report', 10 );


/**
 * @see el_single_calenda_export()
 */
add_action( 'el_single_calenda_export', 'el_single_calenda_export', 10 );


/**
 * @see el_single_add_calendar()
 */
add_action( 'el_single_add_calendar', 'el_single_add_calendar', 10 );


/**
 * @see el_single_export_ical()
 */
add_action( 'el_single_export_ical', 'el_single_export_ical', 10 );


/**
 * @see  el_single_event_content()
 */
add_action( 'el_single_event_content', 'el_single_event_content', 10 );


/**
 * @see  el_single_event_tag()
 */
add_action( 'el_single_event_tag', 'el_single_event_tag', 10 );

/**
 * @see  el_single_event_taxonomy()
 */
add_action( 'el_single_event_taxonomy', 'el_single_event_taxonomy', 10 );

/**
 * @see  el_single_event_ticket_info()
 */
add_action( 'el_single_event_ticket_info', 'el_single_event_ticket_info', 10 );


/**
 * @see  el_single_event_ticket_calendar()
 */
add_action( 'el_single_event_ticket_calendar', 'el_single_event_ticket_calendar', 10 );


/**
 * @see  el_single_event_schedules_time()
 */
add_action( 'el_single_event_schedules_time', 'el_single_event_schedules_time', 10 );


/**
 * @see  el_single_event_video()
 */
add_action( 'el_single_event_video', 'el_single_event_video', 10 );


/**
 * @see  el_single_event_comment()
 */
add_action( 'el_single_event_comment', 'el_single_event_comment', 10 );


/**
 * @see  el_single_event_gallery()
 */
add_action( 'el_single_event_gallery', 'el_single_event_gallery', 10 );

/**
 * @see  el_vendor_calendar_manage_ticket()
 */
add_action( 'el_vendor_calendar_manage_ticket', 'el_vendor_calendar_manage_ticket', 10 );


/**
 * @see  el_vendor_edit_manage_ticket_max()
 */
add_action( 'el_vendor_edit_manage_ticket_max', 'el_vendor_edit_manage_ticket_max', 10 );


/**
 * @see  el_header_cart()
 */
add_action( 'el_header_cart', 'el_header_cart', 10 );


/**
 * @see  el_cart_ticket_type()
 */
add_action( 'el_cart_ticket_type', 'el_cart_ticket_type', 10 );

add_action( 'el_cart_ticket_type', 'el_seating_map', 20 );


/**
 * @see  el_cart_info()
 */
add_action( 'el_cart_info', 'el_cart_info', 10 );


/**
 * @see  el_cart_discount()
 */
add_action( 'el_cart_discount', 'el_cart_discount', 10 );


/**
 * @see  el_cart_next_step_button()
 */
add_action( 'el_cart_next_step_button', 'el_cart_next_step_button', 10 );


add_action( 'the_post', array( 'EL_Event', 'el_setup_event_data' ) );


/**
 * @see el_customer_info()
 */
add_action( 'el_customer_info', 'el_customer_info', 10 );

/**
 * @see el_customer_input()
 */
add_action( 'el_customer_input', 'el_customer_input', 10 );

/**
 * @see el_payment_gateways()
 */
add_action( 'el_payment_gateways', 'el_payment_gateways', 10 );


/**
 * @see  el_payment_method()
 */
add_action( 'el_payment_method', 'el_payment_method', 10 );

/**
 * @see  el_cart_checkout_button()
 */
add_action( 'el_cart_checkout_button', 'el_cart_checkout_button', 10 );


/**
 * @see  el_single_event_related()
 */
add_action( 'el_single_event_related', 'el_single_event_related', 10 );


/**
 * @see  el_single_event_related()
 */
add_action( 'el_venue_filter_first_letter', 'el_venue_filter_first_letter', 10 );


/**
 * @see  el_single_event_date()
 */
add_action( 'el_single_event_date', 'el_single_event_date', 10 );


/**
 * @see  el_single_event_address()
 */
add_action( 'el_single_event_address', 'el_single_event_address', 10 );


/**
 * @see  el_single_event_banner()
 */
add_action( 'el_single_event_banner', 'el_single_event_banner', 10 );



/**
 * @see  el_single_event_policy()
 */
add_action( 'el_single_event_policy', 'el_single_event_policy', 10 );


/**
 * @see  el_single_event_map()
 */
add_action( 'el_single_event_map', 'el_single_event_map', 10 );


/**
 * @see  el_author_info()
 */
add_action( 'el_author_info', 'el_author_info', 10 );



/**
 * @see el_terms_condition()
 */
add_action( 'el_terms_condition', 'el_terms_condition', 10 );



add_filter( 'el_tab_group_after_content_general_cron_event', 'el_setting_update_event_status_manually' );

