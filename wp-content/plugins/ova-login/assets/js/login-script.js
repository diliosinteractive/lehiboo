(function($) {
    "use strict";

/* ready */
    $(document).ready(function() {
        var $select2_init = $("#signupform select").select2();
        var isAdvancedUpload = function() {
            var div = document.createElement('div');
            return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
        }();

        var checkFileIsValid = function( targetFile, $box_uploadfile ){
            var $input      = $box_uploadfile.find('input[type="file"]');
            var $label      = $box_uploadfile.find('label');
            var showFiles   = function(files) {
                $label.text(files[ 0 ].name);
            };
            var $file_accept    = ["image/png", "image/jpeg", "image/jpg", "application/pdf", "application/msword"];
            var $max_size       = parseInt( $input.data("maxsize") * Math.pow( 10, 6 ) );
            $.each(targetFile, function( index, value ) {

                let $file_size = value.size;
                let $file_type = value.type;

                if ( ! $file_accept.includes( $file_type ) ) {
                    $box_uploadfile.find(".box__error").html( $input.data("msgtype") );
                    $input.replaceWith($input.val('').clone(true));
                    $label.text( $input.data("tryagain") );
                    return;
                } else if ( $file_size >= $max_size ) {
                    $box_uploadfile.find(".box__error").html( $input.data("msgsize") );
                    $input.replaceWith($input.val('').clone(true));
                    $label.text( $input.data("tryagain") );
                    return;
                }
                showFiles( targetFile );
            });
        }

        var $box_uploadfile = $('#signupform .uploadfile-field');

        if ( $box_uploadfile.length && isAdvancedUpload ) {
            $box_uploadfile.each( function(i,el){
                $(el).addClass('has-advanced-upload');
                var droppedFiles    = false;
                var $input = $(el).find('input[type="file"]');
                
                $(el).on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }).on('dragover dragenter', function() {
                    $(el).addClass('is-dragover');
                }).on('dragleave dragend drop', function() {
                    $(el).removeClass('is-dragover');
                }).on('drop', function(e) {
                    $(el).find(".box__error").text("");
                    droppedFiles = e.originalEvent.dataTransfer.files;
                    checkFileIsValid( droppedFiles, $(el) );
                });

                $input.on('change', function(e) {
                    $(el).find(".box__error").text("");
                    var fileTargeted = e.target.files;  
                    checkFileIsValid( fileTargeted, $(el) );
                });

            } );
        }

    if ( $("#signupform #vendor").length && $("#signupform #vendor").is(":checked") ) {
        ova_display_vendor_field();
    }

    if ( $("#signupform #user").is(":checked") ) {
        ova_display_user_field();
    }

    $("#signupform input[name='type_user']").on("change", function(e){
        e.preventDefault();
        let $type_user = $(this).val();

        if ( $type_user == "vendor" ) {
            ova_display_vendor_field();

        }
        if ( $type_user == "user" ){
            ova_display_user_field();
        }
    });

    function ova_display_vendor_field(){
        $("#signupform .used_for_user").hide();
        $("#signupform .used_for_vendor").show();
        var $user_field     = $("#signupform .used_for_user .ova_custom_field");
        var $vendor_field   = $("#signupform .used_for_vendor .ova_custom_field");
        if ( $user_field ) {
            if ( $("#signupform .used_for_user").data("required") == "required" ) {
                $user_field.removeClass("required");
                $user_field.addClass("hidden-field");
            }
        }
        if ( $vendor_field ) {
            if ( $("#signupform .used_for_vendor").data("required") == "required" ) {
                $vendor_field.addClass("required");
                $vendor_field.removeClass("hidden-field");
            }
        }
    }

    function ova_display_user_field(){
        $("#signupform .used_for_user").show();
        $("#signupform .used_for_vendor").hide();
        var $user_field     = $("#signupform .used_for_user .ova_custom_field");
        var $vendor_field   = $("#signupform .used_for_vendor .ova_custom_field");
        if ( $user_field ) {
            if ( $("#signupform .used_for_user").data("required") == "required" ) {
                $user_field.addClass("required");
                $user_field.removeClass("hidden-field");
            }
        }
        if ( $vendor_field ) {
            if ( $("#signupform .used_for_vendor").data("required") == "required" ) {
                $vendor_field.removeClass("required");
                $vendor_field.addClass("hidden-field");
            }
        }
    }

    $(document).on("submit","#signupform", function(e) {
        var submit = true;

        // Remove error
        $( '#signupform .text-err' ).text("");
        if ( $( '#signupform .default_field' ).hasClass("invalid") ) {
            $( '#signupform .default_field' ).removeClass("invalid");
        }
        if ( $( '#signupform .ova_custom_field' ) && $( '#signupform .ova_custom_field' ).hasClass("invalid") ) {
            $( '#signupform .ova_custom_field' ).removeClass("invalid");
            if ( $( '#signupform .ova_custom_field' ).attr('type') == 'file' ) {
                $( '#signupform .ova_custom_field' ).closest('.ova_field_wrap').find('.box__error').text("");
            }
            if ( $( '#signupform .ova_custom_field' ).closest('.ova_field_wrap').find('.select2-selection--single') ) {
                $( '#signupform .ova_custom_field' ).closest('.ova_field_wrap').find('.select2-selection--single').removeClass("invalid");
            }
        }

        // Required field
        $('#signupform .required').each( function(i, el){
            let msg = '';

            if ( $(el).attr('type') == 'file' && ! $(el)[0].files.length ) {
                msg = $(el).data('msg');
                $(el).addClass("invalid");
                $(el).closest('.ova_field_wrap').find('.box__error').text(msg);
                submit = false;
            } else if ( $(el).val() == '' ){
                msg = $(el).data('msg');
                $(el).addClass("invalid");
                if ( $(el).closest('.ova_field_wrap').find('.select2-selection--single') ) {
                    $(el).closest('.ova_field_wrap').find('.select2-selection--single').addClass("invalid");
                }
                $(el).closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }
        });

        // handle checkbox group
        if ( $('#signupform .checkbox-field-group').length ) {
            var $checkbox_list = $('#signupform .checkbox-field-group');
            $checkbox_list.each(function(i,el){
                if ( $(el).find('.required:checkbox').length && ! $(el).find('.required:checkbox:checked').length ) {
                    let msg = '';
                    msg = $(el).data('msg');
                    if (msg) {
                        $(el).find('.text-err').text(msg);
                    }
                    submit = false;
                }
            });
        }

        // password
        if( $('#signupform #password').length ){
            const password_val = $('#signupform #password').val();
            const validatePassword = (password) => {
                return password.match(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/);
            };
            let msg = '';
            if( password_val != '' && ! validatePassword( password_val ) ){
                msg = $('#signupform #password').data('error');
                $('#signupform #password').addClass("invalid");
                $('#signupform #password').closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }
        }

        // password confirm
        if( $('#signupform #password_confirm').length ){
            const password_val = $('#signupform #password_confirm').val();
            const validatePassword = (password) => {
                return password.match(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/);
            };
            let msg = '';
            if( password_val != '' && ! validatePassword( password_val ) && password_val != $('#signupform #password').val() ){
                msg = $('#signupform #password_confirm').data('error');
                $('#signupform #password_confirm').addClass("invalid");
                $('#signupform #password_confirm').closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }
        }

        const validateName = (name) => {
            return XRegExp('^[\\p{Letter}\\p{Separator}\p{Common}]*$').test(name);
        };

        // First name
        if ( $("#signupform #first-name").length ) {
            const $first_name = $("#signupform #first-name").val();

            let msg = '';
            if ( $first_name != '' && ! validateName( $first_name ) ) {
                msg = $( '#signupform #first-name' ).data("invalid");
                $( '#signupform #first-name' ).addClass("invalid");
                $("#signupform #first-name").closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }
        }
        // Last name
        if ( $("#signupform #last-name").length ) {
            const $first_name = $("#signupform #last-name").val();

            let msg = '';
            if ( $first_name != '' && ! validateName( $first_name ) ) {
                msg = $( '#signupform #last-name' ).data("invalid");
                $( '#signupform #last-name' ).addClass("invalid");
                $("#signupform #last-name").closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }
        }

        // Email
        if ( $( '#signupform #email' ).length ) {

            const email = $( '#signupform #email' ).val();
            const validateEmail = (email) => {
                return email.match(
                    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                    );
            };
            let msg = '';

            if ( email != "" && ! validateEmail(email) ){
                msg = $( '#signupform #email' ).data("invalid");
                $( '#signupform #email' ).addClass("invalid");
                $("#signupform #email").closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }

        }
            // Email confirm
        if ( $( '#signupform #email_confirm' ).length ) {
            const email = $( '#signupform #email_confirm' ).val();
            const validateEmail = (email) => {
                return email.match(
                    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                    );
            };
            let msg = '';

            if ( email != "" && ( ! validateEmail(email) || email != $( '#signupform #email' ).val() ) ) {
                msg = $( '#signupform #email_confirm' ).data("invalid");
                $( '#signupform #email_confirm' ).addClass("invalid");
                $("#signupform #email_confirm").closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }

        }
            // User name
        if ( $("#signupform #username").length ) {
            const username = $("#signupform #username").val();
            const validateUsername = (username) => {
                return username.match(
                    /^[a-zA-Z0-9]+$/
                    );
            };
            let msg = '';

            if ( username != "" && ! validateUsername(username) ){
                msg = $( '#signupform #username' ).data("invalid");
                $( '#signupform #username' ).addClass("invalid");
                $("#signupform #username").closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }

        }
            // User Phone
        if ( $( '#signupform #user_phone' ).length ) {
            const phone = $( '#signupform #user_phone' ).val();
            const regex = /^(\(?\+?\d{1,3}\)?)?[-. ]?\(?\d{1,4}\)?[-. ]?\d{1,4}[-. ]?\d{1,9}$/;
            let msg = '';
            if ( phone != "" && ! phone.match(regex) ){
                msg = $( '#signupform #user_phone' ).data("invalid");
                $( '#signupform #user_phone' ).addClass("invalid");
                $("#signupform #user_phone").closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }

        }

        // Validate register custom field email
        if ( $( '#signupform input[data-type="email"]' ).length ) {

            $( '#signupform input[data-type="email"]' ).each(function(i,el){
                let email = $(el).val();
                const validateEmail = (email) => {
                    return email.match(
                        /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                        );
                };
                let msg = '';

                if ( ! $(el).hasClass("hidden-field") && email != "" && ! validateEmail(email) ) {
                    msg = $(el).data("invalid");
                    $(el).addClass("invalid");
                    $(el).closest('.ova_field_wrap').find('.text-err').text(msg);
                    submit = false;
                }

            });

        }
        // Custom field phone
        if ( $( '#signupform input[data-type="tel"]' ).length ) {
            $( '#signupform input[data-type="tel"]' ).each(function(i,el){
                let phone   = $(el).val();
                const regex = /^(\(?\+?\d{1,3}\)?)?[-. ]?\(?\d{1,4}\)?[-. ]?\d{1,4}[-. ]?\d{1,9}$/;
                let msg     = '';
                if ( ! $(el).hasClass("hidden-field") && phone != "" && ! phone.match(regex) ){
                    msg     = $(el).data("invalid");
                    $(el).addClass("invalid");
                    $(el).closest('.ova_field_wrap').find('.text-err').text(msg);
                    submit  = false;
                }

            });
        }
        // Terms
        if( $( '#signupform .register_term' ).length && $( '#signupform .register_term' ).hasClass( 'required' ) ){
            let msg = '';
            if ( ! $( '#signupform .register_term:checked' ).length ) {
                msg = $( '#signupform .register_term:checkbox' ).data('msg');
                $('#signupform .register_term').closest('.ova_field_wrap').find('.text-err').text(msg);
                submit = false;
            }
        }

        if( submit == true ){
            return true;
        } else {
            var $text_err = $('#signupform').find(".text-err");
            $text_err.each(function(i,el){
                if ( $(el).text() != "" ) {
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $(el).offset().top - 200
                    }, 1000);
                    return false;
                }
            });
            
            return false;
        }

        e.preventDefault();

    } );

});

} )(jQuery);