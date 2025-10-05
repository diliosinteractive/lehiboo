<?php 
$show_page_heading = get_post_meta( meup_get_current_id(), "ova_met_page_heading", true ) != null ? get_post_meta(meup_get_current_id(), "ova_met_page_heading", true ) : 'yes';
 ?>
<?php if($show_page_heading == 'yes'){ ?>
    <h1 class="page-title">
    	<?php the_title();?>
    </h1>
<?php } ?>

<?php 
	the_content();
?>
<div class="page-links">
	<?php
		wp_link_pages( array(
			'before'      => '<span class="page-links-title">' . esc_html__( 'Pages:', 'meup' ) . '</span>',
			'after'       => '',
			'link_before' => '<span>',
			'link_after'  => '</span>',
			'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'meup' ) . ' </span>%',
			'separator'   => '',
		) );
	?>

</div>