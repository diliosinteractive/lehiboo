<?php
if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly
}
el_get_template('email/email_header.php');
?>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
    <tr>
        <td>&nbsp;</td>
        <td class="container">
            <div class="content">

                <!-- START CENTERED WHITE CONTAINER -->
                <table role="presentation" class="main">

                    <!-- START MAIN CONTENT AREA -->
                    <tr>
                        <td class="wrapper">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <p><?php esc_html_e( 'Your New Password Is Set', 'eventlist' ); ?></p>
                                        <p><?php esc_html_e( 'Success! Your new password is in place and ready to use.', 'eventlist' ); ?></p>

                                        <p>
                                        <?php
                                        $url = isset( $args['url'] ) ? $args['url'] : '';
                                        $link = sprintf( wp_kses( __( 'If you didn’t change your password, we recommend that you <a href="%s">reset</a> it now to make sure your account stays secure.', 'eventlist' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
                                        echo wp_kses_post( $link ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- END MAIN CONTENT AREA -->
</table>
<!-- END CENTERED WHITE CONTAINER -->
<?php
el_get_template('email/email_footer.php');
