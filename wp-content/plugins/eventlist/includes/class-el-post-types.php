<?php

/**
 * @package EventList/Classes
 * @version 1.0
 */
defined( 'ABSPATH' ) || exit;


/**
 * Post Type Class
 */
class EL_Post_Types{
	
	/**
	 * Hook in methods
	 */
	public function __construct(){

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( 'EL_Post_Types', 'register_taxonomies_customize' ) );
		
	}

	public static function register_taxonomies_customize(){

		$number_taxonomy = EL()->options->general->get('el_total_taxonomy', 2);

		if ( $number_taxonomy > 0 ) {
			for ( $i = 1; $number_taxonomy >= $i; $i++ ) {

				$param_arr = [];
				$param_arr = apply_filters( 'register_taxonomy_el_' . $i, $param_arr ) ;


				if ( empty( $param_arr ) || ! is_array( $param_arr ) ) {
					$labels = array(
						'name'              => sprintf( _x( 'Custom Taxonomy %s', 'taxonomy general name', 'eventlist' ), esc_html( $i )  ),
						'singular_name'     => sprintf( _x( 'taxonomy %s', 'taxonomy singular name', 'eventlist' ), esc_html( $i )  ),
						'search_items'      => sprintf( esc_html__( 'Search Taxonomy %s', 'eventlist' ), esc_html( $i )  ),
						'all_items'         => sprintf( esc_html__( 'All Taxonomy %s', 'eventlist' ), esc_html( $i )  ),
						'parent_item'       => sprintf( esc_html__( 'Parent Taxonomy %s', 'eventlist' ), esc_html( $i )  ),
						'parent_item_colon' => sprintf( esc_html__( 'Parent Taxonomy %s: ', 'eventlist' ), esc_html( $i )  ),
						'edit_item'         => sprintf( esc_html__( 'Edit Taxonomy %s', 'eventlist' ), esc_html( $i )  ),
						'update_item'       => sprintf( esc_html__( 'Update Taxonomy %s', 'eventlist' ), esc_html( $i )  ),
						'add_new_item'      => sprintf( esc_html__( 'Add New Taxonomy %s', 'eventlist' ), esc_html( $i )  ),
						'new_item_name'     => sprintf( esc_html__( 'New Taxonomy %s Name', 'eventlist' ), esc_html( $i )  ),
						'menu_name'         => sprintf( esc_html__( 'Custom Taxonomy %s', 'eventlist' ), esc_html( $i )  ),
						'type'         		=> 'taxonomy_default' . $i,
					);

					$args = array(
						'hierarchical'      => true,
						'labels'            => $labels,
						'show_ui'           => true,
						'show_admin_column' => false,
						'query_var'         => true,
						'rewrite'           => array( 'slug' => 'taxonomy_default' . $i ),

					);
				} else {
					$labels = array(
						'name'              => $param_arr['name'],
						'singular_name'     => $param_arr['slug'],
						'search_items'      => sprintf( esc_html__( 'Search %s', 'eventlist' ), esc_html( $param_arr['name'] ) ),
						'all_items'         => sprintf( esc_html__( 'All %s', 'eventlist' ), esc_html( $param_arr['name'] ) ),
						'parent_item'       => sprintf( esc_html__( 'Parent %s', 'eventlist' ), esc_html( $param_arr['name'] ) ),
						'parent_item_colon' => sprintf( esc_html__( 'Parent %s: ', 'eventlist' ),esc_html( $param_arr['name'] ) ),
						'view_item' 		=> sprintf( esc_html__( 'View %s', 'eventlist' ),esc_html( $param_arr['name'] ) ),
						'edit_item'         => sprintf( esc_html__( 'Edit %s', 'eventlist' ),esc_html( $param_arr['name'] ) ),
						'update_item'       => sprintf( esc_html__( 'Update %s', 'eventlist' ),esc_html( $param_arr['name'] ) ),
						'add_new_item'      => sprintf( esc_html__( 'Add New %s', 'eventlist' ),esc_html( $param_arr['name'] ) ),
						'new_item_name'     => sprintf( esc_html__( 'New %s' , 'eventlist' ),esc_html( $param_arr['name'] ) ),
						'menu_name'         => $param_arr['name'],
						'type'         		=> $param_arr['slug'],
					);

					$args = array(
						'hierarchical'      => true,
						'labels'            => $labels,
						'show_ui'           => true,
						'show_admin_column' => false,
						'query_var'         => true,
						'rewrite'           => array( 'slug' => $param_arr['slug'] ),

					);
				}

				$name_taxonomy[$i]['slug'] = $args['labels']['type'];
				$name_taxonomy[$i]['name'] = $args['labels']['name'];

				register_taxonomy( $args['labels']['type'], array( 'event' ), $args );
			}

			// V1 Le Hiboo - Ajouter nos taxonomies natives dans la liste
			$native_taxonomies = array(
				array(
					'slug' => 'event_thematique',
					'name' => __( 'Thématiques', 'eventlist' )
				),
				array(
					'slug' => 'event_special',
					'name' => __( 'Événements Spéciaux', 'eventlist' )
				),
				array(
					'slug' => 'event_saison',
					'name' => __( 'Saisons', 'eventlist' )
				),
			);

			// Fusionner avec les taxonomies personnalisées
			if ( ! empty( $name_taxonomy ) ) {
				$name_taxonomy = array_merge( $name_taxonomy, $native_taxonomies );
			} else {
				$name_taxonomy = $native_taxonomies;
			}

			return $name_taxonomy;
		}

		// V1 Le Hiboo - Toujours retourner les taxonomies natives même si $number_taxonomy = 0
		return array(
			array(
				'slug' => 'event_thematique',
				'name' => __( 'Thématiques', 'eventlist' )
			),
			array(
				'slug' => 'event_special',
				'name' => __( 'Événements Spéciaux', 'eventlist' )
			),
			array(
				'slug' => 'event_saison',
				'name' => __( 'Saisons', 'eventlist' )
			),
		);

	}

	public function register_post_types(){

		$supports   = array( 'author', 'title', 'editor', 'comments', 'excerpt', 'thumbnail'  );
		// V1 Le Hiboo - Ajout des nouvelles taxonomies
		$taxonomies = array( 'event_cat', 'event_tag', 'event_thematique', 'event_special', 'event_saison', 'event_loc' );
		
		$args_event = array(

			'labels'              => array(
				'name'                  => __( 'Events', 'eventlist' ),
				'singular_name'         => __( 'Event', 'eventlist' ),
				'all_items'             => __( 'All Events', 'eventlist' ),
				'menu_name'             => _x( 'Events', 'Admin menu name', 'eventlist' ),
				'add_new'               => __( 'Add New', 'eventlist' ),
				'add_new_item'          => __( 'Add new event', 'eventlist' ),
				'edit'                  => __( 'Edit', 'eventlist' ),
				'edit_item'             => __( 'Edit event', 'eventlist' ),
				'new_item'              => __( 'New event', 'eventlist' ),
				'view_item'             => __( 'View event', 'eventlist' ),
				'view_items'            => __( 'View events', 'eventlist' ),
				'search_items'          => __( 'Search events', 'eventlist' ),
				'not_found'             => __( 'No events found', 'eventlist' ),
				'not_found_in_trash'    => __( 'No events found in trash', 'eventlist' ),
				'parent'                => __( 'Parent event', 'eventlist' ),
				'featured_image'        => __( 'Product image', 'eventlist' ),
				'set_featured_image'    => __( 'Set event image', 'eventlist' ),
				'remove_featured_image' => __( 'Remove event image', 'eventlist' ),
				'use_featured_image'    => __( 'Use as event image', 'eventlist' ),
				'insert_into_item'      => __( 'Insert into event', 'eventlist' ),
				'uploaded_to_this_item' => __( 'Uploaded to this event', 'eventlist' ),
				'filter_items_list'     => __( 'Filter events', 'eventlist' ),
				'items_list_navigation' => __( 'Events navigation', 'eventlist' ),
				'items_list'            => __( 'Events list', 'eventlist' ),
			),
			
			'public'             => true,
			'query_var'          => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'has_archive'        => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'show_in_menu'       => false,	
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => true,
			'taxonomies'         => $taxonomies,
			'supports'           => $supports,
			'hierarchical'       => false,
			'rewrite'            => array(
				'slug'       => _x('event','Event Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			'show_in_rest'        => false,
			'menu_position'      => 4,
			'menu_icon'          => 'dashicons-admin-home'
		);

		register_post_type( 'event', $args_event );
		
		$args_venue = array(

			'labels'              => array(
				'name'                  => __( 'Venues', 'eventlist' ),
				'singular_name'         => __( 'Venue', 'eventlist' ),
				'all_items'             => __( 'All Venues', 'eventlist' ),
				'menu_name'             => _x( 'Venues', 'Admin menu name', 'eventlist' ),
				'add_new'               => __( 'Add New', 'eventlist' ),
				'add_new_item'          => __( 'Add new venue', 'eventlist' ),
				'edit'                  => __( 'Edit', 'eventlist' ),
				'edit_item'             => __( 'Edit venue', 'eventlist' ),
				'new_item'              => __( 'New venue', 'eventlist' ),
				'view_item'             => __( 'View venue', 'eventlist' ),
				'view_items'            => __( 'View venues', 'eventlist' ),
				'search_items'          => __( 'Search venues', 'eventlist' ),
				'not_found'             => __( 'No venues found', 'eventlist' ),
				'not_found_in_trash'    => __( 'No venues found in trash', 'eventlist' ),
				'parent'                => __( 'Parent venue', 'eventlist' ),
				'featured_image'        => __( 'Product image', 'eventlist' ),
				'set_featured_image'    => __( 'Set venue image', 'eventlist' ),
				'remove_featured_image' => __( 'Remove venue image', 'eventlist' ),
				'use_featured_image'    => __( 'Use as venue image', 'eventlist' ),
				'insert_into_item'      => __( 'Insert into venue', 'eventlist' ),
				'uploaded_to_this_item' => __( 'Uploaded to this venue', 'eventlist' ),
				'filter_items_list'     => __( 'Filter venues', 'eventlist' ),
				'items_list_navigation' => __( 'Venues navigation', 'eventlist' ),
				'items_list'            => __( 'Venues list', 'eventlist' ),
			),
			
			'public'             => true,
			'query_var'          => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'has_archive'        => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'show_in_menu'       => false,	
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => false,
			'supports'           => array( 'title', 'thumbnail' ),
			'hierarchical'       => false,
			'rewrite'            => array(
				'slug'       => _x('venue','Venue Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			'show_in_rest'        => false,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-location-alt'
		);

		register_post_type( 'venue', $args_venue );

			$args_payout = array(

			'labels'              => array(
				'name'                  => __( 'All Payouts', 'eventlist' ),
				'singular_name'         => __( 'Payout', 'eventlist' ),
				'all_items'             => __( 'All Payout', 'eventlist' ),
				'menu_name'             => _x( 'Payout', 'Admin menu name', 'eventlist' ),
				'add_new'               => __( 'Add New', 'eventlist' ),
				'add_new_item'          => __( 'Add new payout', 'eventlist' ),
				'edit'                  => __( 'Edit', 'eventlist' ),
				'edit_item'             => __( 'Edit payout', 'eventlist' ),
				'new_item'              => __( 'New payout', 'eventlist' ),
				'view_item'             => __( 'View payout', 'eventlist' ),
				'view_items'            => __( 'View payout', 'eventlist' ),
				'search_items'          => __( 'Search payout', 'eventlist' ),
				'not_found'             => __( 'No payout found', 'eventlist' ),
				'not_found_in_trash'    => __( 'No payout found in trash', 'eventlist' ),
				'parent'                => __( 'Parent payout', 'eventlist' ),
				'featured_image'        => __( 'Product image', 'eventlist' ),
				'set_featured_image'    => __( 'Set venue image', 'eventlist' ),
				'remove_featured_image' => __( 'Remove venue image', 'eventlist' ),
				'use_featured_image'    => __( 'Use as venue image', 'eventlist' ),
				'insert_into_item'      => __( 'Insert into payout', 'eventlist' ),
				'uploaded_to_this_item' => __( 'Uploaded to this payout', 'eventlist' ),
				'filter_items_list'     => __( 'Filter payout', 'eventlist' ),
				'items_list_navigation' => __( 'Payout navigation', 'eventlist' ),
				'items_list'            => __( 'Payout list', 'eventlist' ),
			),
			
			'public'             => true,
			'query_var'          => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'has_archive'        => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'show_in_menu'       => false,	
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'supports'           => array('author', 'title' ),
			'hierarchical'       => false,
			'rewrite'            => array(
				'slug'       => _x('payout','Payout Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			'show_in_rest'        => false,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-money-alt'
		);

		register_post_type( 'payout', $args_payout );


			$args_payout_method = array(

			'labels'              => array(
				'name'                  => __( 'Payout Method', 'eventlist' ),
				'singular_name'         => __( 'Payout Method', 'eventlist' ),
				'all_items'             => __( 'All Payout Method', 'eventlist' ),
				'menu_name'             => _x( 'Payout Method', 'Admin menu name', 'eventlist' ),
				'add_new'               => __( 'Add New', 'eventlist' ),
				'add_new_item'          => __( 'Add new payout method', 'eventlist' ),
				'edit'                  => __( 'Edit', 'eventlist' ),
				'edit_item'             => __( 'Edit payout method', 'eventlist' ),
				'new_item'              => __( 'New payout method', 'eventlist' ),
				'view_item'             => __( 'View payou methodt', 'eventlist' ),
				'view_items'            => __( 'View payout method', 'eventlist' ),
				'search_items'          => __( 'Search payout method', 'eventlist' ),
				'not_found'             => __( 'No payout method found', 'eventlist' ),
				'not_found_in_trash'    => __( 'No payout method found in trash', 'eventlist' ),
				'parent'                => __( 'Parent payout method', 'eventlist' ),
				'featured_image'        => __( 'Product image', 'eventlist' ),
				'set_featured_image'    => __( 'Set venue image', 'eventlist' ),
				'remove_featured_image' => __( 'Remove venue image', 'eventlist' ),
				'use_featured_image'    => __( 'Use as venue image', 'eventlist' ),
				'insert_into_item'      => __( 'Insert into payout method', 'eventlist' ),
				'uploaded_to_this_item' => __( 'Uploaded to this payout method', 'eventlist' ),
				'filter_items_list'     => __( 'Filter payout method', 'eventlist' ),
				'items_list_navigation' => __( 'Payout method navigation', 'eventlist' ),
				'items_list'            => __( 'Payout method list', 'eventlist' ),
			),
			
			'public'             => true,
			'query_var'          => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'has_archive'        => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'show_in_menu'       => false,	
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'supports'           => array('author', 'title' ),
			'hierarchical'       => false,
			'rewrite'            => array(
				'slug'       => _x('payout_method','Payout Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			'show_in_rest'        => false,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-money-alt'
		);

		register_post_type( 'payout_method', $args_payout_method );
		
		$args_package = array(
			'labels'              => array(
				'name'                  => __( 'Package', 'eventlist' ),
				'singular_name'         => __( 'Package', 'eventlist' ),
				'all_items'             => __( 'All Packages', 'eventlist' ),
				'menu_name'             => _x( 'Packages', 'Admin menu name', 'eventlist' ),
				'add_new'               => __( 'Add New', 'eventlist' ),
				'add_new_item'          => __( 'Add new package', 'eventlist' ),
				'edit'                  => __( 'Edit', 'eventlist' ),
				'edit_item'             => __( 'Edit package', 'eventlist' ),
				'new_item'              => __( 'New package', 'eventlist' ),
				'view_item'             => __( 'View package', 'eventlist' ),
				'view_items'            => __( 'View packages', 'eventlist' ),
				'search_items'          => __( 'Search packages', 'eventlist' ),
				'not_found'             => __( 'No packages found', 'eventlist' ),
				'not_found_in_trash'    => __( 'No packages found in trash', 'eventlist' ),
				'parent'                => __( 'Parent package', 'eventlist' ),
				'featured_image'        => __( 'Product image', 'eventlist' ),
				'set_featured_image'    => __( 'Set package image', 'eventlist' ),
				'remove_featured_image' => __( 'Remove package image', 'eventlist' ),
				'use_featured_image'    => __( 'Use as package image', 'eventlist' ),
				'insert_into_item'      => __( 'Insert into package', 'eventlist' ),
				'uploaded_to_this_item' => __( 'Uploaded to this package', 'eventlist' ),
				'filter_items_list'     => __( 'Filter package', 'eventlist' ),
				'items_list_navigation' => __( 'Packages navigation', 'eventlist' ),
				'items_list'            => __( 'Packages list', 'eventlist' ),
			),
			'description'         => __( 'This is where you can add new packages.', 'eventlist' ),
			'public'              => true,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'hierarchical'        => false, 
			'query_var'           => true,
			'supports'         => array( 'title', 'editor', 'thumbnail' ),
			'has_archive'         => false,
			'show_in_menu'	  	  => false,	
			'show_in_nav_menus'   => false,
			'show_in_rest'        => false,
			'menu_position'      => 5,
			'rewrite'             => array(
				'slug'       => _x('package','Packages Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			'menu_icon' => 'dashicons-cart',
		);	
		
		register_post_type( 'package', $args_package );

		$args_manage_membership = array(
			'labels'              => array(
				'name'                  => __( 'Manage Membership', 'eventlist' ),
				'singular_name'         => __( 'Manage Memberships', 'eventlist' ),
				'all_items'             => __( 'Memberships', 'eventlist' ),
				'menu_name'             => _x( 'Memberships', 'Admin menu name', 'eventlist' ),
				'add_new'               => __( 'Add New', 'eventlist' ),
				'add_new_item'          => __( 'Add new membership', 'eventlist' ),
				'edit'                  => __( 'Edit', 'eventlist' ),
				'edit_item'             => __( 'Edit membership', 'eventlist' ),
				'new_item'              => __( 'New membership', 'eventlist' ),
				'view_item'             => __( 'View membership', 'eventlist' ),
				'view_items'            => __( 'View memberships', 'eventlist' ),
				'search_items'          => __( 'Search memberships', 'eventlist' ),
				'not_found'             => __( 'No memberships found', 'eventlist' ),
				'not_found_in_trash'    => __( 'No memberships found in trash', 'eventlist' ),
				'parent'                => __( 'Parent membership', 'eventlist' ),
				'featured_image'        => __( 'Product image', 'eventlist' ),
				'set_featured_image'    => __( 'Set membership image', 'eventlist' ),
				'remove_featured_image' => __( 'Remove membership image', 'eventlist' ),
				'use_featured_image'    => __( 'Use as membership image', 'eventlist' ),
				'insert_into_item'      => __( 'Insert into membership', 'eventlist' ),
				'uploaded_to_this_item' => __( 'Uploaded to this membership', 'eventlist' ),
				'filter_items_list'     => __( 'Filter membership', 'eventlist' ),
				'items_list_navigation' => __( 'Manage Memberships navigation', 'eventlist' ),
				'items_list'            => __( 'Manage Memberships list', 'eventlist' ),
			),
			'description'         => __( 'This is where you can add new memberships.', 'eventlist' ),
			'public'              => true,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'hierarchical'        => false, 
			'query_var'           => true,
			'supports'        	  => array( 'title', 'editor' ),
			'has_archive'         => false,
			'show_in_menu'	  	  => false,	
			'show_in_nav_menus'   => false,
			'show_in_rest'        => false,
			'menu_position'      => 5,
			'rewrite'             => array(
				'slug'       => _x('manage_membership','Manage Memberships Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			'menu_icon' => 'dashicons-admin-users',
		);	
		
		register_post_type( 'manage_membership', $args_manage_membership );

		$args_booking = array(
			'labels'              => array(
				'name'                  => __( 'Bookings', 'eventlist' ),
				'singular_name'         => __( 'Booking', 'eventlist' ),
				'all_items'             => __( 'All Bookings', 'eventlist' ),
				'menu_name'             => _x( 'Bookings', 'Admin menu name', 'eventlist' ),
				'add_new'               => __( 'Add New', 'eventlist' ),
				'add_new_item'          => __( 'Add new booking', 'eventlist' ),
				'edit'                  => __( 'Edit', 'eventlist' ),
				'edit_item'             => __( 'Edit booking', 'eventlist' ),
				'new_item'              => __( 'New booking', 'eventlist' ),
				'view_item'             => __( 'View booking', 'eventlist' ),
				'view_items'            => __( 'View bookings', 'eventlist' ),
				'search_items'          => __( 'Search bookings', 'eventlist' ),
				'not_found'             => __( 'No bookings found', 'eventlist' ),
				'not_found_in_trash'    => __( 'No bookings found in trash', 'eventlist' ),
				'parent'                => __( 'Parent booking', 'eventlist' ),
				'featured_image'        => __( 'Product image', 'eventlist' ),
				'set_featured_image'    => __( 'Set booking image', 'eventlist' ),
				'remove_featured_image' => __( 'Remove booking image', 'eventlist' ),
				'use_featured_image'    => __( 'Use as booking image', 'eventlist' ),
				'insert_into_item'      => __( 'Insert into booking', 'eventlist' ),
				'uploaded_to_this_item' => __( 'Uploaded to this booking', 'eventlist' ),
				'filter_items_list'     => __( 'Filter bookings', 'eventlist' ),
				'items_list_navigation' => __( 'Bookings navigation', 'eventlist' ),
				'items_list'            => __( 'Bookings list', 'eventlist' ),
			),
			'description'         => __( 'This is where you can add new bookings.', 'eventlist' ),
			'public'              => true,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false, 
			'query_var'           => true,
			'supports'         => array( 'author', 'title' ),
			'has_archive'         => false,
			'show_in_nav_menus'   => false,
			'show_in_menu'	  	  => false,	
			'show_in_rest'        => true,
			'menu_position'      => 5,
			'rewrite'             => array(
				'slug'       => _x('el_bookings','Bookings Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			'menu_icon' => 'dashicons-products',
		);
		
		register_post_type( 'el_bookings', $args_booking );

		$args_tickets = array(
			'labels'              => array(
				'name'                  => __( 'Ticket', 'eventlist' ),
				'singular_name'         => __( 'Ticket', 'eventlist' ),
				'all_items'             => __( 'All Tickets', 'eventlist' ),
				'menu_name'             => _x( 'Tickets', 'Admin menu name', 'eventlist' ),
				'add_new'               => __( 'Add New', 'eventlist' ),
				'add_new_item'          => __( 'Add new ticket', 'eventlist' ),
				'edit'                  => __( 'Edit', 'eventlist' ),
				'edit_item'             => __( 'Edit ticket', 'eventlist' ),
				'new_item'              => __( 'New ticket', 'eventlist' ),
				'view_item'             => __( 'View ticket', 'eventlist' ),
				'view_items'            => __( 'View tickets', 'eventlist' ),
				'search_items'          => __( 'Search Tickets', 'eventlist' ),
				'not_found'             => __( 'No tickets found', 'eventlist' ),
				'not_found_in_trash'    => __( 'No tickets found in trash', 'eventlist' ),
				'parent'                => __( 'Parent ticket', 'eventlist' ),
				'featured_image'        => __( 'Product image', 'eventlist' ),
				'set_featured_image'    => __( 'Set ticket image', 'eventlist' ),
				'remove_featured_image' => __( 'Remove ticket image', 'eventlist' ),
				'use_featured_image'    => __( 'Use as ticket image', 'eventlist' ),
				'insert_into_item'      => __( 'Insert into ticket', 'eventlist' ),
				'uploaded_to_this_item' => __( 'Uploaded to this ticket', 'eventlist' ),
				'filter_items_list'     => __( 'Filter ticket', 'eventlist' ),
				'items_list_navigation' => __( 'Tickets navigation', 'eventlist' ),
				'items_list'            => __( 'Tickets list', 'eventlist' ),
			),
			'description'         => __( 'This is where you can add new tickets.', 'eventlist' ),
			'public'              => true,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false, 
			'query_var'           => true,
			'supports'         => array( 'author', 'title' ),
			'has_archive'         => false,
			'show_in_menu'	  	  => false,	
			'show_in_nav_menus'   => false,
			'show_in_rest'        => true,
			'menu_position'      => 5,
			'rewrite'             => array(
				'slug'       => _x('el_tickets','Tickets Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			'menu_icon' => 'dashicons-tickets-alt',
		);	
		
		register_post_type( 'el_tickets', $args_tickets );

	}

	
	// register taxonomy
	public function register_taxonomies() {
		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => _x( 'Categories', 'taxonomy general name', 'eventlist' ),
			'singular_name'     => _x( 'Category', 'taxonomy singular name', 'eventlist' ),
			'search_items'      => __( 'Search Events', 'eventlist' ),
			'all_items'         => __( 'All Events', 'eventlist' ),
			'parent_item'       => __( 'Parent Category', 'eventlist' ),
			'parent_item_colon' => __( 'Parent Category:', 'eventlist' ),
			'edit_item'         => __( 'Edit Category', 'eventlist' ),
			'update_item'       => __( 'Update Category', 'eventlist' ),
			'add_new_item'      => __( 'Add New Category', 'eventlist' ),
			'new_item_name'     => __( 'New Category', 'eventlist' ),
			'menu_name'         => __( 'Categories', 'eventlist' )
		);

		$args = array(
			'hierarchical'       => true,
			'label'              => __( 'Categories', 'eventlist' ),
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => true,
			'publicly_queryable' => true,
			'query_var'          => true,
			'show_in_rest'       => true, // Show in Rest API (display in Event Custom Post Type)
			'rewrite'            => array(
				'slug'       => _x('event_cat','Event Category Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
			
		);

		$args = apply_filters( 'el_register_tax_event_cat', $args );
		register_taxonomy( 'event_cat', array( 'event' ), $args );



		// Add new taxonomy, make it hierarchical (like tags)
		$labels = array(
			'name'              => _x( 'Tags', 'taxonomy general name', 'eventlist' ),
			'singular_name'     => _x( 'Tag', 'taxonomy singular name', 'eventlist' ),
			'search_items'      => __( 'Search Tag', 'eventlist' ),
			'all_items'         => __( 'All Tags', 'eventlist' ),
			'parent_item'       => __( 'Parent Tag', 'eventlist' ),
			'parent_item_colon' => __( 'Parent Tag:', 'eventlist' ),
			'edit_item'         => __( 'Edit Tag', 'eventlist' ),
			'update_item'       => __( 'Update Tag', 'eventlist' ),
			'add_new_item'      => __( 'Add New Tag', 'eventlist' ),
			'new_item_name'     => __( 'New Tag', 'eventlist' ),
			'menu_name'         => __( 'Tags', 'eventlist' )
		);

		$args = array(
			// 'hierarchical'      => true,
			'public'            => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'capabilities'      => array ( 'post' ),
			'show_in_rest'      => true, // Show in Rest API (display in Event Custom Post Type)
			'rewrite'           => array(
				'slug'       => _x('event_tag','Event Tag Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
		);

		$args = apply_filters( 'el_register_tax_event_tag', $args );
		register_taxonomy( 'event_tag', array( 'event' ), $args );


		// ========================================
		// V1 Le Hiboo - Nouvelles taxonomies
		// ========================================

		// Taxonomy : Thématiques
		$labels = array(
			'name'              => _x( 'Thématiques', 'taxonomy general name', 'eventlist' ),
			'singular_name'     => _x( 'Thématique', 'taxonomy singular name', 'eventlist' ),
			'search_items'      => __( 'Rechercher une thématique', 'eventlist' ),
			'all_items'         => __( 'Toutes les thématiques', 'eventlist' ),
			'parent_item'       => __( 'Thématique parente', 'eventlist' ),
			'parent_item_colon' => __( 'Thématique parente:', 'eventlist' ),
			'edit_item'         => __( 'Modifier la thématique', 'eventlist' ),
			'update_item'       => __( 'Mettre à jour la thématique', 'eventlist' ),
			'add_new_item'      => __( 'Ajouter une thématique', 'eventlist' ),
			'new_item_name'     => __( 'Nouvelle thématique', 'eventlist' ),
			'menu_name'         => __( 'Thématiques', 'eventlist' )
		);

		$args = array(
			'hierarchical'      => true,
			'public'            => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'capabilities'      => array ( 'post' ),
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'thematique',
				'with_front' => false,
				'feeds'      => true,
			),
		);

		$args = apply_filters( 'el_register_tax_event_thematique', $args );
		register_taxonomy( 'event_thematique', array( 'event' ), $args );


		// Taxonomy : Événements Spéciaux
		$labels = array(
			'name'              => _x( 'Événements Spéciaux', 'taxonomy general name', 'eventlist' ),
			'singular_name'     => _x( 'Événement Spécial', 'taxonomy singular name', 'eventlist' ),
			'search_items'      => __( 'Rechercher un événement spécial', 'eventlist' ),
			'all_items'         => __( 'Tous les événements spéciaux', 'eventlist' ),
			'parent_item'       => null,
			'parent_item_colon' => null,
			'edit_item'         => __( 'Modifier l\'événement spécial', 'eventlist' ),
			'update_item'       => __( 'Mettre à jour l\'événement spécial', 'eventlist' ),
			'add_new_item'      => __( 'Ajouter un événement spécial', 'eventlist' ),
			'new_item_name'     => __( 'Nouvel événement spécial', 'eventlist' ),
			'menu_name'         => __( 'Événements Spéciaux', 'eventlist' )
		);

		$args = array(
			'hierarchical'      => false,
			'public'            => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'capabilities'      => array ( 'post' ),
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'evenement-special',
				'with_front' => false,
				'feeds'      => true,
			),
		);

		$args = apply_filters( 'el_register_tax_event_special', $args );
		register_taxonomy( 'event_special', array( 'event' ), $args );


		// Taxonomy : Saisons
		$labels = array(
			'name'              => _x( 'Saisons', 'taxonomy general name', 'eventlist' ),
			'singular_name'     => _x( 'Saison', 'taxonomy singular name', 'eventlist' ),
			'search_items'      => __( 'Rechercher une saison', 'eventlist' ),
			'all_items'         => __( 'Toutes les saisons', 'eventlist' ),
			'parent_item'       => null,
			'parent_item_colon' => null,
			'edit_item'         => __( 'Modifier la saison', 'eventlist' ),
			'update_item'       => __( 'Mettre à jour la saison', 'eventlist' ),
			'add_new_item'      => __( 'Ajouter une saison', 'eventlist' ),
			'new_item_name'     => __( 'Nouvelle saison', 'eventlist' ),
			'menu_name'         => __( 'Saisons', 'eventlist' )
		);

		$args = array(
			'hierarchical'      => false,
			'public'            => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'capabilities'      => array ( 'post' ),
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'saison',
				'with_front' => false,
				'feeds'      => true,
			),
		);

		$args = apply_filters( 'el_register_tax_event_saison', $args );
		register_taxonomy( 'event_saison', array( 'event' ), $args );

		// Fin V1 Le Hiboo - Nouvelles taxonomies


		// Add new taxonomy, make it hierarchical (like location)

		$labels = array(
			'name'              => _x( 'Locations', 'taxonomy general name', 'eventlist' ),
			'singular_name'     => _x( 'Location', 'taxonomy singular name', 'eventlist' ),
			'search_items'      => __( 'Search Location', 'eventlist' ),
			'all_items'         => __( 'All Locations', 'eventlist' ),
			'parent_item'       => __( 'Parent Location', 'eventlist' ),
			'parent_item_colon' => __( 'Parent Location:', 'eventlist' ),
			'edit_item'         => __( 'Edit Location', 'eventlist' ),
			'update_item'       => __( 'Update Location', 'eventlist' ),
			'add_new_item'      => __( 'Add New Location', 'eventlist' ),
			'new_item_name'     => __( 'New Location', 'eventlist' ),
			'menu_name'         => __( 'Locations', 'eventlist' )
		);

		$args = array(
			'hierarchical'      => true,
			'public'            => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'capabilities'      => array ( 'post' ),
			'show_in_rest'      => true, // Show in Rest API (display in Event Custom Post Type)
			'rewrite'           => array(
				'slug'       => _x('event_loc','Event Location Slug', 'eventlist'),
				'with_front' => false,
				'feeds'      => true,
			),
		);

		$args = apply_filters( 'el_register_tax_event_loc', $args );
		register_taxonomy( 'event_loc', array( 'event' ), $args );
	}

} 
//end class
//add customfield color in category event

function el_colorpicker_field_add_new_cate_event( $taxonomy ) {
	?>

	<div class="form-field term-colorpicker-wrap">
		<label for="term-colorpicker"><?php esc_html_e( 'Category Color', 'eventlist' ) ?></label>
		<input name="_category_color" value="#f05537" class="colorpicker" id="term-colorpicker" />
		<p><?php esc_html_e( 'This is the field description where you can tell the user how the color is used in the theme.', 'eventlist' ); ?></p>
	</div>

	<?php
}

function el_colorpicker_field_edit_cate_event( $term ) {
	$color = get_term_meta( $term->term_id, '_category_color', true );
	$color = ( ! empty( $color ) ) ? "#{$color}" : '#f05537';

	?>

	<tr class="form-field term-colorpicker-wrap">
		<th scope="row"><label for="term-colorpicker"><?php esc_html_e( 'Color', 'eventlist' ); ?></label></th>
		<td>
			<input name="_category_color" value="<?php echo esc_attr( $color ); ?>" class="colorpicker" id="term-colorpicker" />
			<p class="description"><?php esc_html_e( 'This is the field description where you can tell the user how the color is used in the theme.', 'eventlist' ); ?></p>
		</td>
	</tr>

	<?php
}
add_action( 'event_cat_add_form_fields', 'el_colorpicker_field_add_new_cate_event' );  // Variable Hook Name
add_action( 'event_cat_edit_form_fields', 'el_colorpicker_field_edit_cate_event' );   // Variable Hook Name

// add customfields checkout in category event

function el_custom_checkout_field_add_new_cate_event( $taxonomy ){
	$list_fields = get_option( 'ova_booking_form', array() );
?>
<div class="form-field custom-checkout-field-wrap">
	<label for="custom-checkout-field"><?php esc_html_e( 'Custom Checkout Fields', 'eventlist' ); ?></label>
	<div class="ckf-type">
		<label class="ckf-type-item">
		  	<input type="radio" name="_category_ckf_type" value="all" checked="checked">
		  	<?php esc_html_e( 'All', 'eventlist' ); ?>
		</label>
		<label class="ckf-type-item">
		  	<input type="radio" name="_category_ckf_type" value="special">
		  	<?php esc_html_e( 'Special', 'eventlist' ); ?>
		</label>
	</div>
	<select multiple="multiple" name="_category_checkout_field[]" id="custom-checkout-field" class="postform" aria-describedby="checkout-field-description" data-placeholder="<?php esc_attr_e( 'Select field', 'eventlist' ); ?>">
		<?php if ( $list_fields ): ?>
			<?php foreach ( $list_fields as $name => $field ): ?>
				<?php if ( $field['enabled'] == "on" ): ?>
					<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></option>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</select>
	<p class="description" id="checkout-field-description">
		<?php esc_html_e( 'Choose one or multiple custom fields', 'eventlist' ); ?>
	</p>
</div>
<?php
}

function el_custom_checkout_field_edit_cate_event( $term ){
	$list_fields 		= get_option( 'ova_booking_form', array() );
	$ckf_type 			= get_term_meta( $term->term_id, '_category_ckf_type', true ) ? get_term_meta( $term->term_id, '_category_ckf_type', true ) : 'all';
	$selected_fields 	= get_term_meta( $term->term_id, '_category_checkout_field', true ) ? get_term_meta( $term->term_id, '_category_checkout_field', true ) : array();
?>
<tr class="form-field custom-checkout-field-wrap">
	<th scope="row"><label for="custom-checkout-field"><?php esc_html_e( 'Custom Checkout Fields', 'eventlist' ); ?></label></th>
	<td>
		<div class="ckf-type">
			<label class="ckf-type-item">
			  	<input type="radio" name="_category_ckf_type" value="all"<?php checked( $ckf_type, 'all' ); ?>>
			  	<?php esc_html_e( 'All', 'eventlist' ); ?>
			</label>
			<label class="ckf-type-item">
			  	<input type="radio" name="_category_ckf_type" value="special"<?php checked( $ckf_type, 'special' ); ?>>
			  	<?php esc_html_e( 'Special', 'eventlist' ); ?>
			</label>
		</div>
		<select multiple="multiple" name="_category_checkout_field[]" id="custom-checkout-field" class="postform" aria-describedby="checkout-field-description" data-placeholder="<?php esc_attr_e( 'Select field', 'eventlist' ); ?>">
		<?php if ( $list_fields ): ?>
			<?php foreach ( $list_fields as $name => $field ): ?>
				<?php if ( $field['enabled'] == "on" ):
					$selected = in_array($name, $selected_fields);
					?>
					<option <?php selected( $selected, true ); ?> value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></option>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	
	</select>
		<p class="description" id="checkout-field-description">
			<?php esc_html_e( 'Choose one or multiple custom fields', 'eventlist' ); ?>
		</p>
	</td>
</tr>
<?php
}


add_action( 'event_cat_add_form_fields', 'el_custom_checkout_field_add_new_cate_event' );
add_action( 'event_cat_edit_form_fields', 'el_custom_checkout_field_edit_cate_event' );

// Edit banner image Categories
function el_image_banner_field_add_new_cat_event( $taxonomy ) {
	?>
	
	<div class="form-field term-image-wrap">
		<a class="gallery-add button button-primary button-large text-right" href="#" data-uploader-title="<?php esc_attr_e( "Add image", 'eventlist' ); ?>" data-uploader-button-text="<?php esc_attr_e( "Add image", 'eventlist' ); ?>"><?php esc_html_e( "Add image", 'eventlist' ); ?></a>
		
		<input type="hidden" name="_category_image" class="image_category" value="">
		<img class="image-preview" src="<?php echo esc_url(); ?>">
		<small><a class="remove-image" href="#"><?php esc_html_e( "Remove image", 'eventlist' ); ?></a></small>
	</div>

	<?php
}
// add_action( 'event_cat_add_form_fields', 'el_image_banner_field_add_new_cat_event' );  // Variable Hook Name

function el_image_banner_field_edit_cat_event( $term ) {
	$data_image = get_term_meta( $term->term_id, '_category_image', true );
	?>

	<tr class="form-field term-image-wrap">
		<th scope="row"><label><?php esc_html_e( 'Image', 'eventlist' ); ?></label></th>
		<td>
			<a class="gallery-add button button-primary button-large text-right" href="#" data-uploader-title="<?php esc_attr_e( "Add image", 'eventlist' ); ?>" data-uploader-button-text="<?php esc_attr_e( "Add image", 'eventlist' ); ?>"><?php esc_html_e( "Add image", 'eventlist' ); ?></a>

			<div class="wrap_image_cat">
				<input type="hidden" name="_category_image" class="image_category" value="<?php echo esc_attr(isset($data_image) ? $data_image : ''); ?>">
				<?php 
				if ( $data_image ) { 
					$image = wp_get_attachment_image_src($data_image, 'el_img_rec');
					?>
					<img class="image-preview" src="<?php echo esc_url($image[0]); ?>" alt="Banner Category">
					<small><a class="remove-image" href="#"><?php esc_html_e( "Remove image", 'eventlist' ); ?></a></small>
				<?php } ?>
			</div>
		</td>
	</tr>

	<?php
}
add_action( 'event_cat_edit_form_fields', 'el_image_banner_field_edit_cat_event' );  // Variable Hook Name
// End edit banner image Categories

function el_save_termmeta( $term_id ) {
   // Save term color if possible
	if( isset( $_POST['_category_color'] ) && ! empty( $_POST['_category_color'] ) ) {
		update_term_meta( $term_id, '_category_color', sanitize_hex_color_no_hash( $_POST['_category_color'] ) );
	} else {
		delete_term_meta( $term_id, '_category_color' );
	}

	if( isset( $_POST['_category_image'] ) && ! empty( $_POST['_category_image'] ) ) {
		update_term_meta( $term_id, '_category_image', sanitize_text_field( $_POST['_category_image'] ) );
	} else {
		delete_term_meta( $term_id, '_category_image' );
	}

	if ( isset( $_POST['_category_ckf_type'] ) && ! empty( $_POST['_category_ckf_type'] ) ) {
		update_term_meta( $term_id, '_category_ckf_type', $_POST['_category_ckf_type'] );
	} else {
		delete_term_meta( $term_id, '_category_ckf_type' );
	}

	if ( isset( $_POST['_category_checkout_field'] ) && ! empty( $_POST['_category_checkout_field'] ) ) {
		update_term_meta( $term_id, '_category_checkout_field', $_POST['_category_checkout_field'] );
	} else {
		delete_term_meta( $term_id, '_category_checkout_field' );
	}

}
add_action( 'created_event_cat', 'el_save_termmeta' );
add_action( 'edited_event_cat',  'el_save_termmeta' );

add_action( 'init', 'el_image_banner_taxonomies_customize' );
function el_image_banner_taxonomies_customize() {
	// Taxonomy Image
	$number_tax = EL()->options->general->get('el_total_taxonomy', 2);

	if ( $number_tax > 0 ) {
	    for ( $i = 1; $number_tax >= $i; $i++ ) {
	        $param_arr = [];
	        $param_arr = apply_filters( 'register_taxonomy_el_' . $i, $param_arr );

	        if ( empty( $param_arr ) || ! is_array( $param_arr ) ) {
	            $slug_tax = 'taxonomy_default' . $i;
	        } else {
	            $slug_tax = $param_arr['slug'];
	        }

	        add_action( $slug_tax.'_edit_form_fields', 'el_image_banner_field_edit_cat_event');

	        add_action( 'created_'.$slug_tax, 'el_save_termmeta' );
	        add_action( 'edited_'.$slug_tax, 'el_save_termmeta' );
	    }
	}
}

function el_colorpicker_init_inline() {

	if( null !== ( $screen = get_current_screen() ) && 'edit-event_cat' !== $screen->id ) {
		return;
	}
	wp_enqueue_media();
	?>
	<script>
		jQuery( document ).ready( function( $ ) {
			$( '.colorpicker' ).wpColorPicker();
		} );
	</script>
	<?php

}
add_action( 'admin_print_scripts', 'el_colorpicker_init_inline', 30 );
new EL_Post_Types();