<?php
if( ! defined( 'ABSPATH' ) ) exit();
$filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : "";
?>
<div class="venue-letter">
	<ul>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => ''], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "" ? "active" : "") ?>"><?php esc_html_e("All", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'A'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "A" ? "active" : "") ?>"><?php esc_html_e("A", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'B'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "B" ? "active" : "") ?>"><?php esc_html_e("B", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'C'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "C" ? "active" : "") ?>"><?php esc_html_e("C", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'D'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "D" ? "active" : "") ?>"><?php esc_html_e("D", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'E'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "E" ? "active" : "") ?>"><?php esc_html_e("E", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'F'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "F" ? "active" : "") ?>"><?php esc_html_e("F", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'G'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "G" ? "active" : "") ?>"><?php esc_html_e("G", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'H'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "H" ? "active" : "") ?>"><?php esc_html_e("H", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'I'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "I" ? "active" : "") ?>"><?php esc_html_e("I", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'J'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "J" ? "active" : "") ?>"><?php esc_html_e("J", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'K'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "K" ? "active" : "") ?>"><?php esc_html_e("K", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'L'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "L" ? "active" : "") ?>"><?php esc_html_e("L", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'M'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "M" ? "active" : "") ?>"><?php esc_html_e("M", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'N'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "N" ? "active" : "") ?>"><?php esc_html_e("N", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'O'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "O" ? "active" : "") ?>"><?php esc_html_e("O", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'P'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "P" ? "active" : "") ?>"><?php esc_html_e("P", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'Q'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "Q" ? "active" : "") ?>"><?php esc_html_e("Q", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'R'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "R" ? "active" : "") ?>"><?php esc_html_e("R", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'S'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "S" ? "active" : "") ?>"><?php esc_html_e("S", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'T'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "T" ? "active" : "") ?>"><?php esc_html_e("T", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'U'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "U" ? "active" : "") ?>"><?php esc_html_e("U", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'V'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "V" ? "active" : "") ?>"><?php esc_html_e("V", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'W'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "W" ? "active" : "") ?>"><?php esc_html_e("W", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'X'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "X" ? "active" : "") ?>"><?php esc_html_e("X", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'Y'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "Y" ? "active" : "") ?>"><?php esc_html_e("Y", "eventlist") ?></a></li>
		<li><a href="<?php echo esc_url( add_query_arg(['filter' => 'Z'], get_post_type_archive_link('venue') ) ); ?>" class="<?php echo esc_html($filter == "Z" ? "active" : "") ?>"><?php esc_html_e("Z", "eventlist") ?></a></li>
	</ul>
</div>