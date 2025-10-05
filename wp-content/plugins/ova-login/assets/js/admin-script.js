(function ($) {
   /* object js */
    LG_Admin = {
        init: function () {
            this.ova_register_field();
            this.ova_login_setting();
            this.ovalg_vendor_approve();

            this.add_menu();
        },


        add_menu: function(){


            switch( pagenow ){

            case 'events_page_ovareg_custom_field_settings':
                $('#adminmenu a[href="admin.php?page=ovalg_general_settings"]').addClass('current').closest('li').addClass('current');
                break;
            case 'events_page_ovalg_vendor_approve':
                $('#adminmenu a[href="admin.php?page=ovalg_general_settings"]').addClass('current').closest('li').addClass('current');
                break;
            default:
                break;
            }
        },

        ovalg_vendor_approve_submit: function(){

            var $user_id = $( "#ova-dialog-user-info" ).attr('data-id');
            var $nonce = $( "#ova-dialog-user-info" ).attr('data-nonce');
            var $url = $(document).find('input[name="_wp_http_referer"]').val();

            var data = {
                'action': 'ovalg_vendor_approve_submit',
                'nonce': $nonce,
                'user_id': $user_id,
                'url': $url,
            };

            $(document).find(".ova_vendor_approve_loader").css("display","block");
            $(document).find(".ova_vendor_approve_table_wrapper").addClass("loading");

            $.post(ajax_object.ajax_url, data, function(response) {
                if ( response ) {
                    window.location.href = response;
                }
            });

            dialog_user_info.dialog( "close" );
            return false;
            
        },

        ovalg_vendor_reject_submit: function(){

            var $user_id = $( "#ova-dialog-user-info" ).attr('data-id');
            var $nonce = $( "#ova-dialog-user-info" ).attr('data-nonce');
            var $mess = $("#ova_reason_reject_mess").val();
            var $url = $(document).find('input[name="_wp_http_referer"]').val();

            var data = {
                'action': 'ovalg_vendor_reject_submit',
                'nonce': $nonce,
                'user_id': $user_id,
                'mess': $mess,
                'url': $url,
            };

            $(document).find(".ova_vendor_approve_loader").css("display","block");
            $(document).find(".ova_vendor_approve_table_wrapper").addClass("loading");

            $.post(ajax_object.ajax_url, data, function(response) {
                if ( response ) {
                    window.location.href = response;
                }
            });

            dialog_reason_reject.dialog( "close" );
            dialog_user_info.dialog( "close" );
            return false;
        },

        ovalg_vendor_approve: function(){

            var $check_page = $( "#ova-dialog-user-info" ).attr('data-page'); 
            var $approve_text = $( "#ova-dialog-user-info" ).attr('data-approve');  
            var $reject_text = $( "#ova-dialog-user-info" ).attr('data-reject');  
            var $button_text = $("#ova-dialog-reason-reject-form").attr('data-button');

            var $button_data = [];

            switch( $check_page ) {
                case 'reject':
                $button_data.push(
                    {
                        text: $reject_text,
                        click: function() {
                            dialog_reason_reject.dialog( "open" );
                        }
                    }
                );
                break;
                case 'approve':
                $button_data.push(
                    {
                        text: $approve_text,
                        click: function() {
                            LG_Admin.ovalg_vendor_approve_submit();
                        }
                    }
                );
                break;
                default:
                $button_data = [
                    {
                        text: $reject_text,
                        click: function() {
                            dialog_reason_reject.dialog( "open" );
                        }
                    },
                    {
                        text: $approve_text,
                        click: function() {
                            LG_Admin.ovalg_vendor_approve_submit();
                        }
                    }
                ];
            }

            dialog_user_info = $( "#ova-dialog-user-info" ).dialog({
                autoOpen: false,
                height: 400,
                width: 350,
                modal: true,
                buttons: $button_data,
            });

            dialog_reason_reject = $( "#ova-dialog-reason-reject-form" ).dialog({
                autoOpen: false,
                height: 300,
                width: 350,
                modal: true,
                buttons: [
                    {
                        text: $button_text,
                        click: function() {
                            LG_Admin.ovalg_vendor_reject_submit();
                        }
                    },
                    
                ],
            });

        $(document).find('.show_info_vendor').on('click',function(e){
            e.preventDefault();

            $(document).find('#ova-dialog-user-info .spinner').addClass('is-active');
            $(document).find('#ova-dialog-user-info .table_wrapper').html('');
            var $nonce  = $(this).attr('data-nonce');
            var $id     = $(this).attr('data-id');

            $( "#ova-dialog-user-info" ).attr('data-id',$id);

            var data = {
                'action': 'ovalg_vendor_approve_show_info',
                'nonce': $nonce,
                'user_id': $id,
            };

            $.post(ajax_object.ajax_url, data, function(response) {
                $(document).find('#ova-dialog-user-info .spinner').removeClass('is-active');
                $(document).find('#ova-dialog-user-info .table_wrapper').html(response);
            });

            dialog_user_info.dialog( "open" );
        });

        },

        ova_register_field: function() {
            // Sortable
            $('.ovalg-sortable').sortable({
                update: function( event, ui ) {
                    $(".ova-list-register-field .wrap_loader").show();
                    const ova_post_name = $(".ova-list-register-field .ova_pos_name");
                    var pos = {};
                    ova_post_name.each(function(i,el){
                        
                        $(el).each(function(){
                            pos[$(this).data("name")] = i;
                        });                        
                        
                    });
                    var data = {
                        'action': 'ova_lg_sortable_register_field',
                        'pos': pos,
                    };

                    $.post(ajax_object.ajax_url, data, function(response) {
                        $(".ova-list-register-field .ovalg-sortable").html(response);
                        $(".ova-list-register-field .wrap_loader").hide();
                    });
                }
            });

            // Select
            var OVA_OPTION_ROW_HTML  = '';
                OVA_OPTION_ROW_HTML += '<tr>';
                OVA_OPTION_ROW_HTML += '<td><input type="text" name="ova_options_key[]" placeholder="Option Value" /></td>';
                OVA_OPTION_ROW_HTML += '<td><input type="text" name="ova_options_text[]" placeholder="Option Text" /></td>';
                OVA_OPTION_ROW_HTML += '<td class="ova-box"><a href="javascript:void(0)" class="ovalg_addfield btn btn-blue" title="Add new option">+</a></td>';
                OVA_OPTION_ROW_HTML += '<td class="ova-box"><a href="javascript:void(0)" class="ovalg_remove_row btn btn-red" title="Remove option">x</a></td>';
                OVA_OPTION_ROW_HTML += '<td class="ova-box sort"><span class="dashicons dashicons-menu" title="Drag & Drop"></span></td>';
                OVA_OPTION_ROW_HTML += '</tr>';

            $(document).on('click', '.ova-wrap-popup-register-field .ovalg_addfield', function(e) {
                var table       = $(this).closest('table');
                var optionsSize = table.find('tbody tr').size();
                var height      = $('.ova-wrap-popup-register-field').attr('height');

                if ( height ) {
                    height = parseInt(height) + 5;
                } else {
                    height = 110;
                }
                 
                $('.ova-wrap-popup-register-field').attr('height', height);
                $('.ova-wrap-popup-register-field').css('height', height + 'vh');

                if ( optionsSize > 0 ) {
                    table.find('tbody tr:last').after(OVA_OPTION_ROW_HTML);
                } else {
                    table.find('tbody').append(OVA_OPTION_ROW_HTML);        
                }
            });
             
            $(document).on('click','.ova-wrap-popup-register-field .ovalg_remove_row', function(e) {
                var table = $(this).closest('table');
                $(this).closest('tr').remove();
                var optionsSize = table.find('tbody tr').size();
                     
                if (optionsSize == 0) {
                    table.find('tbody').append(OVA_OPTION_ROW_HTML);
                }
            });
            // End

            // Radio
            var OVA_RADIO_ROW_HTML  = '';
                OVA_RADIO_ROW_HTML += '<tr>';
                OVA_RADIO_ROW_HTML += '<td><input type="text" name="ova_radio_key[]" placeholder="Option Value" /></td>';
                OVA_RADIO_ROW_HTML += '<td><input type="text" name="ova_radio_text[]" placeholder="Option Text" /></td>';
                OVA_RADIO_ROW_HTML += '<td class="ova-box"><a href="javascript:void(0)" class="el_lg_add_radio btn btn-blue" title="Add new option">+</a></td>';
                OVA_RADIO_ROW_HTML += '<td class="ova-box"><a href="javascript:void(0)" class="el_lg_remove_radio btn btn-red" title="Remove option">x</a></td>';
                OVA_RADIO_ROW_HTML += '<td class="ova-box sort"><span class="dashicons dashicons-menu" title="Drag & Drop"></span></td>';
                OVA_RADIO_ROW_HTML += '</tr>';

            $(document).on('click', '.ova-wrap-popup-register-field .el_lg_add_radio', function(e) {
                var table       = $(this).closest('table');
                var optionsSize = table.find('tbody tr').size();
                var height      = $('.ova-wrap-popup-register-field').attr('height');

                if ( height ) {
                    height = parseInt(height) + 5;
                } else {
                    height = 110;
                }
                 
                $('.ova-wrap-popup-register-field').attr('height', height);
                $('.ova-wrap-popup-register-field').css('height', height + 'vh');

                if ( optionsSize > 0 ) {
                    table.find('tbody tr:last').after(OVA_RADIO_ROW_HTML);
                } else {
                    table.find('tbody').append(OVA_RADIO_ROW_HTML);        
                }
            });
             
            $(document).on('click','.ova-wrap-popup-register-field .el_lg_remove_radio', function(e) {
                var table = $(this).closest('table');
                $(this).closest('tr').remove();
                var optionsSize = table.find('tbody tr').size();
                     
                if (optionsSize == 0) {
                    table.find('tbody').append(OVA_RADIO_ROW_HTML);
                }
            });
            // End
            
            // Checkbox
            var OVA_CHECKBOX_ROW_HTML  = '';
                OVA_CHECKBOX_ROW_HTML += '<tr>';
                OVA_CHECKBOX_ROW_HTML += '<td><input type="text" name="ova_checkbox_key[]" placeholder="Option Value" /></td>';
                OVA_CHECKBOX_ROW_HTML += '<td><input type="text" name="ova_checkbox_text[]" placeholder="Option Text" /></td>';
                OVA_CHECKBOX_ROW_HTML += '<td class="ova-box"><a href="javascript:void(0)" class="el_lg_add_checkbox btn btn-blue" title="Add new option">+</a></td>';
                OVA_CHECKBOX_ROW_HTML += '<td class="ova-box"><a href="javascript:void(0)" class="el_lg_remove_checkbox btn btn-red" title="Remove option">x</a></td>';
                OVA_CHECKBOX_ROW_HTML += '<td class="ova-box sort"><span class="dashicons dashicons-menu" title="Drag & Drop"></span></td>';
                OVA_CHECKBOX_ROW_HTML += '</tr>';

            $(document).on('click', '.ova-wrap-popup-register-field .el_lg_add_checkbox', function(e) {
                var table       = $(this).closest('table');
                var optionsSize = table.find('tbody tr').size();
                var height      = $('.ova-wrap-popup-register-field').attr('height');

                if ( height ) {
                    height = parseInt(height) + 5;
                } else {
                    height = 110;
                }
                 
                $('.ova-wrap-popup-register-field').attr('height', height);
                $('.ova-wrap-popup-register-field').css('height', height + 'vh');

                if ( optionsSize > 0 ) {
                    table.find('tbody tr:last').after(OVA_CHECKBOX_ROW_HTML);
                } else {
                    table.find('tbody').append(OVA_CHECKBOX_ROW_HTML);        
                }
            });
             
            $(document).on('click','.ova-wrap-popup-register-field .el_lg_remove_checkbox', function(e) {
                var table = $(this).closest('table');
                $(this).closest('tr').remove();
                var optionsSize = table.find('tbody tr').size();
                     
                if (optionsSize == 0) {
                    table.find('tbody').append(OVA_CHECKBOX_ROW_HTML);
                }
            });
            // End

            $(document).on('click','.ovalg_edit_field_form', function(e) {
                var data            = $(this).data('data_edit');
                var name            = data.name;
                var type            = data.type ? data.type : 'text';
                var label           = data.label;
                var description     = data.description;
                var placeholder     = data.placeholder;

                var ova_class       = data.class;
                var class_icon      = data.class_icon;
                var position        = data.position;
                var max_file_size   = data.max_file_size;
                var used_for        = data.used_for;
                var required        = data.required;

                var ova_options_key     = data.ova_options_key;
                var ova_options_text    = data.ova_options_text;

                var ova_radio_key     = data.ova_radio_key;
                var ova_radio_text    = data.ova_radio_text;

                var ova_checkbox_key    = data.ova_checkbox_key;
                var ova_checkbox_text   = data.ova_checkbox_text;

                // Placeholder

                if ( type == 'radio' || type == 'checkbox' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-placeholder').css('display', 'none');
                } else {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-placeholder').css('display', 'table-row');
                }

                // Select
                var option_html_edit = '';
                 
                if ( type === 'select' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-options table.ova-sub-table tbody').empty();
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-options').css('display', 'table-row');

                    ova_options_key.forEach(function(item, key) {
                        option_html_edit += '<tr>';
                        option_html_edit += '<td><input type="text" name="ova_options_key[]" placeholder="Option Value" value="'+item+'" /></td>';
                        option_html_edit += '<td><input type="text" name="ova_options_text[]" placeholder="Option Text" value="'+ova_options_text[key]+'" /></td>';
                        option_html_edit += '<td class="ova-box"><a href="javascript:void(0)"  class="ovalg_addfield btn btn-blue" title="Add new option">+</a></td>';
                        option_html_edit += '<td class="ova-box"><a href="javascript:void(0)" class="ovalg_remove_row btn btn-red" title="Remove option">x</a></td>';
                        option_html_edit += '<td class="ova-box sort"><span class="dashicons dashicons-menu" title="Drag & Drop"></span></td>';
                        option_html_edit += '</tr>';
                    });

                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-options table.ova-sub-table tbody').append(option_html_edit)
                } else {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-options').css('display', 'none');
                }
                // End
                
                // Radio
                var radio_html_edit = '';
                 
                if ( type === 'radio' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-radio table.ova-sub-table tbody').empty();
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-radio').css('display', 'table-row');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-class-icon').css('display', 'none');

                    ova_radio_key.forEach(function(item, key) {
                        radio_html_edit += '<tr>';
                        radio_html_edit += '<td><input type="text" name="ova_radio_key[]" placeholder="Option Value" value="'+item+'" /></td>';
                        radio_html_edit += '<td><input type="text" name="ova_radio_text[]" placeholder="Option Text" value="'+ova_radio_text[key]+'" /></td>';
                        radio_html_edit += '<td class="ova-box"><a href="javascript:void(0)" class="el_lg_add_radio btn btn-blue" title="Add new option">+</a></td>';
                        radio_html_edit += '<td class="ova-box"><a href="javascript:void(0)" class="el_lg_remove_radio btn btn-red" title="Remove option">x</a></td>';
                        radio_html_edit += '<td class="ova-box sort"><span class="dashicons dashicons-menu" title="Drag & Drop"></span></td>';
                        radio_html_edit += '</tr>';
                    });

                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-radio table.ova-sub-table tbody').append(radio_html_edit)
                } else {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-radio').css('display', 'none');
                }
                // End
                
                // Checkbox
                var checkbox_html_edit = '';
                 
                if ( type === 'checkbox' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-checkbox table.ova-sub-table tbody').empty();
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-checkbox').css('display', 'table-row');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-class-icon').css('display', 'none');

                    ova_checkbox_key.forEach(function(item, key) {
                        checkbox_html_edit += '<tr>';
                        checkbox_html_edit += '<td><input type="text" name="ova_checkbox_key[]" placeholder="Option Value" value="'+item+'" /></td>';
                        checkbox_html_edit += '<td><input type="text" name="ova_checkbox_text[]" placeholder="Option Text" value="'+ova_checkbox_text[key]+'" /></td>';
                        checkbox_html_edit += '<td class="ova-box"><a href="javascript:void(0)" class="el_lg_add_checkbox btn btn-blue" title="Add new option">+</a></td>';
                        checkbox_html_edit += '<td class="ova-box"><a href="javascript:void(0)" class="el_lg_remove_checkbox btn btn-red" title="Remove option">x</a></td>';
                        checkbox_html_edit += '<td class="ova-box sort"><span class="dashicons dashicons-menu" title="Drag & Drop"></span></td>';
                        checkbox_html_edit += '</tr>';
                    });

                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-checkbox table.ova-sub-table tbody').append(checkbox_html_edit)
                } else {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-checkbox').css('display', 'none');
                }
                // End
                
                // File
                if ( type === 'file' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-type .file-format').css('display', 'inline-block');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.max-file-size').css('display', 'table-row');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-placeholder').css('display', 'none');

                } else {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-type .file-format').css('display', 'none');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.max-file-size').css('display', 'none');
                }
                // End
                
                if ( required == 'on' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-required input[name="required"]').prop('checked');
                } else {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-required input[name="required"]').prop('checked', false);
                }

                $('#ova_popup_field_form input[name="ova_action"]').val('edit');
                $('#ova_popup_field_form input[name="ova_old_name"]').val(name);
                $('#ova_popup_field_form input[name="position"]').val(position);
                $('#ova_type').val(type);
                $('#used_for').val(used_for);
                $('#ova_popup_field_form .ova-row-name input').val(name);
                $('#ova_popup_field_form .ova-row-label input').val(label);
                $('#ova_popup_field_form .ova-row-description textarea').val(description);
                $('#ova_popup_field_form .ova-row-placeholder input').val(placeholder);

                $('#ova_popup_field_form .ova-row-class input').val(ova_class);
                $('#ova_popup_field_form .ova-row-class-icon input').val(class_icon);
                $('#ova_popup_field_form input[name="max_file_size"]').val(max_file_size);
                $('.ova-wrap-popup-register-field').css('display', 'block');
            });

            $('#ovalg_openform').on('click', function(e) {
                var ova_count_field = $(".ova-list-register-field").data("field");

                $('#ova_popup_field_form input[name="ova_action"]').val('new');
                $('#ova_popup_field_form input[name="ova_old_name"]').val('');
                $('#ova_popup_field_form input[name="position"]').val(ova_count_field);
                $('.ova-wrap-popup-register-field').css('display', 'block');
                $('#ova_type').val('text');
                $('#used_for').val('both');
                $('.ova-wrap-popup-register-field input[name="name"]').val('');
                $('.ova-wrap-popup-register-field input[name="label"]').val('');
                $('#ova_popup_field_form .ova-row-description textarea').val('');
                $('.ova-wrap-popup-register-field input[name="placeholder"]').val('');
                // Remove old option
                $('.ova-wrap-popup-register-field .row-checkbox .el-sortable').html(`
                    <tr>
                        <td><input type="text" name="ova_checkbox_key[]" placeholder="Option Value" /></td>
                        <td><input type="text" name="ova_checkbox_text[]" placeholder="Option Text" /></td>
                        <td class="ova-box"><a href="javascript:void(0)" class="el_lg_add_checkbox btn btn-blue" title="Add new option">+</a></td>
                        <td class="ova-box"><a href="javascript:void(0)" class="el_lg_remove_checkbox btn btn-red" title="Remove option">x</a></td>
                        <td class="ova-box sort">
                            <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                        </td>
                    </tr>`);
                $('.ova-wrap-popup-register-field .row-radio .el-sortable').html(`
                    <tr>
                        <td><input type="text" name="ova_radio_key[]" placeholder="Option Value" /></td>
                        <td><input type="text" name="ova_radio_text[]" placeholder="Option Text" /></td>
                        <td class="ova-box"><a href="javascript:void(0)" class="el_lg_add_radio btn btn-blue" title="Add new option">+</a></td>
                        <td class="ova-box"><a href="javascript:void(0)" class="el_lg_remove_radio btn btn-red" title="Remove option">x</a></td>
                        <td class="ova-box sort">
                            <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                        </td>
                    </tr>`);
                $('.ova-wrap-popup-register-field .row-options .el-sortable').html(`
                    <tr>
                        <td><input type="text" name="ova_options_key[]" placeholder="Option Value" /></td>
                        <td><input type="text" name="ova_options_text[]" placeholder="Option Text" /></td>
                        <td class="ova-box"><a href="javascript:void(0)" class="ovalg_addfield btn btn-blue" title="Add new option">+</a></td>
                        <td class="ova-box"><a href="javascript:void(0)" class="ovalg_remove_row btn btn-red" title="Remove option">x</a></td>
                        <td class="ova-box sort">
                            <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                        </td>
                    </tr>`);

                $('.ova-wrap-popup-register-field input[name="class"]').val('');
                $('.ova-wrap-popup-register-field input[name="class_icon"]').val('');
                $('.ova-wrap-popup-register-field .row-options').css('display', 'none');
                $('.ova-wrap-popup-register-field .row-radio').css('display', 'none');
                $('.ova-wrap-popup-register-field tr.ova-row-placeholder').css('display', 'table-row');

                $('.ova-wrap-popup-register-field .row-checkbox').css('display', 'none');
                $('.ova-wrap-popup-register-field .ova-row-type .file-format').css('display', 'none');
                $('.ova-wrap-popup-register-field .max-file-size').css('display', 'none');
            });

            $('#ovabrw_manage_custom_checkout_field').on('change', function() {
                $('.ovabrw_product_custom_checkout_field_field').css('display', 'block');

                if ( $(this).val() == 'all' ) {
                    $('.ovabrw_product_custom_checkout_field_field').css('display', 'none');
                }
            });

            $('#ova_type').on('change', function() {
                $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-options').css('display', 'none');
                $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-radio').css('display', 'none');
                $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-checkbox').css('display', 'none');
                $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-placeholder').css('display', 'table-row');

                if ( $(this).val() == 'select' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-options').css('display', 'table-row');
                }

                if ( $(this).val() == 'radio' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-radio').css('display', 'table-row');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-placeholder').css('display', 'none');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-class-icon').css('display', 'none');
                }

                if ( $(this).val() == 'checkbox' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.row-checkbox').css('display', 'table-row');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-placeholder').css('display', 'none');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-class-icon').css('display', 'none');
                }

                // File
                if ( $(this).val() == 'file' ) {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-type .file-format').css('display', 'inline-block');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.max-file-size').css('display', 'table-row');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-placeholder').css('display', 'none');

                } else {
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.ova-row-type .file-format').css('display', 'none');
                    $('.ova-wrap-popup-register-field .ova-popup-wrapper tr.max-file-size').css('display', 'none');

                }
                // End
            });

            $('.ova-list-register-field #ovabrw_select_all_field').on('click', function(e) {
                console.log("checked!");
                var checkAll = jQuery(this).prop('checked');
                $( '.ova-list-register-field table tbody tr td.ova-checkbox input' ).prop( 'checked', checkAll );
            });

            $('#ovabrw_close_popup').on('click', function(e) {
                $('.ova-wrap-popup-register-field').css('display', 'none');
            });

            $('.ova-list-register-field .ova_remove').on('click', function(e) {
                if ( ! confirm('Are you sure?') ) {
                    e.preventDefault();
                }
            });
        },
        ova_login_setting: function() {
            var $hash = window.location.hash;
            if ( $hash != '' ) {
                if ( $(document).find('#ova_login_setting').length ) {
                    $(document).find('#ova_login_setting').attr('action','options.php'+$hash);
                }
            }
            $(document).find( "#tabs" ).on( "tabsactivate", function( event, ui ) {
                var $id = ui.newPanel[0].id;
                if ( $(document).find('#ova_login_setting').length ) {
                    $(document).find('#ova_login_setting').attr('action','options.php'+'#'+$id);
                }
            } );
        },
    };

    $(document).ready(function () {
        LG_Admin.init();
    });
})(jQuery);