<?php

if( !defined( 'ABSPATH' ) ) exit();

?>
<div class="ova_row">

	<span><?php esc_html_e( 'Recommended size: 710x480px','eventlist' ); ?></span><br/>
	<a class="gallery-add button button-primary button-large text-right" href="#" data-uploader-title="<?php esc_attr_e( "Add image(s) to gallery", 'eventlist' ); ?>" data-uploader-button-text="<?php esc_attr_e( "Add image(s)", 'eventlist' ); ?>"><?php esc_html_e( "Add image(s)", 'eventlist' ); ?></a>
	<div class="gallery_wrap">
		<ul id="gallery-metabox-list" data-remove-image="<?php esc_attr_e( "Remove image", 'eventlist' ); ?>">

			<?php if ($this->get_mb_value( 'gallery' )) : foreach ($this->get_mb_value( 'gallery' ) as $key => $value) : $image = wp_get_attachment_image_src($value, 'el_thumbnail'); ?>
				<li>
					<input type="hidden" name="<?php echo esc_attr( $this->get_mb_name( 'gallery' ).'['.$key.']'); ?>" value="<?php echo esc_attr($value); ?>">
					<img class="image-preview" src="<?php echo esc_url($image[0]); ?>">
					<small><a class="remove-image" href="#"><?php esc_html_e( "Remove image", 'eventlist' ); ?></a></small>
				</li>
			<?php endforeach; endif; ?>

		</ul>
	</div>

</div>


<!-- Video -->
<hr>
<div class="ova_row">
	<div class="link_video">
		
		<label class="label" for="link_video"><strong><?php esc_html_e( 'Link Video:', 'eventlist' ); ?></strong>
			<?php esc_html_e( 'Ex: https://www.youtube.com/watch?v=YTT62UdAILs', 'eventlist' ); ?>
		</label>
	
		<input type="text" id="link_video" value="<?php echo esc_attr( $this->get_mb_value( 'link_video' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'link_video' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder ="<?php echo esc_attr( 'https://' ); ?>" autocorrect="off" autocapitalize="none" />

		
		
	</div>
</div>


<!-- single banner -->
<hr>
<div class="ova_row">
	<div class="wrap_single_banner">
		<label class="label"><strong><?php esc_html_e( 'Single Banner:', 'eventlist' ); ?></strong></label>
		<div class="radio_single_banner">
			<span> <input type="radio" name="<?php echo esc_attr( $this->get_mb_name( 'single_banner' ) ) ?>" class="single_banner" value="<?php echo esc_attr('thumbnail'); ?>"  <?php if ($this->get_mb_value( 'single_banner') == 'thumbnail' || $this->get_mb_value( 'single_banner') == '') echo esc_attr('checked') ; ?>  > <?php esc_html_e( 'Thumbnail', 'eventlist' ); ?> </span>

			<span> <input type="radio" name="<?php echo esc_attr( $this->get_mb_name( 'single_banner' ) ) ?>" class="single_banner" value="<?php echo esc_attr('gallery'); ?>" <?php if ($this->get_mb_value( 'single_banner') == 'gallery') echo esc_attr('checked') ; ?> > <?php esc_html_e( 'Gallery', 'eventlist' ); ?> </span>
		</div>
	</div>
</div>