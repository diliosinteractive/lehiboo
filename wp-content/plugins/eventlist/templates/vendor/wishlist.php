<?php 
if ( !defined( 'ABSPATH' ) ) exit();

?>

<div class="vendor_wrap"> 

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

	<div class="contents">
		
		<?php echo el_get_template( '/vendor/heading.php' ); ?>
		
		<?php echo do_shortcode( '[el_my_wishlist /]' ); ?>

	</div>
	
</div>