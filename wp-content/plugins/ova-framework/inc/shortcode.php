<?php

add_shortcode('instagram_meup', 'instagram_meup');
function instagram_meup( $args ) {
	$access_token = isset($args['access_token']) ? $args['access_token'] : '';
	$photo_count = isset($args['photo_count']) ? $args['photo_count'] : '';
	$photo_count  = absint( $photo_count );
	$json_link    = "https://api.instagram.com/v1/users/self/media/recent/?access_token={$access_token}&count={$photo_count}";

	$result = wp_remote_get( $json_link );

	$obj = json_decode( str_replace( '%22', '&rdquo;', $result['body'] ), true );
	$html = "<div class='instagram-meup'>";
	if (isset($obj['data'])) {
		foreach ($obj['data'] as $post){
			$pic_src = str_replace('http://', "https://", $post['images']['standard_resolution']['url']);
			$pic_link = $post['link'];
			$html .= "<a class='link-ins-meup' href='".$pic_link."'><img src='".$pic_src."' /></a>";
		}
	} else {
		$html .= "<p class='ins-token-false'>" . esc_html("Access token false", "meup") . "<p>";
	}
	$html .= "</div>";
	return $html;
}