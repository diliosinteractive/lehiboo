<?php if( ! defined( 'ABSPATH' ) ) exit();

$average_rating = get_average_rating_by_id_event( get_the_id() );
// var_dump($average_rating);
$floor_num_rating = floor($average_rating);
$empty_num_rating = 5 - ceil($average_rating);

$number_comment = get_number_coment_by_id_event( get_the_id() );

?>

<span class="event_ratting">
	<?php if(!empty($average_rating)) : ?>
		<span class="star">
			<?php
			if($floor_num_rating > 0) {
				for( $i=1; $i <= $floor_num_rating; $i++ ) {
					?>
					<i class="icon_star"></i>
					<?php
				}
			}

			if ($floor_num_rating != ceil($average_rating)) {
				?>
				<i class="icon_star-half_alt" ></i>
				<?php
			}

			if ($empty_num_rating > 0) {
				for( $j = 1; $j <= $empty_num_rating; $j++ ) {
					?>
					<i class="icon_star_alt"></i>
					<?php
				}
			}
			?>

		</span>
		<span class="number second_font">(<?php echo esc_html($number_comment) ?>)</span>

<?php endif ?>
</span>