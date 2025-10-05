jQuery(window).load(function(){

	/* Show/Hide Metabox When chooser template */
	var main_layout = jQuery(".postbox-container");

	main_layout.bind("toggle_showhide",function(){
		var template = 	jQuery('#page_template :selected').text();

		if(template != "Default Template" && template != ''){
			jQuery('.cmb2-id-ova-met-main-layout').css("display","none");
			jQuery( '#cmb2-metabox-page_heading_settings' ).css("display","none");
		}else{
			jQuery('.cmb2-id-ova-met-main-layout').css("display","block");
			jQuery( '#cmb2-metabox-page_heading_settings' ).css("display","block");

			
		}

	});
	main_layout.trigger("toggle_showhide");
	jQuery('#page_template').change(function(){
		main_layout.trigger("toggle_showhide");
	});

	/* Remove Template Full Width in Header Footer Post type */
	jQuery( '.post-type-ova_framework_hf_el #page_template option[value="elementor_header_footer"]' ).remove();

	
});
