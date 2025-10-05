<?php if( ! defined( 'ABSPATH' ) ) exit();
get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : "";

$list_venue = get_list_venue_first_letter($filter, $paged);

?>
	<?php
		
		/**
		 * Hook: el_before_main_content
		 * @hooked: el_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked el_breadcrumb - 20
		 */
		do_action( 'el_before_main_content' );
	?>

	<?php
		
		/**
		 * Hook: el_venue_filter_first_letter
		 * @hooked: 
		 * @hooked el_venue_filter_first_letter
		 */
		do_action( 'el_venue_filter_first_letter' );
	?>
	
<table class="venue_table">
	<thead class="event_head">
		<tr>
			<th><?php esc_html_e("Order", "eventlist") ?></th>
			<th><?php esc_html_e("Venue", "eventlist") ?></th>
		</tr>
	</thead>
	<tbody class="event_body">
		<?php
		$i = 0;
		if($list_venue->have_posts()) : while($list_venue->have_posts()) : 
			$list_venue->the_post();
			$i++;
		?>
		<tr>
			<td><?php echo esc_html($i) ?></td>
			<td><a href="<?php echo esc_url( get_the_permalink() ); ?>">
				<?php echo esc_html( get_the_title() ); ?>
			</a></td>
		</tr>
		<?php
		endwhile;
		else :
		?>
		<tr>
			<td colspan="2"><?php esc_html_e("Venue not found", "eventlist") ?></td>
		</tr>
		<?php endif; wp_reset_postdata();?>
	</tbody>
</table>
<?php 
$total = $list_venue->max_num_pages;
if ( $total > 1 ) {
	echo wp_kses_post( pagination_vendor($total) );
}
?>
<?php
	/**
	 * Hook: el_after_main_content.
	 *
	 * @hooked el_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	do_action( 'el_after_main_content' );

?>


<?php



get_footer();