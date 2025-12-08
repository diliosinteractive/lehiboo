<?php
if ( !defined( 'ABSPATH' ) ) exit();

?>

<!-- Custom Assets for Events Listing -->
<link rel="stylesheet" href="<?php echo EL_PLUGIN_URI . 'assets/css/vendor-events-listing.css?v=' . time(); ?>">
<script src="<?php echo EL_PLUGIN_URI . 'assets/js/frontend/vendor-events-listing.js?v=' . time(); ?>" defer></script>

<div class="vendor_wrap">

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

	<div class="contents">

		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<div class="vendor_listing el-events-listing">
			<div class="header_filter">
				<?php echo el_get_template( '/vendor/bulk-action.php' ); ?>
				<?php echo el_get_template( '/vendor/filter-events-status.php' ); ?>
				<?php echo el_get_template( '/vendor/filter-events.php' ); ?>
			</div>

			<div class="wrap_event">
				<?php echo el_get_template( '/vendor/__events-table-head.php' ); ?>
				<?php echo el_get_template( '/vendor/__events-table-body.php' ); ?>
			</div>

		</div>

	</div>

</div>
