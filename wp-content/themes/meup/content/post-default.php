<?php $sticky_class = is_sticky()?'sticky':''; ?>

<?php if( has_post_format('link') ){ ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class('post-wrap '. $sticky_class); ?>  >
		
		<?php
		$link = get_post_meta( $post->ID, 'format_link_url', true );
		$link_description = get_post_meta( $post->ID, 'format_link_description', true );

		if ( is_single() ) {
			printf( '<h1 class="entry-title"><a href="%1$s" target="blank">%2$s</a></h1>',
				$link,
				get_the_title()
			);
		} else {
			printf( '<h2 class="entry-title"><a href="%1$s" target="blank">%2$s</a></h2>',
				$link,
				get_the_title()
			);
		}
		?>
		<?php
		printf( '<a href="%1$s" target="blank">%2$s</a>',
			$link,
			$link_description
		);
		?>
	</article>

<?php }elseif ( has_post_format('aside') ){ ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class('post-wrap '. $sticky_class); ?>  >
		<div class="post-body">
			<?php the_content(); /* Display content  */ ?>
		</div>
	</article>

<?php }else{ ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class('post-wrap '. $sticky_class); ?>  >

		<?php if( has_post_format('audio') ){ ?>

			<div class="post-media">
				<?php meup_postformat_audio(); /* Display video of post */ ?>
			</div>

		<?php }elseif(has_post_format('gallery')){ ?>

			<?php meup_content_gallery(); /* Display gallery of post */ ?>

		<?php }elseif(has_post_format('video')){ ?>

			<div class="post-media">
				<?php meup_postformat_video(); /* Display video of post */ ?>
			</div>

		<?php }elseif(has_post_thumbnail()){ ?>
			
			

			<div class="post-media">
				<?php meup_content_thumbnail('full'); /* Display thumbnail of post */ ?>
			</div>

		<?php } ?>

		<div class="post-title">
			<?php meup_content_title(); /* Display title of post */ ?>
		</div>

		<div class="post-meta">
			<?php meup_content_meta_single(); /* Display Date, Author, Comment */ ?>
		</div>

		<div class="post-body">
			<div class="post-excerpt">
				<?php meup_content_body(); /* Display content of post or intro in category page */ ?>
			</div>
		</div>

		<?php if(!is_single()){ ?> 
			<?php meup_content_readmore(); /* Display read more button in category page */ ?>
		<?php }?>

		<?php if(is_single()){ ?>
			<?php meup_content_tag_single(); /* Display tags, category */ ?>
		<?php } ?>

		<div class="pagination-detail">
			<?php
			$prev_post = get_previous_post();
			?>
			<div class="pre">
				<?php
				if($prev_post) {
					?>
					<div class="num-1">
						<a rel="prev" href="<?php echo esc_attr(get_permalink($prev_post->ID)) ?>" rel="nofollow" aria-label="<?php esc_attr_e( 'previous article', 'meup' ); ?>" >
							<i class="arrow_left"></i>
						</a>
					</div>
					<div  class="num-2">
						<span class="second_font"><?php esc_html_e('Prev Post', 'meup') ?></span>
						<a rel="prev" href="<?php echo esc_attr(get_permalink($prev_post->ID)) ?>" >
							<?php echo esc_html(meup_custom_text(get_the_title($prev_post->ID), 6)) ?>
						</a>
					</div>
					<?php
				}
				?>
			</div>
			
			<div class="next">
				<?php
				$next_post = get_next_post();
				if($next_post) {
					?>
					<div class="num-1">
						<a rel="next" href="<?php echo esc_attr(get_permalink($next_post->ID)) ?> " rel="nofollow" aria-label="<?php esc_attr_e( 'next article', 'meup' ); ?>" >
							<i class="arrow_right"></i>
						</a>
					</div>
					<div  class="num-2">
						<span class="second_font"><?php esc_html_e('Next Post', 'meup') ?></span>
						<a rel="prev" href="<?php echo esc_attr(get_permalink($next_post->ID)) ?>" >
							<?php echo esc_html(meup_custom_text(get_the_title($next_post->ID), 6)) ?>
						</a>
					</div>
					<?php
				}
				?>
			</div>
		</div>

	</article>


<?php } ?>

