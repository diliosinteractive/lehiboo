( function($) {
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
		// Add js for each element

		elementorFrontend.hooks.addAction('frontend/element_ready/el_search_map.default', function(){
			
		});

		//event slider
		elementorFrontend.hooks.addAction('frontend/element_ready/ova_event_slider.default', function(){
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
			'<i class="arrow_left"></i>',
			'<i class="arrow_right"></i>'
			];

			var rtl = false;
			if( $('body').hasClass('rtl') ){
				rtl = true;
			}

			$(".event-slider").each(function(){
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
				var responsive = owlsl.data('responsive');

				if ( responsive ) {
					responsive_value = responsive;
				}

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
					responsive: responsive_value,
					rtl:rtl,
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
		// end event slider

		//event grid
		elementorFrontend.hooks.addAction('frontend/element_ready/ova_event_grid.default', function(){
			$('.ova-event-grid .el-button-filter button:first-child').addClass('active');
			var button = $('.ova-event-grid .el-button-filter');
			button.each(function() {
				button.on('click', 'button', function() {
					button.find('.active').removeClass('active');
					$(this).addClass('active');
				});
			});


			button.on('click', 'button', function(e) {
				e.preventDefault();

				var filter 		= $(this).data('filter');
				var status 		= $(this).data('status');
				var type_event 	= $(this).data('type');
				var order 		= $(this).data('order');
				var orderby 	= $(this).data('orderby');
				var number_post = $(this).data('number_post');
				var column 		= $(this).data('column');
				var term_id_filter_string = $(this).data('term_id_filter_string');
				var display_img = $(this).attr('data-display-img');

				$(this).parents('.ova-event-grid').find('.wrap_loader').fadeIn(500);

				$.ajax({
					url: ajax_object.ajax_url,
					type: 'POST',
					data: ({
						'action': 'el_filter_elementor_grid',
						'filter': filter,
						'status': status,
						'order': order,
						'orderby': orderby,
						'number_post': number_post,
						'column': column,
						'term_id_filter_string': term_id_filter_string,
						'type_event': type_event,
						'display_img': display_img,
					}),
					success: function(response){
						$('.ova-event-grid .wrap_loader').fadeOut(500);
						var items = $('.ova-event-grid .event_archive');
						items.html( response ).fadeOut(0).fadeIn(500);
						EL_Frontend.el_event_loop_slider();
						EL_Frontend.el_tippy();
					},
				})
			});
		});
		//end event grid



		//event venue slider
		elementorFrontend.hooks.addAction('frontend/element_ready/el_location_event.default', function(){
			var responsive_value = {
				0: {
					items: 1,
				},
				768:  {
					items: 3,
				},
				991:  {
					items: 5,
				},

			};
			var navText = [
			'<i class="arrow_left"></i>',
			'<i class="arrow_right"></i>'
			];

			var rtl = false;
			if( $('body').hasClass('rtl') ){
				rtl = true;
			}

			$(".event-venue-slide").each(function(){
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
					responsive: responsive_value,
					rtl: rtl
				});

				$(this).find('.owl-dot').each(function(index) {
				//Add one to index so it starts from 1
					$(this).attr('aria-label', index + 1);
				});

			});

		});
		// end event venue slider

      	/* Slide Show */
      	elementorFrontend.hooks.addAction('frontend/element_ready/el_event_slideshow.default', function(){
	        function fadeInReset(element) {
	            $(element).find('*[data-animation]').each(function(){
	               	var animation = $(this).data( 'animation' );
	               	$(this).removeClass( 'animated' );
	               	$(this).removeClass( animation );
	               	$(this).css({ opacity: 0 });
	            });
	        }

	        function fadeIn(element) {
	            /* Title */
	            var title 			= $(element).find( '.active .elementor-slide-title' );
	            var animation_title = title.data( 'animation' );
	            var duration_title  = parseInt( title.data( 'animation_dur' ) );

	            setTimeout(function(){
	               	title.addClass(animation_title).addClass('animated').css({ opacity: 1 });
	            }, duration_title);


	            /* Tag */
	            var tag = $(element).find( '.active .elementor-slide-tag' )
	            var animation_tag = tag.data( 'animation' );
	            var duration_tag  = parseInt( tag.data( 'animation_dur' ) );

	            setTimeout(function(){
	               	tag.addClass(animation_tag).addClass('animated').css({ opacity: 1 });
	            }, duration_tag);

	            /* Description */
	            var venue = $(element).find( '.active .elementor-slide-venue' );
	            var animation_venue = venue.data( 'animation' );
	            var duration_venue  = parseInt( venue.data( 'animation_dur' ) );

	            setTimeout(function(){
	               	venue.addClass(animation_venue).addClass('animated').css({ opacity: 1 });
	            }, duration_venue);


	            /* Date */
	            var date = $(element).find( '.active .elementor-slide-date' );
	            var animation_date = date.data( 'animation' );
	            var duration_date  = parseInt( date.data( 'animation_dur' ) );

	            setTimeout(function(){
	               	date.addClass(animation_date).addClass('animated').css({ opacity: 1 });
	            }, duration_date);
	        }

	        $(document).ready(function(){
	            $('.elementor-slides').each(function(){
	               	var owl 	= $(this);
	               	var data 	= owl.data("owl_carousel");

	               	owl.on('initialized.owl.carousel', function(event) {
	                  	fadeIn(event.target);

	                  	let count_element = $(this).find('.owl-item.active .elementor-slide-bottom > div').length;
	                  	if ( count_element <= 1 ) {
	                     	$(this).find('.owl-item.active .elementor-slide-bottom > div').css({
	                        	'padding': '0',
	                        	'text-align': 'center'
	                     	});
	                 	 }
	               	});

	               	owl.owlCarousel(data);
	               
	               	owl.on('translate.owl.carousel', function(event){
	                  	fadeInReset(event.target);
	                  	owl.trigger('stop.owl.autoplay');
	                  	owl.trigger('play.owl.autoplay');
	               	});

	               	owl.on('translated.owl.carousel', function(event) {
	                  	fadeIn(event.target);
	                  	owl.trigger('stop.owl.autoplay');
	                  	owl.trigger('play.owl.autoplay');

	                  	let count_element = $(this).find('.owl-item.active .elementor-slide-bottom > div').length;
	                  	if ( count_element <= 1 ) {
	                     	$(this).find('.owl-item.active .elementor-slide-bottom > div').css({
	                        	'padding': '0',
	                        	'text-align': 'center'
	                     	});
	                  	}
	               	});

	               	$(this).find(".owl-prev").attr("aria-label", "prev");
					$(this).find(".owl-prev").attr("role", "button");
					$(this).find(".owl-next").attr("aria-label", "next");
					$(this).find(".owl-next").attr("role", "button");

					$(this).find('.owl-dot').each(function(index) {
					//Add one to index so it starts from 1
						$(this).attr('aria-label', index + 1);
					});
	            });
	        });
      	});
      	/* End Slide Show */

      	/* Name Event Slider */
      	elementorFrontend.hooks.addAction('frontend/element_ready/el_name_event_slider.default', function(){
         	$(document).ready(function(){
	            $('.el_name_event_slider .wrap_item').each(function(){
	               	var owl = $(this);
	               	var data = owl.data("owl");

	               	owl.owlCarousel(data);
	            });
         	});
      	});
      	/* End Name Event Slider */

      	elementorFrontend.hooks.addAction('frontend/element_ready/el_category_event_slider.default', function(){
			$(".el-event-category-slider .container-slider").each(function(){
		        var owlsl 		= $(this) ;
		        var owlsl_ops 	= owlsl.data('options') ? owlsl.data('options') : {};
		        var rtl = false;
				if( $('body').hasClass('rtl') ){
					rtl = true;
				}

		        var responsive_value = {
		            0:{
		              items:1,
		              nav:false
		            },
		            576:{
		              items:2

		            },
		            768:{
		              items:3

		            },
		            992:{
		              items:4
		            },
		            1170:{
		              items:owlsl_ops.items
		            }
		        };
		        
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
					rtl: rtl,
					navText:[
						'<i class="arrow_carrot-left" aria-hidden="true"></i>',
						'<i class="arrow_carrot-right" aria-hidden="true"></i>'
					],
					navContainer: '.ova-nav-container',
					responsive: responsive_value,
		        });

		      	/* Fixed WCAG */
				owlsl.find(".owl-dots button").attr("aria-label", "dot button");
		    });
		    $(".ova-nav-container button").attr("role","button");
		    $(".ova-nav-container button.owl-prev").attr("aria-label", "Prev button");
		    $(".ova-nav-container button.owl-next").attr("aria-label", "Next button");
		});

      	/* Ova Event Near Me */
		elementorFrontend.hooks.addAction('frontend/element_ready/ova_event_near_me.default', function(){

			baron({
				root: '.cate-el-wrapper',
				scroller: '.main__scroller',
				bar: '.main__bar',
				scrollingCls: '_scrolling',
				draggingCls: '_dragging',
				direction: 'h'
			})
       
	        /* Variable Declaration */
	        const dataEvent 		= $(".ova-event-near-me .event_archive").data("event");
	        const dataQuery 		= $(".ova-event-near-me .event_archive").data("query");
	        const radius 			= $(".ova-event-near-me .event_archive").data("radius");
	        const eventType 		= $(".ova-event-near-me .event_archive").data("event-type");
	        const placeContent 		= $(".ova-event-near-me .ova-event-autocomplete .place-content");
	        const searchBox 		= $(".ova-event-near-me .ova-event-autocomplete .search-box");
	        const placeName 		= $(".ova-event-near-me .ova-event-autocomplete .place-content .place-name");
	        const eventLocation 	= $(".ova-event-near-me #ova-event-location");
	        const eventLocPopup 	= $(".ova-event-near-me #ova-event-location-popup");
	        const curentLocation 	= $('.ova-event-near-me .ova-event-nav .curent-location');
	        const eventLink 		= $(".ova-event-near-me .ova-event-nav .event-link");
	        const onlineEvent 		= $(".ova-event-near-me .ova-event-nav .online-event");
	        const locationName 		= $(".ova-event-near-me .location-name");
	        const eventCateLink 	= $(".ova-event-near-me .category-link");
	        const eventTime 		= $(".ova-event-near-me [data-event='time']");
	        var eventLat 			= $(".ova-event-near-me #ova-event-lat");
	        var eventLng 			= $(".ova-event-near-me #ova-event-lng");
	        var eventStatus 		= $(".ova-event-near-me #ova-event-status");
	        var eventCategory 		= $(".ova-event-near-me [data-event='category']");
	        
			function geocodeLatLng(latlng) {
				const geocoder 		= new google.maps.Geocoder();
				geocoder
				.geocode({ location: latlng })
				.then((response) => {
					locationName.html("");
				    placeName.val("");
				    eventLocation.val("");
				    eventLocPopup.val("");
					if ( response.results[0] ) {
						/* Show address */
						for ( let component of response.results[0].address_components ) {
							if ( component.types.includes("administrative_area_level_2") ) {
								locationName.html(component.long_name);
							    placeName.val(component.long_name);

							    $( $('.el_place_autocomplete_container gmp-place-autocomplete')[0].shadowRoot ).find('input').val(component.long_name);
							    break;
							} else if ( component.types.includes("administrative_area_level_1") ) {
								locationName.html(component.long_name);
								placeName.val(component.long_name);
								$( $('.el_place_autocomplete_container gmp-place-autocomplete')[0].shadowRoot ).find('input').val(component.long_name);
								break;
							} else {
								if ( component.types.includes("country") ) {
									locationName.html(component.long_name);
									placeName.val(component.long_name);
									$( $('.el_place_autocomplete_container gmp-place-autocomplete')[0].shadowRoot ).find('input').val(component.long_name);
									break;
								}
							}
						}

					}
				})
				.catch((e) => console.log("Geocoder failed due to: " + e));
			}
			
			function getLocation() {
				if ( event_element_object?.get_location != "" ) {
					if (navigator.geolocation) {
						navigator.geolocation.getCurrentPosition(showPosition, showError);
					} else { 
						console.log("Geolocation is not supported by this browser.");
					}
				}
			}

			async function showPosition(position) {
				const latlng = {
					lat: position.coords.latitude,
					lng: position.coords.longitude,
				};
				/* Set Lat Lng */
				eventLat.val(latlng.lat);
				eventLng.val(latlng.lng);

				eventStatus.val("map");

				$('.ova-event-near-me').find('.wrap_loader').fadeIn(500);
				eventLink.removeClass("active");
				curentLocation.addClass("active");
				/* Handle Address */
				geocodeLatLng(latlng);
				/* Handle get list ID Events */
				const ids = await getListIdEventRadius(latlng,dataEvent);
				const data = {
					'action': 'el_geocode',
					'data': {
						ids: ids,
						query: dataQuery,
						type: eventType,
					},
				};
				/* Ajax Request */
				$.post(ajax_object.ajax_url, data, function(response) {
					$('.ova-event-near-me .wrap_loader').fadeOut(500);
					var items = $('.ova-event-near-me .event_archive');
					items.html( response ).fadeOut(0).fadeIn(500);

					EL_Frontend.el_event_loop_slider();
					EL_Frontend.el_tippy();
				});
			}

			async function getListIdEventRadius(latlng,dataEvent){
				const {spherical} = await google.maps.importLibrary("geometry");
				let ids = [];
				for ( let item of dataEvent ) {
					let location = convertLocation(item);
					const distance = google.maps.geometry.spherical.computeDistanceBetween(location, latlng);
					if ( distance < radius ) {
						ids.push(item.id);
					}
				}
				if ( ids.length == 0 ) {
					ids = [0];
				}
				return ids;
			}

			function getLocationByEvent(dataEvent){
				const locations = {};
			}

			function convertLocation(location){
				location.lat = parseFloat(location.lat);
				location.lng = parseFloat(location.lng);
				return location;
			}

			function getListEventDefault(){
				$('.ova-event-near-me').find('.wrap_loader').fadeIn(500);
				const data = {
					'action': 'el_event_default',
					'data': {
						query: dataQuery,
						type: eventType,
					},
				};
				/* Ajax Request */
				$.post(ajax_object.ajax_url, data, function(response) {
					$('.ova-event-near-me .wrap_loader').fadeOut(500);
					var items = $('.ova-event-near-me .event_archive');
					items.html( response ).fadeOut(0).fadeIn(500);

					EL_Frontend.el_event_loop_slider();
					EL_Frontend.el_tippy();
				});
			}

			function getListOnlineEvent(){
				$('.ova-event-near-me').find('.wrap_loader').fadeIn(500);

				eventStatus.val("online");

				locationName.html("");
			    placeName.val("");

			    locationName.html("Online");
				placeName.val("Online events");

				const data = {
					'action': 'el_event_online',
					'data': {
						query: dataQuery,
						type: eventType,
					},
				};
				/* Ajax Request */
				$.post(ajax_object.ajax_url, data, function(response) {
					$('.ova-event-near-me .wrap_loader').fadeOut(500);
					var items = $('.ova-event-near-me .event_archive');
					items.html( response ).fadeOut(0).fadeIn(500);

					EL_Frontend.el_event_loop_slider();
					EL_Frontend.el_tippy();
				});
			}

			async function getListEventFilterTime(time){
				$('.ova-event-near-me').find('.wrap_loader').fadeIn(500);
				let data, ids;
				/* Check location exit */
				if ( eventStatus.val() == "map" ) {
					let latlng = {
						lat: parseFloat( eventLat.val() ),
						lng: parseFloat( eventLng.val() ),
					};
					ids = await getListIdEventRadius(latlng,dataEvent);
					data = {
						'action': 'el_event_by_time',
						'data': {
							ids: ids,
							query: dataQuery,
							type: eventType,
							time: time,
						},
					};
				} else if ( eventStatus.val() == "online" ) {
					data = {
						'action': 'el_event_by_time',
						'data': {
							status: eventStatus.val(),
							query: dataQuery,
							type: eventType,
							time: time,
						},
					};
				} else {
					data = {
						'action': 'el_event_by_time',
						'data': {
							query: dataQuery,
							type: eventType,
							time: time,
						},
					};
				}
				
				/* Ajax Request */
				$.post(ajax_object.ajax_url, data, function(response) {
					$('.ova-event-near-me .wrap_loader').fadeOut(500);
					var items = $('.ova-event-near-me .event_archive');
					items.html( response ).fadeOut(0).fadeIn(500);

					EL_Frontend.el_event_loop_slider();
					EL_Frontend.el_tippy();
				});
			}

			async function getListEventCategory(cateId){
				$('.ova-event-near-me').find('.wrap_loader').fadeIn(500);
				let data, ids;
				/* Check location exit */

				if ( eventStatus.val() == "map" ) {
					let latlng = {
						lat: parseFloat( eventLat.val() ),
						lng: parseFloat( eventLng.val() ),
					};
					ids = await getListIdEventRadius(latlng,dataEvent);
					data = {
						'action': 'el_geocode',
						'data': {
							ids: ids,
							query: dataQuery,
							type: eventType,
							cate_id: cateId,
						},
					};
				} else if ( eventStatus.val() == "online" ) {
					data = {
						'action': 'el_geocode',
						'data': {
							status: eventStatus.val(),
							query: dataQuery,
							type: eventType,
							cate_id: cateId,
						},
					};
				} else {
					data = {
						'action': 'el_geocode',
						'data': {
							query: dataQuery,
							type: eventType,
							cate_id: cateId,
						},
					};
				}
				
				/* Ajax Request */
				$.post(ajax_object.ajax_url, data, function(response) {
					$('.ova-event-near-me .wrap_loader').fadeOut(500);
					var items = $('.ova-event-near-me .event_archive');
					items.html( response ).fadeOut(0).fadeIn(500);

					EL_Frontend.el_event_loop_slider();
					EL_Frontend.el_tippy();
				});
			}

			function showError(error) {
				switch(error.code) {
				case error.PERMISSION_DENIED:
					getListEventDefault();
					console.log("User denied the request for Geolocation.");
					break;
				case error.POSITION_UNAVAILABLE:
					console.log("Location information is unavailable.");
					break;
				case error.TIMEOUT:
					console.log("The request to get user location timed out.");
					break;
				case error.UNKNOWN_ERROR:
					console.log("An unknown error occurred.");
					break;
				}
			}

			async function initPlaceAutocomplete(){
				// Request needed libraries.
			    await google.maps.importLibrary("places");
			    // Create the input HTML element, and append it.
			    //@ts-ignore
			    const restrict = JSON.parse( $('.el_place_autocomplete_container').attr("data-retrict") );
			    const bound = $('.el_place_autocomplete_container').attr('data-bound');
			    const radius = $('.el_place_autocomplete_container').attr('data-radius');
			    const lng = $('.el_place_autocomplete_container').attr('data-lng');
			    const lat = $('.el_place_autocomplete_container').attr('data-lat');

			    var locationBias = {};

			    if ( bound ) {
			    	locationBias['radius'] = parseFloat(radius);
			    	locationBias['center'] = {
			    		'lat': parseFloat(lat),
			    		'lng': parseFloat(lng)
			    	};
			    }

			    var placeAutocompleteOption = {
			    	includedRegionCodes: restrict.map(v => v.toLowerCase()),
			    };

			    if ( ! $.isEmptyObject( locationBias ) ) {
			    	placeAutocompleteOption['locationBias'] = locationBias;
			    }

			    const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement( placeAutocompleteOption );


			    //@ts-ignore
			    $('.el_place_autocomplete_container').append(placeAutocomplete);
			    // Add the gmp-placeselect listener, and display the results.
			    //@ts-ignore
			    placeAutocomplete.addEventListener('gmp-select', async ({ placePrediction }) => {
			        const place = placePrediction.toPlace();
			        await place.fetchFields({ fields: ['displayName', 'formattedAddress', 'location'] });

			        $('.ova-event-near-me #ova-event-lat').val( place.toJSON()?.location?.lat );
			        $('.ova-event-near-me #ova-event-lng').val( place.toJSON()?.location?.lng );
			        $('.ova-event-near-me .location-name').html( place.toJSON()?.displayName );

			        const latlng = {
						lat: place.toJSON()?.location?.lat,
						lng: place.toJSON()?.location?.lng,
					};

					/* Set Lat Lng */
					eventLat.val(latlng.lat);
					eventLng.val(latlng.lng);

					eventStatus.val("map");

					$('.ova-event-near-me').find('.wrap_loader').fadeIn(500);
					$('.ova-event-near-me .ova-event-autocomplete .search-box .curent-location').addClass("active");
					/* Handle Address */
					geocodeLatLng(latlng);
					/* Handle get list ID Events */
					const ids = await getListIdEventRadius(latlng,dataEvent);
					let cateId = 0;
					if ( eventCateLink.hasClass("active") ) {
						cateId 	= eventCateLink.data("id");
					}
					const data = {
						'action': 'el_geocode',
						'data': {
							ids: ids,
							query: dataQuery,
							type: eventType,
							cate_id: cateId,
						},
					};
					/* Ajax Request */
					$.post(ajax_object.ajax_url, data, function(response) {
						$('.ova-event-near-me .wrap_loader').fadeOut(500);
						var items = $('.ova-event-near-me .event_archive');
						items.html( response ).fadeOut(0).fadeIn(500);

						EL_Frontend.el_event_loop_slider();
						EL_Frontend.el_tippy();
					});
			    });
			}
			
			/* Add Event Listener */
			placeContent.on("click",function(e){
	        	e.preventDefault();
	        	e.stopPropagation();
	        	$([document.documentElement, document.body]).animate({
			        scrollTop: $(".ova-event-near-me").offset().top - 100
			    }, 500);
	        	placeContent.hide();
	        	searchBox.show();
	        });

			$("body").on("click",function(e){
				const target = $(e.target);   
			    if ( ! $(".ova-event-near-me .ova-event-autocomplete").find(target).length && ! locationName.find(target).length ) {
			    	searchBox.hide();
			    	placeContent.show();
			    	$(".ova-event-near-me .ova-event-popup").hide();
			    }
			});

			curentLocation.on("click",function(e){
				e.preventDefault();
				e.stopPropagation();
				eventLink.removeClass("active");
				curentLocation.addClass("active");
				locationName.html("");
				getLocation();
			});

			onlineEvent.on("click",function(e){
				e.preventDefault();
				e.stopPropagation();
				eventLink.removeClass("active");
				onlineEvent.addClass("active");
				getListOnlineEvent();
			});

			$(".ova-event-near-me .title-location").on("click",function(e){
				e.preventDefault();
				e.stopPropagation();
				$(".ova-event-near-me .ova-event-popup").show();
			});

			eventCategory.on("click",function(e){
				e.preventDefault();
				eventCateLink.removeClass("active");
				$(this).addClass("active");
				const cateId = $(this).data("id");
				getListEventCategory(cateId);
			});

			eventTime.on("click",function(e){
				e.preventDefault();
				eventCateLink.removeClass("active");
				$(this).addClass("active");
				const time = $(this).data("id");
				getListEventFilterTime(time);
			});

			/* Call function */
			getLocation();

			initPlaceAutocomplete();
	    });

		/* Ova Event Recent */
      	elementorFrontend.hooks.addAction('frontend/element_ready/ova_event_recent.default', function(){

        	const dataQuery 	= $(".ova-event-recent .event_archive").data("query");
        	const eventType 	= $(".ova-event-recent .event_archive").data("event-type");
        	const eventRecent 	= $(".ova-event-recent .event_archive");
        	
        	eventRecent.on("click",".event_remove",function(e){
        		e.preventDefault();
        		$('.ova-event-recent').find('.wrap_loader').fadeIn(500);
        		const id = $(this).data("id");
        		const data = {
					'action': 'el_event_recent',
					'data': {
						query: dataQuery,
						type: eventType,
						id: id,
					},
				};

				/* Ajax Request */
				$.post(ajax_object.ajax_url, data, function(response) {
					$('.ova-event-recent .wrap_loader').fadeOut(500);
					var items = $('.ova-event-recent .event_archive');
					items.html( response ).fadeOut(0).fadeIn(500);

					EL_Frontend.el_event_loop_slider();
					EL_Frontend.el_tippy();
				});
        	});
      	});

      	/* Ova Event Search 2 */
      	elementorFrontend.hooks.addAction('frontend/element_ready/el_search_form_2.default', function(){

      		const map_lat 			= $(".elementor_search_form_2 input[name='map_lat']");
      		const map_lng 			= $(".elementor_search_form_2 input[name='map_lng']");
      		const inputSearch 		= $(".elementor_search_form_2 input[name='map_address']");
      		const nearMe 			= $(".elementor_search_form_2 .near_me");
      		const advancedSearch 	= $(".elementor_search_form_2 .advanced_search");
      		const dateFormat 		= $(".elementor_search_form_2 .ova_date_time").data("format");
      		const firstDay 			= $(".elementor_search_form_2 .ova_date_time").data("first-day");

      		// Calendar
  			$(".elementor_search_form_2 .ova_select2").select2({
   				allowClear: true
      		});

      		$(".elementor_search_form_2 .ova_category").select2({
      			placeholder: $(".elementor_search_form_2 .ova_control_category").data("placeholder"),
      			allowClear: true
      		});

      		$(".elementor_search_form_2 #ova_event_time").select2({
   				allowClear: true
      		});

      		$(".select2-selection__rendered").attr("aria-hidden","true");
         	$(".select2-selection--single").attr("aria-hidden","true");

      		$(".elementor_search_form_2 #ova_event_time").on('select2:select', function (e) {
			    $(".elementor_search_form_2 input[name='start_date']").val(null);
			    $(".elementor_search_form_2 input[name='end_date']").val(null);
			});

      		$(".elementor_search_form_2 input[name='start_date']").datetimepicker({
      			format: dateFormat,
      			dayOfWeekStart: firstDay,
      			onShow:function( ct ){
      				this.setOptions({
      					maxDate:$(".elementor_search_form_2 input[name='end_date']").val()?$(".elementor_search_form_2 input[name='end_date']").val():false
      				})
      			},
      			timepicker:false,
      			onChangeDateTime:function(dp,$input){
				    $(".elementor_search_form_2 #ova_event_time").val(null).trigger("change");
				}
      		});
      		$(".elementor_search_form_2 input[name='end_date']").datetimepicker({
      			format: dateFormat,
      			dayOfWeekStart: firstDay,
      			onShow:function( ct ){
      				this.setOptions({
      					minDate:$(".elementor_search_form_2 input[name='start_date']").val()?$(".elementor_search_form_2 input[name='start_date']").val():false
      				})
      			},
      			timepicker:false,
      			onChangeDateTime:function(dp,$input){
				    $(".elementor_search_form_2 #ova_event_time").val(null).trigger("change");
				}
      		});

      		// Autocomplete input
      		$(".elementor_search_form_2 input[name='name_venue']").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'POST',
                        dataType: "json",
                        data: {
                            action: 'el_load_venue',
                            keyword: request.term,
                        },
                        success: function(data) {
                            response(data);
                        },
                    });
                },
                delay: 0,
            });

      		// Google Map
      		function getLocation() {

      			if ( event_element_object?.get_location != "" ) {
      				if (navigator.geolocation) {
						navigator.geolocation.getCurrentPosition(showPosition, showError);
					} else { 
						console.log("Geolocation is not supported by this browser.");
					}
      			}
				
			}

			function convertLocation(location){
				location.lat = parseFloat(location.lat);
				location.lng = parseFloat(location.lng);
				return location;
			}

			function showPosition(position) {

				const latlng = {
					lat: position.coords.latitude,
					lng: position.coords.longitude,
				};
				/* Set Lat Lng */
				map_lat.val(latlng.lat);
				map_lng.val(latlng.lng);

				/* Handle Address */
				geocodeLatLng(latlng);
			}

      		function geocodeLatLng(latlng) {
				const geocoder 		= new google.maps.Geocoder();
				geocoder
				.geocode({ location: latlng })
				.then((response) => {

					if ( response.results[0] ) {
						/* Show address */
						inputSearch.val(response.results[0]['formatted_address']);
						$( $('.elementor_search_form_2 gmp-place-autocomplete')[0].shadowRoot ).find('input').val(response.results[0]['formatted_address']);
					}
				})
				.catch((e) => console.log("Geocoder failed due to: " + e));
			}

			function showError(error) {
				switch(error.code) {
				case error.PERMISSION_DENIED:
					console.log("User denied the request for Geolocation.");
					break;
				case error.POSITION_UNAVAILABLE:
					console.log("Location information is unavailable.");
					break;
				case error.TIMEOUT:
					console.log("The request to get user location timed out.");
					break;
				case error.UNKNOWN_ERROR:
					console.log("An unknown error occurred.");
					break;
				}
			}

			nearMe.on("click",function(e){
				e.preventDefault();
				getLocation();
				
			});

			advancedSearch.on("click",function(e){
				e.preventDefault();
				$(".elementor_search_form_2 .ova_filter").slideToggle();
			});
			getLocation();

			if ( inputSearch ) {
				initMap();
			}
			/*
			Place Autocomplete Widget
			*/
			async function initMap(){
				// Request needed libraries.
			    await google.maps.importLibrary("places");
			    // Create the input HTML element, and append it.

			    const restrict = JSON.parse( $('.search_box_wrapper').attr("data-retrict") );
			    const bound = $('.search_box_wrapper').attr('data-bound');
			    const radius = $('.search_box_wrapper').attr('data-radius');
			    const lng = $('.search_box_wrapper').attr('data-lng');
			    const lat = $('.search_box_wrapper').attr('data-lat');

			    var locationBias = {};

			    if ( bound ) {
			    	locationBias['radius'] = parseFloat(radius);
			    	locationBias['center'] = {
			    		'lat': parseFloat(lat),
			    		'lng': parseFloat(lng)
			    	};
			    }

			    var placeAutocompleteOption = {
			    	includedRegionCodes: restrict.map(v => v.toLowerCase()),
			    };
			    if ( ! $.isEmptyObject( locationBias ) ) {
			    	placeAutocompleteOption['locationBias'] = locationBias;
			    }
			    //@ts-ignore
			    const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement( placeAutocompleteOption );
			  
		   		
			    //@ts-ignore
			    $(".ova_control_address").append(placeAutocomplete);

			    

			    // Add the gmp-placeselect listener, and display the results.
			    //@ts-ignore
			    placeAutocomplete.addEventListener('gmp-select', async ({ placePrediction }) => {
			        const place = placePrediction.toPlace();
			        await place.fetchFields({ fields: ['displayName', 'formattedAddress', 'location'] });
					
			        $('input[name="map_lat"]').val( place.toJSON()?.location?.lat );
			        $('input[name="map_lng"]').val( place.toJSON()?.location?.lng );

			        $('input[name="map_address"]').val( place.toJSON()?.displayName );
			    });

			}
			
      	});
		// Ova Event Grid
		elementorFrontend.hooks.addAction('frontend/element_ready/ova_event_grid.default', function(){

			baron({
				root: '.ova__fillter_wrap',
				scroller: '.main__scroller',
				bar: '.main__bar',
				scrollingCls: '_scrolling',
				draggingCls: '_dragging',
				direction: 'h'
			})
      	});

   	});

} ) (jQuery);