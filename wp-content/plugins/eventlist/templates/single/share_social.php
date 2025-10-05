<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>


<div class="el_share_social">
	<a href="javascript: void()">
		<i class="social_share"></i>
		<?php esc_html_e('Share', 'eventlist') ?>
	</a>
	<?php echo apply_filters('ova_share_social', get_the_permalink(), get_the_title() ); ?>
</div>
