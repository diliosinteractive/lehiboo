<?php if( ! defined( 'ABSPATH' ) ) exit();

$page 			= isset( $args['page'] ) ? $args['page'] : '1';
$per_page 		= isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 5;
$max_num_pages 	= $events->max_num_pages;

$page_arr 		= range( 1, $max_num_pages );
$page_arr_chunk = array_chunk( $page_arr, 5 );

?>


<?php if ( count( $page_arr_chunk ) > 0 ): ?>
	
	<nav class="el_special_pagination">
		<ul class="el_pa_list">
			<?php foreach ( $page_arr_chunk as $key => $_arr ):

				if ( ! empty( $_arr ) && in_array( absint( $page ), $_arr) ) {

					// prev button
					if ( isset( $page_arr_chunk[absint($key)-1] ) ) {
						?>
						<li class="el_pa_item">
							<a href="#" class="prev el_page" data-page="<?php echo esc_attr( absint($page)-1 ); ?>">
								<?php esc_html_e( 'Prev', 'eventlist' ); ?>
							</a>
						</li>
						<?php
					}

					foreach ( $_arr as $page_num ) {
						$class = $page == $page_num ? 'el_page el_current_page' : 'el_page';

						?>
						<li class="el_pa_item">
							<a href="#" class="<?php echo esc_attr( $class ); ?>"
								data-page="<?php echo esc_attr( $page_num ); ?>"><?php echo esc_html( $page_num ); ?></a>
						</li>
						<?php
					}

					if ( isset( $page_arr_chunk[absint($key)+1] ) ) {
						?>
						<li class="el_pa_item">
							<a href="#" class="next el_page" data-page="<?php echo esc_attr( absint($page)+1 ); ?>">
								<?php esc_html_e( 'Next', 'eventlist' ); ?>
							</a>
						</li>
						<?php
					}

				}
			?>
			<?php endforeach; ?>
		</ul>
	</nav>

<?php endif; ?>