<?php 
if ( !defined( 'ABSPATH' ) ) exit();

?>

<div class="vendor_wrap"> 

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>
	

	<div class="contents">

		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<div class="vendor_listing">
			<div class="header_filter">
				<?php echo el_get_template( '/vendor/bulk-action.php' ); ?>
				<?php echo el_get_template( '/vendor/filter-events-status.php' ); ?>
				<?php echo el_get_template( '/vendor/filter-events.php' ); ?>
			</div>

			<div class="wrap_event">
				<?php echo el_get_template( '/vendor/__events-table-head.php' ); ?>
				<?php echo el_get_template( '/vendor/__events-table-body.php' ); ?>
			</div> <!-- all_event -->

		</div>

	</div>
	
</div>