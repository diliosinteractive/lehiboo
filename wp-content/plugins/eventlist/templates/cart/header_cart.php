<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>

<?php
$cookie_ide = isset( $_COOKIE['id_event'] ) ? ( $_COOKIE['id_event'] ) : '';
$cookie_idcal = isset( $_COOKIE['id_cal'] ) ? ( $_COOKIE['id_cal'] ) : '';
$id_event = (isset($_GET['ide'])) ? $_GET['ide'] : $cookie_ide;
$idcal = (isset($_GET['idcal'])) ? $_GET['idcal'] : $cookie_idcal;

$venue_arr = get_post_meta($id_event, OVA_METABOX_EVENT . 'venue', true);
$venue = is_array($venue_arr) ? implode(", ", $venue_arr) : "";

$venue = !empty($venue) ? $venue . ' - ' : "";

$address = get_post_meta($id_event, OVA_METABOX_EVENT . 'address', true);

$option_calendar = get_post_meta($id_event, OVA_METABOX_EVENT . 'option_calendar', true);

if ($option_calendar == 'auto') {
	
	$calendar_recurrence = get_post_meta( $id_event, OVA_METABOX_EVENT . 'calendar_recurrence', true);

	if( $calendar_recurrence ){
		foreach ($calendar_recurrence as $key => $value) {

			if($value['calendar_id'] == $idcal ){

				$start_time = $value['start_time'];
				$end_time = $value['end_time'];
				$date = strtotime( $value['date'] );

			}

		}	
	}
	

} else {

	$calendar = get_post_meta( $id_event, OVA_METABOX_EVENT . 'calendar', true);

	foreach ($calendar as $value) {
		if ($idcal == $value['calendar_id']) {

			$date = strtotime($value['date']);
			$end_date = isset($value['end_date']) ? strtotime($value['end_date']) : '';
			$start_time = $value['start_time'];
			$end_time = $value['end_time'];
		}
	}
}

$date_format = get_option('date_format');
$time_format = get_option('time_format');

?>

<div class="el_wrap_site cart-header">
	<h1 class="title-event"><a href="<?php the_permalink($id_event); ?>"><?php echo esc_html( get_the_title($id_event) ); ?></a></h1>
	<?php if ( !empty($venue) || !empty($address) ) : ?>
	<p class="venue">
		<?php if( apply_filters( 'el_e_detail_show_venue', true ) ) echo esc_html($venue); ?>
		<?php if( apply_filters( 'el_e_detail_show_address', true ) ) echo esc_html($address) ?>
	</p>
<?php endif ?>

<?php if ( EL()->options->event->get('show_hours_single', 'yes') == 'yes' ) { ?>
	<p class="date">
		<?php if ( isset($end_date) && ($date && $end_date && $date != $end_date) ) { 
			echo esc_html( date_i18n('l', $date).', '.date_i18n($date_format, $date) . ' - ' . date_i18n('l', $end_date).', '.date_i18n($date_format, $end_date) );
		} else {
			echo esc_html( date_i18n('l', $date).', '.date_i18n($date_format, $date) );
		} ?>

		<?php if( $start_time || $end_time ){ ?>
			@ 
			<?php echo esc_html( date_i18n( $time_format, strtotime( $start_time ) ) ); ?> 
			<?php if( $end_time ){ ?>
				- <?php echo esc_html( date_i18n( $time_format, strtotime($end_time) ) ); ?>
			<?php } ?>
		<?php } ?>
	</p>
<?php } else { ?>
	<p class="date"><?php echo esc_html( date_i18n('l', $date).', '.date_i18n( $date_format, $date ) ); ?></p>
<?php } ?>
</div>

