<?php


add_action( 'cmb2_init', 'el_payout_method_metaboxes' );
function el_payout_method_metaboxes() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = 'ova_met_';


	/* Post payout method *********************************************************************************/
    /* *******************************************************************************/
    $post_payout_method = new_cmb2_box( array(
        'id'            => 'post_payout_method',
        'title'         => esc_html__( 'Payout Method', 'eventlist' ),
        'object_types'  => array( 'payout_method'), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
    ) );

    $group_field_id = $post_payout_method->add_field( array(
        'id'          => $prefix. 'payout_method_group',
        'type'        => 'group',
        'description' => __( 'Payout Method Field', 'eventlist' ),
        'options'     => array(
        'group_title'       => __( 'Field {#}', 'eventlist' ), 
        'add_button'        => __( 'Add Another Field', 'eventlist' ),
        'remove_button'     => __( 'Remove Field', 'eventlist' ),
        'sortable'          => true,
        
    ),
    ) );

    $post_payout_method->add_group_field( $group_field_id, array(
        'name' => esc_html__( 'Label', 'eventlist' ),
        'id'   => $prefix.'label_method',
        'type' => 'text',
    ) );

    $post_payout_method->add_group_field( $group_field_id, array(
        'name' => esc_html__( 'Name', 'eventlist' ),
        'id'   => $prefix.'name_method',
        'type' => 'text',
        'description'	=> esc_html__( 'Only use lowercase, not space', 'eventlist' ),
        'sanitization_cb' => 'ova_lowercase_remove_space',
    ) );

    $post_payout_method->add_group_field( $group_field_id, array(
        'name' => esc_html__( 'Placeholder', 'eventlist' ),
        'id'   => $prefix.'placeholder',
        'type' => 'text',
    ) );

    $post_payout_method->add_group_field( $group_field_id, array(
        'name' => esc_html__( 'Required', 'eventlist' ),
        'id'   => $prefix . 'required',
        'type' => 'radio_inline',
        'options' => array(
            'yes' => __( 'Yes', 'eventlist' ),
            'no'   => __( 'No', 'eventlist' ),
        ),
        'default' => 'yes',
    ) );

}