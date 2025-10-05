<?php if ( ! defined( 'ABSPATH' ) ) exit(); 

$id 		= get_the_ID();
$url 		= get_permalink($id);
$author_id 	= get_the_author_meta('ID');
$no_img_tmb = apply_filters( 'el_img_no_tmb', EL_PLUGIN_URI.'assets/img/no_tmb_square.png' );

if ( has_post_thumbnail() && get_the_post_thumbnail() ) {
	$image = has_image_size( 'el_img_squa' ) ?  get_the_post_thumbnail_url( $id, 'el_img_squa' ) : get_the_post_thumbnail_url( $id, 'el_img_squa' );
} else {
	$image = $no_img_tmb;
}

$ticket_link 	= get_post_meta( $id, OVA_METABOX_EVENT.'ticket_link', true );

$title 			= get_the_title();
$description 	= wp_strip_all_tags( get_the_content() );
$price 			= get_price_ticket_by_id_event( array( 'id_event' => $id ) );
$priceCurrency 	= EL()->options->general->get( 'currency','USD' );
$seat_option 	= get_post_meta( $id, OVA_METABOX_EVENT.'seat_option', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'seat_option', true ) : '';
$address 		= get_post_meta( $id, OVA_METABOX_EVENT.'map_address', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'map_address', true ) : '';
$name_address 	= get_post_meta( $id, OVA_METABOX_EVENT.'address', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'address', true ) : '';
$start_date 	= get_post_meta( $id, OVA_METABOX_EVENT.'start_date_str', true ) ? wp_date( 'Y-m-d H:i:s', get_post_meta( $id, OVA_METABOX_EVENT.'start_date_str', true ) ) : '';
$end_date 		= get_post_meta( $id, OVA_METABOX_EVENT.'end_date_str', true ) ? wp_date( 'Y-m-d H:i:s', get_post_meta( $id, OVA_METABOX_EVENT.'end_date_str', true ) ) : '';
$timezone 		= get_post_meta( $id, OVA_METABOX_EVENT.'time_zone', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'time_zone', true ) : '';

if ( $timezone ) {
	$tz_string = el_get_timezone_string( $timezone );
} else {
	$tz_string = wp_timezone_string();
}

if ( $start_date ) {
	$start_date = date_format( date_create( $start_date, timezone_open( $tz_string ) ), 'c' );
}
if ( $end_date ) {
	$end_date = date_format( date_create( $end_date, timezone_open( $tz_string ) ), 'c' );
}

$tickets = array();

if ( $ticket_link !== "ticket_external_link" ) {
	if ( $seat_option === 'map' ) {
		$ticket_map = get_post_meta( $id, OVA_METABOX_EVENT.'ticket_map', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'ticket_map', true ) : [];
		$desc_seat 	= isset( $ticket_map['desc_seat'] ) ? $ticket_map['desc_seat'] : [];
		$valid_from = '';
		$ticket_date = '';
		
		if ( ! empty( $ticket_map['start_ticket_date'] ) && ! empty( $ticket_map['start_ticket_time'] ) ) {
			$ticket_date = $ticket_map['start_ticket_date'].' '.$ticket_map['start_ticket_time'];
		}

		if ( $ticket_date ) {
			$valid_from = date_format( date_create( $ticket_date, timezone_open( $tz_string ) ), 'c' );
		}

		if ( ! empty( $desc_seat ) && is_array( $desc_seat ) ) {
			foreach ( $desc_seat as $key => $value ) {
				$tickets[] = [ 
					"@type"			=> "Offer",
					"name" 			=> isset( $value['map_type_seat'] ) ? $value['map_type_seat'] : '',
					"description"	=> isset( $value['map_desc_type_seat'] ) ? $value['map_desc_type_seat'] : '',
					"url" 			=> $url,
					"price" 		=> isset( $value['map_price_type_seat'] ) ? $value['map_price_type_seat'] : 0,
					"priceCurrency" => $priceCurrency,
					"availability" 	=> 'http://schema.org/InStock',
					"validFrom" 	=> $valid_from
				];
			}
		}
		
	} else {
		$ticket_arr = get_post_meta( $id, OVA_METABOX_EVENT.'ticket', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'ticket', true ) : [];

		if ( ! empty( $ticket_arr ) && is_array( $ticket_arr ) ) {
			foreach ( $ticket_arr as $key => $value ) {
				$valid_from = '';

				$ticket_date = $value['start_ticket_date'].' '.$value['start_ticket_time'];
				
				if ( $ticket_date ) {
					$valid_from = date_format( date_create( $ticket_date, timezone_open( $tz_string ) ), 'c' );
				}
				
				$tickets[] = [ 
					"@type"			=> "Offer",
					"name" 			=> isset( $value['name_ticket'] ) ? $value['name_ticket'] : '',
					"description"	=> isset( $value['desc_ticket'] ) ? $value['desc_ticket'] : '',
					"url" 			=> $url,
					"price" 		=> isset( $value['price_ticket'] ) ? $value['price_ticket'] : 0,
					"priceCurrency" => $priceCurrency,
					"availability" 	=> 'http://schema.org/InStock',
					"validFrom" 	=> $valid_from
				];
			}
		}
	}
} else {
	$ticket_external_link = get_post_meta( $id, OVA_METABOX_EVENT.'ticket_external_link', true );
	$price = get_post_meta( $id, OVA_METABOX_EVENT."ticket_external_link_price", true );
	$price = preg_replace('/[^0-9]/', '', $price );
	$tickets = [
		"@type" 		=> "Offer",
		"name" 			=> $title,
	    "availability" 	=> "https://schema.org/InStock",
	    "price" 		=> $price,
	    "priceCurrency" => $priceCurrency,
	    'validFrom' 	=> $start_date,
	    'url' 			=> $ticket_external_link,
	];
}


$info_organizer = get_post_meta( $id, OVA_METABOX_EVENT.'info_organizer', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'info_organizer', true ) : '';

if ( $info_organizer == 'checked' ) {
	$display_name = get_post_meta( $id, OVA_METABOX_EVENT.'name_organizer', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'name_organizer', true ) : get_the_author_meta( 'display_name', $author_id );
} else {
	$display_name = get_user_meta( $author_id, 'display_name', true ) ? get_user_meta( $author_id, 'display_name', true ) : get_the_author_meta( 'display_name', $author_id );
}


$event_type = get_post_meta( $id, OVA_METABOX_EVENT.'event_type', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'event_type', true ) : 'classic';


$location = array(
	'@type' 			=> 'Place',
	'address' 			=> array(
		'@type' 		=> 'PostalAddress',
		'streetAddress' => $address,
	),
	'name' 				=> $name_address
);

if ( $event_type == 'online' ) {
	$location = array(
		'@type' => 'VirtualLocation',
		'url' 	=> get_permalink( $id ),
	);
}

?>

<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "Event",
		"name": "<?php echo esc_html( $title ); ?>",
		"startDate": "<?php echo esc_html( $start_date ); ?>",
		"endDate": "<?php echo esc_html( $end_date ); ?>",
		<?php if ( $event_type == 'online' ) { ?>
		"eventAttendanceMode": "https://schema.org/OnlineEventAttendanceMode",	
		<?php } elseif ( $event_type == 'classic' ) { ?>
		"eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",		
		<?php } ?>
		"eventStatus": "https://schema.org/EventScheduled",
		"location": <?php echo json_encode( $location ); ?>,
		"image": "<?php echo esc_html( $image ); ?>",
		"description": "<?php echo esc_html($description); ?>",
		"offers": <?php echo json_encode( $tickets ); ?>,
		"performer": 
		{
			"@type": "Organization",
			"name": "<?php echo esc_html( $display_name ); ?>"
		},
		"organizer": {
	        "@type": "Organization",
	        "name": "<?php echo esc_html( $display_name ); ?>",
	        "url": "<?php echo esc_html( $url ); ?>"
	    }
	}
</script>