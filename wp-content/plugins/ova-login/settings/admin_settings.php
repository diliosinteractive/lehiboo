<?php 

if( !defined( 'ABSPATH' ) ) exit();


if( !class_exists( 'OVALG_Admin_Settings' ) ){

	/**
	 * Make Admin Class
	 */
	class OVALG_Admin_Settings{

		/**
		 * Construct Admin
		 */
		public function __construct(){
			add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
			add_action( 'admin_init', array( $this, 'register_options' ) );
		}


		public function load_media() {
			wp_enqueue_media();
		}


		public function print_options_section(){
			return true;
		}

		public function register_options(){

			$args = array(
				'type' => 'string', 
				'sanitize_callback' => array( $this, 'settings_callback' ),
				'default' => NULL,
			);

			register_setting(
				'ovalg_options_group', // Option group
				'ovalg_options', // Name Option
				$args // Call Back
			);

			/**
			 * General Settings
			 */
			// Add Section: General Settings
			add_settings_section(
				'ovalg_general_section_id', // ID
				esc_html__('General Setting', 'ova-login'), // Title
				array( $this, 'print_options_section' ),
				'ovalg_general_settings' // Page
			);

			add_settings_field(
				'login_page', // ID
				esc_html__('Sign In Page','ova-login'),
				array( $this, 'login_page' ),
				'ovalg_general_settings', // Page
				'ovalg_general_section_id' // Section ID
			);

			add_settings_field(
				'login_success_page', // ID
				esc_html__('Sign in Successfully Page','ova-login'),
				array( $this, 'login_success_page' ),
				'ovalg_general_settings', // Page
				'ovalg_general_section_id' // Section ID
			);

			add_settings_field(
				'register_page', // ID
				esc_html__('Sign up Page','ova-login'),
				array( $this, 'register_page' ),
				'ovalg_general_settings', // Page
				'ovalg_general_section_id' // Section ID
			);

			add_settings_field(
				'forgot_password_page', // ID
				esc_html__('Forgot Your Password Page','ova-login'),
				array( $this, 'forgot_password_page' ),
				'ovalg_general_settings', // Page
				'ovalg_general_section_id' // Section ID
			);

			add_settings_field(
				'pick_new_password_page', // ID
				esc_html__('Pick a New Password Page','ova-login'),
				array( $this, 'pick_new_password_page' ),
				'ovalg_general_settings', // Page
				'ovalg_general_section_id' // Section ID
			);


			add_settings_field(
				'term_condition_page_id', // ID
				esc_html__('Term Condition Page','ova-login'),
				array( $this, 'term_condition_page_id' ),
				'ovalg_general_settings', // Page
				'ovalg_general_section_id' // Section ID
			);

			add_settings_field(
				'admin_approve_vendor', // ID
				esc_html__('Admin Approved Vendor','ova-login'),
				array( $this, 'admin_approve_vendor' ),
				'ovalg_general_settings', // Page
				'ovalg_general_section_id' // Section ID
			);

			/**
			 * Register Form Settings
			 */
			// Add Section: Register Custom Field Settings
			add_settings_section(
				'ovalg_register_cf_section_id', // ID
				esc_html__('Register User Form Settings', 'ova-login'), // Title
				array( $this, 'print_options_section' ),
				'ovalg_register_cf_settings' // Page
			);

			add_settings_field(
				'show_email_confirm', // ID
				esc_html__('Show Confirm Email','ova-login'),
				array( $this, 'show_email_confirm' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			add_settings_field(
				'show_first_name', // ID
				esc_html__('Show First Name','ova-login'),
				array( $this, 'show_first_name' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			add_settings_field(
				'show_last_name', // ID
				esc_html__('Show Last Name','ova-login'),
				array( $this, 'show_last_name' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			add_settings_field(
				'show_password', // ID
				esc_html__('Show Password','ova-login'),
				array( $this, 'show_password' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			add_settings_field(
				'show_phone', // ID
				esc_html__('Show Phone','ova-login'),
				array( $this, 'show_phone' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			add_settings_field(
				'show_website', // ID
				esc_html__('Show Website','ova-login'),
				array( $this, 'show_website' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			add_settings_field(
				'show_job', // ID
				esc_html__('Show Job','ova-login'),
				array( $this, 'show_job' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			add_settings_field(
				'show_address', // ID
				esc_html__('Show Address','ova-login'),
				array( $this, 'show_address' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			add_settings_field(
				'show_description', // ID
				esc_html__('Show Description','ova-login'),
				array( $this, 'show_description' ),
				'ovalg_register_cf_settings', // Page
				'ovalg_register_cf_section_id' // Section ID
			);

			/**
			 * Recapcha Settings
			 */
			// Add Section: Recapcha Settings
			add_settings_section(
				'ovalg_recapcha_key_section_id', // ID
				esc_html__('Key Settings', 'ova-login'), // Title
				array( $this, 'print_options_section' ),
				'ovalg_recapcha_settings' // Page
			);

			add_settings_field(
				'recapcha_type', // ID
				esc_html__('reCAPTCHA Type','ova-login'),
				array( $this, 'recapcha_type' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_key_section_id' // Section ID
			);

			add_settings_field(
				'recapcha_site_key', // ID
				esc_html__('Site Key','ova-login'),
				array( $this, 'recapcha_site_key' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_key_section_id' // Section ID
			);

			add_settings_field(
				'recapcha_secret_key', // ID
				esc_html__('Secret Key','ova-login'),
				array( $this, 'recapcha_secret_key' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_key_section_id' // Section ID
			);

			add_settings_section(
				'ovalg_recapcha_status_section_id', // ID
				esc_html__('Status Settings', 'ova-login'), // Title
				array( $this, 'print_options_section' ),
				'ovalg_recapcha_settings' // Page
			);

			add_settings_field(
				'recapcha_enable_login', // ID
				esc_html__('Enable for Login','ova-login'),
				array( $this, 'recapcha_enable_login' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_status_section_id' // Section ID
			);

			add_settings_field(
				'recapcha_enable_register', // ID
				esc_html__('Enable for Register','ova-login'),
				array( $this, 'recapcha_enable_register' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_status_section_id' // Section ID
			);

			add_settings_field(
				'recapcha_enable_lost_password', // ID
				esc_html__('Enable for Lost Password','ova-login'),
				array( $this, 'recapcha_enable_lost_password' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_status_section_id' // Section ID
			);

			add_settings_field(
				'recapcha_enable_reset_password', // ID
				esc_html__('Enable for Reset Password','ova-login'),
				array( $this, 'recapcha_enable_reset_password' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_status_section_id' // Section ID
			);

			add_settings_field(
				'recapcha_enable_comment_form', // ID
				esc_html__('Enable for Comment Form','ova-login'),
				array( $this, 'recapcha_enable_comment_form' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_status_section_id' // Section ID
			);

			add_settings_section(
				'ovalg_recapcha_event_section_id', // ID
				esc_html__('Event Settings', 'ova-login'), // Title
				array( $this, 'print_options_section' ),
				'ovalg_recapcha_settings' // Page
			);

			add_settings_field(
				'recapcha_enable_send_mail_vendor', // ID
				esc_html__('Enable for Send Mail Vendor','ova-login'),
				array( $this, 'recapcha_enable_send_mail_vendor' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_event_section_id' // Section ID
			);

			add_settings_field(
				'recapcha_enable_create_event', // ID
				esc_html__('Enable for Create Event','ova-login'),
				array( $this, 'recapcha_enable_create_event' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_event_section_id' // Section ID
			);

			add_settings_field(
				'recapcha_enable_cart_event', // ID
				esc_html__('Enable for Cart Event','ova-login'),
				array( $this, 'recapcha_enable_cart_event' ),
				'ovalg_recapcha_settings', // Page
				'ovalg_recapcha_event_section_id' // Section ID
			);

			/**
			 * Mail Settings
			 */
			// Add Section: Mail Settings
			add_settings_section(
				'ovalg_mail_section_id', // ID
				esc_html__('Notice of new registrar', 'ova-login'), // Title
				array( $this, 'print_options_section' ),
				'ovalg_mail_settings' // Page
			);

			add_settings_field(
				'enable_send_new_vendor_email', // ID
				esc_html__('Enable','ova-login'),
				array( $this, 'enable_send_new_vendor_email' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_section_id' // Section ID
			);

			add_settings_field(
				'mail_new_vendor_subject', // ID
				esc_html__('Subject','ova-login'),
				array( $this, 'mail_new_vendor_subject' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_section_id' // Section ID
			);

			add_settings_field(
				'mail_new_vendor_from_name', // ID
				esc_html__('From name','ova-login'),
				array( $this, 'mail_new_vendor_from_name' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_section_id' // Section ID
			);

			add_settings_field(
				'mail_new_vendor_from_email', // ID
				esc_html__('Send from email','ova-login'),
				array( $this, 'mail_new_vendor_from_email' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_section_id' // Section ID
			);

			add_settings_field(
				'mail_new_vendor_recipient', // ID
				esc_html__('Recipient(s)','ova-login'),
				array( $this, 'mail_new_vendor_recipient' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_section_id' // Section ID
			);

			add_settings_field(
				'mail_new_vendor_send_admin', // ID
				esc_html__('Send to Admin','ova-login'),
				array( $this, 'mail_new_vendor_send_admin' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_section_id' // Section ID
			);

			add_settings_field(
				'mail_new_vendor_content', // ID
				esc_html__('Email Content','ova-login'),
				array( $this, 'mail_new_vendor_content' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_section_id' // Section ID
			);

			// Rejection

			add_settings_section(
				'ovalg_mail_reject_section_id', // ID
				esc_html__('Rejection', 'ova-login'), // Title
				array( $this, 'print_options_section' ),
				'ovalg_mail_settings' // Page
			);

			add_settings_field(
				'mail_vendor_reject_subject', // ID
				esc_html__('Subject','ova-login'),
				array( $this, 'mail_vendor_reject_subject' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_reject_section_id' // Section ID
			);

			add_settings_field(
				'mail_vendor_reject_from_name', // ID
				esc_html__('From name','ova-login'),
				array( $this, 'mail_vendor_reject_from_name' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_reject_section_id' // Section ID
			);

			add_settings_field(
				'mail_vendor_reject_from_email', // ID
				esc_html__('Send from email','ova-login'),
				array( $this, 'mail_vendor_reject_from_email' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_reject_section_id' // Section ID
			);

			add_settings_field(
				'mail_vendor_reject_content', // ID
				esc_html__('Email Content','ova-login'),
				array( $this, 'mail_vendor_reject_content' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_reject_section_id' // Section ID
			);

			// Approved

			add_settings_section(
				'ovalg_mail_approve_section_id', // ID
				esc_html__('Approved', 'ova-login'), // Title
				array( $this, 'print_options_section' ),
				'ovalg_mail_settings' // Page
			);

			add_settings_field(
				'mail_vendor_approve_subject', // ID
				esc_html__('Subject','ova-login'),
				array( $this, 'mail_vendor_approve_subject' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_approve_section_id' // Section ID
			);

			add_settings_field(
				'mail_vendor_approve_from_name', // ID
				esc_html__('From name','ova-login'),
				array( $this, 'mail_vendor_approve_from_name' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_approve_section_id' // Section ID
			);

			add_settings_field(
				'mail_vendor_approve_from_email', // ID
				esc_html__('Send from email','ova-login'),
				array( $this, 'mail_vendor_approve_from_email' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_approve_section_id' // Section ID
			);

			add_settings_field(
				'mail_vendor_approve_content', // ID
				esc_html__('Email Content','ova-login'),
				array( $this, 'mail_vendor_approve_content' ),
				'ovalg_mail_settings', // Page
				'ovalg_mail_approve_section_id' // Section ID
			);

		}

		public function settings_callback( $args ){
			// var_dump( $args );die();
			$new_input = array();
			if ( ! empty( $args ) ) {
				foreach ( $args as $key => $input ) {

					switch ( $key ) {
						case 'mail_new_vendor_content':
						case 'mail_vendor_reject_content':
						case 'mail_vendor_approve_content':
						$new_input[$key] = $input ? $input : '';
							break;
						
						default:
						$new_input[$key] = sanitize_text_field( $input ) ? sanitize_text_field( $input ) : '';
							break;
					}
					
				}
			}
			return $new_input;
		}
		


		public function login_page(){ ?>
		
			<select name="ovalg_options[login_page]">

			<?php if ( $dropdownpages = ovalg_dropdown_pages_login() ): ?>

				<?php foreach ( $dropdownpages as $key => $value ): ?>

					<?php
						$ovalg_login_page = OVALG_Settings::login_page();
					?>

					<option value="<?php echo esc_attr( $key ) ?>"<?php echo $ovalg_login_page == $key ? ' selected="selected"' : '' ?> >
						<?php printf( '%s', $value ).$ovalg_login_page ?>
					</option>

					
					<?php endforeach; ?>

				<?php endif; ?>

			</select>
			<br>
			<?php esc_html_e( 'Include shortcode in page: [custom-login-form]', 'ova-login' ) ?>

		<?php }

		public function login_success_page(){ ?>
		
			<select name="ovalg_options[login_success_page]">

			<?php if ( $dropdownpages = ovalg_dropdown_pages() ): ?>

				<?php
					$ovalg_login_page = OVALG_Settings::login_success_page();
				?>

				<?php foreach ( $dropdownpages as $key => $value ): ?>

					<option value="<?php echo esc_attr( $key ) ?>"<?php echo $ovalg_login_page == $key ? ' selected="selected"' : '' ?> >
						<?php printf( '%s', $value ).$ovalg_login_page ?>
					</option>

					
					<?php endforeach; ?>

				<?php endif; ?>

			</select>
			

		<?php }


		public function register_page(){ ?>
		
			<select name="ovalg_options[register_page]">

			<?php if ( $dropdownpages = ovalg_dropdown_pages_register() ): ?>

				<?php foreach ( $dropdownpages as $key => $value ): ?>

					<?php
						$ovalg_login_page = OVALG_Settings::register_page();
					?>

					<option value="<?php echo esc_attr( $key ) ?>"<?php echo $ovalg_login_page == $key ? ' selected="selected"' : '' ?> >
						<?php printf( '%s', $value ).$ovalg_login_page ?>
					</option>

					
					<?php endforeach; ?>

				<?php endif; ?>

			</select>

			<br>
			<?php esc_html_e( 'Include shortcode in page: [custom-register-form]', 'ova-login' ) ?>

		<?php }


		public function forgot_password_page(){ ?>
		
			<select name="ovalg_options[forgot_password_page]">

			<?php if ( $dropdownpages = ovalg_dropdown_pages_forgot_pw() ): ?>

				<?php foreach ( $dropdownpages as $key => $value ): ?>

					<?php
						$ovalg_login_page = OVALG_Settings::forgot_password_page();
					?>

					<option value="<?php echo esc_attr( $key ) ?>"<?php echo $ovalg_login_page == $key ? ' selected="selected"' : '' ?> >
						<?php printf( '%s', $value ).$ovalg_login_page ?>
					</option>

					
					<?php endforeach; ?>

				<?php endif; ?>

			</select>

			<br>
			<?php esc_html_e( 'Include shortcode in page: [custom-password-lost-form]', 'ova-login' ) ?>

		<?php }

		public function pick_new_password_page(){ ?>
		
			<select name="ovalg_options[pick_new_password_page]">

			<?php if ( $dropdownpages = ovalg_dropdown_pages_reset_pw() ): ?>

				<?php foreach ( $dropdownpages as $key => $value ): ?>

					<?php
						$ovalg_login_page = OVALG_Settings::pick_new_password_page();
					?>

					<option value="<?php echo esc_attr( $key ) ?>"<?php echo $ovalg_login_page == $key ? ' selected="selected"' : '' ?> >
						<?php printf( '%s', $value ).$ovalg_login_page ?>
					</option>

					
					<?php endforeach; ?>

				<?php endif; ?>

			</select>

			<br>
			<?php esc_html_e( 'Include shortcode in page: [custom-password-reset-form]', 'ova-login' ) ?>

		<?php }


		

		public static function create_admin_setting_page() { ?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( "General Settings", "ova-login" ); ?></h1>


				<a href="admin.php?page=ovareg_custom_field_settings" class="page-title-action">
					<?php esc_html_e( 'Custom Register Fields', 'ova-login' ); ?>
				</a>
				
				<?php if ( OVALG_Settings::admin_approve_vendor() == 'yes' ): ?>
					<a href="admin.php?page=ovalg_vendor_approve" class="page-title-action">
						<?php esc_html_e( 'Manage Vendor', 'ova-login' ); ?>
					</a>
				<?php endif; ?>

				<hr class="wp-header-end">

				<form id="ova_login_setting" method="post" action="options.php">
					<div id="el_tabs">

						<?php settings_fields( 'ovalg_options_group' ); // Options group ?>

						<!-- Menu Tab -->
						<ul>
							<li><a href="#ovalg_general_settings"><?php esc_html_e( 'General Settings', 'ova-login' ); ?></a></li>

							<li><a href="#ovalg_register_cf_settings"><?php esc_html_e( 'Register User Form Settings', 'ova-login' ); ?></a></li>
							<li><a href="#ovalg_recapcha_settings"><?php esc_html_e( 'reCAPTCHA Settings', 'ova-login' ); ?></a></li>
							<?php if ( OVALG_Settings::admin_approve_vendor() === 'yes' ): ?>
								<li><a href="#ovalg_mail_settings"><?php esc_html_e( 'Mail Settings', 'ova-login' ); ?></a></li>
							<?php endif; ?>
						</ul>

						<!-- General Settings -->  
						<div id="ovalg_general_settings" class="OVALG_Admin_Settings">
							<?php do_settings_sections( 'ovalg_general_settings' ); // Page ?>
						</div>
						<!-- Register Custom Field Settings -->
						<div id="ovalg_register_cf_settings" class="OVALG_Admin_Settings">
							<?php do_settings_sections( 'ovalg_register_cf_settings' ); // Page ?>
						</div>
						<!-- Recapcha Settings -->
						<div id="ovalg_recapcha_settings" class="OVALG_Admin_Settings">
							<?php do_settings_sections( 'ovalg_recapcha_settings' ); // Page ?>
						</div>
						<?php if ( OVALG_Settings::admin_approve_vendor() === 'yes' ): ?>
							<!-- Mail Settings -->
							<div id="ovalg_mail_settings" class="OVALG_Admin_Settings">
								<?php do_settings_sections( 'ovalg_mail_settings' ); // Page ?>
							</div>
						<?php endif; ?>
					</div>

					<?php submit_button(); ?>
				</form>
			</div>

		<?php }

        public static function create_register_setting_page(){
            ovalg_get_template("admin/custom_register_field.php");
        }

		public function term_condition_page_id(){ ?>
		
			<select name="ovalg_options[term_condition_page_id]">

			<?php if ( $dropdownpages = ovalg_dropdown_pages() ): ?>

				<?php
					$ovalg_term_condition_page = OVALG_Settings::term_condition_page_id();
				?>

				<?php foreach ( $dropdownpages as $key => $value ): ?>

					<option value="<?php echo esc_attr( $key ) ?>"<?php echo $ovalg_term_condition_page == $key ? ' selected="selected"' : '' ?> >
						<?php printf( '%s', $value ).$ovalg_term_condition_page ?>
					</option>

					
					<?php endforeach; ?>

				<?php endif; ?>

			</select>
			

		<?php }

		public function admin_approve_vendor(){
			$selected = OVALG_Settings::admin_approve_vendor();
			?>
			<select name="ovalg_options[admin_approve_vendor]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_email_confirm(){
			$selected = OVALG_Settings::show_email_confirm();
			?>
			<select name="ovalg_options[show_email_confirm]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_first_name(){
			$selected = OVALG_Settings::show_first_name();
			?>
			<select name="ovalg_options[show_first_name]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_last_name(){
			$selected = OVALG_Settings::show_last_name();
			?>
			<select name="ovalg_options[show_last_name]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_password(){
			$selected = OVALG_Settings::show_password();
			?>
			<select name="ovalg_options[show_password]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_phone(){
			$selected = OVALG_Settings::show_phone();
			?>
			<select name="ovalg_options[show_phone]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_website(){
			$selected = OVALG_Settings::show_website();
			?>
			<select name="ovalg_options[show_website]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_job(){
			$selected = OVALG_Settings::show_job();
			?>
			<select name="ovalg_options[show_job]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_address(){
			$selected = OVALG_Settings::show_address();
			?>
			<select name="ovalg_options[show_address]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function show_description(){
			$selected = OVALG_Settings::show_description();
			?>
			<select name="ovalg_options[show_description]">
				<option value="yes" <?php selected( $selected, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'ova-login' ); ?></option>
				<option value="no" <?php selected( $selected, 'no' ); ?> ><?php esc_html_e( 'No', 'ova-login' ); ?></option>
			</select>
			<?php
		}

		public function recapcha_type(){
			$checked = OVALG_Settings::recapcha_type();
			?>
			<label for="recapcha_type_v2"><input name="ovalg_options[recapcha_type]" <?php checked( $checked, 'v2'); ?> type="radio" id="recapcha_type_v2" value="v2"><?php echo esc_html('V2'); ?></label>
			<label for="recapcha_type_v3"><input name="ovalg_options[recapcha_type]" <?php checked( $checked, 'v3'); ?> type="radio" id="recapcha_type_v3" value="v3"><?php echo esc_html('V3'); ?></label>
			<?php
		}

		public function recapcha_site_key(){
			$input_value = OVALG_Settings::recapcha_site_key();
			?>
			<input type="text" class="regular-text" name="ovalg_options[recapcha_site_key]" value="<?php echo esc_attr( $input_value ); ?>">
			<?php
		}

		public function recapcha_secret_key(){
			$input_value = OVALG_Settings::recapcha_secret_key();
			?>
			<input type="text" class="regular-text" name="ovalg_options[recapcha_secret_key]" value="<?php echo esc_attr( $input_value ); ?>">
			<?php
		}

		public function recapcha_enable_login(){
			$checked = OVALG_Settings::recapcha_enable_login();
			?>
			<input name="ovalg_options[recapcha_enable_login]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<?php
		}

		public function recapcha_enable_register(){
			$checked = OVALG_Settings::recapcha_enable_register();
			?>
			<input name="ovalg_options[recapcha_enable_register]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<?php
		}

		public function recapcha_enable_lost_password(){
			$checked = OVALG_Settings::recapcha_enable_lost_password();
			?>
			<input name="ovalg_options[recapcha_enable_lost_password]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<?php
		}

		public function recapcha_enable_reset_password(){
			$checked = OVALG_Settings::recapcha_enable_reset_password();
			?>
			<input name="ovalg_options[recapcha_enable_reset_password]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<?php
		}

		public function recapcha_enable_comment_form(){
			$checked = OVALG_Settings::recapcha_enable_comment_form();
			?>
			<input name="ovalg_options[recapcha_enable_comment_form]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<?php
		}

		public function recapcha_enable_send_mail_vendor(){
			$checked = OVALG_Settings::recapcha_enable_send_mail_vendor();
			?>
			<input name="ovalg_options[recapcha_enable_send_mail_vendor]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<?php
		}

		public function recapcha_enable_create_event(){
			$checked = OVALG_Settings::recapcha_enable_create_event();
			?>
			<input name="ovalg_options[recapcha_enable_create_event]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<?php
		}

		public function recapcha_enable_cart_event(){
			$checked = OVALG_Settings::recapcha_enable_cart_event();
			?>
			<input name="ovalg_options[recapcha_enable_cart_event]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<?php
		}

		public function enable_send_new_vendor_email(){
			$checked = OVALG_Settings::enable_send_new_vendor_email();
			?>
			<input name="ovalg_options[enable_send_new_vendor_email]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<p class="description"><?php esc_html_e( 'Allow send email when new registrar','ova-login' ); ?></p>
			<?php
		}
		
		public function mail_new_vendor_subject(){
			$input_value = OVALG_Settings::mail_new_vendor_subject();
			?>
			<input name="ovalg_options[mail_new_vendor_subject]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'You will see subject in list mail','ova-login' ); ?></p>
			<?php
		}
		
		public function mail_new_vendor_from_name(){
			$input_value = OVALG_Settings::mail_new_vendor_from_name();
			?>
			<input name="ovalg_options[mail_new_vendor_from_name]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'The subject displays in mail detail','ova-login' ); ?></p>
			<?php
		}
		
		public function mail_new_vendor_from_email(){
			$input_value = OVALG_Settings::mail_new_vendor_from_email();
			?>
			<input name="ovalg_options[mail_new_vendor_from_email]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'Customers will know them to receive mail from which email address is','ova-login' ); ?></p>
			<?php
		}

		public function mail_new_vendor_recipient(){
			$input_value = OVALG_Settings::mail_new_vendor_recipient();
			?>
			<input name="ovalg_options[mail_new_vendor_recipient]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)','ova-login' ); ?></p>
			<?php
		}
		
		public function mail_new_vendor_send_admin(){
			$checked = OVALG_Settings::mail_new_vendor_send_admin();
			?>
			<input name="ovalg_options[mail_new_vendor_send_admin]" <?php checked( $checked, '1'); ?> type="checkbox" value="1">
			<p class="description"><?php esc_html_e( 'email the admin.', 'ova-login' ); ?></p>
			<?php
		}

		
		public function mail_new_vendor_content(){
			$content = OVALG_Settings::mail_new_vendor_content();
			$editor_id = 'mail_new_vendor_content';
			$settings  = array( 'textarea_name' => 'ovalg_options[mail_new_vendor_content]', 'textarea_rows' => 15, 'wpautop' => false);

			wp_editor( wpautop( $content ), $editor_id, $settings );
			?>
			<p class="description"><?php esc_html_e( 'Example: The account with email address [user_email] has requested to become a vendor at [your_website], please visit the [approve_url] link for approval.','ova-login' ); ?></p>
			<?php
		}

		public function mail_vendor_reject_subject(){
			$input_value = OVALG_Settings::mail_vendor_reject_subject();
			?>
			<input name="ovalg_options[mail_vendor_reject_subject]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'You will see subject in list mail','ova-login' ); ?></p>
			<?php
		}
		
		public function mail_vendor_reject_from_name(){
			$input_value = OVALG_Settings::mail_vendor_reject_from_name();
			?>
			<input name="ovalg_options[mail_vendor_reject_from_name]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'The subject displays in mail detail','ova-login' ); ?></p>
			<?php
		}
		
		public function mail_vendor_reject_from_email(){
			$input_value = OVALG_Settings::mail_vendor_reject_from_email();
			?>
			<input name="ovalg_options[mail_vendor_reject_from_email]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'Customers will know them to receive mail from which email address is','ova-login' ); ?></p>
			<?php
		}

		
		public function mail_vendor_reject_content(){
			$content = OVALG_Settings::mail_vendor_reject_content();
			$editor_id = 'mail_vendor_reject_content';
			$settings  = array( 'textarea_name' => 'ovalg_options[mail_vendor_reject_content]', 'textarea_rows' => 15, 'wpautop' => false);

			wp_editor( wpautop( $content ), $editor_id, $settings );
			?>
			<p class="description"><?php echo esc_html( '[reason]' ); ?></p>
			<?php
		}

		public function mail_vendor_approve_subject(){
			$input_value = OVALG_Settings::mail_vendor_approve_subject();
			?>
			<input name="ovalg_options[mail_vendor_approve_subject]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'You will see subject in list mail','ova-login' ); ?></p>
			<?php
		}
		
		public function mail_vendor_approve_from_name(){
			$input_value = OVALG_Settings::mail_vendor_approve_from_name();
			?>
			<input name="ovalg_options[mail_vendor_approve_from_name]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'The subject displays in mail detail','ova-login' ); ?></p>
			<?php
		}
		
		public function mail_vendor_approve_from_email(){
			$input_value = OVALG_Settings::mail_vendor_approve_from_email();
			?>
			<input name="ovalg_options[mail_vendor_approve_from_email]" type="text" value="<?php echo esc_attr( $input_value ); ?>">
			<p class="description"><?php esc_html_e( 'Customers will know them to receive mail from which email address is','ova-login' ); ?></p>
			<?php
		}

		
		public function mail_vendor_approve_content(){
			$content = OVALG_Settings::mail_vendor_approve_content();
			$editor_id = 'mail_vendor_approve_content';
			$settings  = array( 'textarea_name' => 'ovalg_options[mail_vendor_approve_content]', 'textarea_rows' => 15, 'wpautop' => false);

			wp_editor( wpautop( $content ), $editor_id, $settings );
			?>
			<p class="description"><?php echo esc_html( '[my_account]' ); ?></p>
			<?php
		}
		
	}
	new OVALG_Admin_Settings();
}
