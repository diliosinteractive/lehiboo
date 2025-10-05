<?php  if ( !defined( 'ABSPATH' ) ) exit(); ?>


<div class="vendor_wrap">

	<div class="sidebar">
		<?php echo el_get_template( 'vendor/sidebar.php' ); ?>
	</div>

	<div class="contents" >
		<h4 class="error"><?php echo $args ?></h4>
		<br>
		<?php echo el_get_template( 'vendor/heading.php' ); ?>
		
		<?php echo el_get_template( 'vendor/_package_content.php' ); ?>
	
		<?php echo el_get_template( 'vendor/_package_table_user_res.php' ) ?>
		
		<?php wp_reset_postdata(); ?>

		<?php echo el_get_template( 'vendor/_package_payment.php' ); ?>
	</div>

</div>
