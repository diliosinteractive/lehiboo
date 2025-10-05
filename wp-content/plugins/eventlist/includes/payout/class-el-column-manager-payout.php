<?php
defined( 'ABSPATH' ) || exit();

if( !class_exists( 'EL_Column_payout_Manager' ) ){

	class EL_Column_payout_Manager{

		public function __construct(){

			add_action( 'manage_payout_posts_custom_column', array( $this, 'event_payout_posts_custom_column' ), 10, 2  );
			add_filter( 'manage_edit-payout_sortable_columns', array( $this, 'posts_column_register_sortable') , 10 ,1 );
			add_filter( 'manage_edit-payout_columns',array($this, 'event_payout_replace_column_title_method' ) );

			add_action( 'pre_get_posts', array( $this, 'el_manage_payout_column_data' ), 99 );
			
			add_action( 'restrict_manage_posts', array( $this, 'el_filter_withdrawal_status' ) );
			add_filter( 'parse_query', array( $this, 'el_filter_withdrawal_status_query' ) );

		}
	
		public function el_filter_withdrawal_status() {
		  
		  global $typenow;
		  
		    if ( $typenow == 'payout' ) { // Your custom post type slug
		      $current_withdrawal_status = '';
		      if( isset( $_GET['slug'] ) ) {
		        $current_withdrawal_status = $_GET['slug']; // Check if option has been selected
		      } ?>
		      <select name="slug" id="slug">
			      	<option value="Pending" <?php selected( 'Pending', $current_withdrawal_status ); ?>>
			      		<?php esc_html_e( 'Pending', 'eventlist' ); ?>
			      	</option>

			      	<option value="all" <?php selected( 'all', $current_withdrawal_status ); ?>>
			      		<?php esc_html_e( 'All', 'eventlist' ); ?>
			      	</option>

			      	<option value="Completed" <?php selected( 'Completed', $current_withdrawal_status ); ?>>
			      		<?php esc_html_e( 'Completed', 'eventlist' ); ?>
			      	</option>

			      	<option value="Canceled" <?php selected( 'Canceled', $current_withdrawal_status ); ?>>
			      		<?php esc_html_e( 'Canceled', 'eventlist' ); ?>
			      	</option>
		      </select>
		  <?php }
		}




		public function el_filter_withdrawal_status_query( $query ) {

		  global $pagenow;
		  // Get the post type
		  $post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

		  if ( is_admin() && $pagenow=='edit.php' && $post_type == 'payout' && isset( $_GET['slug'] ) && $_GET['slug'] !='all' ) {

		    $query->query_vars['meta_key'] = OVA_METABOX_EVENT . 'withdrawal_status';
		    $query->query_vars['meta_value'] = $_GET['slug'];
		    $query->query_vars['meta_compare'] = '=';

		  }else if ( is_admin() && $pagenow=='edit.php' && $post_type == 'payout'&& isset( $_GET['slug'] ) && $_GET['slug'] == 'all' ) {

		    $query->query_vars['meta_key'] = OVA_METABOX_EVENT . 'withdrawal_status';
		    $query->query_vars['meta_value'] = array( 'Pending','Completed', 'Canceled' );
		    $query->query_vars['meta_compare'] = 'IN';

		  }else if ( is_admin() && $pagenow=='edit.php' && $post_type == 'payout'&& isset( $_GET['slug'] ) && $_GET['slug'] == 'Canceled' ) {

		  	$query->query_vars['meta_key'] = OVA_METABOX_EVENT . 'withdrawal_status';
		   	$query->query_vars['meta_value'] = 'Canceled';
		   	$query->query_vars['meta_compare'] = '=';

		   } else if ( is_admin() && $pagenow=='edit.php' && $post_type == 'payout' ) {

		  	$query->query_vars['meta_key'] = OVA_METABOX_EVENT . 'withdrawal_status';
		   	$query->query_vars['meta_value'] = 'Pending';
		   	$query->query_vars['meta_compare'] = '=';

		   }


		}
		


		public function el_manage_payout_column_data ( $query ) {
			
			if ( isset( $_GET['post_type'] ) && 'payout' == $_GET['post_type'] && is_admin() && !isset( $_GET['action'] ) && !isset( $_GET['post_status'] ) ) {
				
				switch ( apply_filters( 'el_manage_payout_show_status_admin', '' ) ) {

					case 'Completed':
						$query->set( 'meta_key',  OVA_METABOX_EVENT . 'withdrawal_status' );
						$query->set( 'meta_value',  'Completed' );
						$query->set( 'meta_compare',  '=' );
						break;

					case 'Pending':
						$query->set( 'meta_key',  OVA_METABOX_EVENT . 'withdrawal_status' );
						$query->set( 'meta_value',  'Pending' );
						$query->set( 'meta_compare',  '=' );
						break;	

					case 'Canceled':
						$query->set( 'meta_key',  OVA_METABOX_EVENT . 'withdrawal_status' );
						$query->set( 'meta_value',  'Canceled' );
						$query->set( 'meta_compare',  '=' );
						break;		
					
					default:
						break;
				}

				
			
			};
			

			remove_action( 'pre_get_posts', 'el_manage_payout_column_data' );

		}
		

		public function event_payout_posts_custom_column( $column_name, $post_id ) {

			$id_author = get_post_field('post_author', $post_id);

			$payout_method = get_post_meta( $post_id, OVA_METABOX_EVENT . 'payout_method', true );

			if ($column_name == 'title') {
				echo esc_html( $post_id );

			}
            
			if( $column_name == 'name_vendor' ){
				$id_author = get_post_field('post_author', $post_id);
				$user_obj = get_userdata($id_author);
				echo esc_html($user_obj->data->user_nicename);
			}
			

			if ($column_name == 'customer_info') {

				$html = '';

				if( $payout_method == 'bank' ){

					if( $user_bank_owner = get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_owner', true ) ){
						?>
						<div class="customer_info_bank"><?php echo esc_html__("Account Owner: ", "eventlist"); ?><br><strong><?php echo esc_html( $user_bank_owner ); ?></strong><br></div>
						<?php
					}
					
					if( $user_bank_number = get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_number', true ) ){
						?>
						<div class="customer_info_bank"><?php echo esc_html__("Account Number: ", "eventlist"); ?><br><strong><?php echo esc_html( $user_bank_number ); ?></strong><br></div>
						<?php
					}
						
					if( $user_bank_name = get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_name', true ) ){
						?>
						<div class="customer_info_bank"><?php echo esc_html__("Bank Name: ", "eventlist"); ?><br><strong>
							<?php echo esc_html( $user_bank_name ); ?>
						</strong><br></div>
						<?php
					}

					if( $user_bank_branch = get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_branch', true ) ){
						?>
						<div class="customer_info_bank"><?php echo esc_html__("Branch: ", "eventlist"); ?><br><strong><?php echo esc_html( $user_bank_branch ); ?></strong><br></div>
						<?php
					}

					if( $user_bank_routing = get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_routing', true ) ){
						?>
						<div class="customer_info_bank"><?php echo esc_html__("Routing Number: ", "eventlist"); ?><br><strong><?php echo esc_html($user_bank_routing); ?></strong><br></div>
						<?php
					}

					if( $user_bank_iban = get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_iban', true ) ){
						?>
						<div class="customer_info_bank">
							<?php echo esc_html__("IBAN: ", "eventlist"); ?>
							<br><strong>
								<?php echo esc_html( $user_bank_iban ); ?>
							</strong><br></div>
						<?php
					}

					if( $user_bank_swift_code = get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_swift_code', true ) ){
						?>
						<div class="customer_info_bank">
							<?php echo esc_html__("Swift Code: ", "eventlist"); ?><br><strong>
								<?php echo esc_html( $user_bank_swift_code ); ?>
							</strong><br></div>
						<?php
					}

					if( $user_bank_ifsc_code = get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_ifsc_code', true ) ){
						?>
						<div class="customer_info_bank">
							<?php echo esc_html__("IFSC Code: ", "eventlist"); ?><br><strong>
								<?php echo esc_html( $user_bank_ifsc_code ); ?>
							</strong><br></div>
						<?php
					}
					

				}else if( $payout_method == 'paypal' ){

					echo esc_html( get_post_meta( $post_id, OVA_METABOX_EVENT . 'user_bank_paypal_email', true ) );

				}else{

					$data_payout_method_field = get_post_meta( $post_id, OVA_METABOX_EVENT . 'data_payout_method_field', true );
					
					$data_payout_method_field = ! empty( $data_payout_method_field ) ? json_decode( $data_payout_method_field , true) : [];
					$list_payout_method_field = [];
					
					$list_field = get_post_meta( $payout_method, 'ova_met_payout_method_group', true);

					?>

					<div class="field_payout_method">
						<?php if(!empty($list_field)) {?>
							<div class="list_field_payout_method">
								<ul>
									<?php
									foreach ($list_field as $field) {

										$label = isset($field['ova_met_label_method']) ? $field['ova_met_label_method'] : '';
										$name = isset($field['ova_met_name_method']) ? $field['ova_met_name_method'] : '';
										$payout_method_field = isset($data_payout_method_field[$name]) ? $data_payout_method_field[$name] : '' ;
										?>

										<li class="vendor_field other_field_method">


											<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html($label ); ?><br>
												<strong><?php echo esc_attr($payout_method_field); ?></strong><br>
											</label>

										</li>
										<?php
									}
									?>
								</ul>
							</div>
						<?php }?>
					</div>
					<?php
				}
			}

			if ($column_name == 'amount') {
				echo wp_kses_post( el_price( get_post_meta( $post_id, OVA_METABOX_EVENT . 'amount', true ) ) );
			
			}

			if ($column_name == 'withdraw_day' && get_post_meta( $post_id, OVA_METABOX_EVENT . 'time', true ) ) {
				$time = get_post_meta( $post_id, OVA_METABOX_EVENT . 'time', true );
				echo esc_html( wp_date( get_option( 'date_format' ).' '.get_option( 'time_format' ), $time ) );
			
			}


			if ($column_name == 'withdrawal_status') {

				echo esc_html( get_post_meta( $post_id, OVA_METABOX_EVENT . 'withdrawal_status', true ) );

			}

			

			if( $column_name == 'payout_method' ){

				if(( $payout_method) == 'bank' ) {

					$method = esc_html__('Bank ', 'eventlist');

				}else if (( $payout_method ) == 'paypal' ){

					$method = esc_html__(' Paypal', 'eventlist');

				}else {

					$title = get_the_title($payout_method);

					$method = $title; 

				}

				echo esc_html( $method );
			}
		}

		

		public function event_payout_replace_column_title_method( $columns ) {

			$columns = array(
				'cb' 				=> "<input type ='checkbox' />",
				'title' 			=> esc_html__( 'ID', "eventlist" ),
				'amount' 			=> esc_html__( "Amount", "eventlist" ),
				'name_vendor' 		=> esc_html__( 'Name Vendor', 'eventlist' ),
				'payout_method' 	=> esc_html__( "Payout Method", "eventlist" ),
				'customer_info' 	=> esc_html__( 'Info', "eventlist" ),
				'withdrawal_status' => esc_html__( "Withdrawal Status", "eventlist" ),
				'withdraw_day' 		=> esc_html__( "Withdrawal Date", "eventlist" ),
				'date' 				=> esc_html__( 'Date', 'eventlist' ),
				
			);
			
			return $columns;  
		}

		
		function posts_column_register_sortable( $columns ) {
			$columns['title'] = 'title';
			return $columns;
		}


	}
	new EL_Column_payout_Manager();

}