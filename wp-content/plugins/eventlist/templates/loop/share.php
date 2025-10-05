<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php if( has_filter( 'ova_share_social' ) ){ ?>
	<div class="el-share-social">
		<span class="share-social">
			<a href="#" rel="nofollow" aria-label="<?php esc_attr_e( 'share', 'eventlist' ); ?>" >
				<i class="fa fa-share-alt"></i>
			</a>
			<?php echo apply_filters('ova_share_social', get_the_permalink(), get_the_title() ); ?>
		</span>
	</div>
	
<?php } ?>