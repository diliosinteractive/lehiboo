(function($){
	"use strict";
	

	$(window).on('elementor/frontend/init', function () {

		
		/* Menu Shrink */
		elementorFrontend.hooks.addAction('frontend/element_ready/ova_menu.default', function(){

			$( '.ova_menu_clasic .ova_openNav' ).on( 'click', function(){
				$( this ).closest('.ova_wrap_nav').find( '.ova_nav' ).removeClass( 'hide' );
				$( this ).closest('.ova_wrap_nav').find( '.ova_nav' ).addClass( 'show' );
				$( '.ova_menu_clasic  .ova_closeCanvas' ).css( 'width', '100%' );

				
				$( 'body' ).css( 'background-color', 'rgba(0,0,0,0.4)' );
				
			});

			$( '.ova_menu_clasic  .ova_closeNav' ).on( 'click', function(){
				$( this ).closest('.ova_wrap_nav').find( '.ova_nav' ).removeClass( 'show' );
				$( this ).closest('.ova_wrap_nav').find( '.ova_nav' ).addClass( 'hide' );
				$( '.ova_closeCanvas' ).css( 'width', '0' );


				
				$( 'body' ).css( 'background-color', 'transparent' );
				
			});

			// Display in mobile
			$( '.ova_menu_clasic li.menu-item button.dropdown-toggle').off('click').on( 'click', function() {
				$(this).parent().toggleClass('active_sub');
			});

			if( $('.ovamenu_shrink').length > 0 && $( 'body' ).data('elementor-device-mode') == 'desktop' ){
				
				if( !$('.show_mask_header').hasClass( 'mask_header_shrink' ) ){
					$( '<div class="show_mask_header mask_header_shrink" style="position: relative; height: 0;"></div>' ).insertAfter('.ovamenu_shrink');
				}

				
				var header = $('.ovamenu_shrink');
				var header_shrink_height = header.height();
				

				$(window).scroll(function () {

					var scroll = $(this).scrollTop();

					if (scroll >= header_shrink_height+150 ) {
						header.addClass( 'active_fixed' );
						$('.mask_header_shrink').css('height',header_shrink_height);
					} else {
						header.removeClass('active_fixed');
						$('.mask_header_shrink').css('height','0');
					}
				});
			}

			if( $('.ovamenu_shrink_mobile').length > 0 && $( 'body' ).data('elementor-device-mode') != 'desktop' ){
				
				if( !$('.show_mask_header_mobile').hasClass( 'mask_header_shrink_mobile' ) ){
					$( '<div class="show_mask_header_mobile mask_header_shrink_mobile" style="position: relative; height: 0;"></div>' ).insertAfter('.ovamenu_shrink_mobile');
					
				}
				
				var header = $('.ovamenu_shrink_mobile');
				var header_shrink_height = header.height();
				

				$(window).scroll(function () {

					var scroll = $(this).scrollTop();

					if (scroll >= header_shrink_height+150 ) {
						header.addClass( 'active_fixed' );
						$('.mask_header_shrink_mobile').css('height',header_shrink_height);
					} else {
						header.removeClass('active_fixed');
						$('.mask_header_shrink_mobile').css('height','0');
					}
				});
			}

		});


		//blog slider
		elementorFrontend.hooks.addAction('frontend/element_ready/ova_blog_slider.default', function(){

			var responsive_value = {
				0: {
					items: 1,
				},
				768:  {
					items: 2,
				},
				991:  {
					items: 3,
				},

			};
			var navText = [
			'<i class="fa fa-angle-left"></i>',
			'<i class="fa fa-angle-right"></i>'
			];

			$(".blog-slider").each(function(){
				var owlsl = $(this) ;
				var owlsl_df = {
					margin: 0, 
					responsive: false, 
					smartSpeed:500,
					autoplay:false,
					autoplayTimeout: 6000,
					items:3,
					loop:true, 
					nav: true, 
					dots: true,
					center:false,
					autoWidth:false,
					thumbs:false, 
					autoplayHoverPause: true,
					slideBy: 1,
				};
				var owlsl_ops = owlsl.data('options') ? owlsl.data('options') : {};
				owlsl_ops = $.extend({}, owlsl_df, owlsl_ops);
				owlsl.owlCarousel({
					autoWidth: owlsl_ops.autoWidth,
					margin: owlsl_ops.margin,
					items: owlsl_ops.items,
					loop: owlsl_ops.loop,
					autoplay: owlsl_ops.autoplay,
					autoplayTimeout: owlsl_ops.autoplayTimeout,
					center: owlsl_ops.center,
					nav: owlsl_ops.nav,
					dots: owlsl_ops.dots,
					thumbs: owlsl_ops.thumbs,
					autoplayHoverPause: owlsl_ops.autoplayHoverPause,
					slideBy: owlsl_ops.slideBy,
					smartSpeed: owlsl_ops.smartSpeed,
					navText: navText,
					rtl: owlsl_ops.rtl,
					responsive: {
                  0: {
                     items: owlsl_ops.response_mobile,
                  },
                  768:  {
                     items: owlsl_ops.response_tablet,
                  },
                  991:  {
                     items: owlsl_ops.response_desk,
                  },

               },
            });

           $(this).find('.owl-dot').each(function(index) {
				//Add one to index so it starts from 1
					$(this).attr('aria-label', index + 1);
				});

			});

		});
		// end blog slider


		elementorFrontend.hooks.addAction('frontend/element_ready/ova_testimonial.default', function(){

			var responsive_value = {
				0: {
					items: 1,
				},
			};
			var navText = [
			'<i class="arrow_left"></i>',
			'<i class="arrow_right"></i>'
			];

			$(".wp-testimonial").each(function(){
				var owlsl = $(this) ;
				var owlsl_df = {
					margin: 0, 
					responsive: false, 
					smartSpeed:500,
					autoplay:false,
					autoplayTimeout: 6000,
					items:3,
					loop:true, 
					nav: true, 
					dots: true,
					center:false,
					autoWidth:false,
					thumbs:false, 
					autoplayHoverPause: true,
					slideBy: 1,
				};
				var owlsl_ops = owlsl.data('options') ? owlsl.data('options') : {};
				owlsl_ops = $.extend({}, owlsl_df, owlsl_ops);
				owlsl.owlCarousel({
					autoWidth: owlsl_ops.autoWidth,
					margin: owlsl_ops.margin,
					items: owlsl_ops.items,
					loop: owlsl_ops.loop,
					autoplay: owlsl_ops.autoplay,
					autoplayTimeout: owlsl_ops.autoplayTimeout,
					center: owlsl_ops.center,
					nav: owlsl_ops.nav,
					dots: owlsl_ops.dots,
					thumbs: owlsl_ops.thumbs,
					autoplayHoverPause: owlsl_ops.autoplayHoverPause,
					slideBy: owlsl_ops.slideBy,
					smartSpeed: owlsl_ops.smartSpeed,
					rtl: owlsl_ops.rtl,
					navText: navText,
					responsive: responsive_value,
				});

				$(this).find('.owl-dot').each(function(index) {
				//Add one to index so it starts from 1
					$(this).attr('aria-label', index + 1);
				});

				$(this).find(".owl-prev").attr("aria-label", "prev");
	         $(this).find(".owl-prev").attr("role", "button");
	         $(this).find(".owl-next").attr("aria-label", "next");
	         $(this).find(".owl-next").attr("role", "button");

			});

		});
		// end tesimonial

		/* Skill bar */
		elementorFrontend.hooks.addAction('frontend/element_ready/ova_skill_bar.default', function(){
			

			$('.skillbar').appear();

			$(document.body).on( 'appear', '.skillbar', function(){

				
				jQuery(this).find('.skillbar-bar').animate({
					width:jQuery(this).attr('data-percent'),
				},3000);

				jQuery(this).find('.percent').animate({
					left: jQuery(this).attr('data-percent') 
				},{
					duration: 3000,
					step: function( now, fx ){
						var data = Math.round(now);
						$(this).find('.relative span').html(data + '%');
					}

				});
				

			});
			
		});
		/* end skill bar */



   });

})(jQuery);
