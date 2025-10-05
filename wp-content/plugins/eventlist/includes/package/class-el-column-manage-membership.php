<?php
defined( 'ABSPATH' ) || exit();

if( !class_exists( 'EL_Column_Manage_Membership' ) ){

	class EL_Column_Manage_Membership{

		public function __construct(){
			add_action( 'manage_manage_membership_posts_custom_column', array( $this, 'memebership_posts_custom_column' ), 10, 2  );
			add_filter( 'manage_edit-manage_membership_sortable_columns', array( $this, 'posts_column_register_sortable') , 10 ,1 );
			add_filter( 'manage_edit-manage_membership_columns',array($this, 'membership_replace_column_title_method' ) );

		}
		

		public function memebership_posts_custom_column( $column_name, $post_id ) {

			if( $column_name == 'title' ){
				echo esc_html( get_the_title($post_id) );
			}

			if ($column_name == 'start_date') {
				$membership_start_date = get_post_meta( $post_id, OVA_METABOX_EVENT . 'membership_start_date', true );
				echo esc_html( date_i18n( get_option( 'date_format' ), $membership_start_date ) );

			}

			if ($column_name == 'end_date') {
				$membership_end_date = get_post_meta( $post_id, OVA_METABOX_EVENT . 'membership_end_date', true );
				if( $membership_end_date == '-1' ){
					esc_html_e( 'Unlimit', 'eventlist' );
				}else{
					echo esc_html( date_i18n( get_option( 'date_format' ), $membership_end_date ) );	

				}
			}

			if ($column_name == 'status') {
				echo esc_html( get_post_meta( $post_id, OVA_METABOX_EVENT . 'status', true ) );
			}

			if ($column_name == 'total') {
				$total = get_post_meta( $post_id, OVA_METABOX_EVENT . 'total', true );
				echo wp_kses_post( el_price( $total ) );
			}

			if ($column_name == 'payment') {
				$payment = get_post_meta( $post_id, OVA_METABOX_EVENT . 'payment', true );
				$wooid = get_post_meta( $post_id, OVA_METABOX_EVENT . 'wooid', true );
				$woo_link = $wooid ? ' - <a target="_blank" href="'.home_url('/').'wp-admin/post.php?post='.$wooid.'&action=edit">'.$wooid.'</a>' : '';
				echo wp_kses_post( $payment.$woo_link );	

			}

			if ($column_name == 'user_name') {
				$author_id = get_post_meta( $post_id, OVA_METABOX_EVENT . 'membership_user_id', true );
				?>
				<a href="<?php echo esc_url( get_author_posts_url($author_id) ); ?>"><?php echo esc_html( get_the_author_meta('user_nicename', $author_id) ); ?></a>
				<?php
			}


		}

		public function membership_replace_column_title_method( $columns ) {

			$columns = array(
				'cb' 			=> "<input type ='checkbox' />",
				'title' 		=> esc_html__( 'Package', "eventlist" ),
				'start_date' 	=> esc_html__( 'Start Date', 'eventlist' ),
				'end_date' 		=> esc_html__( 'End Date', "eventlist" ),
				'total' 		=> esc_html__( "Total", "eventlist" ),
				'user_name' 	=> esc_html__( "User Name", "eventlist" ),
				'payment' 		=> esc_html__( "Payment", "eventlist" ),
				'date' 			=> esc_html__( 'Created', 'eventlist' )
				
			);

			return $columns;  
		}

		
		function posts_column_register_sortable( $columns ) {
			$columns['event_id'] = 'event_id';
			return $columns;
		}

	}
	new EL_Column_Manage_Membership();

}