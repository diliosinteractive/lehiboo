<?php
$sidebar = apply_filters( 'meup_theme_sidebar', '' );
if ($sidebar == 'layout_1c' || $sidebar == ''){
    return;
}
?>

<?php if(is_active_sidebar('main-sidebar')){ ?>
        <aside id="sidebar" class="sidebar">
        	<div class="content-sideber">
        		<?php  dynamic_sidebar('main-sidebar'); ?>
        	</div>
        </aside>
<?php } ?>