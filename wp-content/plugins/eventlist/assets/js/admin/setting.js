(function ($) {
	$(document).ready(function () {

		window.el_settings = {
			init: function(){
				this.map();
				this.accordion();
				this.package();
			},
			map: function(){

				$('#event_retrict').select2({
					maximumSelectionLength: 5
				});

				if ( ! $('#event_bound').is(":checked") ) {
						$('#event_lat').closest('tr').hide();
			        	$('#event_lng').closest('tr').hide();
			        	$('#event_radius').closest('tr').hide();
				}

		        $('#event_bound').on('change',function(e){
		        	if( $(this).is(":checked") ) {
			            $('#event_lat').closest('tr').show();
			            $('#event_lng').closest('tr').show();
			            $('#event_radius').closest('tr').show();
			        } else {
			        	$('#event_lat').closest('tr').hide();
			        	$('#event_lng').closest('tr').hide();
			        	$('#event_radius').closest('tr').hide();
			        }
		        });
			},
			accordion: function(){
				$( ".ova_accordion" ).accordion({
		        	heightStyle: "content",
					collapsible: true,
					animate: {
				        duration: 500
				    }
			    });
			},
			package: function(){
				$("#sync_data_package").off().on("click",function(e){
					e.preventDefault();

					var $this = $(this);
					var nonce = $this.attr("data-nonce");
					var $icon = $this.find(".dashicons");

					if ( $this.hasClass("completed") ) {
						return false;
					}

					var data = {
						'action': 'el_sync_data_package',
						'nonce': nonce,
					};

					$icon.addClass("rotating");

					$(".ova_el_wrapper_content").block({
	                    message: null,
	                    overlayCSS:  { 
	                        backgroundColor: '#fff', 
	                        opacity: 0.3, 
	                        cursor: 'wait' 
	                    },
	                });

					$.post( ajax_object.ajax_url, data, function(res){
						var res = JSON.parse( res );

						if ( res.status === "success" ) {
							$this.addClass("completed");
							$icon.removeClass("rotating dashicons-update");
							$icon.addClass("dashicons-yes");
						} else {
							$icon.removeClass("rotating");
						}
						
						if ( res.mess ) {
							alert(res.mess);
						}
						$(".ova_el_wrapper_content").unblock();
					} );
				});
			}
		};
		
		el_settings.init();

    });
})(jQuery);