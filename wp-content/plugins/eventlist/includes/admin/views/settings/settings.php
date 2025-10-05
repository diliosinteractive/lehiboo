<?php
if ( !defined( 'ABSPATH' ) ) {
	exit();
}

$el_settings = apply_filters( 'el_admin_settings', array() );

?>

<?php if ( $el_settings ): ?>

	<?php $current_tab = isset( $_GET['tab'] ) && $_GET['tab'] ? sanitize_text_field( $_GET['tab'] ) : current( array_keys( $el_settings ) ); ?>

	<form method="POST" name="ova_el_options" action="options.php">
		<?php  settings_fields( $this->options->_prefix ); ?>
		<div class="wrap ova_el_settings_wrapper">
			<!--	Tabs	-->
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $el_settings as $key => $title ): ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ova_el_setting&tab=' . $key ) ); ?>" class="nav-tab<?php echo $current_tab === $key ? ' nav-tab-active' : ''; ?>" data-tab="<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $title ); ?>
					</a>
				<?php endforeach; ?>
			</h2>
			<!--	Content 	-->
			<div class="ova_el_wrapper_content" data-edit-url="<?php echo esc_attr(  wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ) )['path'] ); ?>">
				<?php foreach ( $el_settings as $key => $title ): ?>
					<div id="<?php echo esc_attr( $key ); ?>" class="el-tab <?php echo $current_tab === $key ? 'nav-tab-active' : ''; ?>">
						<?php do_action( 'el_admin_setting_' . $key . '_content' ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php submit_button(); ?>
	</form>
<?php endif; ?>