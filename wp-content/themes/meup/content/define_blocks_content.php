<?php

/* This is functions define blocks to display post */

if ( ! function_exists( 'meup_content_thumbnail' ) ) {
	function meup_content_thumbnail( $size ) {
		if ( has_post_thumbnail()  && ! post_password_required() || has_post_format( 'image') )  :
			the_post_thumbnail( $size, array('class'=> 'img-responsive' ));
	endif;
}
}

if ( ! function_exists( 'meup_postformat_video' ) ) {
	function meup_postformat_video( ) { ?>
		<?php if(has_post_format('video') && wp_oembed_get(get_post_meta(get_the_id(), "ova_met_embed_media", true))){ ?>
			<div class="js-video postformat_video">
				<?php echo wp_oembed_get(get_post_meta(get_the_id(), "ova_met_embed_media", true)); ?>
			</div>
		<?php } ?>
	<?php }
}

if ( ! function_exists( 'meup_postformat_audio ') ) {
	function meup_postformat_audio( ) { ?>
		<?php if(has_post_format('audio') && wp_oembed_get(get_post_meta(get_the_id(), "ova_met_embed_media", true))){ ?>
			<div class="js-video postformat_audio">
				<?php echo wp_oembed_get(get_post_meta(get_the_id(), "ova_met_embed_media", true)); ?>
			</div>
		<?php } ?>
	<?php }
}

if ( ! function_exists( 'meup_content_title' ) ) {
	function meup_content_title() { ?>

		<?php if ( is_single() ) : ?>
			<h1 class="post-title second_font">
				<?php the_title(); ?>
			</h1>
			<?php else : ?>
				<h2 class="post-title">
					<a class="second_font" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
						<?php the_title(); ?>
					</a>
				</h2>
			<?php endif;
		}
	}

	if ( ! function_exists( 'meup_content_meta' ) ) {
		function meup_content_meta( ) { ?>
			<span class="post-meta-content">
				<?php if(get_the_category()) : ?>
					<span class="general-meta categories">
						<i class="flaticon-gift-box-outline"></i>
						<?php the_category('&sbquo;&nbsp;'); ?>
						<span class="meta-slash"><?php echo esc_html_e('|', 'meup') ?></span>
					</span>
				<?php endif ?>
				
				<span class="general-meta post-date">
					<span class="left"><i class="flaticon-clock"></i></span>
					<span class="right"><?php the_time( get_option( 'date_format' ) );?></span>
				</span>
			</span>
		<?php }
	}

	if ( ! function_exists( 'meup_content_meta_single' ) ) {
		function meup_content_meta_single( ) { ?>
			<span class="post-meta-content">
				<span class="general-meta post-date">
					<span class="left"><i class="flaticon-clock"></i></span>
					<span class="right"><?php the_time( get_option( 'date_format' ) );?></span>
				</span>
				<?php if(get_the_category()) : ?>
					<span class="general-meta categories">
						<span class="meta-slash"><?php echo esc_html_e('|', 'meup') ?></span>
						<i class="flaticon-gift-box-outline"></i>
						<?php the_category('&sbquo;&nbsp;'); ?>
					</span>
				<?php endif ?>
				
			</span>
		<?php }
	}

	if ( ! function_exists( 'meup_content_body' ) ) {
		function meup_content_body( ) { ?>
			<div class="post-excerpt">
				<?php if(is_single()){
					the_content();
					wp_link_pages( array(
						'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'meup' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
						'pagelink'    => '%',
						'separator'   => '',
					) );             
				}else{
					the_excerpt();
				}?>
			</div>

			<?php 
		}
	}

	if ( ! function_exists( 'meup_content_readmore' ) ) {
		function meup_content_readmore( ) { ?>
			<div class="post-footer">
				<div class="post-readmore-meup">
					<a class="btn btn-theme btn-theme-transparent second_font" href="<?php the_permalink(); ?>"><?php  esc_html_e('View detail', 'meup'); ?></a>
				</div>
			</div>
		<?php }
	}

	if ( ! function_exists( 'meup_content_tag' ) ) {
		function meup_content_tag( ) { ?>
			
			<footer class="post-tag">
				<?php if(has_tag()){ ?>
					<span class="post-tags">
						<span class="ovatags"><?php esc_html_e('Tags: ', 'meup'); ?></span>
						<?php the_tags('','&nbsp;&nbsp;',''); ?>
					</span>
				<?php } ?>
				<div class="clearboth"></div>
				<?php if(has_category( )){ ?>
					<span class="post-categories">
						<span class="ovacats"><?php esc_html_e('Categories: ', 'meup'); ?></span>
						<?php the_category('&nbsp;&nbsp;'); ?>
					</span>
				<?php } ?>

				<?php if( has_filter( 'meup_share_social' ) ){ ?>
					<div class="share_social">
						<span class="ova_label"><?php esc_html_e('Share: ', 'meup'); ?></span>
						<?php echo apply_filters('meup_share_social', get_the_permalink(), get_the_title() ); ?>
					</div>
				<?php } ?>
			</footer>
			
		<?php }
	}

	if ( ! function_exists( 'meup_content_tag_single' ) ) {
		function meup_content_tag_single( ) { ?>
			<?php if (has_tag() || has_filter( 'ova_share_social' ) ) : ?>
			<footer class="post-tag-constrau">
				<?php if(has_tag()){ ?>
					<div class="post-tags-constrau">
						<span class="ovatags second_font"><?php esc_html_e('Tags: ', 'meup'); ?></span>
						<?php the_tags('','',''); ?>
					</div>
				<?php } ?>

				<?php if( has_filter( 'ova_share_social' ) ){ ?>
					<div class="socials-inner">
						<div class="share-social">
							<span class="text-social second_font"><?php echo esc_html_e("Share: ", 'meup') ?></span>
							<a href="#" rel="nofollow" aria-label="<?php esc_attr_e( 'share social', 'meup' ) ?>"><i class="flaticon-share"></i></a>
							<?php echo apply_filters('ova_share_social', get_the_permalink(), get_the_title() ); ?>
						</div>
					</div>
				<?php } ?>
			</footer>
		<?php endif ?>

	<?php }
}

if ( ! function_exists( 'meup_content_gallery' ) ) {
	function meup_content_gallery( ) {

		$post_id = get_the_ID();

		$gallery = get_post_meta($post_id, 'ova_met_file_list', true) ? get_post_meta($post_id, 'ova_met_file_list', true) : '';

		$carousel_id = 'carousel'.$post_id.'gallery';

		$k = 0;
		if($gallery){ $i=0; ?>

			<div id="<?php echo esc_attr($carousel_id); ?>" class="carousel slide" data-ride="carousel">
				<!-- Indicators -->
				<ol class="carousel-indicators">
					<?php foreach ($gallery as $key => $value) { $active = ( $i == 0 ) ? 'active' : ''; ?>
					<li data-target="#<?php echo esc_attr($carousel_id); ?>" data-slide-to="<?php echo esc_attr($i); ?>" class="<?php echo esc_attr($active); ?>"></li>
					<?php $i++; } ?>
				</ol>

				<!-- Wrapper for slides -->
				<div class="carousel-inner" role="listbox">
					<?php foreach ($gallery as $key => $value) { 
						$active_dot = ( $k == 0 ) ? 'active' : ''; ?>
						<div class="carousel-item <?php echo esc_attr($active_dot); $k++; ?>">
							<img class="img-responsive" src="<?php  echo esc_attr($value); ?>" alt="<?php the_title_attribute(); ?>">
						</div>
					<?php } ?>
				</div>

			</div>

			<?php
		}
	}
}



//Custom comment List:
if ( ! function_exists( 'meup_theme_comment' ) ) {
	function meup_theme_comment($comment, $args, $depth) {

		$GLOBALS['comment'] = $comment;
		$comment_id = get_comment_ID();
		?>   
		<li <?php comment_class(); ?> id="li-comment-<?php echo esc_attr($comment_id); ?>">
			<article class="comment_item" id="comment-<?php echo esc_attr( $comment_id ); ?>">

				<header class="comment-author">

					<?php

						$comment_info = get_comment( $comment_id );

						$user_id = $comment_info->user_id;
						$author_id_image = get_user_meta( $user_id, 'author_id_image', true ) ? get_user_meta( $user_id, 'author_id_image', true ) : '';
						$img_path = ( $author_id_image && wp_get_attachment_image_url($author_id_image, 'el_thumbnail') ) ? wp_get_attachment_image_url($author_id_image, 'el_thumbnail') : EL_PLUGIN_URI.'assets/img/unknow_user.png';

						add_comment_meta($comment_id, 'el_comment_image_author', $img_path);
						$link_url = get_comment_meta($comment_id, 'el_comment_image_author', true );

						if( $img_path ){ ?>
							<a href="#" rel="nofollow">
								<img src="<?php echo esc_attr( $link_url ) ?>">
							</a>
							

						<?php } else {

							echo get_avatar($comment,$size='70', $default = 'mysteryman' ); 

						}
					
					?>

				</header>
				<section class="comment-details">

					<div class="author-name">

						<div class="name">
							<?php printf('%s', get_comment_author_link()) ?>

							<?php 
							if ( is_singular('event') ) {
								$user_id = $GLOBALS['comment']->user_id;
								$post_ID = $GLOBALS['comment']->comment_post_ID;

								$agrs_base = [
									'post_type' => 'el_bookings',
									'post_status' => 'publish',
									'meta_query' => [
										'relation' => 'AND',
										array(
											array(
												'key' => OVA_METABOX_EVENT . 'id_event',
												'value' => $post_ID
											),
											array(
												'key' => OVA_METABOX_EVENT . 'status',
												'value' => 'completed'
											)
										)
									],
								];
								$args = new WP_Query( $agrs_base );
								$id_booking = [];
								$user_id_purchase = [];

								if($args->have_posts() ) : while ( $args->have_posts() ) : $args->the_post();
									$id_booking[] = get_the_id();
								endwhile; endif; wp_reset_postdata();

								foreach ($id_booking as $value) {
									$user_id_purchase[] = get_post_meta( $value, OVA_METABOX_EVENT . 'id_customer', true );
								}

								foreach ($user_id_purchase as $k1 => $v1) {
									if ( $v1 == '0' ) {
										unset($user_id_purchase[$k1]);
									}
								}
								if ( in_array( $user_id, $user_id_purchase ) ) { ?>
									<span class="purchased"> <?php esc_html_e( 'purchased', 'meup' ); ?></span>
								<?php	}
							}
							?>
						</div>

						<div class="date">
							<?php if(is_singular( 'event' ) ) { ?>
								<span><?php printf(get_comment_date() . esc_html__( ' at ', 'meup' ) . get_comment_time()); ?></span>

							<?php } else { ?>
								
								<span><?php printf(get_comment_date() . esc_html__( ' at ', 'meup' ) . get_comment_time()); ?></span>

								<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>


							<?php } ?>

							<?php edit_comment_link( esc_html__( '(Edit)', 'meup' ), '  ', '' ); ?>

						</div>
						
					</div> 

					<?php if(is_singular( 'event' ) && function_exists('comment_rating_display_rating') ) { ?>
						<div class="rating">	
							<?php echo comment_rating_display_rating(); ?>
						</div>
					<?php } ?>

					<div class="comment-body clearfix comment-content">
						<?php comment_text(); ?>
					</div>

				</section>

				<?php if ($comment->comment_approved == '0') : ?>
					<em><?php esc_html_e('Your comment is awaiting moderation.', 'meup') ?></em>
					<br />
				<?php endif; ?>

			</article>
		</li>
		<?php
	}
}
