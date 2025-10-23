<?php
/**
 * Template: Vérification OTP
 * Affiché dans le popup après inscription
 *
 * @package LeHiboo
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;
$user = get_userdata( $user_id );

if ( ! $user ) {
	wp_die( 'Utilisateur introuvable.' );
}

$email = $user->user_email;
$email_masked = substr( $email, 0, 2 ) . '***' . substr( strstr( $email, '@' ), 0, 3 ) . '***';
?>

<!-- Contenu OTP pour le popup -->
<div class="otp_verification_content">

	<!-- Icon -->
	<div class="otp_icon">
		<i class="fas fa-envelope-open-text"></i>
	</div>

	<!-- Title -->
	<h3 class="otp_title"><?php esc_html_e( 'Vérifiez votre email', 'meup-child' ); ?></h3>

	<!-- Description -->
	<p class="otp_description">
		<?php
		printf(
			esc_html__( 'Nous avons envoyé un code de vérification à %s', 'meup-child' ),
			'<strong>' . esc_html( $email_masked ) . '</strong>'
		);
		?>
	</p>

	<!-- Formulaire OTP -->
	<form id="otp_verification_form" class="otp_form" method="post">

		<!-- Champs OTP (6 digits) -->
		<div class="otp_inputs">
			<input type="text" class="otp_digit" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="0">
			<input type="text" class="otp_digit" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="1">
			<input type="text" class="otp_digit" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="2">
			<input type="text" class="otp_digit" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="3">
			<input type="text" class="otp_digit" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="4">
			<input type="text" class="otp_digit" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="5">
		</div>

		<input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>">
		<?php wp_nonce_field( 'otp_verification_nonce', 'otp_nonce' ); ?>

		<!-- Submit Button -->
		<button type="submit" class="otp_submit_btn">
			<span class="btn_text">
				<i class="fas fa-check-circle"></i>
				<?php esc_html_e( 'Vérifier', 'meup-child' ); ?>
			</span>
			<span class="btn_loader" style="display: none;">
				<i class="fas fa-spinner fa-spin"></i>
			</span>
		</button>

		<!-- Notifications -->
		<div class="otp_notifications">
			<div class="otp_notification success" style="display: none;"></div>
			<div class="otp_notification error" style="display: none;"></div>
		</div>

	</form>

	<!-- Resend OTP -->
	<div class="otp_resend_section">
		<p class="otp_resend_text">
			<?php esc_html_e( 'Vous n\'avez pas reçu le code ?', 'meup-child' ); ?>
		</p>
		<button type="button" class="otp_resend_btn" id="resend_otp_btn" data-user-id="<?php echo esc_attr( $user_id ); ?>">
			<i class="fas fa-redo-alt"></i>
			<?php esc_html_e( 'Renvoyer le code', 'meup-child' ); ?>
		</button>
		<div id="resend_countdown" style="display: none;">
			<?php esc_html_e( 'Renvoyer dans', 'meup-child' ); ?> <span id="countdown_timer">60</span>s
		</div>
	</div>

	<!-- Info -->
	<div class="otp_info">
		<i class="fas fa-info-circle"></i>
		<?php esc_html_e( 'Le code est valide pendant 10 minutes', 'meup-child' ); ?>
	</div>

</div>

<style>
/* OTP Verification Styles */
.otp_verification_content {
	text-align: center;
	padding: 20px;
	max-width: 400px;
	margin: 0 auto;
}

.otp_icon {
	width: 80px;
	height: 80px;
	margin: 0 auto 24px;
	background: linear-gradient(135deg, #ff601f 0%, #ff8247 100%);
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	font-size: 36px;
	box-shadow: 0 8px 24px rgba(255, 96, 31, 0.3);
}

.otp_title {
	margin: 0 0 16px 0;
	font-size: 24px;
	font-weight: 600;
	color: #333;
}

.otp_description {
	margin: 0 0 32px 0;
	font-size: 15px;
	color: #666;
	line-height: 1.6;
}

.otp_inputs {
	display: flex;
	justify-content: center;
	gap: 12px;
	margin-bottom: 32px;
}

.otp_digit {
	width: 50px;
	height: 60px;
	font-size: 24px;
	font-weight: 600;
	text-align: center;
	border: 2px solid #e5e3f2;
	border-radius: 8px;
	outline: none;
	transition: all 0.3s ease;
	background: #fff;
	color: #333;
}

.otp_digit:focus {
	border-color: #ff601f;
	box-shadow: 0 0 0 3px rgba(255, 96, 31, 0.1);
	transform: scale(1.05);
}

.otp_digit.filled {
	border-color: #ff601f;
	background: #fff8f5;
}

.otp_submit_btn {
	width: 100%;
	padding: 14px 24px;
	background: #ff601f;
	color: #fff;
	border: none;
	border-radius: 8px;
	font-size: 16px;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.3s ease;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	box-shadow: 0 4px 12px rgba(255, 96, 31, 0.3);
	margin-bottom: 24px;
}

.otp_submit_btn:hover:not(:disabled) {
	background: #e64e0f;
	transform: translateY(-2px);
	box-shadow: 0 6px 16px rgba(255, 96, 31, 0.4);
}

.otp_submit_btn:disabled {
	opacity: 0.6;
	cursor: not-allowed;
}

.otp_resend_section {
	margin: 24px 0;
	padding: 20px;
	background: #f9f9f9;
	border-radius: 8px;
}

.otp_resend_text {
	margin: 0 0 12px 0;
	font-size: 14px;
	color: #666;
}

.otp_resend_btn {
	background: transparent;
	border: 2px solid #ff601f;
	color: #ff601f;
	padding: 10px 20px;
	border-radius: 6px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.3s ease;
	display: inline-flex;
	align-items: center;
	gap: 8px;
}

.otp_resend_btn:hover:not(:disabled) {
	background: #ff601f;
	color: #fff;
	transform: translateY(-2px);
}

.otp_resend_btn:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

#resend_countdown {
	margin-top: 12px;
	font-size: 14px;
	color: #888;
}

#countdown_timer {
	font-weight: 600;
	color: #ff601f;
}

.otp_info {
	margin-top: 24px;
	padding: 12px 16px;
	background: #e3f2fd;
	border-left: 4px solid #2196f3;
	border-radius: 4px;
	font-size: 13px;
	color: #1565c0;
	display: flex;
	align-items: center;
	gap: 10px;
	justify-content: center;
}

.otp_notifications {
	margin-top: 16px;
}

.otp_notification {
	padding: 12px 16px;
	border-radius: 8px;
	font-size: 14px;
	margin-bottom: 12px;
}

.otp_notification.success {
	background: #d4edda;
	color: #155724;
	border: 1px solid #c3e6cb;
}

.otp_notification.error {
	background: #f8d7da;
	color: #721c24;
	border: 1px solid #f5c6cb;
}

@media (max-width: 600px) {
	.otp_digit {
		width: 40px;
		height: 50px;
		font-size: 20px;
	}

	.otp_inputs {
		gap: 8px;
	}
}
</style>
