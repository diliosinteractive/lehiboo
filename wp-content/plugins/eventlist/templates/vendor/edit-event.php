<?php 
if ( !defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';

$event_req_field = apply_filters( 'el_event_req_field', array(
	'event_name' => array(
		'required' => true,
		'message'  => __( 'Event name is required', 'eventlist' )
	),
	'event_cat' => array(
		'required' 	=> true,
		'message' 	=> __( 'Category is required', 'eventlist' ),
	),
	'description' => array(
		'required' 	=> false,
		'message' 	=> __( 'Description is required', 'eventlist' ),
	),
	'img_thumbnail' => array(
		'required' 	=> false,
		'message' 	=> __( 'Image feature is required', 'eventlist' ),
	),
	'timezone' => array(
		'required' 	=> false,
		'message' 	=> __( 'Timezone is required', 'eventlist' ),
	),
	'event_tag' => array(
		'required' 	=> false,
		'message' 	=> __( 'Tag is required', 'eventlist' ),
	),
	'event_venue' => array(
		'required' 	=> false,
		'message' 	=> __( 'Venue name is required', 'eventlist' )
	),
	'event_gallery' => array(
		'required' 	=> false,
		'message' 	=> __( 'Gallery is required', 'eventlist' )
	),
	'event_video' => array(
		'required' 	=> false,
		'message' 	=> __( 'Video is required', 'eventlist' ),
	),
) );

?>


<div class="vendor_wrap">

	<div class="sidebar">
		<?php echo el_get_template( 'vendor/sidebar.php' ); ?>
	</div>

	<div class="contents">

		<!-- Heading caché car on utilise le titre dans la sticky bar -->
		<div style="display: none;"><?php echo el_get_template( '/vendor/heading.php' ); ?></div>

		<div class="vendor_edit_event">

			<?php if( $post_id ){
				$action_event = el_can_edit_event();
			}else{
				$action_event = el_can_add_event();
			}?>
			<?php if( $action_event ) : ?>

				<!-- Barre sticky avec boutons Aperçu et Enregistrer -->
				<div class="event_form_sticky_bar">
					<div class="sticky_bar_inner">
						<div class="sticky_bar_left">
							<h3><?php echo $post_id ? esc_html__( 'Modifier l\'événement', 'eventlist' ) : esc_html__( 'Créer un événement', 'eventlist' ); ?></h3>
						</div>
						<div class="sticky_bar_right">
							<?php if( $post_id ){ ?>
								<a class="btn_preview" target="_blank" href="<?php echo get_preview_post_link( $post_id ); ?>">
									<i class="icon_search"></i>
									<span><?php esc_html_e( 'Aperçu', 'eventlist' ); ?></span>
								</a>
							<?php } ?>
							<button type="button" class="btn_save_event" id="trigger_save_event">
								<i class="icon_check"></i>
								<span><?php esc_html_e( 'Enregistrer', 'eventlist' ); ?></span>
							</button>
						</div>
					</div>
				</div>

				<form id="event_edit_form" action="" method="post" enctype="multipart/form-data" class="content event_form_wrapper event_form_single_page" autocomplete="off" autocorrect="off" autocapitalize="none"
					data-required="<?php echo esc_attr( json_encode( $event_req_field ) ); ?>">
					<input type="hidden" value="<?php echo esc_attr( $post_id ); ?>" id="el_post_id" name="el_post_id"/>

					<!-- Navigation verticale à gauche avec coches de validation -->
					<div class="profile_navigation_sidebar">
						<nav class="profile_tabs_nav">
							<ul>
								<li class="profile_tab_item active" data-section="general-info">
									<a href="#general-info">
										<span class="nav_icon"><i class="icon_documents_alt"></i></span>
										<span class="nav_text"><?php esc_html_e( 'Informations générales', 'eventlist' ); ?></span>
										<span class="validation_status">
											<i class="icon_check validation_check"></i>
										</span>
									</a>
								</li>
								<li class="profile_tab_item" data-section="presentation">
									<a href="#presentation">
										<span class="nav_icon"><i class="icon_images"></i></span>
										<span class="nav_text"><?php esc_html_e( 'Présentation', 'eventlist' ); ?></span>
										<span class="validation_status">
											<i class="icon_check validation_check"></i>
										</span>
									</a>
								</li>
								<li class="profile_tab_item" data-section="localisation">
									<a href="#localisation">
										<span class="nav_icon"><i class="icon_pin_alt"></i></span>
										<span class="nav_text"><?php esc_html_e( 'Localisation', 'eventlist' ); ?></span>
										<span class="validation_status">
											<i class="icon_check validation_check"></i>
										</span>
									</a>
								</li>
								<li class="profile_tab_item" data-section="creneaux">
									<a href="#creneaux">
										<span class="nav_icon"><i class="icon_calendar"></i></span>
										<span class="nav_text"><?php esc_html_e( 'Créneaux', 'eventlist' ); ?></span>
										<span class="validation_status">
											<i class="icon_check validation_check"></i>
										</span>
									</a>
								</li>
								<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
									<li class="profile_tab_item" data-section="billetterie">
										<a href="#billetterie">
											<span class="nav_icon"><i class="icon_tag_alt"></i></span>
											<span class="nav_text"><?php esc_html_e( 'Billetterie', 'eventlist' ); ?></span>
											<span class="validation_status">
												<i class="icon_check validation_check"></i>
											</span>
										</a>
									</li>
								<?php } ?>
								<li class="profile_tab_item" data-section="publication">
									<a href="#publication">
										<span class="nav_icon"><i class="icon_cloud-upload_alt"></i></span>
										<span class="nav_text"><?php esc_html_e( 'Publication', 'eventlist' ); ?></span>
										<span class="validation_status">
											<i class="icon_check validation_check"></i>
										</span>
									</a>
								</li>
								<?php if ( apply_filters( 'el_create_event_show_extra_service_tab', true ) == true ): ?>
									<li class="profile_tab_item" data-section="services-extra">
										<a href="#services-extra">
											<span class="nav_icon"><i class="icon_star"></i></span>
											<span class="nav_text"><?php esc_html_e( 'Services Extra', 'eventlist' ); ?></span>
											<span class="validation_status">
												<i class="icon_check validation_check"></i>
											</span>
										</a>
									</li>
								<?php endif; ?>
							</ul>
						</nav>
					</div>

					<!-- Contenu unique avec encarts visibles -->
					<div class="profile_content_area">

						<!-- ENCART 1: Informations générales -->
						<div id="general-info" class="form_card">
							<div class="form_card_header">
								<h2 class="form_card_title">
									<i class="icon_documents_alt"></i>
									<?php esc_html_e( 'Informations générales', 'eventlist' ); ?>
								</h2>
								<p class="form_card_description"><?php esc_html_e( 'Les informations essentielles de votre événement', 'eventlist' ); ?></p>
							</div>
							<div class="form_card_content" id="mb_basic">
								<?php echo el_get_template( '/vendor/__edit-event-general.php', array( 'event_req_field' => $event_req_field ) ); ?>
							</div>
						</div>

						<!-- ENCART 2: Présentation (Description, Images, Vidéo) -->
						<div id="presentation" class="form_card">
							<div class="form_card_header">
								<h2 class="form_card_title">
									<i class="icon_images"></i>
									<?php esc_html_e( 'Présentation', 'eventlist' ); ?>
								</h2>
								<p class="form_card_description"><?php esc_html_e( 'Présentez votre événement avec du texte, des images et une vidéo', 'eventlist' ); ?></p>
							</div>
							<div class="form_card_content">
								<?php echo el_get_template( '/vendor/__edit-event-presentation.php', array( 'event_req_field' => $event_req_field ) ); ?>
							</div>
						</div>

						<!-- ENCART 3: Localisation -->
						<div id="localisation" class="form_card">
							<div class="form_card_header">
								<h2 class="form_card_title">
									<i class="icon_pin_alt"></i>
									<?php esc_html_e( 'Localisation', 'eventlist' ); ?>
								</h2>
								<p class="form_card_description"><?php esc_html_e( 'Où se déroule votre événement ?', 'eventlist' ); ?></p>
							</div>
							<div class="form_card_content">
								<?php echo el_get_template( '/vendor/__edit-event-localisation.php', array( 'event_req_field' => $event_req_field ) ); ?>
							</div>
						</div>

						<!-- ENCART 4: Créneaux (Calendrier) -->
						<?php if( apply_filters( 'el_create_event_show_calendar_tab', true ) ){ ?>
							<div id="creneaux" class="form_card">
								<div class="form_card_header">
									<h2 class="form_card_title">
										<i class="icon_calendar"></i>
										<?php esc_html_e( 'Créneaux', 'eventlist' ); ?>
									</h2>
									<p class="form_card_description"><?php esc_html_e( 'Définissez les dates et horaires de votre événement', 'eventlist' ); ?></p>
								</div>
								<div class="form_card_content" id="mb_calendar">
									<?php echo el_get_template( '/vendor/__edit-event-calendar.php' ); ?>
								</div>
							</div>
						<?php } ?>

						<!-- ENCART 5: Billetterie -->
						<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
							<div id="billetterie" class="form_card">
								<div class="form_card_header">
									<h2 class="form_card_title">
										<i class="icon_tag_alt"></i>
										<?php esc_html_e( 'Billetterie', 'eventlist' ); ?>
									</h2>
									<p class="form_card_description"><?php esc_html_e( 'Configurez les billets et tarifs pour votre événement', 'eventlist' ); ?></p>
								</div>
								<div class="form_card_content" id="mb_ticket">
									<?php echo el_get_template( '/vendor/__edit-event-ticket.php' ); ?>
								</div>
							</div>
						<?php } ?>

						<!-- ENCART 6: Publication -->
						<div id="publication" class="form_card">
							<div class="form_card_header">
								<h2 class="form_card_title">
									<i class="icon_cloud-upload_alt"></i>
									<?php esc_html_e( 'Publication', 'eventlist' ); ?>
								</h2>
								<p class="form_card_description"><?php esc_html_e( 'Choisissez la visibilité et le statut de votre événement', 'eventlist' ); ?></p>
							</div>
							<div class="form_card_content">
								<?php echo el_get_template( '/vendor/__edit-event-publication.php', array( 'event_req_field' => $event_req_field ) ); ?>
							</div>
						</div>

						<!-- ENCART 7: Services Extra (optionnel) -->
						<?php if ( apply_filters( 'el_create_event_show_extra_service_tab', true ) == true ): ?>
							<div id="services-extra" class="form_card">
								<div class="form_card_header">
									<h2 class="form_card_title">
										<i class="icon_star"></i>
										<?php esc_html_e( 'Services Extra', 'eventlist' ); ?>
									</h2>
									<p class="form_card_description"><?php esc_html_e( 'Ajoutez des services supplémentaires à votre événement', 'eventlist' ); ?></p>
								</div>
								<div class="form_card_content" id="mb_extra_service">
									<?php echo el_get_template( '/vendor/__edit-event-extra-service.php' ); ?>
								</div>
							</div>
						<?php endif; ?>

						<!-- Coupons et autres (cachés pour l'instant) -->
						<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' && apply_filters( 'el_edit_event_show_coupon', true ) ) { ?>
							<div id="mb_coupon" style="display:none;">
								<?php echo el_get_template( '/vendor/__edit-event-coupon.php' ); ?>
							</div>
						<?php } ?>

						<?php if( apply_filters( 'el_create_event_show_member_tab', true ) && EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ){ ?>
							<div id="mb_api_key" style="display:none;">
								<?php echo el_get_template( '/vendor/__edit-event-api-key.php' ); ?>
							</div>
						<?php } ?>

						<?php if ( EL()->options->cancel->get('cancel_enable', 1 ) && EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
							<div id="mb_cancel_booking" style="display:none;">
								<?php echo el_get_template( '/vendor/__edit-event-cancel-booking.php' ); ?>
							</div>
						<?php } ?>

						<?php echo apply_filters( 'meup_send_create_event_recapcha', '' ); ?>

						<!-- Bouton de soumission -->
						<div class="wrap_btn_submit">
							<input class="el_edit_event_submit el_btn_add" name="el_edit_event_submit" type="submit" value="<?php esc_html_e( 'Save Event', 'eventlist' ); ?>" />
							<?php wp_nonce_field( 'el_edit_event_nonce', 'el_edit_event_nonce' ); ?>
							<div class="submit-load-more sendmail">
								<div class="load-more">
									<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
								</div>
							</div>
						</div>

						<p class="error-total-event"><?php echo esc_html_e('You should upgrade to high package because your current package is limit number events', 'eventlist') ?></p>
						<p class="error-time-limit"><?php echo esc_html_e('Your package time is expired', 'eventlist') ?></p>

					</div> <!-- End form_content_area -->
				</form>

			<?php else: 
				esc_html_e( 'You don\'t have permission add new event', 'eventlist' );
			endif; ?>

		</div>

	</div>

</div>