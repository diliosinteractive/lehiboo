<?php 
if ( !defined( 'ABSPATH' ) ) exit();
?>

<ul class="el_list_page">
	<?php if ( $arr_chunk ): ?>
		<?php foreach ( $arr_chunk as $key => $arr_ids ): ?>

			<li class="el_item">
				<input type="hidden" class="item_booking_ids" value="<?php echo json_encode( $arr_ids ); ?>" />
				<a href="#" class="el_page_item" data-page="<?php echo esc_attr( $key + 1 ); ?>">
					<?php echo esc_html( $key + 1 ); ?>
				</a>
			</li>

		<?php endforeach; ?>

	<?php endif; ?>
</ul>