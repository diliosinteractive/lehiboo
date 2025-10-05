<?php 

defined( 'ABSPATH' ) || exit();


$number_tax = EL()->options->general->get('el_total_taxonomy', 2);
?>

<div class="wrap el_wrap">
    <h1><?php esc_html_e( 'Custom Taxonomy', 'eventlist' ); ?></h1>

    <?php if ( absint( $number_tax ) > 0 ): ?>
        <ul class="el_vertival_menu">
            <?php for ($i=1; $i <= absint( $number_tax ); $i++) {
                $param_arr = [];
                $param_arr = apply_filters( 'register_taxonomy_el_' . $i, $param_arr );

                $name = ! empty( $param_arr['name'] ) ? $param_arr['name'] : sprintf( esc_html__( 'Custom Taxonomy %s', 'eventlist' ), $i );
                $slug = ! empty( $param_arr['slug'] ) ? $param_arr['slug'] : 'taxonomy_default' . $i;
                ?>
            	<li>
            		<a href="edit-tags.php?taxonomy=<?php echo esc_attr( $slug ); ?>&post_type=event"><?php echo esc_html( $name ); ?></a>
            	</li>
            <?php } ?>
        </ul>
    <?php endif; ?>
</div>