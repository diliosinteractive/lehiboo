<?php if( ! defined( 'ABSPATH' ) ) exit();

if ( !is_singular()) //if it is not a post or a page
return;

?>

<meta property="og:title" content="<?php the_title(); ?>"/>
<meta property="og:type" content="article"/>
<meta property="og:url" content="<?php the_permalink(); ?>"/>
<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"/>

<?php if(has_post_thumbnail( get_the_ID() )) {
	$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
	if( $thumbnail_src ){

?>
	<meta property="og:image" content="<?php echo esc_url( $thumbnail_src[0] ); ?>"/>
	<meta property="og:image:width" content="<?php echo esc_attr( $thumbnail_src[1] ); ?>"/>
	<meta property="og:image:height" content="<?php echo esc_attr( $thumbnail_src[2] ); ?>"/>

<?php } }