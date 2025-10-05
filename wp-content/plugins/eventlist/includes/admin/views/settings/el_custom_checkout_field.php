<?php defined( 'ABSPATH' ) || exit();

    // Display Custom Checkout Fields
    $list_fields = get_option( 'ova_booking_form', array() );
    $_POST = recursive_array_replace( '\\', '', $_POST );

    $action_popup = isset( $_POST['ova_action'] ) ? sanitize_text_field( $_POST['ova_action'] ) : '';
    $name = isset( $_POST['name'] ) ? sanitize_text_field( sanitize_title( $_POST['name'] ) ) : '';

    if ( $name ) {
        $name = str_replace( '-', '_', $name );
    }

    //update popup
    if ( ! empty( $action_popup ) ) {
        if ( isset( $_POST ) && array_key_exists( 'name', $_POST ) && ! empty( $_POST['name'] ) ) {
            $list_fields[$name] = array(
                'type'          => isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '',
                'label'         => isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : '',
                'default'       => isset( $_POST['default'] ) ? sanitize_text_field( $_POST['default'] ) : '',
                'placeholder'   => isset( $_POST['placeholder'] ) ? sanitize_text_field( $_POST['placeholder'] ): '',
                'class'         => isset( $_POST['class'] ) ? sanitize_text_field( $_POST['class'] ): '',
                'required'      => isset( $_POST['required'] ) ? sanitize_text_field( $_POST['required'] ): '',
                'enabled'       => isset( $_POST['enabled'] ) ? sanitize_text_field( $_POST['enabled'] ): '',
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
            $max_file_size = $_POST['max_file_size'] ? $_POST['max_file_size'] : 10;
            $list_fields[$name]['max_file_size']    = $max_file_size;
            $list_fields[$name]['default']          = '';
            $list_fields[$name]['placeholder']      = '';
        }

        if ( isset( $_POST ) ) {
            if ( $action_popup == 'new' ) {
                update_option( 'ova_booking_form', $list_fields );
            } elseif ( $action_popup == 'edit' ) {
                $old_name = isset( $_POST['ova_old_name'] ) ? $_POST['ova_old_name'] : '';

                if ( ! empty( $old_name ) && array_key_exists( $old_name, $list_fields ) && $old_name != $name  ) {
                    unset( $list_fields[$old_name] );
                }

                if ( ! $name ) {
                    unset( $list_fields[$name] );
                }

                update_option( 'ova_booking_form', $list_fields );
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

        update_option( 'ova_booking_form', $list_fields );
    }
    
    ?>
    <div class="wrap">
        <div class="ova-list-checkout-field">
            <form method="post" id="ova_update_form" action="" enctype="multipart/form-data" >
                <input type="hidden" name="ovabrw_update_table" value="update_table" >
                <table cellspacing="0" cellpadding="10px">
                    <thead>
                        <th colspan="6">
                            <button type="button" class="button button-primary" id="ovabrw_openform">
                                + <?php esc_html_e( 'Add field', 'eventlist' ); ?>
                            </button>
                            <button class="button ova_remove" name="remove" value="Remove">
                                <?php esc_html_e('Remove', 'eventlist'); ?>
                            </button>
                            <button class="button" name="enable" value="Enable">
                                <?php esc_html_e('Enable', 'eventlist'); ?>
                            </button>
                            <button class="button" name="disable" value="Disable">
                                <?php esc_html_e('Disable', 'eventlist'); ?>
                            </button>
                        </th>
                        <tr>
                            <th class="check-column"><input type="checkbox" style="margin:0px 4px -1px -1px;" id="ovabrw_select_all_field" /></th>
                            <th class="name"><?php esc_html_e('Slug', 'eventlist'); ?></th>
                            <th class="id"><?php esc_html_e('Type', 'eventlist'); ?></th>
                            <th><?php esc_html_e('Label', 'eventlist'); ?></th>
                            <th><?php esc_html_e('Placeholder', 'eventlist'); ?></th>
                            <th class="status"><?php esc_html_e('Required', 'eventlist'); ?></th>
                            <th class="status"><?php esc_html_e('Enabled', 'eventlist'); ?></th>    
                            <th class="action"><?php esc_html_e('Edit', 'eventlist'); ?></th>   
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $list_fields ) ):
                            foreach ( $list_fields as $key => $field ):
                                $name           = $key;
                                $type           = array_key_exists( 'type', $field ) ? $field['type'] : '';
                                $label          = array_key_exists( 'label', $field ) ? $field['label'] : '';
                                $placeholder    = array_key_exists( 'placeholder', $field ) ? $field['placeholder'] : '';
                                $default        = array_key_exists( 'default', $field ) ? $field['default'] : '';
                                $class          = array_key_exists( 'class', $field ) ? $field['class'] : '';
                                $required       = array_key_exists( 'required', $field ) ? $field['required'] : '';
                                $enabled        = array_key_exists( 'enabled', $field ) ? $field['enabled'] : '';
                                $max_file_size  = array_key_exists( 'max_file_size', $field ) ? $field['max_file_size'] : 10;

                                // Select
                                $ova_options_key    = array_key_exists( 'ova_options_key', $field ) ? $field['ova_options_key'] : [];
                                $ova_options_text   = array_key_exists( 'ova_options_text', $field ) ? $field['ova_options_text'] : [];

                                // Radio
                                $ova_radio_key      = array_key_exists( 'ova_radio_key', $field ) ? $field['ova_radio_key'] : [];
                                $ova_radio_text     = array_key_exists( 'ova_radio_text', $field ) ? $field['ova_radio_text'] : [];

                                // Checkbox
                                $ova_checkbox_key      = array_key_exists( 'ova_checkbox_key', $field ) ? $field['ova_checkbox_key'] : [];
                                $ova_checkbox_text     = array_key_exists( 'ova_checkbox_text', $field ) ? $field['ova_checkbox_text'] : [];

                                $required_status    = $required ? '<span class="dashicons dashicons-yes tips" data-tip="Yes"></span>' : '-';
                                $enabled_status     = $enabled ? '<span class="dashicons dashicons-yes tips" data-tip="Yes"></span>' : '-';

                                $class_disable  = ! $enabled ? 'class="ova-disable"' : '';
                                $disable_button = ! $enabled ? 'disabled' : '';
                                $value_enabled  = ( $enabled == 'on' ) ? $name : '';

                                $data_edit = [
                                    'required'          => $required,
                                    'name'              => $name,
                                    'type'              => $type,
                                    'label'             => $label,
                                    'placeholder'       => $placeholder,
                                    'default'           => $default,
                                    'class'             => $class,
                                    'ova_options_key'   => $ova_options_key,
                                    'ova_options_text'  => $ova_options_text,
                                    'ova_radio_key'     => $ova_radio_key,
                                    'ova_radio_text'    => $ova_radio_text,
                                    'ova_checkbox_key'  => $ova_checkbox_key,
                                    'ova_checkbox_text' => $ova_checkbox_text,
                                    'max_file_size'     => $max_file_size,
                                ];

                                $data_edit = json_encode( $data_edit );
                            ?>
                            <tr <?php echo esc_attr( $class_disable ); ?>>
                                <input type="hidden" name="remove_field[]" value="">
                                <input type="hidden" name="enable_field[]" value="<?php echo esc_attr( $value_enabled ); ?>">
                                <td class="ova-checkbox">
                                    <input type="checkbox" name="select_field[]" value="<?php echo esc_attr( $name ); ?>" />
                                </td>
                                <td class="ova-name"><?php echo esc_html( $key ); ?></td>
                                <td class="ova-type"><?php echo esc_html( $type ); ?></td>
                                <td class="ova-label"><?php echo esc_html( $label ); ?></td>
                                <td class="ova-placeholder"><?php echo esc_html( $placeholder ); ?></td>
                                <td class="ova-require status"><?php echo wp_kses_post( $required_status ); ?></td>
                                <td class="ova-enable status"><?php echo wp_kses_post( $enabled_status ); ?></td>
                                <td class="ova-edit edit">
                                    <button type="button" <?php echo esc_attr( $disable_button ); ?> class="button ova-button ovabrw_edit_field_form" data-data_edit="<?php echo esc_attr( $data_edit ); ?>">
                                        <?php esc_html_e( 'Edit', 'eventlist' ) ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>           
                    </tbody>
                </table>
            </form>
        </div>
        <div class="ova-wrap-popup-ckf">
            <div id="ova_new_field_form" title="New Checkout Field" class="ova-popup-wrapper">
                <a href="javascript:void(0)" class="close_popup" id="ovabrw_close_popup">X</a>
                <?php ova_output_popup_form_fields( 'new', $list_fields ); ?>
            </div>
        </div>
    </div>
<?php 

function ova_output_popup_form_fields( $form_type, $list_fields = [] ) {
    ?>
    <form method="post" id="ova_popup_field_form" action="">
        <input type="hidden" name="ova_action" value="<?php echo esc_attr( $form_type ); ?>" />
        <input type="hidden" name="ova_old_name" value="" />
        <table width="100%">
            <tr>                
                <td colspan="2" class="err_msgs"></td>
            </tr>
            <tr class="ova-row-type">
                <td class="label"><?php esc_html_e( 'Type', 'eventlist' ); ?></td>
                <td>
                    <select name="type" id="ova_type">
                        <option value="text"><?php esc_html_e('Text', 'eventlist'); ?></option>
                        <option value="password"><?php esc_html_e('Password', 'eventlist'); ?></option>
                        <option value="email"><?php esc_html_e('Email', 'eventlist'); ?></option>
                        <option value="tel"><?php esc_html_e('Phone', 'eventlist'); ?></option>
                        <option value="textarea"><?php esc_html_e('Textarea', 'eventlist'); ?></option>
                        <option value="select"><?php esc_html_e('Select', 'eventlist'); ?></option>
                        <option value="radio"><?php esc_html_e('Radio', 'eventlist'); ?></option>
                        <option value="checkbox"><?php esc_html_e('Checkbox', 'eventlist'); ?></option>
                        <option value="file"><?php esc_html_e('File', 'eventlist'); ?></option>
                    </select>
                    <span class="file-format">
                        <?php esc_html_e( 'Formats: .jpg, .jpeg, .png, .pdf, .doc', 'eventlist' ); ?>
                    </span>
                </td>
            </tr>
            <tr class="row-options">
                <td width="30%" class="label" valign="top"><?php esc_html_e('Options', 'eventlist'); ?></td>
                <td>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                        <tbody class="el-sortable">
                            <tr>
                                <td><input type="text" name="ova_options_key[]" placeholder="Option Value" /></td>
                                <td><input type="text" name="ova_options_text[]" placeholder="Option Text" /></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="ovabrw_addfield btn btn-blue" title="Add new option">+</a></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="ovabrw_remove_row btn btn-red" title="Remove option">x</a></td>
                                <td class="ova-box sort">
                                    <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>                
                </td>
            </tr>
            <tr class="row-radio">
                <td width="30%" class="label" valign="top"><?php esc_html_e('Options', 'eventlist'); ?></td>
                <td>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                        <tbody class="el-sortable">
                            <tr>
                                <td><input type="text" name="ova_radio_key[]" placeholder="Option Value" /></td>
                                <td><input type="text" name="ova_radio_text[]" placeholder="Option Text" /></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="el_add_radio btn btn-blue" title="Add new option">+</a></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="el_remove_radio btn btn-red" title="Remove option">x</a></td>
                                <td class="ova-box sort">
                                    <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>                
                </td>
            </tr>
            <tr class="row-checkbox">
                <td width="30%" class="label" valign="top"><?php esc_html_e('Options', 'eventlist'); ?></td>
                <td>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                        <tbody class="el-sortable">
                            <tr>
                                <td><input type="text" name="ova_checkbox_key[]" placeholder="Option Value" /></td>
                                <td><input type="text" name="ova_checkbox_text[]" placeholder="Option Text" /></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="el_add_checkbox btn btn-blue" title="Add new option">+</a></td>
                                <td class="ova-box"><a href="javascript:void(0)" class="el_remove_checkbox btn btn-red" title="Remove option">x</a></td>
                                <td class="ova-box sort">
                                    <span class="dashicons dashicons-menu" title="Drag & Drop"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>                
                </td>
            </tr>
            <tr class="max-file-size">
                <td class="label"><?php esc_html_e( 'Maximum file size', 'eventlist' ); ?></td>
                <td>
                    <input type="text" name="max_file_size" value="10">
                    <span><?php esc_html_e( 'Default: 10MB', 'eventlist' ); ?></span>
                </td>
            </tr>
            <tr class="ova-row-name">
                <td class="label"><?php esc_html_e( 'Slug', 'eventlist' ); ?></td>
                <td>
                    <input type="text" name="name" value="">
                    <span><?php esc_html_e( 'Unique, only lowercase, not space', 'eventlist' ); ?></span>
                </td>
            </tr>
            <tr class="ova-row-label">
                <td class="label"><?php esc_html_e( 'Label', 'eventlist' ); ?></td>
                <td>
                    <input type="text" name="label" value="" >
                </td>
            </tr>
            <tr class="ova-row-placeholder">
                <td class="label"><?php esc_html_e( 'Placeholder', 'eventlist' ); ?></td>
                <td>
                    <input type="text" name="placeholder" value="" >
                </td>
            </tr>
            <tr class="ova-row-default">
                <td class="label"><?php esc_html_e( 'Default value', 'eventlist' ); ?></td>
                <td>
                    <input type="text" name="default" value="" >
                </td>
            </tr>
            <tr class="ova-row-class">
                <td class="label"><?php esc_html_e( 'Class', 'eventlist' ); ?></td>
                <td>
                    <input type="text" name="class" value="" >
                </td>
            </tr>
            <tr class="row-required">
                <td>&nbsp;</td>
                <td class="check-box">
                    <input id="ova_required" type="checkbox" name="required" checked>
                    <label for="ova_required"><?php esc_html_e( 'Required', 'eventlist' ); ?></label>
                    <br/>
                    <input id="ova_enable" type="checkbox" name="enabled" checked>
                    <label for="ova_enable"><?php esc_html_e( 'Enable', 'eventlist' ); ?></label>
                    <br/>
                </td>                     
                <td class="label"></td>
            </tr>
        </table>
        <button type='submit' class="button button-primary"><?php esc_html_e( 'save', 'eventlist' ); ?></button>
    </form>
    <?php
}