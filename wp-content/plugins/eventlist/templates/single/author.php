<?php if( ! defined( 'ABSPATH' ) ) exit(); 

$author_id = get_the_author_meta('ID');
$display_name = get_user_meta( $author_id, 'display_name', true ) ? get_user_meta( $author_id, 'display_name', true ) : get_the_author_meta( 'display_name', $author_id );

?>
<p class="event-single-author">
	<span class="text"><?php esc_html_e( "By: ", "eventlist" ) ?></span>
	<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" class="author-event"><?php echo esc_html($display_name); ?></a>
</p>