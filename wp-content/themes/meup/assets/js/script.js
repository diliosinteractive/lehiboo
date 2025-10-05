(function($){
	"use strict";
	$(document).ready(function(){

		/* Scroll to top */
		meup_scrollUp();
		function meup_scrollUp(options) {

         var defaults = {
            scrollName: 'scrollUp', 
            topDistance: 600, 
            topSpeed: 800, 
            animation: 'fade', 
            animationInSpeed: 200, 
            animationOutSpeed: 200, 
            scrollText: '<i class="fas fa-angle-up"></i>', 
            scrollImg: false, 
            activeOverlay: false 
         };

         var o = $.extend({}, defaults, options),
         scrollId = '#' + o.scrollName;


         $('<a/>', {
            id: o.scrollName,
            href: '#top',
            title: o.scrollText
         }).appendTo('body');


         if (!o.scrollImg) {

            $(scrollId).html(o.scrollText);
         }


         $(scrollId).css({'display': 'none', 'position': 'fixed', 'z-index': '998'});


         if (o.activeOverlay) {
            $("body").append("<div id='" + o.scrollName + "-active'></div>");
            $(scrollId + "-active").css({'position': 'absolute', 'top': o.topDistance + 'px', 'width': '100%', 'border-top': '1px dotted ' + o.activeOverlay, 'z-index': '998'});
         }


         $(window).scroll(function () {
            switch (o.animation) {
            case "fade":
               $(($(window).scrollTop() > o.topDistance) ? $(scrollId).fadeIn(o.animationInSpeed) : $(scrollId).fadeOut(o.animationOutSpeed));
               break;
            case "slide":
               $(($(window).scrollTop() > o.topDistance) ? $(scrollId).slideDown(o.animationInSpeed) : $(scrollId).slideUp(o.animationOutSpeed));
               break;
            default:
               $(($(window).scrollTop() > o.topDistance) ? $(scrollId).show(0) : $(scrollId).hide(0));
            }
         });


         $(scrollId).on( "click", function (event) {
            $('html, body').animate({scrollTop: 0}, o.topSpeed);
            event.preventDefault();
         });

      }

      /* Fix empty menu in test_uni_data */
      if( $( '.widget_nav_menu ul li' ).length > 0 ){
         $( '.widget_nav_menu ul li a:empty' ).parent().css('display','none');
      }

      /* Select 2 */
      // $('select').not(".not_select2").select2({
      //    width: '100%'
      // });
      $('body:not(.woocommerce-page) select').each( function() {
         var placeholder = $(this).find('option[value=""]').text();
         $(this).not(".not_select2").select2({
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            dropdownAutoWidth: false
         });
      });

      /* Popup Image - PrettyPhoto */
      if( $("a[data-gal^='prettyPhoto']").length > 0 ){
         $("a[data-gal^='prettyPhoto']").prettyPhoto({hook: 'data-gal', theme: 'facebook',slideshow:5000, autoplay_slideshow:true});
      }

      $( '.ovatheme_header_default li.menu-item button.dropdown-toggle').off('click').on( 'click', function() {
         $(this).parent().toggleClass('active_sub');
      });

      $(".categories a").siblings('i').css('display', 'inline-block');

      $('.menu-item-language a').hover(function(e){
        $(this).attr('title', '');
     });

      // Stretch Column Left/Right
      $('.meup_section_margin_left').each( function() {
         var that = $(this);
         if ( that.length != null ) {
            meup_calculate_width( that );
         }  
      });
      
      $('.meup_stretch_column_left').each( function() {
         var that = $(this);
         if ( that.length != null ) {
            meup_calculate_width( that );
         }
      });

      $('.meup_stretch_column_right').each( function() {
         var that = $(this);
         if ( that.length != null ) {
            meup_calculate_width( that );
         }
      });

    // Calculate width with special class
      function meup_calculate_width( directly ){

         if( $(directly).length ){

            var col_offset = $(directly).offset();

            var width_curr = $( window ).width();

            if ( width_curr > 1023 ) {

               if( ! $("body").hasClass('rtl') && directly.hasClass('meup_stretch_column_left') ){

                  var ending_left = col_offset.left;
                  var width_left    = $(directly).outerWidth() + ending_left;
                  
                  $('.meup_stretch_column_left .elementor-widget-wrap').css('width', width_left);
                  $('.meup_stretch_column_left .elementor-widget-wrap').css('margin-left', -ending_left);

               } else {
                  var ending_right  = ($(window).width() - (col_offset.left + $(directly).outerWidth()));
                  var width_right   = $(directly).outerWidth() + ending_right;

                  $('.meup_stretch_column_left .elementor-widget-wrap').css('width', width_right);
                  $('.meup_stretch_column_left .elementor-widget-wrap').css('margin-right', -ending_right);
               }

               if( ! $("body").hasClass('rtl') && directly.hasClass('meup_stretch_column_right') ){

                  var ending_right  = ($(window).width() - (col_offset.left + $(directly).outerWidth()));
                  var width_right   = $(directly).outerWidth() + ending_right;

                  directly.find('>.elementor-widget-wrap').css('width', width_right);
                  directly.find('>.elementor-widget-wrap').css('margin-right', -ending_right);

               } else {
                  var ending_left   = col_offset.left;
                  var width_left    = $(directly).outerWidth() + ending_left;
                  
                  directly.find('>.elementor-widget-wrap').css('width', width_left);
                  directly.find('>.elementor-widget-wrap').css('margin-left', -ending_left);
               }

            }

         }
      }

      $(window).resize(function () {

         $('.meup_stretch_column_left').each( function() {
            var that = $(this);
            if ( that.length != null ) {
               meup_calculate_width( that );
            }
         });

         $('.meup_stretch_column_right').each( function() {
            var that = $(this);
            if ( that.length != null ) {
               meup_calculate_width( that );
            }
         });

      });
      
   });

})(jQuery);