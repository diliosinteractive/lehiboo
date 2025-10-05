<?php if (post_password_required()) return; /* ?>

<div class="content_comments">
	<div id="comments" class="comments">

		<?php if(have_comments()){ ?>
			<div>
				<h4 class="number-comments second_font"> 
					<?php 
					if (is_singular( 'event' )) {
						comments_number( esc_html__('Reviews', 'meup'), esc_html__( 'Review', 'meup' ).('<span>(1)</span>'), esc_html__( 'Reviews', 'meup' ).('<span>(%)</span>') ); 
					} else {
						comments_number( esc_html__('Comments', 'meup'), esc_html__( 'Comment', 'meup' ).('<span>(1)</span>'), esc_html__( 'Comments', 'meup' ).('<span>(%)</span>') );
					}
					?>
				</h4>
			</div>

		<?php } ?>

		<?php if (have_comments()) { ?>
			<ul class="commentlists">
				<?php wp_list_comments('callback=meup_theme_comment'); ?>
			</ul>
			<?php
      	// Are there comments to navigate through?

			if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
				<footer class="navigation comment-navigation" role="navigation">
					<div class="nav_comment_text"><?php esc_html_e( 'Comment navigation', 'meup' ); ?></div>
					<div class="previous"><?php previous_comments_link(__('&larr; Older Comments', 'meup')); ?></div>
					<div class="next right"><?php next_comments_link(__('Newer Comments &rarr;', 'meup')); ?></div>
				</footer><!-- .comment-navigation -->
			<?php endif; // Check for comment navigation ?>

			<?php if (!comments_open() && get_comments_number()) { ?>
				<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'meup' ); ?></p>
			<?php } ?>

		<?php } ?>

		<?php

		$aria_req = ($req ? ' aria-required=true' : '');

		$commenter = wp_get_current_commenter();
		$consent   = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';

		if (is_singular( 'event' )) {
			$title_comment = esc_html__( 'Add A Review', 'meup' );

			$field_author = '<div class="name"><label class="label_field second_font">'.esc_html__( 'Name*', 'meup' ).'</label><input type="text" name="author" value="' . esc_attr($commenter['comment_author']) . '" ' . esc_attr($aria_req) . ' class="form-control" placeholder="'. esc_attr__('Type your name here','meup') .'" /></div>';
			$field_email = '<div class="email"><label class="label_field second_font">'.esc_html__( 'Your Email*', 'meup' ).'</label><input type="text" name="email" value="' . esc_attr($commenter['comment_author_email']) . '" ' . esc_attr($aria_req) . ' class="form-control" placeholder="'. esc_attr__('Your email','meup') .'" /></div>';
			$field_cookies = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . esc_attr($consent) . ' />' . '<label for="wp-comment-cookies-consent">'.esc_html__('Save my name, email, and website in this browser for the next time I comment.', 'meup').'</label></p>';
			
		} else {
			$title_comment = esc_html__( 'Leave a reply', 'meup' )
			;
			$field_author = '<div class="name"><i class="far fa-user"></i><input type="text" name="author" value="' . esc_attr($commenter['comment_author']) . '" ' . esc_attr($aria_req) . ' class="form-control" placeholder="'. esc_attr__('Type your name here','meup') .'" /></div>';
			$field_email = '<div class="email"><i class="far fa-envelope"></i><input type="text" name="email" value="' . esc_attr($commenter['comment_author_email']) . '" ' . esc_attr($aria_req) . ' class="form-control" placeholder="'. esc_attr__('Your email','meup') .'" /></div>';
			$field_cookies = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . esc_attr($consent) . ' />' . '<label for="wp-comment-cookies-consent">'.esc_html__('Save my name, email, and website in this browser for the next time I comment.', 'meup').'</label></p>';
		}

		$comment_args = array(
			'title_reply' 			=> $title_comment,
			'title_reply_before' 	=> '<span class="title-comment second_font">',
			'title_reply_after' 	=> '</span>',
			'fields' 				=> apply_filters('comment_form_default_fields', array(
				'author' 	=> $field_author,
				'email' 	=> $field_email,
				'cookies' 	=> $field_cookies,
			)),
			'comment_field' 		=> '<div class="wrap_comment"><label class="label_field second_font">'.esc_html__( 'Comment', 'meup' ).'</label><textarea class="form-control" rows="4" name="comment" placeholder="'. esc_attr__('Your comment','meup') .'"></textarea></div>',
			'label_submit' 			=> esc_html__('Post Comment','meup'),
			'comment_notes_before' 	=> '',
			'comment_notes_after' 	=> '',
		);
		?>

		<?php global $post; ?>
		<?php if ('open' == $post->comment_status) { ?>
			<div class="wrap_comment_form">
				<div class="row">
					<div class="col-md-12">
						<?php comment_form($comment_args); ?>        
					</div>
				</div>
			</div><!-- end commentform -->
		<?php } ?>


	</div><!-- end comments -->
</div>

<?php */