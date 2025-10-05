<?php defined( 'ABSPATH' ) || exit();

    // Display Custom Checkout Fields
    $list_fields = get_option( 'ova_register_form', array() );
    $_POST = recursive_array_replace( '\\', '', $_POST );

    $action_popup = isset( $_POST['ova_action'] ) ? sanitize_text_field( $_POST['ova_action'] ) : '';
    $name = isset( $_POST['name'] ) ? sanitize_text_field( sanitize_title( $_POST['name'] ) ) : '';

    if ( $name ) {
        $name = str_replace( '-', '_', $name );

        // Check name exist
        if ( $action_popup == "new" && $list_fields && array_key_exists( $name, $list_fields ) ) {
            $name = $name . '_' . current_time( 'timestamp' );
        }
    }

    //update popup
    if ( ! empty( $action_popup ) ) {
        
        if ( isset( $_POST ) && array_key_exists( 'name', $_POST ) && ! empty( $_POST['name'] ) ) {
            $list_fields[$name] = array(
                'type'          => isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '',
                'label'         => isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : '',
                'description'   => isset( $_POST['description'] ) ? sanitize_text_field( $_POST['description'] ) : '',
                'placeholder'   => isset( $_POST['placeholder'] ) ? sanitize_text_field( $_POST['placeholder'] ): '',
                'class'         => isset( $_POST['class'] ) ? sanitize_text_field( $_POST['class'] ): '',
                'class_icon'    => isset( $_POST['class_icon'] ) ? sanitize_text_field( $_POST['class_icon'] ): '',
                'used_for'      => isset( $_POST['used_for'] ) ? sanitize_text_field( $_POST['used_for'] ): '',
                'required'      => isset( $_POST['required'] ) ? sanitize_text_field( $_POST['required'] ): '',
                'enabled'       => isset( $_POST['enabled'] ) ? sanitize_text_field( $_POST['enabled'] ): '',
                'position'      => isset( $_POST['position'] ) ? sanitize_text_field( $_POST['position'] ): '',
                'show_in_email' => isset( $_POST['show_in_email'] ) ? sanitize_text_field( $_POST['show_in_email'] ): '',
                'show_in_order' => isset( $_POST['show_in_order'] ) ? sanitize_text_field( $_POST['show_in_order'] ): '',
            );
        }

        // Select
        if ( isset( $_POST['type'] ) && $_POST['type'] == 'select' ) {
            if ( ! empty( $_POST['ova_options_key'] ) && is_array( $_POST['ova_options_key'] ) ) {
                $new_keys = [];

                foreach ( $_POST['ova_options_key'] as $key ) {
                    // Check key empty, exists
                    if ( $key != '' && ! in_array( $key, $new_keys ) ) {
                        array_push( $new_keys, sanitize_text_field( sanitize_title( $key ) ) );
                    }
                }
                $_POST['ova_options_key'] = $new_keys;
            }

            $list_fields[$name]['ova_options_key']  = $_POST['ova_options_key'];
            $list_fields[$name]['ova_options_text'] = $_POST['ova_options_text'];
        }

        // Radio
        if ( isset( $_POST['type'] ) && $_POST['type'] == 'radio' ) {
            if ( ! empty( $_POST['ova_radio_key'] ) && is_array( $_POST['ova_radio_key'] ) ) {
                $new_keys = [];

                foreach ( $_POST['ova_radio_key'] as $key ) {
                    // Check key empty, exists
                    if ( $key != '' && ! in_array( $key, $new_keys ) ) {
                        array_push( $new_keys, sanitize_text_field( sanitize_title( $key ) ) );
                    }
                    
                }

                $_POST['ova_radio_key'] = $new_keys;
            }

            $list_fields[$name]['ova_radio_key']  = $_POST['ova_radio_key'];
            $list_fields[$name]['ova_radio_text'] = $_POST['ova_radio_text'];
            $list_fields[$name]['placeholder']      = '';
        }

        // Checkbox
        if ( isset( $_POST['type'] ) && $_POST['type'] == 'checkbox' ) {
            if ( ! empty( $_POST['ova_checkbox_key'] ) && is_array( $_POST['ova_checkbox_key'] ) ) {
                $new_keys = [];

                foreach ( $_POST['ova_checkbox_key'] as $key ) {
                    // Check key empty, exists
                    if ( $key != '' && ! in_array( $key, $new_keys ) ) {
                        array_push( $new_keys, sanitize_text_field( sanitize_title( $key ) ) );
                    }
                }

                $_POST['ova_checkbox_key'] = $new_keys;
            }

            $list_fields[$name]['ova_checkbox_key']  = $_POST['ova_checkbox_key'];
            $list_fields[$name]['ova_checkbox_text'] = $_POST['ova_checkbox_text'];
            $list_fields[$name]['placeholder']      = '';
        }

        // Checkbox
        if ( isset( $_POST['type'] ) && $_POST['type'] == 'file' ) {
            $max_file_size = $_POST['max_file_size'] ? sanitize_text_field( $_POST['max_file_size'] ) : 10;
            $list_fields[$name]['max_file_size']    = is_numeric( $max_file_size ) ? intval( $max_file_size ) : 10;
            $list_fields[$name]['default']          = '';
            $list_fields[$name]['placeholder']      = '';
        }

        if ( isset( $_POST ) ) {
            if ( $action_popup == 'new' ) {
                update_option( 'ova_register_form', $list_fields );
            } elseif ( $action_popup == 'edit' ) {
                $old_name = isset( $_POST['ova_old_name'] ) ? $_POST['ova_old_name'] : '';

                if ( ! empty( $old_name ) && array_key_exists( $old_name, $list_fields ) && $old_name != $name  ) {
                    unset( $list_fields[$old_name] );
                }

                if ( ! $name ) {
                    unset( $list_fields[$name] );
                }

                update_option( 'ova_register_form', $list_fields );
            }
        }
    }
    //end popup

    $action_update = isset( $_POST['ovabrw_update_table'] ) ? sanitize_text_field( $_POST['ovabrw_update_table'] ) : '';

    if ( $action_update === 'update_table' ) {
        if ( isset( $_POST['remove'] ) && $_POST['remove'] == 'Remove' ) {
            $select_field = isset( $_POST['select_field'] ) ? $_POST['select_field'] : [];

            if ( is_array( $select_field ) && ! empty( $select_field ) ) {
                foreach ( $select_field as $field ) {
                    if ( array_key_exists( $field, $list_fields ) ) {
                        unset( $list_fields[$field] );
                    }
                }
            }
        }

        if ( isset( $_POST['enable'] ) && $_POST['enable'] == 'Enable' ) {
            $select_field = isset( $_POST['select_field'] ) ? $_POST['select_field'] : [];

            if ( is_array( $select_field ) && ! empty( $select_field ) ) {
                foreach ( $select_field as $field ) {
                    if ( ! empty( $field ) && array_key_exists( $field, $list_fields ) ) {
                        $list_fields[$field]['enabled'] = 'on';
                    }
                }
            }
        }

        if ( isset( $_POST['disable'] ) && $_POST['disable'] == 'Disable' ) {
            $select_field = isset( $_POST['select_field'] ) ? $_POST['select_field'] : [];

            if ( is_array( $select_field ) && ! empty( $select_field ) ) {
                foreach ( $select_field as $field ) {
                    if ( ! empty( $field ) && array_key_exists( $field, $list_fields ) ) {
                        $list_fields[$field]['enabled'] = '';
                    }
                }
            }
        }

        update_option( 'ova_register_form', $list_fields );
    }
    
    $count_field = count( $list_fields );
    ?>
    <div class="wrap">

        <h1 class="wp-heading-inline">
            <?php esc_html_e( 'Custom Register Field', 'ova-login' ); ?>
        </h1>
        <hr class="wp-header-end">

        <div class="ova-list-register-field" data-field="<?php echo esc_attr( $count_field ); ?>">
            <div class="wrap_loader">
                <span class="loader"></span>
            </div>
            <form method="post" id="ova_update_form" action="" enctype="multipart/form-data" >
                <input type="hidden" name="ovabrw_update_table" value="update_table" >
                <table cellspacing="0" cellpadding="10px">
                    <thead>
                        <th colspan="6">
                            <button type="button" class="button button-primary" id="ovalg_openform">
                                + <?php esc_html_e( 'Add field', 'ova-login' ); ?>
                            </button>
                            <button class="button ova_remove" name="remove" value="Remove">
                                <?php esc_html_e('Remove', 'ova-login'); ?>
                            </button>
                            <button class="button" name="enable" value="Enable">
                                <?php esc_html_e('Enable', 'ova-login'); ?>
                            </button>
                            <button class="button" name="disable" value="Disable">
                                <?php esc_html_e('Disable', 'ova-login'); ?>
                            </button>
                        </th>
                        <tr>
                            <th class="check-column"><input type="checkbox" style="margin:0px 4px -1px -1px;" id="ovabrw_select_all_field" /></th>
                            <th class="name"><?php esc_html_e('Slug', 'ova-login'); ?></th>
                            <th class="id"><?php esc_html_e('Type', 'ova-login'); ?></th>
                            <th><?php esc_html_e('Label', 'ova-login'); ?></th>
                            <th><?php esc_html_e('Placeholder', 'ova-login'); ?></th>
                            <th><?php esc_html_e('Used for', 'ova-login'); ?></th>
                            <th class="status"><?php esc_html_e('Required', 'ova-login'); ?></th>
                            <th class="status"><?php esc_html_e('Enabled', 'ova-login'); ?></th>    
                            <th class="action"><?php esc_html_e('Edit', 'ova-login'); ?></th>   
                        </tr>
                    </thead>
                    <tbody class="ovalg-sortable">
                        <?php ovalg_get_list_fields( $list_fields ); ?>          
                    </tbody>
                </table>
            </form>
        </div>
        <div class="ova-wrap-popup-register-field">
            <div id="ova_new_field_form" title="New Checkout Field" class="ova-popup-wrapper">
                <a href="javascript:void(0)" class="close_popup" id="ovabrw_close_popup">X</a>
                <?php ova_output_popup_form_fields( 'new', $list_fields ); ?>
            </div>
        </div>
    </div>
<?php 

function ova_output_popup_form_fields( $form_type, $list_fields = [] ) {
    $position = count( $list_fields );
    ?>
    <form method="post" id="ova_popup_field_form" action="">
        <input type="hidden" name="ova_action" value="<?php echo $form_type; ?>" />
        <input type="hidden" name="ova_old_name" value="" />
        <input type="hidden" name="position" value="<?php echo esc_attr( $position ); ?>" />
        <table width="100%">
            <tr>                
                <td colspan="2" class="err_msgs"></td>
            </tr>
            <tr class="ova-row-type">
                <td class="label"><?php esc_html_e( 'Type', 'ova-login' ); ?></td>
                <td>
                    <select name="type" id="ova_type">
                        <option value="text"><?php esc_html_e('Text', 'ova-login'); ?></option>
                        <option value="password"><?php esc_html_e('Password', 'ova-login'); ?></option>
                        <option value="email"><?php esc_html_e('Email', 'ova-login'); ?></option>
                        <option value="tel"><?php esc_html_e('Phone', 'ova-login'); ?></option>
                        <option value="textarea"><?php esc_html_e('Textarea', 'ova-login'); ?></option>
                        <option value="select"><?php esc_html_e('Select', 'ova-login'); ?></option>
                        <option value="radio"><?php esc_html_e('Radio', 'ova-login'); ?></option>
                        <option value="checkbox"><?php esc_html_e('Checkbox', 'ova-login'); ?></option>
                        <option value="file"><?php esc_html_e('File', 'ova-login'); ?></option>
                    </select>
                    <span class="file-format">
                        <?php esc_html_e( 'Formats: .jpg, .jpeg, .png, .pdf, .doc', 'ova-login' ); ?>
                    </span>
                </td>
            </tr>
            <tr class="row-options">
                <td width="30%" class="label" valign="top"><?php esc_html_e('Options', 'ova-login'); ?></td>
                <td>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                        <tbody class="el-sortable">
                            <tr>
                                <td><input type="text" name="ova_options_key[]" placeholder="Option Value" /></td>
                                <td><input type="text" name="ova_options_text[]" placeholder="Option Text" /></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="ovalg_addfield btn btn-blue" title="Add new option">+</a></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="ovalg_remove_row btn btn-red" title="Remove option">x</a></td>
                                <td class="ova-box sort">
                                    <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>                
                </td>
            </tr>
            <tr class="row-radio">
                <td width="30%" class="label" valign="top"><?php esc_html_e('Options', 'ova-login'); ?></td>
                <td>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                        <tbody class="el-sortable">
                            <tr>
                                <td><input type="text" name="ova_radio_key[]" placeholder="Option Value" /></td>
                                <td><input type="text" name="ova_radio_text[]" placeholder="Option Text" /></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="el_lg_add_radio btn btn-blue" title="Add new option">+</a></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="el_lg_remove_radio btn btn-red" title="Remove option">x</a></td>
                                <td class="ova-box sort">
                                    <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>                
                </td>
            </tr>
            <tr class="row-checkbox">
                <td width="30%" class="label" valign="top"><?php esc_html_e('Options', 'ova-login'); ?></td>
                <td>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                        <tbody class="el-sortable">
                            <tr>
                                <td><input type="text" name="ova_checkbox_key[]" placeholder="Option Value" /></td>
                                <td><input type="text" name="ova_checkbox_text[]" placeholder="Option Text" /></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="el_lg_add_checkbox btn btn-blue" title="Add new option">+</a></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="el_lg_remove_checkbox btn btn-red" title="Remove option">x</a></td>
                                <td class="ova-box sort">
                                    <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>                
                </td>
            </tr>
            <tr class="max-file-size">
                <td class="label"><?php esc_html_e( 'Maximum file size', 'ova-login' ); ?></td>
                <td>
                    <input type="text" name="max_file_size" value="10">
                    <span><?php esc_html_e( 'Default: 10MB' ); ?></span>
                </td>
            </tr>
            <tr class="ova-row-name">
                <td class="label"><?php esc_html_e( 'Slug', 'ova-login' ); ?></td>
                <td>
                    <input type="text" name="name" value="">
                    <span><?php esc_html_e( 'Unique, only lowercase, not space' ); ?></span>
                </td>
            </tr>
            <tr class="ova-row-label">
                <td class="label"><?php esc_html_e( 'Label', 'ova-login' ); ?></td>
                <td>
                    <input type="text" name="label" value="" >
                </td>
            </tr>
            <tr class="ova-row-description">
                <td class="label"><?php esc_html_e( 'Description', 'ova-login' ); ?></td>
                <td>
                    <textarea name="description" style="width: 100%;" rows="5"></textarea>
                </td>
            </tr>
            <tr class="ova-row-placeholder">
                <td class="label"><?php esc_html_e( 'Placeholder', 'ova-login' ); ?></td>
                <td>
                    <input type="text" name="placeholder" value="" >
                </td>
            </tr>
            <tr class="ova-row-class">
                <td class="label"><?php esc_html_e( 'Class', 'ova-login' ); ?></td>
                <td>
                    <input type="text" name="class" value="" >
                </td>
            </tr>
            <tr class="ova-row-class-icon">
                <td class="label"><?php esc_html_e( 'Class Icon', 'ova-login' ); ?></td>
                <td>
                    <input type="text" name="class_icon" value="" >
                </td>
            </tr>
            <tr class="ova-row-type">
                <td class="label"><?php esc_html_e( 'Used for', 'ova-login' ); ?></td>
                <td>
                    <select name="used_for" id="used_for">
                        <option value="both"><?php esc_html_e('Both', 'ova-login'); ?></option>
                        <option value="vendor"><?php esc_html_e('Vendor', 'ova-login'); ?></option>
                        <option value="user"><?php esc_html_e('User', 'ova-login'); ?></option>
                    </select>
                </td>
            </tr>
            <tr class="row-required">
                <td>&nbsp;</td>
                <td class="check-box">
                    <input id="ova_required" type="checkbox" name="required" checked>
                    <label for="ova_required"><?php esc_html_e( 'Required', 'ova-login' ); ?></label>
                    <br/>
                    <input id="ova_enable" type="checkbox" name="enabled" checked>
                    <label for="ova_enable"><?php esc_html_e( 'Enable', 'ova-login' ); ?></label>
                    <br/>
                </td>                     
                <td class="label"></td>
            </tr>
        </table>
        <button type='submit' class="button button-primary"><?php esc_html_e( 'save', 'ova-login' ); ?></button>
    </form>
    <?php
}