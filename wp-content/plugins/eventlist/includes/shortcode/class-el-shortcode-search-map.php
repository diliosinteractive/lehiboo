<?php defined( 'ABSPATH' ) || exit;

class El_Shortcode_Search_Map extends EL_Shortcode {

	public $shortcode = 'el_search_map';

	public function __construct() {

		parent::__construct();
	}

	

	function add_shortcode( $args, $content = null ) {

		
		if( EL()->options->general->get('event_google_key_map') ){
			wp_enqueue_script( 'google','//maps.googleapis.com/maps/api/js?key='.EL()->options->general->get('event_google_key_map').'&libraries=places&callback=Function.prototype', array('jquery'), false, true);
		}else{
			wp_enqueue_script( 'google','//maps.googleapis.com/maps/api/js?sensor=false&libraries=places&callback=Function.prototype', array('jquery'), false, true);
		}
		wp_enqueue_script( 'google-marker', EL_PLUGIN_URI.'assets/libs/markerclusterer.js', array('jquery'), false, true);
		wp_enqueue_script( 'google-richmarker', EL_PLUGIN_URI.'assets/libs/richmarker-compiled.js', array('jquery'), false, true);

		/* Override market google map when more event the same location*/
		wp_enqueue_script('oms', EL_PLUGIN_URI.'assets/libs/oms.js', array('jquery'), false, true);

		$list_taxonomy = EL_Post_Types::register_taxonomies_customize();

		$str_list_taxonomy = '';
		if( $list_taxonomy && is_array( $list_taxonomy ) ) {

			$i = 0;
			$total_tax = count( $list_taxonomy );


			foreach( $list_taxonomy as $tax ) {
				$i++;
				if( $i != $total_tax ) {
					$str_list_taxonomy .= $tax['slug'] . ' , ';
				} else {
					$str_list_taxonomy .= $tax['slug'];
				}
			}
		}

		$type = 'type1';
		$column = 'two-column';
		$zoom = 4;

		$args = shortcode_atts( array(
			'type' => $type,
			'column' => $column,
			'zoom' => $zoom,
			'pos1' => '',
			'pos2' => '',
			'pos3' => '',
			'pos4' => '',
			'pos5' => '',
			'pos6' => '',
			'pos7' => '',
			'pos8' => '',
			'pos9' => '',
			'pos10' => '',
			'taxonomy_customize' => $str_list_taxonomy,
			'marker_option' => 'icon',
			'marker_icon' => '',
			'show_featured' => '',
		), $args );

		$template = apply_filters( 'el_shortcode_search_map_template', 'search/search-map.php' );

		ob_start();



		el_get_template( $template, $args );



		return ob_get_clean();
	}

}

new El_Shortcode_Search_Map();