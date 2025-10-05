<?php

if ( ! defined('ABSPATH') ) {
	exit();
}

if ( ! class_exists('EL_Legacy') ) {
	
	class EL_Legacy {

		static $instance = null;

		public function __construct(){

			if ( apply_filters( 'el_update_event_min_max_price', false ) === true ) {
				$this->el_update_event_min_max_price();
			}

			// Sync data package
			$check_event_old = EL_Event::get_event_lastest_not_exists_membership_id();
			
			if ( count( $check_event_old ) > 0 ) {
				add_filter( 'el_setting_package_general', array( $this, 'el_setting_sync_data_package' ) );
				add_action( 'admin_notices', array( $this, 'el_sync_data_package_notice' ) );
			}
			
		}

		public static function instance() {
			
			if ( is_null( self::$instance ) ) {
				$instance = new EL_Legacy;
			}
			
			return $instance;
		}

		public function el_update_event_min_max_price(){
			$args = array(
				'post_type' 		=> 'event',
				'posts_per_page' 	=> -1,
				'post_status' 		=> 'publish',
				'fields' 			=> 'ids',
			);
			$events = get_posts( $args );

			if ( count( $events ) > 0 ) {

				foreach ( $events as $event_id ) {
					$ticket_prices = array();
					$ticket_link 	= get_post_meta( $event_id, OVA_METABOX_EVENT.'ticket_link', true );
					$ticket_external_link_price = get_post_meta( $event_id, OVA_METABOX_EVENT.'ticket_external_link_price', true );
					$seat_option 	= get_post_meta( $event_id, OVA_METABOX_EVENT.'seat_option', true );
					$ticket 		= get_post_meta( $event_id, OVA_METABOX_EVENT.'ticket', true );
					$ticket_map 	= get_post_meta( $event_id, OVA_METABOX_EVENT.'ticket_map', true );

					if ( $ticket_link !== 'ticket_internal_link' ) {
						if ( $ticket_external_link_price  ) {
							$ticket_prices['ticket_external_link'][] = preg_replace('/[^0-9]/', '', $ticket_external_link_price);

						}
					}

					if ( ! empty( $ticket ) ) {
						foreach ($ticket as $key => $value) {
							if ( isset( $value['price_ticket'] ) ) {
								$ticket_prices['none'][] = (float) $value['price_ticket'];
								$ticket_prices['simple'][] = (float) $value['price_ticket'];
							}
						}
					}

					if ( ! empty( $ticket_map ) ) {
						if ( isset ( $ticket_map['seat'] ) && ! empty( $ticket_map['seat'] ) ) {
							foreach ( $ticket_map['seat'] as $key => $value ) {
								if ( $value['price'] ) {
									$ticket_prices['map'][] = (float) $value['price'];
								} else {
									$person_price = ! empty( $value['person_price'] ) ? json_decode( $value['person_price'] ) : [];
									if ( ! empty( $person_price ) ) {
										foreach ( $person_price as $val ) {
											$ticket_prices['map'][] = (float)$val;
										}
									}
								}
							}
							foreach ($ticket_map['area'] as $key => $value) {
								if ( $value['price'] ) {
									$ticket_prices['map'][] = (float) $value['price'];
								} elseif ( $value['person_price'] ) {
									$person_type = json_decode( $value['person_price'] );
									foreach ( $person_type as $price ) {
										$ticket_prices['map'][] = (float) $price;
									}
								}
							}
						}
					}

					// min_max_price
					$min_max_price = '';
					if ( count( $ticket_prices ) > 0 ) {
						if ( $ticket_link === 'ticket_external_link' ) {
							if ( isset( $ticket_prices['ticket_external_link'] ) ) {
								$min_max_price = implode("-", $ticket_prices['ticket_external_link']);
							} else {
								$min_max_price = '0';
							}
						} else {
							switch ( $seat_option ) {
								case 'none':
								if ( isset( $ticket_prices['none'] ) ) {
									$min_max_price = implode("-", $ticket_prices['none']);
								} else {
									$min_max_price = '0';
								}
								break;
								case 'simple':
								if ( isset( $ticket_prices['simple'] ) ) {
									$min_max_price = implode("-", $ticket_prices['simple']);
								} else {
									$min_max_price = '0';
								}
								break;
								case 'map':
								if ( isset( $ticket_prices['map'] ) ) {
									$min_max_price = implode("-", $ticket_prices['map']);
								} else {
									$min_max_price = '0';
								}
								break;
								default:
								break;
							}
						}
					} else {
						$min_max_price = '0';
					}

					$min_price = get_post_meta( $event_id, OVA_METABOX_EVENT.'min_price', true );
					$max_price = get_post_meta( $event_id, OVA_METABOX_EVENT.'max_price', true );

					if ( $min_max_price != '' ) {
						$min_max_price = explode("-", $min_max_price);
						$min_max_price = array_map('floatval', $min_max_price);
						$min_price = min($min_max_price);
						$max_price = max($min_max_price);
					}

					update_post_meta( $event_id, OVA_METABOX_EVENT.'min_price', $min_price);
					update_post_meta( $event_id, OVA_METABOX_EVENT.'max_price', $max_price);
				}
			}
		}

		public function el_setting_sync_data_package( $field_data ){
			$field_data[0]['fields'][] = array(
				'type' 		=> 'button',
				'label' 	=> __( 'Sync Data Package', 'eventlist' ),
				'desc' 		=> __( 'Synchronize data to match the current version, this function will be hidden after the synchronization process is complete.', 'eventlist' ),
				'atts' 		=> array(
					'id' 	=> 'sync_data_package',
					'class' => 'button button-secondary sync_data_package',
				),
				'name' 		=> 'sync_data_package',	
			);
			return apply_filters( 'el_setting_sync_data_package', $field_data );
		}

		public function el_sync_data_package_notice(){
			$url 	= admin_url( 'edit.php?post_type=event&page=ova_el_setting&tab=package' );
			$link 	= sprintf( '<a href="%1$s">%2$s</a>', $url, esc_html__( 'Click here to sync data', 'eventlist' ) );
			?>
			<div class="notice notice-warning"><p><?php echo sprintf( esc_html__( 'The package data has some changes in the new version, please synchronize the data to ensure the website works well. %s', 'eventlist' ), wp_kses_post( $link ) ); ?></p></div>
			<?php
		}
	}
	
}

add_action( 'admin_init', array( 'EL_Legacy', 'instance' ) );