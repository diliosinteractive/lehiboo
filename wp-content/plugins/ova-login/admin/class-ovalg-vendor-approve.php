<?php defined( 'ABSPATH' ) || exit;

class Ova_Login_Vendor_Approve {

    /**
     * Display the list table page
     *
     * @return Void
     */
    public static function list_table_page(){
        $vendor_approve_table = new Vendor_Approve_List_Table();
        $vendor_approve_table->prepare_items();
        $page_filter = isset( $_GET['filter_user'] ) ? sanitize_text_field( $_GET['filter_user'] ) : '';
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <?php esc_html_e( 'Manage Vendor', 'ova-login' ); ?>
            </h1>
            <hr class="wp-header-end">
            <div class="ova_vendor_approve_table">
                
                <?php
                /* display message */
                if ( isset( $_GET['send_mail'] ) ) {
                    if ( $_GET['send_mail'] == 'error' ) {
                        ?>
                         <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'An error occurred while sending mail to vendor.', 'ova-login' ); ?></p></div>
                        <?php
                    } elseif ( $_GET['send_mail'] == 'success' ) {
                        $send_mail_mess = esc_html__( 'Send email to vendor successfully.', 'ova-login' );
                        ?>
                        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Send email to vendor successfully.', 'ova-login' ); ?></p></div>
                        <?php
                    }
                }

                if ( isset( $_GET['status'] ) && isset( $_GET['action'] ) ) {
                    if ( $_GET['status'] == 'error' ) {
                        switch ( $_GET['action'] ) {
                            case 'reject':
                            ?>
                            <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Refuse to fail.', 'ova-login' ); ?></p></div>
                            <?php
                                break;
                            case 'approve':
                            ?>
                            <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Approval failed.', 'ova-login' ); ?></p></div>
                            <?php
                                break;
                            default:
                                break;
                        }
                    } elseif ( $_GET['status'] == 'success' ) {
                        switch ( $_GET['action'] ) {
                            case 'reject':
                            ?>
                            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Refused successfully.', 'ova-login' ); ?></p></div>
                            <?php
                                break;
                            case 'approve':
                            ?>
                            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Approved successfully.', 'ova-login' ); ?></p></div>
                            <?php
                                break;
                            default:
                                break;
                        }
                    }
                }
                ?>
                <img class="ova_vendor_approve_loader" src="<?php echo esc_url( includes_url() . 'js/tinymce/skins/lightgray/img//loader.gif' ); ?>" />
                <?php
                $vendor_approve_table->views();
                ?>
                <form class="ova_vendor_approve_table_wrapper" method="POST">
                    <?php
                    $vendor_approve_table->search_box(esc_html__( 'Search User', 'ova-login' ), 'ova_vendor_approve_search');
                    $vendor_approve_table->display();
                    ?>
                </form>
                <div id="ova-dialog-user-info"
                title="<?php esc_attr_e( 'User Info', 'ova-login' ); ?>"
                data-approve="<?php esc_attr_e( 'Approve','ova-login' ); ?>"
                data-reject="<?php esc_attr_e( 'Reject', 'ova-login' ); ?>"
                data-page="<?php echo esc_attr( $page_filter ); ?>"
                data-id=""
                data-nonce="<?php echo esc_attr( wp_create_nonce( 'ovalg_vendor_approve_action' ) ); ?>">
                    <span class="spinner"></span>
                    <div class="table_wrapper"></div>
                </div>

                <div id="ova-dialog-reason-reject-form" title="<?php esc_attr_e( 'Reason for Rejection', 'ova-login' ); ?>" data-button="<?php esc_attr_e( 'Send', 'ova-login' ); ?>">
                    <form>
                        <fieldset>
                            <textarea id="ova_reason_reject_mess" cols="40" rows="10"></textarea>
                        </fieldset>
                    </form>
                </div>

            </div>
        </div>
        <?php
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Vendor_Approve_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct( [
            'singular' => 'vendor_approve_item',
            'plural'   => 'vendor_approve_items',
            'ajax'     => false
        ] );
    }
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items(){

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->process_bulk_action();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = get_option( 'posts_per_page' );
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns(){
        $columns = array(
            'cb'          => '<label class="screen-reader-text" for="cb-select-all-1">'.esc_html__( 'Select All', 'ova-login' ).'</label>
                <input id="cb-select-all" type="checkbox">',
            'ID'          => 'ID',
            'user_login'  => __( 'Username', 'ova-login' ),
            'user_email'  => __( 'Email', 'ova-login' ),
            'update_vendor_time'  => __( 'Updated Time', 'ova-login' ),
            'info'        => __( 'Info', 'ova-login' ),
        );

        return $columns;
    }

    protected function extra_tablenav( $which ) {
        $order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : '';
        if ( $which == "top" ) : ?>
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e( 'Sort by date', 'ova-login' ); ?></label><select name="order" id="bulk-action-selector-top">
                    <option value="" <?php selected( $order, "" ); ?> ><?php esc_html_e( 'Sort by date', 'ova-login' ); ?></option>
                    <option value="asc" <?php selected( $order, "asc" ); ?> ><?php esc_html_e( 'Ascending', 'ova-login' ); ?></option>
                    <option value="desc" <?php selected( $order, "desc" ); ?> ><?php esc_html_e( 'Descending', 'ova-login' ); ?></option>
                </select>
                <input type="submit" id="doaction" class="button action" value="<?php esc_html_e( 'Filter', 'ova-login' ); ?>">
            </div>
        <?php endif;
    }

    protected function get_views() { 
        $views = array();
        $current = ( !empty($_REQUEST['filter_user']) ? $_REQUEST['filter_user'] : 'pending');

        $class = ( $current == 'pending' ? ' class="current"' :'' );
        $pending_url = remove_query_arg('filter_user');
        $pending_url = remove_query_arg('status',$pending_url);
        $pending_url = remove_query_arg('send_mail',$pending_url);
        $pending_number_user = ovalg_count_user_by_vendor_status('pending');
        $views['pending'] = '<a href="'.$pending_url.'" '.$class.'>'.esc_html__( 'Pending', 'ova-login' ).' ('.$pending_number_user.')'.'</a>';


        $approve_url = add_query_arg('filter_user','approve');
        $approve_url = remove_query_arg('status',$approve_url);
        $approve_url = remove_query_arg('send_mail',$approve_url);
        $class = ($current == 'approve' ? ' class="current"' :'');
        $approve_number_user = ovalg_count_user_by_vendor_status('approve');
        $views['approve'] = '<a href="'.$approve_url.'" '.$class.'>'.esc_html__( 'Approve', 'ova-login' ).' ('.$approve_number_user.')'.'</a>';


        $reject_url = add_query_arg('filter_user','reject');
        $reject_url = remove_query_arg('status',$reject_url);
        $reject_url = remove_query_arg('send_mail',$reject_url);
        $class = ($current == 'reject' ? ' class="current"' :'');
        $reject_number_user = ovalg_count_user_by_vendor_status('reject');
        $views['reject'] = '<a href="'.$reject_url.'" '.$class.'>'.esc_html__( 'Reject', 'ova-login' ).' ('.$reject_number_user.')'.'</a>';

        return $views;
    }


    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns(){
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns(){
        return array('update_vendor_time' => array('update_vendor_time', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data(){

        $keyword_search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        $filter_user = ( !empty( $_REQUEST['filter_user'] ) ? sanitize_text_field( $_REQUEST['filter_user'] ) : 'pending');

        $args = array(
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'meta_key' => 'update_vendor_time',
            'search_columns' => array( 'user_login', 'user_email' ),
            'meta_query' => array(
                array(
                    'key' => 'vendor_status',
                    'value' => $filter_user,
                ),
            ),
        );

        if ( $keyword_search ) {
            $args['search'] = '*'.$keyword_search.'*';
        }

        $users = get_users( $args );

        $users_arr = [];

        if ( count( $users ) > 0 ) {
            foreach ( $users as $user ) {
                $user_data = $user->to_array();
                $user_data['update_vendor_time'] = get_user_meta( $user->ID, 'update_vendor_time', true );
                $users_arr[] = $user_data;
            }
        }

        return $users_arr;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ){
        switch( $column_name ) {
            case 'ID':
            case 'user_login':
                return $item[ $column_name ];
            case 'user_email':
                return '<a href="mailto:'.esc_attr( $item[ $column_name ] ).'">'.esc_html( $item[ $column_name ] ).'</a>';
            case 'update_vendor_time':
                $format = get_option('date_format') . ' ' . get_option('time_format');
                return date_i18n( $format, $item[ $column_name ] );
                break;
            case 'info':
                return '<a href="#" data-id="'.esc_attr( $item['ID'] ).'" data-nonce="'.esc_attr( wp_create_nonce( 'ovalg_show_info_vendor' ) ).'" class="button button-primary show_info_vendor">'.esc_html__( 'Show Info', 'ova-login' ).'</a>';
            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ){
        // Set defaults
        $orderby = 'update_vendor_time';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if( !empty( $_GET['orderby']) ){
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if( !empty($_GET['order']) ){
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if( $order === 'asc' ){
            return $result;
        }

        return -$result;
    }

    function column_cb( $item ) {
        return '<input id="cb-select-'.esc_attr( $item['ID'] ).'" type="checkbox" name="user[]" value="'.esc_attr( $item['ID'] ).'">';
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'bulk-approve' => __( 'Approve', 'ova-login' ),
            'bulk-reject' => __( 'Reject', 'ova-login' ),
        ];

        return $actions;
    }

    public function no_items() {
        esc_html_e( 'No items avaliable.', 'ova-login' );
    }

    protected function process_bulk_action() {
        // Detect when a bulk action is being triggered.
        $nonce          = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
        $redirect_url   = isset( $_POST['_wp_http_referer'] ) ? sanitize_url( $_POST['_wp_http_referer'] ) : '';
        $paged          = isset( $_POST['paged'] ) ? sanitize_text_field( $_POST['paged'] ) : -1;
        $keyword_search = isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '';
        $users          = isset( $_POST['user'] ) ? $_POST['user'] : array();
        $order          = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : '';

        if ( $redirect_url ) {

            if ( ! $nonce || ! wp_verify_nonce( $nonce, 'bulk-vendor_approve_items' ) ) {
                wp_safe_redirect( $redirect_url );
                exit();
            }

            $redirect_url = remove_query_arg('s', $redirect_url);
            $redirect_url = remove_query_arg('order', $redirect_url);
            $redirect_url = remove_query_arg('action', $redirect_url);
            $redirect_url = remove_query_arg('status', $redirect_url);
            $redirect_url = remove_query_arg('send_mail', $redirect_url);
            $redirect_url = remove_query_arg('paged', $redirect_url);

            if ( $keyword_search ) {
                $redirect_url = add_query_arg( 's', $keyword_search, $redirect_url );
            }

            if ( $order ) {
                $redirect_url = add_query_arg( 'order', $order, $redirect_url );
            }

            if ( $this->current_action() != -1 && count( $users ) > 0 ) {

                $flag_vendor_status = true;
                $flag_mail_status = true;
                switch ( $this->current_action() ) {
                    case 'bulk-approve':
                    $redirect_url = add_query_arg( 'action', 'approve', $redirect_url );
                    foreach ( $users as $user_id ) {
                        $user = get_user_by( 'id', $user_id );
                        
                        if ( ! update_user_meta( $user_id, 'vendor_status', 'approve' ) ) {
                            $flag_vendor_status = false;
                            $flag_mail_status = false;

                        } else {
                            $current_time = current_time( 'timestamp' );
                            update_user_meta( $user_id, 'update_vendor_time', $current_time);
                            $user->set_role( 'el_event_manager' );

                            if ( function_exists('EL') ) {
                                $enable_package = EL()->options->package->get( 'enable_package', 'yes' );
                                $default_package = EL()->options->package->get( 'package' );
                                $current_package = get_user_meta( $user_id, 'package', true );

                                if ( ! $current_package && $enable_package == 'yes' && $default_package ){
                                    $pid = EL_Package::instance()->get_package( $default_package );
                                    EL_Package::instance()->add_membership( $pid['id'], $user_id, $status = 'new' );
                                }
                            }

                            if ( ! ova_admin_send_mail_vendor_approve( $user->user_email ) ) {
                                $flag_mail_status = false;
                            }
                        }
                        
                    }
                    break;
                    case 'bulk-reject':
                    $redirect_url = add_query_arg( 'action', 'reject', $redirect_url );
                    foreach ( $users as $user_id ) {
                        $user = get_user_by( 'id', $user_id );
                        if ( ! update_user_meta( $user_id, 'vendor_status', 'reject' ) ) {
                            $flag_vendor_status = false;
                            $flag_mail_status = false;
                        } else {
                            $current_time = current_time( 'timestamp' );
                            update_user_meta( $user_id, 'update_vendor_time', $current_time);
                            $user->set_role('subscriber');

                            if ( ! ova_admin_send_mail_vendor_reject( $user->user_email ) ) {
                                $flag_mail_status = false;
                            }
                        }
                    }
                    break;
                    default:
                    break;
                }

                if ( ! $flag_vendor_status ) {
                    $redirect_url = add_query_arg( 'status', 'error', $redirect_url );
                } else {
                    $redirect_url = add_query_arg( 'status', 'success', $redirect_url );
                }

                if ( ! $flag_mail_status ) {
                    $redirect_url = add_query_arg( 'send_mail', 'error', $redirect_url );
                } else {
                    $redirect_url = add_query_arg( 'send_mail', 'success', $redirect_url );
                }

            }

            if ( $paged ) {
                $redirect_url = add_query_arg( 'paged', $paged, $redirect_url );
            }

            wp_safe_redirect( $redirect_url );
            exit();

        }

    }

}
?>