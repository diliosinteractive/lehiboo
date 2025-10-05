<?php if( ! defined( 'ABSPATH' ) ) exit();

global $wp_query;

if ( $wp_query->max_num_pages <= 1 ) {
	return;
}

?>
<nav class="el-pagination">
	<?php
	echo wp_kses_post( paginate_links( apply_filters( 'el_pagination_args', array(
		'base'         => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
		'format'       => '',
		'add_args'     => '',
		'current'      => max( 1, get_query_var( 'paged' ) ),
		'total'        => $wp_query->max_num_pages,
		'prev_text'    => __( 'Previous', 'eventlist' ),
		'next_text'    => __( 'Next', 'eventlist' ),
		'type'         => 'list',
		'end_size'     => 3,
		'mid_size'     => 3
	) ) ) );
	?>
</nav>
