<?php defined( 'ABSPATH' ) || exit;

$html = '<div id="event_breadcrumbs">';
	       
	$separator = '<li class="li_separator"><span class="separator"></span></li>';
    $home = esc_html__('Home', 'eventlist');
    $before = '<li>';
    $after = '</li>'; 


	$html .= '<ul class="breadcrumb">';
		global $post;
        global $wp_query;
        
        $homeLink = home_url('/');
        $type=get_post_type();

        
        $html .= '<li><a href="' . $homeLink . '">' . $home . '</a></li> ' . $separator . ' ';	
        
        
        if ( is_category() ) {
	        
	        $cat_obj = $wp_query->get_queried_object();
	        $thisCat = $cat_obj->term_id;
	        $thisCat = get_category($thisCat);
	        $parentCat = get_category($thisCat->parent);
	        if ($thisCat->parent != 0) $html .=  get_category_parents($parentCat, TRUE, ' ');
	        $html .= wp_kses_post( $before) . '' . single_cat_title('', false) . '' . $after;



        } elseif ( is_day() ) {

	        $html .= '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>' . $separator . ' ';
	        $html .= '<li><a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a></li>' . $separator . ' ';
	        $html .= wp_kses_post($before ) . esc_html__('Archive by date', 'eventlist').' ' . get_the_time('d') . '"' . $after;

        } elseif ( is_month() ) {

	        $html .= '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>' . $separator . ' ';
	        $html .= wp_kses_post($before) . esc_html__('Archive by month', 'eventlist').' ' . get_the_time('F') . '"' . $after;

        } elseif ( is_year() ) {

        	$html .= wp_kses_post($before) . esc_html__('Archive by year', 'eventlist').' ' . get_the_time('Y') . '"' . $after;

        } elseif ( is_single() && !is_attachment() ) {

	        if ( get_post_type() != 'post' ) {
		        $post_type = get_post_type_object(get_post_type());
		        $slug = $post_type->rewrite;
		        $html .= '<li><a href="' . $homeLink .  $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a></li>' . $separator . ' ';
		        $html .= wp_kses_post($before) . get_the_title() . $after;
	        } else {
		        $cat = get_the_category(); $cat = $cat[0];
		        $html .= ' ' . get_category_parents($cat, TRUE, ' ' . $separator . ' ') . ' ';
		        $html .= wp_kses_post( $before ) . '' . get_the_title() . ' ' . $after;
	        }
        }elseif ( is_search()) {
            $html .= wp_kses_post( $before ) . esc_html__('Search results for', 'eventlist').' ' . get_search_query() . '"' . $after;
        }elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {

	        $post_type = get_post_type_object(get_post_type());

	        $html .= $post_type ? wp_kses_post( $before ) . $post_type->labels->singular_name . $after : '';

        } elseif ( is_attachment() ) {

	        $parent_id  = $post->post_parent;
	        $breadcrumbs = array();
	        while ($parent_id) {
		        $page = get_page($parent_id);
		        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
		        $parent_id    = $page->post_parent;
	        }
	        $breadcrumbs = array_reverse($breadcrumbs);
	        foreach ($breadcrumbs as $crumb) $html .= ' ' . $crumb . ' ' . $separator . ' ';
	        $html .= wp_kses_post( $before ) . '' . get_the_title() . '' . $after;

        }elseif ( is_page() && !$post->post_parent ) {

        	$html .= wp_kses_post( $before ) . '' . get_the_title() . '' . $after;

        } elseif ( is_page() && $post->post_parent ) {

	        $parent_id  = $post->post_parent;
	        $breadcrumbs = array();
	        while ($parent_id) {
		        $page = get_page($parent_id);
		        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
		        $parent_id    = $page->post_parent;
	        }
	        $breadcrumbs = array_reverse($breadcrumbs);
	        foreach ($breadcrumbs as $crumb) $html .= ' ' . $crumb . ' ' . $separator . ' ';
        	$html .= wp_kses_post( $before ) . '' . get_the_title() . '' . $after;

        }elseif ( is_tag() ) {
        	$html .= wp_kses_post( $before ) . esc_html__('Archive by tag', 'eventlist').' ' . single_tag_title('', false) . '"' . $after;
        } elseif ( is_author() ) {
        global $author;
        $userdata = get_userdata($author);
        	$html .= wp_kses_post( $before ) . esc_html__('Articles posted by', 'eventlist').' ' . $userdata->display_name . '"' . $after;
        } elseif ( is_home() ){
        	$html .= wp_kses_post( $before ) . esc_html__('Blog', 'eventlist').'&nbsp;' . $after;
        }elseif ( is_404() ) {
        	$html .= wp_kses_post( $before ) . esc_html__('You got it Error 404 not Found', 'eventlist').'&nbsp;' . $after;
        }
        if ( get_query_var('paged') ) {
	        if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) $html .= ' ';
	        
	        if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) $html .= '';
        }
		$html .= '</ul>';

$html .= '</div>';

echo wp_kses_post( $html );
