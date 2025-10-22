<?php if( ! defined( 'ABSPATH' ) ) exit(); 

get_header();

$author_id = get_query_var( 'author' );

// V1 Le Hiboo - Utiliser le nom de l'organisation au lieu du nom d'utilisateur WordPress
// Priorité : org_display_name > display_name > WordPress display_name
$org_display_name = get_user_meta( $author_id, 'org_display_name', true );
$user_display_name = get_user_meta( $author_id, 'display_name', true );
$wp_display_name = get_the_author_meta( 'display_name', $author_id );

$display_name = ! empty( $org_display_name ) ? $org_display_name : ( ! empty( $user_display_name ) ? $user_display_name : $wp_display_name );

$archive_type = 'type3'; // You can change value to typ1, type2, type3, type4, type5
$layout_column = 'single-column'; // You can change value to single-column, two-column, three-column

$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

?>

<div class="author_page">
	
	<div class="author_page_sidebar">
		<?php do_action( 'el_author_info' ); ?>
	</div>

	<!-- Event List -->
	<div class="event_list">
		<div class="ova_heading_wrapper row">
			<h3 class="heading second_font col-md-6">
				<?php echo esc_html( $display_name ); esc_html_e( '\'s Listing ', 'eventlist' ); ?>
			</h3>
			<div class="ova_filter_wrap col-md-6 mt-md-0 mt-3">
				<form method="GET" class="filter_form row">
						<div class="col-sm-8">
							<select name="status" class="form-control">
								<option value="" <?php selected( $status, "" ); ?> ><?php esc_html_e( 'Event Status', 'eventlist' ); ?></option>
								<option value="all" <?php selected( $status, "all" ); ?> ><?php esc_html_e( 'All', 'eventlist' ); ?></option>
								<option value="opening" <?php selected( $status, "opening" ); ?> ><?php esc_html_e( 'Opening', 'eventlist' ); ?></option>
								<option value="upcoming" <?php selected( $status, "upcoming" ); ?> ><?php esc_html_e( 'Upcoming', 'eventlist' ); ?></option>
								<option value="past" <?php selected( $status, "past" ); ?> ><?php esc_html_e( 'Closed', 'eventlist' ); ?></option>
							</select>
						</div>
						<div class="col-sm-4 mt-sm-0 mt-3">
							<button class="btn ova_filter_event"><i class="fas fa-search"></i></button>
						</div>
				</form>
			</div>
		</div>
		
		
		<?php if( have_posts() ): ?>

			<?php
				/**
				 * Hook: el_before_archive_loop
				 * @hooked: 
				 */
				do_action( 'el_before_archive_loop' );
			?>
				
					<div id="el_main_content">
						
						<div class="event_archive <?php echo esc_attr( $archive_type ); ?> <?php echo esc_attr( $layout_column ); ?>">

							<?php while ( have_posts() ) : the_post(); ?>
					
								<?php el_get_template_part( 'content', 'event-'.sanitize_file_name( $archive_type ) ); ?>

							<?php endwhile; wp_reset_query(); // end of the loop. ?>
							
						</div>

					</div>
				
			<?php
				/**
				 * Hook: el_after_archive_loop.
				 *
				 * @hooked el_pagination - 10
				 */
				do_action( 'el_after_archive_loop' );
			?>	
		<?php else : ?>
			<p><?php esc_html_e('Event not found', 'eventlist') ?></p>
		<?php endif; ?>
		

	</div>

</div>


<?php

get_footer();