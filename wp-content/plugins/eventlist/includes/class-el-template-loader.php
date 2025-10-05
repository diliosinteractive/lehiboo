<?php if ( !defined( 'ABSPATH' ) ) exit;

class EL_Template_Loader {

	
	public function __construct() {
     // template loader
		add_filter( 'template_include', array( $this, 'template_loader' ) );
		add_filter( 'theme_page_templates', array( $this, 'el_theme_page_templates' ), 10, 4 );
		add_action( 'template_redirect', array( $this, 'el_template_redirect' ) );
	}

	/**
     * filter template
     */
	public function template_loader( $template ) {

		$post_type = isset($_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : get_post_type();
		$check_qrcode = isset( $_REQUEST['check_qrcode'] ) ? $_REQUEST['check_qrcode'] : '';
		$customer_check_qrcode = isset( $_REQUEST['customer_check_qrcode'] ) ? $_REQUEST['customer_check_qrcode'] : '';
		$info_qrcode = isset( $_REQUEST['info_qrcode'] ) ? $_REQUEST['info_qrcode'] : '';

		$file = '';
		$find = array();

		if( is_page_template( 'eventlist_authors' ) ){

			$file = 'authors.php';
			$find[] = $file;
			$find[] = el_template_path() . '/' . $file;				

		}else if ( $post_type !== 'event' && $post_type !== 'venue' && ! is_tax('event_cat')  && ! is_tax( 'event_loc' ) && ! is_tax( 'event_tag' ) && ! is_author() && ! el_is_tax_event() ){
			
			return $template;
		}
		
		if ( is_post_type_archive( 'event' ) || el_is_tax_event() ) {

			$file = 'archive-event.php';
			$find[] = $file;
			$find[] = el_template_path() . '/' . $file;

		} else if ( is_singular('event') ) {

			

			// Current user can Preview Event
			if( el_can_preview_event() && is_preview() ){

				$file = 'single-event-preview.php';
				$find[] = $file;
				$find[] = el_template_path() . '/' . $file;

			} else {
				$file = 'single-event.php';
				$find[] = $file;
				$find[] = el_template_path() . '/' . $file;
			}


		} else if ( is_tax( 'event_cat' ) || is_tax( 'event_tag' ) || is_tax( 'event_loc' ) ) {
			
			$term = get_queried_object();

			$taxonomy = $term->taxonomy;

			$file = 'taxonomy-' . $taxonomy . '.php';

			$find[] = 'taxonomy-' . $taxonomy . '-' . $term->slug . '.php';
			$find[] = el_template_path() . '/' . 'taxonomy-' . $taxonomy . '-' . $term->slug . '.php';
			$find[] = $file;
			$find[] = el_template_path() . '/' . $file;

		} else if ( is_post_type_archive( 'venue' ) ) {

			$file = 'archive-venue.php';
			$find[] = $file;
			$find[] = el_template_path() . '/' . $file;

		} else if ( is_singular('venue') ) {

			$file = 'single-venue.php';
			$find[] = $file;
			$find[] = el_template_path() . '/' . $file;


		} else if ( is_author() ) {

			$file = 'author.php';
			$find[] = $file;
			$find[] = el_template_path() . '/' . $file;

		}


		if( $check_qrcode ){
			$file = 'ticket-info.php';
			$find[] = $file;
			$find[] = el_template_path() . '/' . $file;
			
		}

		if ( $customer_check_qrcode == 'true' || $info_qrcode ) {
			$file = 'ticket-info-no-checkin.php';
			$find[] = $file;
			$find[] = el_template_path() . '/' . $file;
		}

		if ( $file ) {

			$find[] = el_template_path() . $file;

			$el_template = untrailingslashit( EL_PLUGIN_PATH ) . '/templates/' . $file;

         // Find Template in theme
			$template = locate_template( array_unique( $find ) );

         // If template doesn't have in theme, it will get in plugin
			if ( !$template && file_exists( $el_template ) ) {
				$template = $el_template;
			}

		}




		return $template;
	}

	public function el_theme_page_templates( $page_templates, $wp_theme, $post ){

		
		$page_templates = [
			'eventlist_authors' => _x( 'Authors', 'Page Template', 'eventlist' ),
		] + $page_templates;

		return $page_templates;

	}

	public function el_template_redirect(){
		$el_login_booking = EL()->options->checkout->get('el_login_booking','no');
		$cart_page_id = EL()->options->general->get('cart_page_id','');
		if ( is_page( $cart_page_id ) ) {
			if ( $el_login_booking === 'yes' ) {
			if ( ! is_user_logged_in() ) {
				$ide 	= isset( $_GET['ide'] ) ? $_GET['ide'] : '';
				$idcal 	= isset( $_GET['idcal'] ) ? $_GET['idcal'] : '';
				$cart_page_current = add_query_arg( 
					array(
					    'ide' => $ide,
					    'idcal' => $idcal,
					), 
					get_cart_page() 
				);

				$login_link = add_query_arg( 
					array(
						'redirect_to' => urlencode( $cart_page_current ),
					), 
					get_login_page()
				);

				wp_safe_redirect( $login_link );
				exit();
				}
			}
		}
		
	}

}

new EL_Template_Loader();
