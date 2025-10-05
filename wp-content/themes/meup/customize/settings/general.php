<?php
$general_css = '';

/* Primary Font */
$default_primary_font = json_decode(meup_default_primary_font());
$primary_font = json_decode(get_theme_mod('primary_font')) ? json_decode(get_theme_mod('primary_font')) : $default_primary_font;
$primary_font_family = $primary_font->font;

/* General Typo */
$general_font_size = get_theme_mod('general_font_size', '16px');
$general_line_height = get_theme_mod('general_line_height', '23px');
$general_letter_space = get_theme_mod('general_letter_space', '0px');
$general_color = get_theme_mod('general_color', '#333333');

/* Primary Color */
$primary_color = get_theme_mod('primary_color', '#ff601f');

/* Second Font */
$default_second_font = json_decode(meup_default_second_font());
$second_font = json_decode(get_theme_mod('second_font')) ? json_decode(get_theme_mod('second_font')) : $default_second_font;
$second_font_family = $second_font->font;



$button_color_add = get_theme_mod('button_color_add', '#82b440');
$button_color_remove = get_theme_mod('button_color_remove', '#ff601f');
$button_color_add_cart = get_theme_mod('button_color_add_cart', '#90ba3e');
$color_error_cart = get_theme_mod('color_error_cart', '#f16460');
$link_color = get_theme_mod('link_color', '#3d64ff');
$color_rating_color = get_theme_mod('color_rating_color', '#ffa800');
$vendor_sidebar_bgcolor = get_theme_mod('vendor_sidebar_bgcolor', '#343353');
$vendor_sidebar_color = get_theme_mod('vendor_sidebar_color', '#ffffff');

$vendor_color_one  = get_theme_mod('vendor_color_one', '#233D4C');
$vendor_color_two  = get_theme_mod('vendor_color_two', '#666666');
$vendor_color_three  = get_theme_mod('vendor_color_three', '#888888');
$vendor_color_four  = get_theme_mod('vendor_color_four', '#222222');
$vendor_color_five  = get_theme_mod('vendor_color_five', '#333333');
$vendor_color_six = get_theme_mod('vendor_color_six', '#cccccc');

$general_css .= <<<CSS

body{
	font-family: {$primary_font_family};
	font-weight: 400;
	font-size: {$general_font_size};
	line-height: {$general_line_height};
	letter-spacing: {$general_letter_space};
	color: {$general_color};
}
p{
	color: {$general_color};
	line-height: {$general_line_height};
}

.ui-widget,
.ui-widget input,
.vendor_wrap .vendor_field label{
	font-family: {$primary_font_family};
}

h1,h2,h3,h4,h5,h6,.second_font, .nav_comment_text,
.woocommerce ul.products li.product .price,
.woocommerce.single-product .product .price
{
	font-family: {$second_font_family};
}

/*** blog **/
article.post-wrap .post-meta .post-meta-content .general-meta i:before,
article.post-wrap .post-title h2.post-title a:hover,
.blog_v2 article.post-wrap .post-title h2.post-title a:hover,
.sidebar .widget.widget_custom_html .ova_search form .search button i,
.sidebar .widget ul li a:hover,
.sidebar .widget ul li a:hover:before,
.sidebar .widget.widget_tag_cloud .tagcloud a:hover,
.single-post article.post-wrap .post-tag-constrau .socials-inner .share-social > a,
.single-post article.post-wrap .pagination-detail .pre .num-1 a i:before,
.single-post article.post-wrap .pagination-detail .next .num-1 a i:before,
.single-post article.post-wrap .pagination-detail .pre .num-2 a:hover,
.single-post article.post-wrap .pagination-detail .next .num-2 a:hover,
.content_comments .comments .commentlists article.comment_item .comment-details .author-name .date .comment-reply-link:hover,
.content_comments .comments .commentlists article.comment_item .comment-details .author-name .date .comment-edit-link:hover,
.content_comments .comments .comment-respond .comment-form > div i,
.content_comments .comments .comment-respond .form-submit #submit,

.ova-blog-slider .blog-slider .item-blog .content .post-meta-blog i:before,
.ova-blog-slider .blog-slider .item-blog .content .post-meta-blog a:hover,
.ova-blog-slider .blog-slider .item-blog .content .title h3 a:hover,
.sidebar .widget.recent-posts-widget-with-thumbnails ul li a .rpwwt-post-title:hover,
.sidebar .widget.recent-posts-widget-with-thumbnails ul li .rpwwt-post-date:before,
.ova-blog-slider .owl-nav .owl-prev:hover i,
.ova-blog-slider .owl-nav .owl-next:hover i,
.ova-event-slider .owl-nav button:hover i,
.ova-event-grid .el-button-filter button:not(.active):hover,
.ova-subcrible .submit input[type=submit],
.meup-counter .elementor-counter .elementor-counter-number-wrapper .elementor-counter-number-prefix,
.meup-counter .elementor-counter .elementor-counter-number-wrapper .elementor-counter-number,
.ova-feature .content .title a:hover,
.ova-subcrible .submit i,
.single-event .event_related .owl-nav button:hover,
.meup_footer_link .elementor-text-editor ul li a:hover,
.blogname,
article.post-wrap .post-meta .post-meta-content a:hover,
.sidebar .widget.widget_rss ul li a.rsswidget:hover,
.content_comments .comments ul.commentlists li.pingback .author-name a:hover,
.content_comments .comments ul.commentlists li.trackback .author-name a:hover,
.content_comments .comments .comment-respond small a:hover,
.according-meup .elementor-accordion .elementor-accordion-item .elementor-tab-title .elementor-accordion-icon .elementor-accordion-icon-opened i,
.ova-testimonial.version_1 .owl-nav .owl-prev:hover i,
.ova-testimonial.version_1 .owl-nav .owl-next:hover i,
.ova-testimonial.version_2 .owl-nav .owl-prev:hover i,
.ova-testimonial.version_2 .owl-nav .owl-next:hover i,
.ova-about-team .ova-media .image .social li a:hover i,
.ova-contact .icon i:before,
.ova-contact .address a:hover,
.meup-contact-form-1 .input i,
.meup-contact-form-1 input[type=submit],
.meup_404_page .pnf-content h2,
.ovatheme_header_default nav.navbar ul.nav li.active> a,
.woocommerce ul.products li.product h2.woocommerce-loop-product__title,
.woocommerce.single-product .product .price,
.woocommerce p.stars a,
.single-post article.post-wrap .post-body .qoute-post-meup i:before,
.authors_page ul.authors li .ova-content .contact i,
.authors_page ul.authors li .ova-content .title,
.authors_page ul.authors li .ova-content .contact a:hover,
.list-box-wallet .list-payment-menthod .payment_method .title_payment,
.withdraw_form .modal-content .form-Withdraw .payment_methods_info span,
.wallet_list .withdraw_form .modal-content .form-Withdraw .withdraw_balance_info span
{
	color: {$primary_color};
}

.ova-event-grid .event_archive .wrap_loader .loader circle
{
	stroke: {$primary_color};
}

.ova-blog-slider .owl-nav .owl-prev:hover,
.ova-blog-slider .owl-nav .owl-next:hover,
article.post-wrap.sticky,
.meup-contact-form-1 input[type=submit]
{
	border-color: {$primary_color}!important;
}

.ova-blog-slider .owl-nav .owl-prev:hover,
.ova-blog-slider .owl-nav .owl-next:hover,
.content_comments .comments .comment-respond .form-submit #submit
{
	border-color: {$primary_color}!important;
}

article.post-wrap .post-footer .post-readmore-meup a:hover,
.pagination-wrapper .blog_pagination .pagination li.active a,
.pagination-wrapper .blog_pagination .pagination li a:hover,
.single-post article.post-wrap .post-tag-constrau .post-tags-constrau > a:hover,
.single-post article.post-wrap .pagination-detail .pre .num-1 a:hover,
.single-post article.post-wrap .pagination-detail .next .num-1 a:hover,
.woocommerce ul.products li.product .button:hover,
.sidebar .widget.widget_tag_cloud .tagcloud a:hover,
.search-form input[type="submit"],
.woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt,
.woocommerce #respond input#submit.alt:hover, .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover, .woocommerce input.button.alt:hover
{
	border-color: {$primary_color};
}

article.post-wrap .post-footer .post-readmore-meup a:hover,
.pagination-wrapper .blog_pagination .pagination li.active a,
.pagination-wrapper .blog_pagination .pagination li a:hover,
.single-post article.post-wrap .post-tag-constrau .post-tags-constrau > a:hover,
.single-post article.post-wrap .post-tag-constrau .socials-inner .share-social .share-social-icons li a:hover,
.single-post article.post-wrap .pagination-detail .pre .num-1 a:hover,
.single-post article.post-wrap .pagination-detail .next .num-1 a:hover,
.content_comments .comments .number-comments:after,
.content_comments .comments .wrap_comment_form .comment-respond .title-comment:after,
.ova-blog-slider .blog-slider .owl-dots .owl-dot.active span,
.ova-event-slider .owl-dots .owl-dot.active span,
.ova-heading .line,

.page-links a:hover,
.page-links a:focus,
.ova-testimonial .owl-dots .owl-dot.active span,
.ova_social .content a:hover,
.ova-blog-slider .blog-slider .owl-dots .owl-dot.active span,
.meup_404_page .pnf-content .go_back,
.search-form input[type="submit"],
.woocommerce ul.products li.product .button:hover,
.woocommerce.single-product .product .woocommerce-Reviews #review_form_wrapper .form-submit input#submit,
.woocommerce ul.products li.product .onsale,
.woocommerce.single-product .product .onsale,
.woocommerce button.button.alt,
.woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt
.woocommerce #respond input#submit.alt:hover, .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover, .woocommerce input.button.alt:hover,
.general_sales .el-wp-bar .skill-active,
.single-event .schedules_form .content_schedules .booking_schedules_time
{
	background-color: {$primary_color};
}

.meup-contact-form-1 input[type=submit]:hover
{
	background-color: {$primary_color};
	border-color: {$primary_color};
	color: #fff;
}

.content_comments .comments .commentlists article.comment_item .comment-details .author-name .name,
.single-post article.post-wrap .post-body .qoute-post-meup,
.single-post article.post-wrap .post-body .qoute-post-meup p,
.sidebar .widget.recent-posts-widget-with-thumbnails ul li a .rpwwt-post-title,
.ova-subcrible .submit input[type=submit]
{
	font-family: {$second_font_family};
}

/*** footer ***/
.ova_social .content a:hover
{
	background-color: {$primary_color};
}

.ova-menu-acount .my-account a:hover
{
	color: {$primary_color} !important;
}



/********** Eventlist Plugin **********/
/* Alway Use for Active, Hover, Focus*/
.vendor_wrap .contents .vendor_listing .header_listing ul li.active a,
.vendor_wrap span.status .closed,
.vendor_wrap tbody.event_body i,
.vendor_wrap .active_color,
.vendor_wrap a:hover,
.vendor_wrap .active_color,
.meta_event li i,
.vendor_wrap .contents .info-sales li .value,
.packages_list .item ul li .value,


.event_item.type4 .event_detail .el-wp-content .date-event .wp-date .month,

.event-loop-favourite:hover i,
.event-loop-favourite.active i,
.event_item.type5 .event_detail .el-wp-content .content-event .event_meta_cat a:hover,
.event_item.type5  .event_detail .loop_title a:hover,
.event-loop-favourite:hover i,
.info_user .contact i,
.info_user .contact a:hover,
.info_user .send_mess,
.single-event .event-tag .wp-link-tag a:hover,
.single-event .event_comments .content_comments .commentlists .comment-details .rating .count_star,
.event_item.type2 .event_detail .event-location-time .event_location a:hover,
.event_item.type3  .info_event .event-loop-favourite:hover i,
.event_item.type3  .info_event .event_location a:hover,
.event_item.type3  .info_event .event-loop-favourite.active i,
.info_user .el-sendmail-author .submit-sendmail,
.vendor_wrap .vendor_sidebar ul.dashboard_nav li.active a,
.vendor_wrap .vendor_sidebar ul.dashboard_nav li:hover a,
.el-event-category .el-media i,
.el-event-category .content-cat .cate-name a:hover,
.wrap_form_search.type3 form .field_search .icon_field,
.el-event-venue .event-venue-slide .item-venue .el-content .venue-name a:hover,
.single-event .event_related ul .event_entry .event_item .event_thumbnail .event-loop-favourite:hover i,
.single-event .event_related ul .event_entry .event_item .event_detail .loop_title a:hover,
.single-event .event_related ul .event_entry .event_item .event_detail .event-location-time .event_location a:hover,
.event_item.type4 .event_thumbnail .el-share-social .share-social .share-social-icons li a:hover,
.cart_detail .cart-sidebar .cart-discount-button a:hover,
.cart_detail .cart-sidebar .cart-info .wp-cart-info .cart_title span.edit:hover,
.single-event .event-gallery .slide_gallery i,
.el_wrap_site .venue-letter ul li a:hover,
.el_wrap_site .venue-letter ul li a.active,
.author_page .event_list .item_event .info_event .event_loop_price,
.author_page .event_list .item_event .info_event .event-loop-favourite:hover i,
.author_page .event_list .item_event .info_event .loop_title a:hover,
.author_page .event_list .item_event .info_event .event_location a:hover,
.wp-cart-header .cart-header .title-event a:hover,
.cart_detail .cart-sidebar .cart-info .wp-cart-info .content-cart-info .total-discount p,
.el_name_event_slider .wrap_item .item i,
.el_name_event_slider .wrap_item .item .title i,
.wrap_search_map .wrap_search .job_filters .wrap_search_location .locate-me,
.wrap_search_map .wrap_search #show_map .iw_map .title a:hover,
.info_user .top .author_name a:hover,
.single-event .act_share .add_cal a:hover,
.single-event .act_share .export_ical a:hover, 
.info_user .social .social_item a:hover,
.single-event .act_share .el_share_social a:hover,
.single-event h3.heading.map a:hover,
.info_user .social .social_item a:hover i,
.info_user .social .social_item a:hover,
.cart_detail .cart-sidebar .auto_reload:hover,
.vendor_wrap .vendor_sidebar .el_vendor_mobile_menu a i,
.single-event .event-banner .gallery-banner .owl-nav button i,
.ova-login-form-container p.login-submit #wp-submit,
.ova-login-form-container .login-username:after, 
.ova-login-form-container .login-password:after,
.ova-login-form-container .forgot-password:hover,
.ova_register_user p.form-row:after,
.ova_register_user .signup-submit .ova-btn:hover,
.single-event .schedules_form .modal-content .time_form-schedules,
.event_item.type1 .event_thumbnail .event-loop-favourite.active i
{
	color: {$primary_color};
}

.info_user .send_mess,
.single-event .event-tag .wp-link-tag a:hover,
.single-event .event_comments .content_comments .commentlists .comment-details .rating .count_star,
.info_user .el-sendmail-author .submit-sendmail,
.el-event-category:hover,
.single-event .event_related ul .event_entry .event_item .event_thumbnail .img-author a:hover img,
.wrap_search_map .wrap_search .job_filters .wrap_search_radius #wrap_pointer span,
.wrap_search_map .wrap_search .search_result .el-pagination ul li span.current,
.event_item.type5 .event_detail .el-wp-content .content-event .event_location a:hover,
.img-author a:hover img,
.ova-login-form-container p.login-submit #wp-submit
{
	border-color: {$primary_color};
}

.act_booking a,
.el-pagination .page-numbers li .page-numbers.current,
.el-pagination .page-numbers li .page-numbers:hover,

.info_user .el-sendmail-author .submit-sendmail:hover,
.el-event-venue .owl-dots .owl-dot.active span,
.wrap_form_search form .el_submit_search input,
.single-event .event_related ul .event_entry .event_item .event_detail .meta-footer .event-button a:hover,
.single-event .ticket-calendar .item-calendar-ticket .button-book a,
.author_page .event_list .item_event .info_event .event-status .status.closed,
.packages_list .item button.button,
.vendor_wrap .contents .info-sales li:hover,
.single-event .event-banner .gallery-banner .owl-nav button:hover,
.ova-login-form-container p.login-submit #wp-submit:hover,
.ova_register_user .signup-submit .ova-btn
{
	background-color: {$primary_color};
	border-color: {$primary_color};
	color: #fff;
}

.wrap_search_map .wrap_search .search_result .el-pagination ul li span:hover,
.fc-h-event{
	background-color: {$primary_color}!important;
	border-color: {$primary_color}!important;
}

.event_meta_cat a:hover,
.event_item.type3 .image_feature .categories a:hover,
.single-event .event_related ul .event_entry .event_item .event_thumbnail .event_meta_cat a:hover,
.woocommerce button.button.alt,
.cart_detail .cart-sidebar .checkout_button a:hover,
.cart_detail .cart-sidebar .next_step_button a:hover,
.author_page .event_list .item_event .image_feature .categories a:hover,
.select2-container .select2-dropdown .select2-results__option--highlighted,
.vendor_wrap .contents .vendor_listing .sales .el-wp-bar .skill-active,
#show_map .my-marker,
.ova-login-form-container h3.title:after,
.ova_register_user h3.title:after
{
	background-color: {$primary_color}!important;
}

.el-wp-content .date-event .wp-date .month,
.single-event .wp-date .month,
.event-status .status.closed,
.info_user .send_mess:hover,
.single-event h3.heading:after,
.single-event .event_related .desc:after,
.ova-event-grid .el-button-filter button.active,
.vendor_wrap .contents .table-list-booking .el-export-csv a,
.vendor_wrap .contents .table-list-ticket .el-export-csv a,
.single-event .ticket-calendar .item-calendar-ticket .button-book a.un-selling
{
	background-color: {$primary_color};
}


/* Button add - Alway use for Add, Add Button */

.vendor_wrap span.status .opening, 
.vendor_wrap span.status .upcomming,
.packages_list .item ul li i.pcheck,
.packages_list .item .price
{
	color: {$button_color_add};
}

button.button,
a.button,
input.el_btn_add,
button.el_btn_add,
.vendor_wrap .el_submit_btn, 
.vendor_wrap .el_edit_event_submit{
	background-color: {$button_color_add};
	border-color: {$button_color_add};
	color: #fff;
}


/* Alway use for Remove, Delete */
.vendor_wrap .contents .info-sales li:hover,
.vendor_wrap .contents .table-list-booking .el-export-csv a, 
.vendor_wrap .contents .table-list-ticket .el-export-csv a,
.vendor_wrap .contents .table-list-booking .list-check-export-csv button.export-csv-extra, 
.vendor_wrap .contents .table-list-ticket .list-check-export-csv button.export-csv-extra,
.vendor_wrap .vendor_tab li.ui-tabs-active,
.packages_list .item button.button,
.vendor_wrap .contents .vendor_edit_event #mb_calendar .manual .item_calendar .remove_calendar,
.vendor_wrap .contents .vendor_edit_event #mb_coupon .item_coupon .remove_coupon,
a.button.remove_social,
button.button.remove_social
{
	background-color: {$button_color_remove};
	border-color:{$button_color_remove};
	color: #fff;
}
.packages_list .item ul li i.pclose,
.vendor_wrap .contents .vendor_edit_event #mb_basic .image_feature .remove_image,
a.remove_image,
.vendor_wrap .contents .vendor_edit_event #mb_gallery .wrap_single_banner .wrap_image_banner .remove_image_banner,
.vendor_wrap .contents .vendor_edit_event #mb_basic .location #mb_venue #data_venue li .remove_venue,
.vendor_wrap .vendor_profile #el_save_profile .author_image .wrap .remove_image,
.accounting ul.filter li.active a, 
.accounting ul.filter li.active > span,
.image_gallery .gallery_item a,
.vendor_wrap .contents .vendor_edit_event #mb_gallery .image_gallery .gallery_list .gallery_item .change_image_gallery
{
	color: {$button_color_remove};
}


.cart_detail .cart-sidebar .checkout_button a,
.cart_detail .cart-sidebar .next_step_button a,
.cart_detail .el_payments ul li .type-payment input[type=radio]:checked ~ .outer-circle:before
{
	background-color: {$button_color_add_cart};
}

.cart_detail .cart-sidebar .cart-info .wp-cart-info .cart_title span.edit,
.cart_detail .el_payments ul li .type-payment input[type=radio]:checked ~ label,
.cart_detail .cart-sidebar .cart-info .wp-cart-info .content-cart-info .item-info .info-type-ticket .wp-seat-info span
{
	color: {$button_color_add_cart};
}

.cart_detail .el_payments ul li .type-payment input[type=radio]:checked ~ .outer-circle
{
	border-color: {$button_color_add_cart};
}

#submit-code-discount{
	background-color: {$button_color_add_cart}!important;
	border-color: {$button_color_add_cart}!important;
}


.cart_detail .cart-content .cart-ticket-info .error-empty-cart span,
.cart_detail .error-empty-input span
{
	background: {$color_error_cart};
}
.cart_detail .cart-content .cart-ticket-info .item-ticket-type .quanty-ticket .error,
.cart_detail .cart-sidebar .cart-discount-button .form-discount .error
{
	color: {$color_error_cart};
}

a,
.vendor_wrap a,
.el_wrap_site table.venue_table a,
.single-event .act_share .share:hover,
.single-event .act_share .add_cal:hover,
.single-event .act_share .export_ical:hover,
.single-event .act_share .share a:hover,
.single-event .act_share .add_cal a:hover,
.single-event .act_share .export_ical a:hover,
.info_user .social .social_item a:hover,
.info_user .social .social_item a:hover i,
.info_user .top .author_name a:hover,
.single-event h3.heading.map a:hover,
.single-event .act_share .el_share_social a:hover
{
	color: {$link_color};
}

.event_ratting .star i {
	color: {$color_rating_color};
}

.vendor_wrap,
.vendor_wrap .vendor_sidebar{
	background-color: {$vendor_sidebar_bgcolor};
}

.vendor_wrap .vendor_sidebar ul.dashboard_nav li a,
.vendor_wrap .vendor_sidebar{
	color: {$vendor_sidebar_color};
}


.vendor_wrap,
.vendor_wrap p,
.vendor_wrap tbody.event_body .date .slash, .vendor_wrap tbody.event_body .date .time,
.event_item.type5 .event_detail .loop_title a
{
	color: {$vendor_color_one}
}



/* Color text 1 */
.event_meta_cat a,
.event_location,
.event_location a,
.event-time .time,
.event_item.type3  .info_event .event-time .time,
.event_item.type3  .info_event .event_location,
.event_item.type3  .info_event .event_location a,
.event_item.type5  .event_item .event_detail .el-wp-content .date-event .wp-date .day-week,
.single-event .wp-date .day-week
{
	color: {$vendor_color_two};
}

/* color text 2 */
.el-share-social .share-social > a i:before,
.event-loop-favourite i,
.event_item.type3 .info_event .event-loop-favourite i,
.el-event-category .content-cat .count-event
{
	color: {$vendor_color_three};
}

.loop_title a,
.el-pagination .page-numbers li .page-numbers
{
	color: {$vendor_color_four};
}
.event-button a,
.event_item.type2 .event_detail .event-location-time .event-time .time,
.event_item.type2 .event_detail .event-location-time .event_location,
.event_item.type2 .event_detail .event-location-time .event_location a
{
	color: {$vendor_color_five};
}
.event_item.type1 .event_detail .event-location-time .event-icon i,
.event_item.type3 .info_event .event-time .event-icon i,
.event_item.type3 .info_event .event_location .event-icon i
{
	color: {$vendor_color_six};
	
}




CSS;

return $general_css;
